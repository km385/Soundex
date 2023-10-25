<?php

namespace App\Jobs;

use App\Http\UtilityClasses\FileService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class CutFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $af, private $path, private $newExtension, private $guestId, private $isPrivate)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        try {
            $this->path = FileService::convertFile($this->path, $this->newExtension);

        } catch (\Exception $e) {
            error_log('exception caught');
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }

        $name = pathinfo($this->path, PATHINFO_FILENAME);
        $ext = pathinfo($this->path, PATHINFO_EXTENSION);
        // TODO: either combine 2 try/catch block together or give them different messages
        try {
            $coverPath = FileService::extractCover($this->path);
        } catch (\Exception $e) {
            // TODO: determine if cutter/speedup need to stop if error while extracting a cover
            error_log('exception caught');
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }

        try{
            FFMpeg::fromDisk('')
                ->open($name.'.'.$ext)
                ->export()
                ->toDisk('')
                ->addFilter('-af', $this->af)
                ->save($name.'temp.'.$ext);
        }catch (\Exception $e){
            error_log($e);
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }

        Storage::delete($this->path);
        Storage::move($name.'temp.'.$ext, $this->path);

        FileService::addCover($this->path, $coverPath);

        FileService::createAndNotify($this->path, $this->isPrivate, $this->guestId);

    }


}
