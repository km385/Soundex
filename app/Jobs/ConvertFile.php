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
    public function __construct(private $fileInfo, private $newExtension, private $bitrate, private $guestId, private $isPrivate)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        try {
            $coverPath = FileService::extractCover($this->fileInfo['path']);
        } catch (\Exception $e) {
            // TODO: determine if cutter/speedup need to stop if error while extracting a cover
            error_log('exception caught');
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }

        try {
            $this->fileInfo['path'] = FileService::convertFile($this->fileInfo['path'], $this->newExtension);
            // change bitrate
            $filename = pathinfo($this->fileInfo['path'], PATHINFO_FILENAME);
            $ext = pathinfo($this->fileInfo['path'], PATHINFO_EXTENSION);
            error_log($this->fileInfo['path']);
            FFMpeg::fromDisk('')
                ->open($this->fileInfo['path'])
                ->export()
                ->toDisk('')
                ->addFilter('-b:a', $this->bitrate."K")
                ->save($filename.'temp.'.$ext);

            Storage::delete($this->fileInfo['path']);
            Storage::move($filename.'temp.'.$ext, $filename.'.'.$ext);
            FileService::addCover($this->fileInfo['path'], $coverPath);

            $this->fileInfo['path'] = $filename.'.'.$ext;
            FileService::createAndNotify($this->fileInfo, $this->isPrivate, $this->guestId);
        } catch (\Exception $e) {
            error_log('exception caught');
            error_log($e);
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }
    }
}
