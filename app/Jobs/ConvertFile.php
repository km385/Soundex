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
    public function __construct(private $fileInfo, private $newExtension, private $bitrate, private $guestId, private $isPrivate, private $scheduleFileDeletion = true)
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
            $coverPath = FileService::extractCover($this->fileInfo['path']);
        } catch (\Exception $e) {
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }

        try {
            $this->fileInfo['path'] = FileService::convertFile($this->fileInfo['path'], $this->newExtension);
            $filename = pathinfo($this->fileInfo['path'], PATHINFO_FILENAME);
            $ext = pathinfo($this->fileInfo['path'], PATHINFO_EXTENSION);

            $ffmpeg = FFMpeg::fromDisk('')
                ->open($this->fileInfo['path'])
                ->export()
                ->toDisk('');
            if($this->bitrate !== "flac" && $this->bitrate !== "wav") {
                $ffmpeg->addFilter('-b:a', $this->bitrate."K");
            }

            $ffmpeg->save($filename.'temp.'.$ext);

            Storage::delete($this->fileInfo['path']);
            Storage::move($filename.'temp.'.$ext, $filename.'.'.$ext);
            FileService::addCover($this->fileInfo['path'], $coverPath);

            $this->fileInfo['path'] = $filename.'.'.$ext;
            $res = FileService::createAndNotify($this->fileInfo, $this->isPrivate, $this->guestId, $this->scheduleFileDeletion);


            $endTime = now();
            $executionTime = $endTime->diffInMilliseconds($startTime);
            if(!$res) {
                Storage::delete($this->fileInfo['path']);
                return;
            }
            FileService::logSuccess('Convert File', $this->guestId, $executionTime, $this->isPrivate);

        } catch (\Exception $e) {
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }
    }
}
