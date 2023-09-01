<?php

namespace App\CustomUtilityClasses;

use App\Events\FileReadyToDownload;
use App\Events\PrivateFileReadyToDownload;
use App\Models\TemporaryFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class FileService
{
    public static function createAndNotify($path, $isPrivate, $userId): void
    {
        $tempFile = TemporaryFile::create(['filePath' => $path]);

        $temporaryUrl = URL::temporarySignedRoute(
            'downloadFile', // Route name
            now()->addHours(1), // Expiration time
            ['fileName' => $tempFile->token] ,
            false
        );
        $parts = explode('/files/', $temporaryUrl);
        $extractedUrl = end($parts);
        error_log($extractedUrl);
        if($isPrivate){
            error_log('creating private event');
            event(new PrivateFileReadyToDownload($extractedUrl, $userId));
        } else {
            error_log('creating public event');
            event(new FileReadyToDownload($extractedUrl, $userId));
        }
    }

    public static function extractCover($filePath): string
    {
        // TODO: continuing after catch might cause bugs, check if cover available with FFProbe
        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
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

    public static function addCover($filePath, $coverPath): void
    {
        // adding cover to opus is not supported yet by ffmpeg
        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if($ext == "ogg") {
            error_log('opus/ogg file not compatible');
            return;
        }

        // TODO: check other extensions
        // TODO: possibly convert any to jpg
        if(in_array(strtolower(File::extension($coverPath)), ['webp', 'png'])) {
            FFMpeg::fromDisk('')
                ->open($coverPath)
                ->export()
                ->toDisk('')
                ->save(pathinfo($coverPath, PATHINFO_FILENAME).'.jpg');
            $coverPath = pathinfo($coverPath, PATHINFO_FILENAME).'.jpg';
            error_log('converted webp to jpg');
        }
        // it works for mp3 and jpg
        FFMpeg::fromDisk('')
            ->open($filename.'.'.$ext)
            ->export()
            ->toDisk('')
            ->addFilter('-i', Storage::path($coverPath))
            ->addFilter('-map', "0:0")
            ->addFilter('-map', "1:0")
            ->addFilter('-c', "copy")
            ->addFilter('-id3v2_version', '3')
            ->addFilter('-metadata:s:v', "title='Album cover'")
            ->addFilter('-metadata:s:v', "comment='Cover (front)'")
            ->save('output.mp3');
        Storage::delete($filename.'.'.$ext);
        Storage::delete($coverPath);

        Storage::move('output.mp3', $filename.'.'.$ext);
    }

}
