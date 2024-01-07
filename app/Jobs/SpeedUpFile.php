<?php

namespace App\Jobs;

use App\Http\UtilityClasses\FileService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\FFProbe;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class SpeedUpFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $fileInfo, private $pitch, private $speed, private $isPrivate, private $userId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = now();

        try {
            $currentCoverPath = FileService::extractCover($this->fileInfo['path']);
        } catch (\Exception $e) {
            FileService::errorNotify("ERROR", $this->isPrivate, $this->userId);
            return;
        }

//        ffprobe -i .\song.mp3 -show_entries stream=sample_rate -of default=noprint_wrappers=1:nokey=1 -hide_banner -loglevel quiet
        $sample_rate = null;
        try{

            $values = FFProbe::create()
                ->getFFProbeDriver()
                ->command(['-v', 'quiet',
                    '-print_format', 'json',
                    '-show_streams', Storage::path($this->fileInfo['path'])]);

            $json = json_decode($values, true);
            $sample_rate = $json['streams'][0]['sample_rate'];
        } catch (\Exception $e){
            error_log($e);
        }

        if(!$sample_rate){
            error_log('default sample_rate');
            $sample_rate = 48000;
        }
        error_log('sample rate obtained');

        try {
            FFMpeg::fromDisk('')
                ->open($this->fileInfo['path'])
                ->export()
                ->addFilter('-filter:a',
                    "asetrate=".$sample_rate*$this->pitch
                    .",aresample=".$sample_rate
                    .",atempo=".(1/$this->pitch)
                    .",atempo=".$this->speed)
                ->save(pathinfo($this->fileInfo['path'], PATHINFO_FILENAME).'temp.mp3');
        } catch (\Exception $e){
            Storage::delete($this->fileInfo['path']);
            error_log($e);
            FileService::errorNotify("ERROR", $this->isPrivate, $this->userId);
            return;
        }
        error_log('file sped up');

        FileService::addCover(pathinfo($this->fileInfo['path'], PATHINFO_FILENAME).'temp.mp3', $currentCoverPath);
        Storage::delete($currentCoverPath);


        Storage::delete($this->fileInfo['path']);
        Storage::move(pathinfo($this->fileInfo['path'], PATHINFO_FILENAME).'temp.mp3', $this->fileInfo['path']);


        FileService::createAndNotify($this->fileInfo, $this->isPrivate, $this->userId);

        $endTime = now();
        $executionTime = $endTime->diffInMilliseconds($startTime);
        FileService::logSuccess('SpeedUp File', $this->userId, $executionTime, $this->isPrivate);

    }
}
