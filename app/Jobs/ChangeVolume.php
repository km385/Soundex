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
    public function __construct(private $path, private $volume, private $guestId, private $isPrivate)
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
        error_log($this->volume);
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
                ->addFilter('-filter:a', "volume=".(1 + $this->volume))
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
