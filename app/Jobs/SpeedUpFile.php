<?php

namespace App\Jobs;

use App\CustomUtilityClasses\FileService;
use App\Events\FileReadyToDownload;
use App\Events\PrivateFileReadyToDownload;
use App\Models\TemporaryFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\FFProbe;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class SpeedUpFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $path, private $tempo, private $speed, private $isPrivate, private $userId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

//        ffprobe -i .\song.mp3 -show_entries stream=sample_rate -of default=noprint_wrappers=1:nokey=1 -hide_banner -loglevel quiet
        try{

            $values = FFProbe::create()
                ->getFFProbeDriver()
                ->command(['-v', 'quiet',
                    '-print_format', 'json',
                    '-show_streams',Storage::path($this->path)]);

            $json = json_decode($values, true);
            $sample_rate = $json['streams'][0]['sample_rate'];
        } catch (\Exception $e){
            error_log($e);
            return;
        }

        if(!$sample_rate){
            error_log('default sample_rate');
            $sample_rate = 48000;
        }
        error_log('sample rate obtained');
        // ffmpeg.exe -i .\song.mp3 -filter:a "atempo=1.06,asetrate=48000*1.2" speed.mp3
        try {
            FFMpeg::fromDisk('')
                ->open($this->path)
                ->export()
                ->addFilter('-filter:a', "atempo=".$this->tempo.",asetrate=".$sample_rate."*".$this->speed)
                ->save(pathinfo($this->path, PATHINFO_FILENAME).'temp.mp3');
        } catch (\Exception $e){
            Storage::delete($this->path);
            error_log($e);
            error_log('error');
        }
        error_log('file sped up');

        Storage::delete($this->path);
        Storage::move(pathinfo($this->path, PATHINFO_FILENAME).'temp.mp3', $this->path);


        FileService::createAndNotify($this->path, $this->isPrivate, $this->userId);

    }
}
