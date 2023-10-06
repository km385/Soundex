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

    public function __construct(private $path, private $newCoverPath, private $guestId, private $isPrivate)
    {
    }
    // //helper functions
    // function averageLocalEnergy($energyBuffer, $blocksPerSecond)
    // {
    //     $energySum = 0.0;
    //     foreach ($energyBuffer as $e) {
    //         $energySum += $e;
    //     }

    //     return $energySum / $blocksPerSecond;
    // }

    // function energyVariance($energyBuffer, $average, $blocksPerSecond)
    // {
    //     $energyVariance = 0.0;

    //     foreach ($energyBuffer as $e) {
    //         $energyVariance += ($e - $average) * ($e - $average);
    //     }

    //     return $energyVariance / $blocksPerSecond;
    // }

    // function C($variance)
    // {
    //     return (1.5142857) + (-0.0000075) * $variance;
    // }

    /*
    //main operation function
    function calculateBPM($audioFile, $duration)
    {
        $samplesPerBlock = 1024;
        $numChannels = 1;
        $sampleRate = 48000;

        try {
            $values = FFProbe::create()
                ->getFFProbeDriver()
                ->command([
                    '-v',
                    'quiet',
                    '-print_format',
                    'json',
                    '-show_streams',
                    Storage::path($audioFile)
                ]);

            $json = json_decode($values, true);

            if (isset($json['streams'][0]['channels'])) {
                $numChannels = $json['streams'][0]['channels'];
            }

            if (isset($json['streams'][0]['sample_rate'])) {
                $sampleRate = $json['streams'][0]['sample_rate'];
            }
        } catch (\Exception $e) {
            error_log('ERROR' . $e);
            throw $e;
        }

        $blocksPerSecond = (int) ($sampleRate / $samplesPerBlock);
        $blockBuffer = array_fill(0, $numChannels * $samplesPerBlock, 0.0);
        $energyBufferPointer = 0;
        $blockCounter = 0;
        $beatCounter = 0;
        $bpm = 1.0;
        $instantBpm = [];

        $localBlockCounter = 0;
        $localPeakCounter = 0;
        $localBeatCounter = 0;
        $readFrames = $this->audioFileReadNormalizedFrames($blockBuffer, $samplesPerBlock);

        while ($readFrames != 0) {
            if ($readFrames == $samplesPerBlock) {
                $energy = 0.0;
                for ($i = 0; $i < $samplesPerBlock; $i++) {
                    $energy += $blockBuffer[2 * $i] * $blockBuffer[2 * $i] +
                        $blockBuffer[2 * $i + 1] * $blockBuffer[2 * $i + 1];
                }
                $energyBuffer[$energyBufferPointer] = $energy;
                $energyBufferPointer = ($energyBufferPointer + 1) % $blocksPerSecond;
                $blockCounter++;
                $localBlockCounter++;

                if ($blockCounter > $blocksPerSecond) {
                    $average = $this->averageLocalEnergy();
                    $variance = $this->energyVariance($average);

                    $Cparameter = $this->C($variance);
                    $soil = $Cparameter * $average;

                    if ($energy > $soil) {
                        $localPeakCounter++;
                        if ($localPeakCounter == 4) {
                            $localPeakCounter = 0;
                            $localBeatCounter++;
                            $beatCounter++;
                        }
                    } else {
                        $localPeakCounter = 0;
                    }

                    if ($localBlockCounter > $sampleRate * 5 / $samplesPerBlock) {
                        $beatsPerMinute = ($localBeatCounter * $sampleRate * 60.0) / ($localBlockCounter * $samplesPerBlock);

                        $instantBpm[] = $beatsPerMinute;

                        $localBeatCounter = 0;
                        $localBlockCounter = 0;
                    }
                }
            }
            $readFrames = $this->audioFileReadNormalizedFrames($blockBuffer, $samplesPerBlock);
        }
        $bpm = ($beatCounter * $sampleRate * 60.0) / ($blockCounter * $samplesPerBlock);

        return $bpm;
    }
    */
    /*
        public function handle()
        {
            $name = pathinfo($this->path, PATHINFO_FILENAME);
            $ext = pathinfo($this->path, PATHINFO_EXTENSION);
            $outputFilePath = $name . 'temp.' . $ext;
            try {
                FFMpeg::fromDisk('')
                    ->open($this->path)
                    ->export()
                    ->inFormat(new \FFMpeg\Format\Audio\Wav)
                    ->save($outputFilePath);

                $sec = 16;
                $bpm = $this->calculateBPM($outputFilePath,$sec);
            } catch (\Exception $e) {
                FileService::errorNotify(substr("ERROR" . $e, 0, 4000), $this->isPrivate, $this->guestId);
                return;
            }
            Storage::delete($outputFilePath);
            FileService::bpmNotify($bpm, $this->isPrivate, $this->guestId);
        }
    */

    //main operation function


    public function handle()
    {
        $name = pathinfo($this->path, PATHINFO_FILENAME);
        $outputFilePath = $name . 'temp.' . 'wav';
        try {
            FFMpeg::fromDisk('')
                ->open($this->path)
                ->export()
                ->inFormat(new \FFMpeg\Format\Audio\Wav)
                ->save($outputFilePath);

            $duration = FFMpeg::open($outputFilePath)->getDurationInSeconds();

        } catch (\Exception $e) {
            FileService::errorNotify(substr("ERROR#1" . $e, 0, 4000), $this->isPrivate, $this->guestId);
            throw $e;
        }

        $split_seconds = 16;
        $pieces = ceil($duration / $split_seconds);

        if ($pieces) {
            for ($piece = 0; $piece < $pieces; $piece++) {
                $startTime = $piece * $split_seconds;
                $outputPartName = $name . "-part{$piece}.wav";
                //ffmpeg -y -i "" -vn -c:a pcm_s16le -ar 44100 -ac 2 -ss 0 -t 16 "FoaSriDkV3ZkfJHQpy8EShGbdXtE9XOfRELb8Pq4-part0.wav" -threads 12

                try {
                    FFMpeg::open($this->path)
                        ->export()
                        ->toDisk('')
                        ->addFilter('-vn')
                        ->addFilter('-acodec', 'pcm_s16le') // Set audio codec to pcm_s16le
                        ->addFilter('-ss', $startTime)
                        ->addFilter('-t', $split_seconds)
                        ->save($outputPartName);

                } catch (\Exception $e) {
                    FileService::errorNotify(substr("ERROR#2" . $e, 0, 4000), $this->isPrivate, $this->guestId);
                    throw $e;
                }

                try {
                    if (file_exists(Storage::Path($outputPartName))) {
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
                    }
                } catch (\Exception $e) {
                    error_log('ERROR#3' . $e);
                    throw $e;
                } finally {
                    if (Storage::Path($outputPartName)) {
                        unlink(Storage::Path($outputPartName));
                    }
                }
            }
        }
        $thresholdLeniency = 5;
        $bpmCounted = array();

        foreach ($pieces_bpm as $bpm) {
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


        $bpm = "";
        // For Later if list of Bpm values will be needed
        // foreach ($bpmCounted as $bpmValue => $count) {
        //     $bpm .= "BPM: $bpmValue, Count: $count |||";
        // }
            
        $bpm = array_key_first($bpmCounted);
        unlink(Storage::Path($outputFilePath));
        FileService::bpmNotify($bpm, $this->isPrivate, $this->guestId);
    }
}