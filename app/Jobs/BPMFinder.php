<?php

namespace App\Jobs;

use App\Http\UtilityClasses\FileService;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Filters\Audio\AudioFilters;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\FFProbe;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class BPMFinder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private $fileInfo, private $guestId, private $isPrivate)
    {
    }

    public function handle()
    {
        $startTime = now();

        $name = pathinfo($this->fileInfo['path'], PATHINFO_FILENAME);
        $ext = pathinfo($this->fileInfo['path'], PATHINFO_EXTENSION);
        $outputFilePath = $name . 'temp.' . 'wav';
        $duration = 0;

        //convert to wav
        try {
            FFMpeg::fromDisk('')
                ->open($name . '.' . $ext)
                ->export()
                ->toDisk('')
                ->inFormat(new \FFMpeg\Format\Audio\Wav)
                ->save($outputFilePath);

            $duration = FFMpeg::open($outputFilePath)->getDurationInSeconds();

        } catch (\Exception $e) {
            error_log($e);
            FileService::errorNotify("ERROR#1", $this->isPrivate, $this->guestId);
            return;
        }
        $split_seconds = 5;
        $pieces = ceil($duration / $split_seconds);

        //split into parts
        if ($pieces) {
            for ($piece = 0; $piece < $pieces + 1; $piece++) {
                $start = $piece * $split_seconds;
                $outputPartName = $name . "-part{$piece}.wav";

                try {
                    FFMpeg::open($outputFilePath)
                        ->export()
                        ->toDisk('')
                        ->addFilter('-vn')
                        ->addFilter('-acodec', 'pcm_s16le')
                        ->addFilter('-ss', $start)
                        ->addFilter('-t', $split_seconds)
                        ->save($outputPartName);

                } catch (\Exception $e) {
                    error_log($e);
                    FileService::errorNotify("ERROR#2", $this->isPrivate, $this->guestId);
                    return;
                }

                //calc parts
                try {
                    $cmd = 'soundstretch "' . Storage::Path($outputPartName) . '" -bpm 2>&1';
                    exec($cmd, $piece_bpm);
                    foreach ($piece_bpm as $line) {
                        if (strpos($line, "Detected BPM rate") !== false) {
                            $line = explode(" ", $line);
                            $piece_bpm = round($line[3]);
                            break;
                        }
                    }
                    $pieces_bpm[] = round(intval($piece_bpm));

                } catch (\Exception $e) {
                    error_log($e);
                    FileService::errorNotify("ERROR#3", $this->isPrivate, $this->guestId);
                    return;
                } finally {
                    if (Storage::Path($outputPartName)) {
                        unlink(Storage::Path($outputPartName));
                    }
                }
            }
        }

        //clear outliers
        $filtered_bpm = [];
        foreach ($pieces_bpm as $piece_bpm) {
            $bpm = intval($piece_bpm);

            if ($bpm >= 10 && $bpm <= 300) {
                $filtered_bpm[] = round($bpm);
            }
        }

        //group and count bpm
        $thresholdLeniency = 10;
        $bpmCounted = array();
        foreach ($filtered_bpm as $bpm) {
            $found = false;
            foreach ($bpmCounted as $key => $count) {
                if (abs($bpm - $key) <= $thresholdLeniency) {
                    $bpmCounted[$key]++;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $bpmCounted[$bpm] = 1;
            }
        }
        arsort($bpmCounted);
        exec('soundstretch "' . Storage::Path($outputFilePath) . '" -bpm 2>&1', $bpm);
        foreach ($bpm as $line) {
            if (strpos($line, "Detected BPM rate") !== false) {
                $line = explode(" ", $line);
                $bpm = round($line[3]);
                break;
            }
        }

        //format and send to user formated data
        $bpmArray = array(
            array('BPM' => $bpm, 'Count' => 1),
        );
        unlink(Storage::Path($outputFilePath));
        foreach ($bpmCounted as $bpmValue => $count) {
            $bpmArray[] = array('BPM' => $bpmValue, 'Count' => $count);
        }

        $res = FileService::createAndNotify($this->fileInfo, $this->isPrivate, $this->guestId, true, $bpmArray);
        //notify success
        $endTime = now();
        $executionTime = $endTime->diffInMilliseconds($startTime);
        if(!$res) {
    Storage::delete($this->fileInfo['path']);
    return;
}
FileService::logSuccess('BPM Finder', $this->guestId, $executionTime, $this->isPrivate);
    }
}
