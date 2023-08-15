<?php

namespace App\Jobs;

use App\Events\FileReadyToDownload;
use App\Events\PrivateFileReadyToDownload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use function Laravel\Prompts\error;

class CutFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $af, private $path, private $guestId, private $isPrivate)
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

        $coverPath = $this->extractCover($name, $ext);
        error_log('cover extracted');
        try{
            FFMpeg::fromDisk('')
                ->open($name.'.'.$ext)
                ->export()
                ->toDisk('')
                ->addFilter('-af', $this->af)
                ->save($name.'temp.'.$ext);
        }catch (\Exception $e){
            error_log($e);
        }


        Storage::delete($this->path);
        Storage::move($name.'temp.'.$ext, $this->path);

        if (!empty($coverPath)){
            $this->addCover($name, $ext, $coverPath);

        }
        error_log('cover added');

        if($this->isPrivate){
            error_log('creating private event');
            event(new PrivateFileReadyToDownload($this->path, $this->guestId));
        } else {
            error_log('creating public event');
            event(new FileReadyToDownload($this->path, $this->guestId));
        }
    }

    private function addCover($filename, $ext, $cover_path){
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
            ->addFilter('-map', "0")
            ->addFilter('-map', "1")
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
        // TODO ta  sciezke jakos inaczej przesylac, albo tworzyc potem
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
