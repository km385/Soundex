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

class ChangeVolume implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $fileInfo, private $volume, private $guestId, private $isPrivate, private $scheduleFileDeletion = true)
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
        $ext = pathinfo($this->fileInfo['path'], PATHINFO_EXTENSION);
        try {
            $coverPath = FileService::extractCover($this->fileInfo['path']);
        } catch (\Exception $e) {
            // TODO: determine if cutter/speedup need to stop if error while extracting a cover
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }


        try{
            FFMpeg::fromDisk('')
                ->open($name.'.'.$ext)
                ->export()
                ->toDisk('')
                ->addFilter('-filter:a', "volume=".(1 + $this->volume))
                ->save($name.'temp.'.$ext);
        }catch (\Exception $e){
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }

        Storage::delete($this->fileInfo['path']);
        Storage::move($name.'temp.'.$ext, $this->fileInfo['path']);
        FileService::addCover($this->fileInfo['path'], $coverPath);

        $res = FileService::createAndNotify($this->fileInfo, $this->isPrivate, $this->guestId, $this->scheduleFileDeletion);

        $endTime = now();
        $executionTime = $endTime->diffInMilliseconds($startTime);
        if(!$res) {
            Storage::delete($this->fileInfo['path']);
            return;
        }
        FileService::logSuccess('Change Volume', $this->guestId, $executionTime, $this->isPrivate);


    }
}
