<?php

namespace App\Jobs;

use App\Http\UtilityClasses\FileService;
use FFMpeg\Filters\Audio\AudioFilters;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ChangeMetadata implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $fileInfo, private $newCoverPath, private $metadata, private $newExtension, private $guestId, private $isPrivate, private $scheduleFileDeletion = true)
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
            $this->fileInfo['path'] = FileService::convertFile($this->fileInfo['path'], $this->newExtension);
        }catch (\Exception $e) {
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
        }

        $meta = $this->metadata;
        $name = pathinfo($this->fileInfo['path'], PATHINFO_FILENAME);
        $ext = pathinfo($this->fileInfo['path'], PATHINFO_EXTENSION);

        try {
            $currentCoverPath = FileService::extractCover($this->fileInfo['path']);
        } catch (\Exception $e) {
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }
        // vn dodane po m4a, nie testowane w innych, uzywa tylko sound stream
        // wav works for tags but it is not shown is windows context menu. cover does not work
        try {
            FFMpeg::fromDisk('')
                ->open($this->fileInfo['path'])
                ->export()
//            ->addFilter('-vn')
                ->addFilter(function (AudioFilters $filters) use ($meta){
                    $filters->addMetadata($meta);
                })
                ->save($name.'temp.'.$ext);
        }catch (\Exception $e) {
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }
        Storage::delete($this->fileInfo['path']);
        Storage::move($name.'temp.'.$ext, $name.'.'.$ext);
        $this->fileInfo['path'] = $name.'.'.$ext;

        if (is_null($this->newCoverPath)) {
            FileService::addCover($this->fileInfo['path'], $currentCoverPath);
        } else {
            FileService::addCover($this->fileInfo['path'], $this->newCoverPath);
        }

        $res = FileService::createAndNotify($this->fileInfo, $this->isPrivate, $this->guestId, $this->scheduleFileDeletion);

        $endTime = now();
        $executionTime = $endTime->diffInMilliseconds($startTime);
        if(!$res) {
            Storage::delete($this->fileInfo['path']);
            return;
        }
        FileService::logSuccess('Change Metadata', $this->guestId, $executionTime, $this->isPrivate);

    }

}
