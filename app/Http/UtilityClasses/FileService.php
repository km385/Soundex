<?php

namespace App\Http\UtilityClasses;

use App\Events\FileReadyToDownload;
use App\Events\PrivateFileReadyToDownload;
use App\Models\TemporarySong;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\FFProbe;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use function Laravel\Prompts\error;

class FileService
{
    public static function createAndNotify($path, $isPrivate, $userId): void
    {
        $tempFile = TemporarySong::create(['filePath' => $path]);

        $temporaryUrl = URL::temporarySignedRoute(
            'downloadFile',
            // Route name
            now()->addHours(1),
            // Expiration time
            ['fileName' => $tempFile->token],
            false
        );
        $parts = explode('/files/', $temporaryUrl);
        $extractedUrl = end($parts);
        error_log($extractedUrl);
        if ($isPrivate) {
            error_log('creating private event');
            event(new PrivateFileReadyToDownload($extractedUrl, $userId));
        } else {
            error_log('creating public event');
            event(new FileReadyToDownload($extractedUrl, $userId));
        }
    }

    public static function bpmNotify($bpm, $isPrivate, $userId): void
    {
        if ($isPrivate) {
            error_log('creating private event');
            event(new PrivateFileReadyToDownload($bpm, $userId));
        } else {
            error_log('creating public event');
            event(new FileReadyToDownload($bpm, $userId));
        }
    }

    public static function errorNotify($error, $isPrivate, $userId): void
    {
        if ($isPrivate) {
            error_log('creating private event');
            event(new PrivateFileReadyToDownload($error, $userId));
        } else {
            error_log('creating public event');
            event(new FileReadyToDownload($error, $userId));
        }
    }

    /**
     * @throws \Exception
     */
    public static function convertFile($filePath, $newExtension): string
    {
        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        try {
            FFMpeg::fromDisk('')
                ->open($filePath)
                ->export()
                ->toDisk('')
                ->save($filename.'temp.'.$newExtension);
        } catch (\Exception $e) {
            error($e);
            throw $e;
        }
        Storage::delete($filePath);

        Storage::move($filename.'temp.'.$newExtension, $filename.'.'.$newExtension);
        return $filename.'.'.$newExtension;
    }

    /**
     * @throws \Exception
     */
    private static function hasCover($filePath): bool
    {
        try {
            $FFProbe = FFProbe::create()
                ->getFFProbeDriver()
                ->command([
                    '-v', 'error',
                    '-select_streams', '1',
                    '-show_entries', 'stream=codec_name:format_tags=cover_art',
                    '-of', 'default=noprint_wrappers=1:nokey=1',
                    Storage::path($filePath)
                ]);
            error_log($FFProbe);
            if (empty($FFProbe)) {
                error_log('hasCover: no cover present');
                return false;
            } else {
                error_log('hasCover: cover exist');
                return true;
            }
        } catch (\Exception $e) {
            error_log('hasCover: ERROR');
            throw $e;
        }
    }


    /**
     * @throws \Exception
     */
    public static function extractCover($filePath): string
    {
        // TODO: continuing after catch might cause bugs, check if cover available with FFProbe
        $hasCover = self::hasCover($filePath);
        if (!$hasCover) {
            return "";
        }

        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        try {
            FFMpeg::open($filename . '.' . $ext)
                ->export()
                ->toDisk('')
                ->addFilter('-an')
                ->addFilter('-vcodec', 'copy')
                ->save($filename . '.jpg');
        } catch (\Exception $e) {
            error_log('error during extracting a cover');
            throw $e;
        }
        return $filename . '.jpg';
    }

    public static function addCover($filePath, $coverPath): void
    {
        if ($coverPath === "") {
            error_log('no cover to add');
            return;
        }
        // adding cover to opus is not supported yet by ffmpeg
        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if ($ext == "ogg") {
            error_log('opus/ogg file not compatible');
            return;
        }
        // TODO: check other extensions
        // TODO: possibly convert any to jpg
        // TODO: prohibit svg files ever making out of client
        if (in_array(strtolower(File::extension($coverPath)), ['webp', 'png'])) {
            FFMpeg::fromDisk('')
                ->open($coverPath)
                ->export()
                ->toDisk('')
                ->save(pathinfo($coverPath, PATHINFO_FILENAME) . '.jpg');
            $coverPath = pathinfo($coverPath, PATHINFO_FILENAME) . '.jpg';
            error_log('converted to jpg');
        }
        // it works for mp3 and jpg
//        ffmpeg.exe -i '.\song (1).flac' -i .\cover.jpg  -map 0:a -map 1 -codec copy -metadata:s:v title="Album cover" -metadata:s:v comment="Cover (front)" -disposition:v attached_pic songcover.flac

        try {
            $ffmpeg = FFMpeg::fromDisk('')
                ->open($filename . '.' . $ext)
                ->export()
                ->toDisk('')
                ->addFilter('-i', Storage::path($coverPath))
                ->addFilter('-map', "0:0")
                ->addFilter('-map', "1:0")
                ->addFilter('-c', "copy")
                ->addFilter('-id3v2_version', '3')
                ->addFilter('-metadata:s:v', "title='Album cover'")
                ->addFilter('-metadata:s:v', "comment='Cover (front)'");

            if (strtolower(File::extension($filePath)) === "flac") {
                $ffmpeg->addFilter('-disposition:v', 'attached_pic');
            }
            $ffmpeg->save('output.' . strtolower(File::extension($filePath)));
        } catch (\Exception $e) {
            error_log($e);
        }


        Storage::delete($filename . '.' . $ext);
        Storage::delete($coverPath);

        Storage::move('output.' . strtolower(File::extension($filePath)), $filename . '.' . $ext);

        error_log('added/kept cover');
    }

}
