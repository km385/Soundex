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
        $userId = explode("-", $userId)[0];
        try {

            SuccessfulJobs::create([
                'tool_name' => $toolName,
                'is_guest' => !$isPrivate,
                'time' => $time,
                'user_id' => $isPrivate ? $userId : null
            ]);
            \Laravel\Prompts\info("job $toolName completed successfully");
        } catch (\Exception $e) {

        }
    }

    public static function createAndNotify($fileInfo,  $isPrivate, $userId, $scheduleFileDeletion = true, $bpmArray = null): bool
    {
        try {
            $tags = FileService::extractMetadata($fileInfo['path']);
            if (!isset($tags['tags']['year']) || !DateTime::createFromFormat('Y-m-d', $tags['tags']['year']) !== false) {
                $tags['tags']['year'] = null;
            }

            if (!isset($tags['size'])) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            error($e->getMessage());
            self::errorNotify('error', $isPrivate, $userId);
            return false;
        }

        try {
            $tempFile = TemporarySong::create([
                'song_path' => $fileInfo['path'],
                'extension' => $fileInfo['originalExt'],
                'title' => $tags['tags']['title'] ?? $fileInfo['originalName'],
                'duration_sec' => $tags['duration'] ?? null,
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
            if(!$tempFile) {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            self::errorNotify('error', $isPrivate, $userId);
            return false;
        }

        $temporaryUrl = URL::temporarySignedRoute(
            'downloadFile',
            now()->addHours(1),
            ['fileName' => $tempFile->token],
            false
        );

        $parts = explode('/files/', $temporaryUrl);
        $extractedUrl = end($parts);

        if ($isPrivate) {
            event(new PrivateFileReadyToDownload($extractedUrl, $userId, $bpmArray));
        } else {
            event(new FileReadyToDownload($extractedUrl, $userId, $bpmArray));
        }

        if($scheduleFileDeletion) {
            self::scheduleFileDeletion($tempFile->id);
        }
        return true;
    }

    public static function scheduleFileDeletion($tempFileId): void
    {
        DeleteTempFileJob::dispatch($tempFileId)->delay(now()->addHour());
    }

    public static function diagnoseNotify($data, $pathToSavedFile, $isPrivate, $userId, $scheduleFileDeletion = true): void
    {
        $temporaryUrl = URL::temporarySignedRoute(
            'downloadDiagnoseFile',
            now()->addHours(1),
            ['fileName' => $pathToSavedFile],
            false
        );
        $parts = explode('/file/', $temporaryUrl);
        $extractedUrl = end($parts);
        $data['path_to_saved_file'] = $extractedUrl;
        $jsonData = json_encode($data);

        if ($isPrivate) {
            event(new PrivateFileReadyToDownload($jsonData, $userId));
        } else {
            event(new FileReadyToDownload($jsonData, $userId));
        }
        if($scheduleFileDeletion) {
            DeleteErrorFileJob::dispatch($pathToSavedFile)->delay(now()->addMinute());
        }
    }



    public static function errorNotify($error, $isPrivate, $userId): void
    {
        if ($isPrivate) {
            event(new PrivateFileReadyToDownload($error, $userId));
        } else {
            event(new FileReadyToDownload($error, $userId));
        }
    }

    /**
     * @throws \Exception
     */
    public static function extractMetadata($filePath)
    {
        $filePath = Storage::path($filePath);

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

            return $arr['format'];
        } catch (\Exception $e) {
            throw new \Exception();
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
            if (empty($FFProbe)) {
                return false;
            } else {
                return true;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }


    /**
     * @throws \Exception
     */
    public static function extractCover($filePath): string
    {
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
            throw $e;
        }
        return $filename . '.jpg';
    }

    public static function addCover($filePath, $coverPath): void
    {
        if ($coverPath === "") {
            return;
        }
        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if ($ext == "ogg") {
            return;
        }

        if (in_array(strtolower(File::extension($coverPath)), ['webp', 'png'])) {
            FFMpeg::fromDisk('')
                ->open($coverPath)
                ->export()
                ->toDisk('')
                ->save(pathinfo($coverPath, PATHINFO_FILENAME) . '.jpg');
            $coverPath = pathinfo($coverPath, PATHINFO_FILENAME) . '.jpg';

        }

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

        }


        Storage::delete($filename . '.' . $ext);
        Storage::delete($coverPath);
        Storage::delete($filePath);

        Storage::move('output.' . strtolower(File::extension($filePath)), $filename . '.' . $ext);

    }

}
