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

class MixFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $pathBg, private $pathFg, private $guestId, private $isPrivate)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try{
            FFMpeg::fromDisk('')
                ->open($this->pathBg)
                ->export()
                ->addFilter('-i', Storage::path($this->pathFg))
                ->addFilter('-filter_complex', 'amix=inputs=2:duration=first')
                ->save(pathinfo($this->pathBg, PATHINFO_FILENAME).'temp.mp3');

        }catch (\Exception $e){
            error_log($e);
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        } finally {
            Storage::delete($this->pathBg);
            Storage::delete($this->pathFg);
        }

        $finalPath = pathinfo($this->pathBg, PATHINFO_FILENAME).'.mp3';

        Storage::move(pathinfo($this->pathBg, PATHINFO_FILENAME).'temp.mp3', $finalPath);

        $fileInfo = [
            'originalName' => 'mixed',
            'originalExt' => 'mp3',
            'path' => $finalPath,
        ];

        FileService::createAndNotify($fileInfo, $this->isPrivate, $this->guestId);

        FileService::logSuccess('MixFile');

    }
}
