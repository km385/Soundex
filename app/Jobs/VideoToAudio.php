<?php

namespace App\Jobs;

use App\Http\UtilityClasses\FileService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class VideoToAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $fileInfo, private $guestId, private $isPrivate)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = now();

        $name = pathinfo($this->fileInfo['path'], PATHINFO_FILENAME);
        try {
            FFMpeg::fromDisk('')
                ->open($this->fileInfo['path'])
                ->export()
                ->toDisk('')
                ->addFilter('-vn')
                ->save($name.'temp.mp3');
        } catch (\Exception $e) {
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }

        Storage::delete($this->fileInfo['path']);
        Storage::move($name.'temp.mp3', $name.'.mp3');
        $this->fileInfo['path'] = $name.'.mp3';

        FileService::createAndNotify($this->fileInfo, $this->isPrivate, $this->guestId);

        $endTime = now();
        $executionTime = $endTime->diffInMilliseconds($startTime);
        FileService::logSuccess('Video To Audio', $this->guestId, $executionTime, $this->isPrivate);


    }
}
