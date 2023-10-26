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

class ConvertFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $path, private $newExtension, private $bitrate, private $guestId, private $isPrivate)
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
            // change bitrate
            $filename = pathinfo($this->path, PATHINFO_FILENAME);
            $ext = pathinfo($this->path, PATHINFO_EXTENSION);
            error_log($this->path);
            FFMpeg::fromDisk('')
                ->open($this->path)
                ->export()
                ->toDisk('')
                ->addFilter('-b:a', $this->bitrate."K")
                ->save($filename.'temp.'.$ext);

            Storage::delete($this->path);
            Storage::move($filename.'temp.'.$ext, $filename.'.'.$ext);
            FileService::createAndNotify($filename.'.'.$ext, $this->isPrivate, $this->guestId);
        } catch (\Exception $e) {
            error_log('exception caught');
            error_log($e);
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }
    }
}
