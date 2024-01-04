<?php

namespace App\Http\UtilityClasses;

use App\Events\FileReadyToDownload;
use App\Events\PrivateFileReadyToDownload;
use App\Jobs\DeleteErrorFileJob;
use App\Jobs\DeleteTempFileJob;
use App\Models\SuccessfulJobs;
use App\Models\TemporarySong;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\FFProbe;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use function Laravel\Prompts\error;

class FileService
{
    public static function logSuccess($toolName, $userId, $time, $isPrivate): void
    {
        try {

            SuccessfulJobs::create([
                'tool_name' => $toolName,
                'is_guest' => !$isPrivate,
                'time' => $time,
                'user_id' => $isPrivate ? $userId : null
            ]);
            \Laravel\Prompts\info("job $toolName completed successfully");
        } catch (\Exception $e) {
            error_log($e);
        }
    }
    public static function createAndNotify($fileInfo, $isPrivate, $userId): void
    {
        error_log('createandnotify');
        try {
            $tags = FileService::extractMetadata($fileInfo['path']);
            if (!isset($tags['tags']['year']) || !DateTime::createFromFormat('Y-m-d', $tags['tags']['year']) !== false) {
                $tags['tags']['year'] = null;
            }

            if(!isset($tags['size'])) {
                error('no size info');
                throw new \Exception();
            }
        } catch (\Exception $e) {
            error($e->getMessage());
            self::errorNotify('error', $isPrivate, $userId);
            return;
        }
        try{
            $tempFile = TemporarySong::create([
                'song_path' => $fileInfo['path'],
                'extension' => $fileInfo['originalExt'],

                'title' => $tags['tags']['title'] ?? $fileInfo['originalName'],
                'album' => $tags['tags']['album'] ?? null,
                'year' => $tags['tags']['year'] ?? null,
                'artist' => $tags['tags']['artist'] ?? null,
                'genre' => $tags['tags']['genre'] ?? null,
                'size_kb' => $tags['size'] / 1024,
                'composer' => $tags['tags']['composer'] ?? null,
                'comment' => $tags['tags']['comment'] ?? null,
                'copyright_message' => $tags['tags']['copyrightMessage'] ?? null,
                'publisher' => $tags['tags']['publisher'] ?? null,
                'track_number' => $tags['tags']['trackNumber'] ?? null,
                'lyrics' => $tags['tags']['lyrics'] ?? null,

            ]);

        } catch (\Exception $e) {
            error($e);
        }

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
        error_log($tempFile->id);
        self::scheduleFileDeletion($tempFile->id);
    }

    public static function scheduleFileDeletion($tempFileId): void
    {
        DeleteTempFileJob::dispatch($tempFileId)->delay(now()->addHour());
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

    public static function diagnoseNotify($data, $pathToSavedFile, $isPrivate, $userId): void
    {
        error_log("notify");
        $temporaryUrl = URL::temporarySignedRoute(
            'downloadDiagnoseFile',
            // Route name
            now()->addHours(1),
            // Expiration time
            ['fileName' => $pathToSavedFile],
            false
        );
        $parts = explode('/file/', $temporaryUrl);
        $extractedUrl = end($parts);
        $data['path_to_saved_file'] = $extractedUrl;
        $jsonData = json_encode($data);

        if ($isPrivate) {
            error_log('creating private event');
            event(new PrivateFileReadyToDownload($jsonData, $userId));
        } else {
            error_log('creating public event');
            event(new FileReadyToDownload($jsonData, $userId));
        }
        DeleteErrorFileJob::dispatch($pathToSavedFile)->delay(now()->addMinute());
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

    public static function extractMetadata($filePath)
    {
        $filePath = Storage::path($filePath);
        error_log($filePath);

        try {
            $FFProbe = FFProbe::create()
                ->getFFProbeDriver()
                ->command([
                    '-v', 'quiet',
                    '-print_format', 'json',
                    '-show_format',
                    $filePath
                ]);
            $arr = json_decode($FFProbe, 1);

            // if mp3
            return $arr['format'];
        } catch (\Exception $e) {
            return $e->getMessage();
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
        Storage::delete($filePath);

        Storage::move('output.' . strtolower(File::extension($filePath)), $filename . '.' . $ext);

        error_log('added/kept cover');
    }

}
