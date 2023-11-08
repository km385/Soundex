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
    public function __construct(private $fileInfo, private $newCoverPath, private $metadata, private $newExtension, private $guestId, private $isPrivate)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
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
        error_log("metadata added");

        if (is_null($this->newCoverPath)) {
            error_log('keeping the same cover');
            FileService::addCover($this->fileInfo['path'], $currentCoverPath);
        } else {
            error_log('adding new cover');
            FileService::addCover($this->fileInfo['path'], $this->newCoverPath);
        }
        error_log("cover added");

        FileService::createAndNotify($this->fileInfo, $this->isPrivate, $this->guestId);
    }

}
