<?php

namespace App\Jobs;

use App\CustomUtilityClasses\FileService;
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
    public function __construct(private $path, private $newCoverPath, private $metadata, private $guestId, private $isPrivate)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $meta = $this->metadata;
        $name = pathinfo($this->path, PATHINFO_FILENAME);
        $ext = pathinfo($this->path, PATHINFO_EXTENSION);

        $currentCoverPath = FileService::extractCover($this->path);
        // vn dodane po m4a, nie testowane w innych, uzywa tylko sound stream
        try {
            FFMpeg::fromDisk('')
                ->open($this->path)
                ->export()
//            ->addFilter('-vn')
                ->addFilter(function (AudioFilters $filters) use ($meta){
                    $filters->addMetadata($meta);
                })
                ->save($name.'temp.'.$ext);
        }catch (\Exception $e) {
            error_log($e);
            return;
        }
        Storage::delete($this->path);
        Storage::move($name.'temp.'.$ext, $name.'.'.$ext);
        error_log("metadata added");

        if(is_null($this->newCoverPath)) {
            if($currentCoverPath !== ""){
                error_log('keeping the same cover');
                FileService::addCover($this->path, $currentCoverPath);
                Storage::delete($currentCoverPath);
            }
        } else {
            error_log('adding new cover');
            FileService::addCover($this->path, $this->newCoverPath);
            Storage::delete($this->newCoverPath);
        }
        error_log("cover added");

        FileService::createAndNotify($this->path, $this->isPrivate, $this->guestId);
    }

}
