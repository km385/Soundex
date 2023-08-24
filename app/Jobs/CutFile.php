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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use function Laravel\Prompts\error;

class CutFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $af, private $path, private $guestId, private $isPrivate)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $name = pathinfo($this->path, PATHINFO_FILENAME);
        $ext = pathinfo($this->path, PATHINFO_EXTENSION);

        $coverPath = FileService::extractCover($this->path);
        error_log('cover extracted');
        try{
            FFMpeg::fromDisk('')
                ->open($name.'.'.$ext)
                ->export()
                ->toDisk('')
                ->addFilter('-af', $this->af)
                ->save($name.'temp.'.$ext);
        }catch (\Exception $e){
            error_log($e);
        }

        Storage::delete($this->path);
        Storage::move($name.'temp.'.$ext, $this->path);
        if (!empty($coverPath)){
            FileService::addCover($this->path, $coverPath);
        }

        error_log('cover added');

        FileService::createAndNotify($this->path, $this->isPrivate, $this->guestId);

    }


}
