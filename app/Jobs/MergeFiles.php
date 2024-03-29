<?php

namespace App\Jobs;

use App\Http\UtilityClasses\FileService;
use App\Events\FileReadyToDownload;
use App\Events\PrivateFileReadyToDownload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class MergeFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $paths, private $guestId, private $isPrivate, private $scheduleFileDeletion = true)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = now();

        $filter_complex_value = $this->get_filter_complex_value();

        try {
            $ffmpeg = FFMpeg::fromDisk('')
                ->open($this->paths[0])
                ->export()
                ->toDisk('');
            foreach ($this->paths as $path){
                // skip the first one since it's already in use
                if($path == $this->paths[0]) continue;

                $ffmpeg
                    ->addFilter('-i', Storage::path($path));

            }


            $ffmpeg
                ->addFilter('-filter_complex', $filter_complex_value)
                ->addFilter('-map', "[a]")
                ->save(pathinfo($this->paths[0], PATHINFO_FILENAME).'temp.mp3');

        }catch (\Exception $e){
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }

        foreach ($this->paths as $path){
            Storage::delete($path);
        }
        $finalPath = pathinfo($this->paths[0], PATHINFO_FILENAME).'.mp3';
        Storage::move(pathinfo($this->paths[0], PATHINFO_FILENAME).'temp.mp3', $finalPath);

        $fileInfo = [
            'originalName' => 'merge',
            'originalExt' => 'mp3',
            'path' => $finalPath,
        ];

        $res = FileService::createAndNotify($fileInfo, $this->isPrivate, $this->guestId, $this->scheduleFileDeletion);


        $endTime = now();
        $executionTime = $endTime->diffInMilliseconds($startTime);
        if(!$res) {
            Storage::delete($fileInfo['path']);
            return;
        }
        FileService::logSuccess('Merge Files', $this->guestId, $executionTime, $this->isPrivate);

    }

    /**
     * @return string
     */
    public function get_filter_complex_value(): string
    {
        $filter_complex_value = "";
        for ($i = 0; $i < count($this->paths); $i++) {
            $filter_complex_value .= "[$i:a]";
        }
        $filter_complex_value .= "concat=n=" . count($this->paths) . ":v=0:a=1[a]";

        return $filter_complex_value;
    }
}
