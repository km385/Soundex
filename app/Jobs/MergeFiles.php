<?php

namespace App\Jobs;

use App\CustomUtilityClasses\FileService;
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
    public function __construct(private $paths, private $guestId, private $isPrivate)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $filter_complex_value = "";
        for($i = 0; $i < count($this->paths); $i++){
            $filter_complex_value .= "[$i:a]";
        }
        $filter_complex_value .= "concat=n=".count($this->paths).":v=0:a=1[a]";
        error_log("filter_complex: ".$filter_complex_value);
        try {
            error_log('creating ffmpeg command based on the files');
            $ffmpeg = FFMpeg::fromDisk('')
                ->open($this->paths[0])
                ->export()
                ->toDisk('');
            foreach ($this->paths as $path){
                // skip the first one since is already in use
                if($path == $this->paths[0]) continue;

                $ffmpeg
                    ->addFilter('-i', Storage::path($path));

            }
            error_log('ffmpeg process starting');

            $ffmpeg
                ->addFilter('-filter_complex', $filter_complex_value)
                ->addFilter('-map', "[a]")
                ->save(pathinfo($this->paths[0], PATHINFO_FILENAME).'temp.mp3');
            error_log('ffmpeg done successfully');

        }catch (\Exception $e){
            // TODO: when error clean up and notify user
            error_log($e);
        }

        foreach ($this->paths as $path){
            Storage::delete($path);
        }
        $finalPath = pathinfo($this->paths[0], PATHINFO_FILENAME).'.mp3';
        Storage::move(pathinfo($this->paths[0], PATHINFO_FILENAME).'temp.mp3', $finalPath);
        error_log('final file created: '.$finalPath);

        FileService::createAndNotify($finalPath, $this->isPrivate, $this->guestId);
    }
}
