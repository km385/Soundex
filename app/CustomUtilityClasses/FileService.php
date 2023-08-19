<?php

namespace App\CustomUtilityClasses;

use App\Events\FileReadyToDownload;
use App\Events\PrivateFileReadyToDownload;
use App\Models\TemporaryFile;
use Illuminate\Support\Facades\URL;

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
}
