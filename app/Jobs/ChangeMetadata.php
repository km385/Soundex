<?php

namespace App\Jobs;

use App\CustomUtilityClasses\FileService;
use FFMpeg\Filters\Audio\AudioFilters;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ChangeMetadata implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $path, private $newCoverPath, private $metadata, private $guestId, private $isPrivate)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $meta = $this->metadata;
        $name = pathinfo($this->path, PATHINFO_FILENAME);
        $ext = pathinfo($this->path, PATHINFO_EXTENSION);

        $currentCoverPath = $this->extractCover($name, $ext);

        // vn dodane po m4a, nie testowane w innych, uzywa tylko sound stream
        FFMpeg::fromDisk('')
            ->open($this->path)
            ->export()
//            ->addFilter('-vn')
            ->addFilter(function (AudioFilters $filters) use ($meta){
                $filters->addMetadata($meta);
            })
            ->save($name.'temp.'.$ext);
        Storage::delete($this->path);
        Storage::move($name.'temp.'.$ext, $name.'.'.$ext);
        error_log("metadata added");

        if(is_null($this->newCoverPath)) {
            error_log('keeping the same cover');
            $this->addCover($name, $ext, $currentCoverPath);
            Storage::delete($currentCoverPath);
        } else {
            error_log('adding new cover');
            $this->addCover($name, $ext, $this->newCoverPath);
            Storage::delete($this->newCoverPath);
        }
        error_log("cover added");

        FileService::createAndNotify($this->path, $this->isPrivate, $this->guestId);
    }

    private function addCover($filename, $ext, $cover_path): void
    {
        // convert cover to jpg
        // TODO possibly convert any to jpg
        // TODO check if file and cover have both appropriate extensions
        // adding cover to opus is not supported yet by ffmpeg
        if($ext == "opus") return;
        if(File::extension($cover_path) == "webp"){
            FFMpeg::fromDisk('')
                ->open($cover_path)
                ->export()
                ->toDisk('')
                ->save(pathinfo($cover_path, PATHINFO_FILENAME).'.jpg');
            $cover_path = pathinfo($cover_path, PATHINFO_FILENAME).'.jpg';
        }
        // napewno dla mp3 i jpg dziala
        error_log(Storage::path($cover_path));
        FFMpeg::fromDisk('')
            ->open($filename.'.'.$ext)
            ->export()
            ->toDisk('')
            ->addFilter('-i', Storage::path($cover_path))
            ->addFilter('-map', "0:0")
            ->addFilter('-map', "1:0")
            ->addFilter('-c', "copy")
            ->addFilter('-id3v2_version', '3')
            ->addFilter('-metadata:s:v', "title='Album cover'")
            ->addFilter('-metadata:s:v', "comment='Cover (front)'")
            ->save('output.mp3');
        Storage::delete($filename.'.'.$ext);
        Storage::delete($cover_path);

        Storage::move('output.mp3', $filename.'.'.$ext);
    }

    private function extractCover($filename, $ext): string
    {
        try {
            FFMpeg::open($filename . '.' . $ext)
                ->export()
                ->toDisk('')
                ->addFilter('-an')
                ->addFilter('-vcodec', 'copy')
                ->save($filename . '.png');
        } catch (\Exception $e) {
            error_log('error during extracting a cover');
            return "";
        }

        return $filename . '.png';
    }
}
