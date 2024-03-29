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

class Recorder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $path, private $path2, private $guestId, private $isPrivate, private $scheduleFileDeletion = true)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = now();

        try{
            FFMpeg::fromDisk('')
                ->open($this->path)
                ->export()
                ->addFilter('-i', Storage::path($this->path2))
                ->addFilter('-filter_complex', 'amix=inputs=2:duration=longest')
                ->save(pathinfo($this->path, PATHINFO_FILENAME).'temp.mp3');

        }catch (\Exception $e){
            Storage::delete($this->path);
            Storage::delete($this->path2);
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }

        Storage::delete($this->path);
        Storage::delete($this->path2);
        $finalPath = pathinfo($this->path, PATHINFO_FILENAME).'.mp3';
        Storage::move(pathinfo($this->path, PATHINFO_FILENAME).'temp.mp3', $finalPath);

        $fileInfo = [
            'originalName' => 'recording',
            'originalExt' => 'mp3',
            'path' => $finalPath,
        ];

        $res = FileService::createAndNotify($fileInfo, $this->isPrivate, $this->guestId, $this->scheduleFileDeletion);

        $endTime = now();
        $executionTime = $endTime->diffInMilliseconds($startTime);
        if (!$res) {
            Storage::delete($fileInfo['path']);
            return;
        }
        FileService::logSuccess('Recorder', $this->guestId, $executionTime, $this->isPrivate);
    }
}
