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
use ProtoneMedia\LaravelFFMpeg\FFMpeg\FFProbe;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class DiagnoseFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $fileInfo, private $guestId, private $isPrivate)
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
        try {
            $output = FFMpeg::fromDisk('')
                ->open($this->fileInfo['path'])
                ->export()
                ->addFilter('-v', "error")
                ->addFilter(['-filter:a', 'volumedetect', '-f', 'null'])
                ->getProcessOutput();

//            $mess = print_r($output->all(), true);

            $pathToSavedFile = $this->saveErrorsToFile($output->all(), $name);

        } catch (\Exception $e) {
            FileService::errorNotify("ERROR", $this->isPrivate, $this->guestId);
            return;
        }

        $data = $this->getMetadataValues($this->fileInfo, sizeof($output->all()), $pathToSavedFile);

        Storage::delete($this->fileInfo['path']);

        FileService::diagnoseNotify($data, $pathToSavedFile, $this->isPrivate, $this->guestId);

        $endTime = now();
        $executionTime = $endTime->diffInMilliseconds($startTime);
        FileService::logSuccess('Diagnose File', $this->guestId, $executionTime, $this->isPrivate);

    }

    private function getMetadataValues($fileInfo, $numberOfErrors, $pathToErrors): array
    {
        try{

            $values = FFProbe::create()
                ->getFFProbeDriver()
                ->command(['-v', 'quiet',
                    '-print_format', 'json',
                    '-show_format',
                    '-show_streams', Storage::path($fileInfo['path'])]);
            error_log($values);
            $json = json_decode($values, true);
            $sample_rate = $json['streams'][0]['sample_rate' ?? ""];
            $duration = $json['streams'][0]['duration'] ?? "";
            $bitrate = $json['streams'][0]['bit_rate'] ?? "";
            $title = $json['format']['tags']['title'] ?? "";
            $genre = $json['format']['tags']['genre'] ?? "";
            $album = $json['format']['tags']['album'] ?? "";
            $artist = $json['format']['tags']['artist'] ?? "";
        } catch (\Exception $e){
            error_log($e);
        }

        return [
            'numberOfErrors' => $numberOfErrors,
            'path_to_saved_file' => $pathToErrors,
            'name' => $fileInfo['originalName'],
            'extension' => $fileInfo['originalExt'],
            'bitrate' => $bitrate ?? "",
            'duration' => $duration ?? "",
            'sample_rate' => $sample_rate ?? "",
            'title' => $title ?? "",
            'artist' => $artist ?? "",
            'genre' => $genre ?? "",
            'album' => $album ?? "",
        ];
    }

    private function saveErrorsToFile($content, $name): string
    {
        $outputJson = json_encode($content, JSON_PRETTY_PRINT);
        $outputFolderPath = $directory = "diagnose_files" . DIRECTORY_SEPARATOR;
        $fileName = "{$name}_output.txt";
        $outputFilePath = "{$outputFolderPath}{$fileName}";

        if (!Storage::exists($outputFolderPath)) {
            Storage::makeDirectory($outputFolderPath);
        }

        Storage::put($outputFilePath, $outputJson);
        return $fileName;
    }
}
