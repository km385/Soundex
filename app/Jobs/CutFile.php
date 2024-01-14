<?php

namespace App\Jobs;

use App\Http\UtilityClasses\FileService;
use App\Models\SuccessfulJobs;
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
    public function __construct(private $params, private $fileInfo, private $guestId, private $isPrivate, private $scheduleFileDeletion = true)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = now();


        $name = pathinfo($this->fileInfo['path'], PATHINFO_FILENAME);
        $ext = pathinfo($this->fileInfo['path'], PATHINFO_EXTENSION);
        // TODO: either combine 2 try/catch block together or give them different messages
        try {
            $coverPath = FileService::extractCover($this->fileInfo['path']);
        } catch (\Exception $e) {
            // TODO: determine if cutter/speedup need to stop if error while extracting a cover
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }
        $af = $this->get_configuration();
        try{
            FFMpeg::fromDisk('')
                ->open($name.'.'.$ext)
                ->export()
                ->toDisk('')
                ->addFilter('-af', $af)
                ->save($name.'temp.'.$ext);
        }catch (\Exception $e){

            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }

        Storage::delete($this->fileInfo['path']);
        Storage::move($name.'temp.'.$ext, $this->fileInfo['path']);

        FileService::addCover($this->fileInfo['path'], $coverPath);

        $res = FileService::createAndNotify($this->fileInfo, $this->isPrivate, $this->guestId, $this->scheduleFileDeletion);


        $endTime = now();
        $executionTime = $endTime->diffInMilliseconds($startTime);
        if (!$res) {
            Storage::delete($this->fileInfo['path']);
            return;
        }
        FileService::logSuccess('Cut File', $this->guestId, $executionTime, $this->isPrivate);

    }

    private function get_configuration(): string
    {
//        ffmpeg -i lol.mp3 -af "aselect='between(t,4,6.5)+between(t,17,26)+between(t,74,91)',asetpts=N/SR/TB" out.mp3

        $af = "";
        $start = $this->params['start'];
        $end = $this->params['end'];
        if($this->params['start2'] && $this->params['end2']){
            $start2 = $this->params['start2'];
            $end2 = $this->params['end2'];

            if (($start >= $start2 && $start <= $end2) || ($start2 >= $start && $start2 <= $end)) {
                error_log(min($start, $start2, $end, $end2));
                error_log(max($start, $start2, $end, $end2));
                $af = "aselect='between(t,".min($start, $start2, $end, $end2).",".max($start, $start2, $end, $end2).")',asetpts=N/SR/TB";
            } else {
                if($start < $start2){
                    $af = "aselect='between(t,".$start.",".$end.")+between(t,".$start2.",".$end2.")',asetpts=N/SR/TB";
                } else {
                    $af = "aselect='between(t,".$start2.",".$end2.")+between(t,".$start.",".$end.")',asetpts=N/SR/TB";
                }
            }
        } else {
            $af = "aselect='between(t,".$start.",".$end.")',asetpts=N/SR/TB";
        }
        return $af;
    }


}
