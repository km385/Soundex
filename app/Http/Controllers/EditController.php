<?php

namespace App\Http\Controllers;

use App\Http\UtilityClasses\FileService;
use App\Jobs\ChangeMetadata;
use App\Jobs\ChangeVolume;
use App\Jobs\ConvertFile;
use App\Jobs\CutFile;
use App\Jobs\DiagnoseFile;
use App\Jobs\MergeFiles;
use App\Jobs\MixFile;
use App\Jobs\Recorder;
use App\Jobs\SpeedUpFile;
use App\Jobs\BPMFinder;
use App\Jobs\VideoToAudio;
use App\Models\Song;
use App\Models\TemporarySong;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function Laravel\Prompts\error;

class EditController extends Controller
{

    public function test() {
        error_log('test');
        $file = Request::file('file');
        $path = Storage::putFile($file);

//        $coverPath = FileService::extractCover($path);
        FileService::addCover($path, 'pMPeKH8Lu61NStiLH2DSngdVhahWwTKsbOygR7rL.png');
    }
    public function downloadFile ($token): StreamedResponse|JsonResponse
    {
        if(!Request::hasValidSignature(false)){
            error_log('expired url');
            return response()->json(['message' => 'expired url'], 500);
        }
        $file = TemporarySong::where('token', $token)->first();
        if($file) {
            $filePath = $file->song_path;
            error_log($filePath);
            return Storage::disk('')->download($filePath);
        }
        error_log('no file');

        return response()->json(['message' => 'no file']);

    }

    public function downloadDiagnosticFile($path): StreamedResponse|JsonResponse
    {
        if(!Request::hasValidSignature(false)){
            error_log('expired url');
            return response()->json(['message' => 'expired url'], 500);
        }

        if(Storage::exists("/diagnose_files/".$path)) {
            return Storage::disk('')->download('diagnose_files'.DIRECTORY_SEPARATOR.$path);
        } else {
            error("plik nie istnieje");
            return response()->json(['message' => 'no file']);
        }

    }

    public function saveToLibrary(): JsonResponse
    {
        $user = Auth::user();
        if(!$user){
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        if($user->files_stored >= 50) {
            return response()->json(['message' => 'number of files limit reached'], 401);
        }
        $link = Request::input('token');

        $fullLink = "http://localhost/files/".$link;
        if(!URL::isValidUrl($fullLink)){
            error_log('not valid');
            return response()->json(['message' => 'not valid'], 401);
        }

        error_log('valid');
        $parts = explode("?", $link);
        $token = $parts[0];
        $file = TemporarySong::where('token', $token)->first();

        if(Song::where('song_path', $file->song_path)->first()) {
            error_log('already exist');
            return response()->json(['message' => 'already saved'], 401);
        }

        if(234800 - $user->storage_used <= $file->size_kb) {
            error('no storage left');
            return response()->json(['message' => 'no space left'], 401);
        }

        try {
            $song = new Song();
            // TODO: path as file name or userId/path
            $song->song_path = $file->song_path;
            // todo: check title saving
            // $song->title = $file->originalName;
            $song->extension = $file->extension;
            $song->size_kb = $file->size_kb;
            // todo: supports mp3 and flac
            $song->title = $file->title;
            $song->album = $file->album;
            // todo: validate client side dateformat
            $song->year = $file->year;
            $song->artist = $file->artist;
            $song->genre = $file->genre;

            $user->songs()->save($song);
            $user->storage_used = $user->storage_used + $file->size_kb;
            $user->files_stored = $user->files_stored + 1;
            $user->save();
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }



        $directory = "user_files" . DIRECTORY_SEPARATOR . $user->id. DIRECTORY_SEPARATOR;
        if(Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        Storage::copy($file->song_path, $directory . $file->song_path);

        return response()->json(['message' => 'success']);

    }

    public function diagnosis(): JsonResponse
    {
        $user = Request::user();

        if(!$user){
            $isPrivate = false;
        } else {
            $isPrivate = true;
        }

        $file = Request::file('file');
        $guestId = Request::input('guestId');

        $file = Request::file("file");
        $fileInfo = $this->getFileInfo($file);

        DiagnoseFile::dispatch($fileInfo, $guestId, $isPrivate);
        return response()->json(['message' => 'success']);

    }

    public function volumeChanger(): JsonResponse
    {
        $user = Request::user();

        if(!$user){
            $isPrivate = false;
        } else {
            $isPrivate = true;
        }

        $file = Request::file('file');
        $volume = Request::input('volume');
        $guestId = Request::input('guestId');

        $fileInfo = $this->getFileInfo($file);

        ChangeVolume::dispatch($fileInfo, $volume, $guestId, $isPrivate);
        return response()->json(['message' => 'success']);

    }

    public function layerMixer(): JsonResponse
    {
        $user = Request::user();

        if(!$user){
            $isPrivate = false;
        } else {
            $isPrivate = true;
        }

        $file = Request::file('background');
        $file2 = Request::file('foreground');
        $guestId = Request::input('guestId');
        $pathBg = Storage::putFile($file);
        $pathFg = Storage::putFile($file2);

        MixFile::dispatch($pathBg, $pathFg, $guestId, $isPrivate);



        return \response()->json(['message' => 'success']);
    }

    public function tagEditor(): JsonResponse
    {
        $user = Request::user();

        if(!$user){
            $isPrivate = false;
        } else {
            $isPrivate = true;
        }

        $guestId = Request::input('guestId');

        if(Request::hasFile('fileRef')) {
            $audioFile = Request::file('fileRef');
            $fileInfo = $this->getFileInfo($audioFile);

        } else {
            return response()->json(['error' => 'file is required'], 400);
        }

        if (Request::hasFile('coverRef')) {
            $coverFile = Request::file('coverRef');
            $coverPath = Storage::putFile($coverFile);
        } else {
            $coverPath = null;
        }
        $metadata = [];
        // mp3, flac support these and cover arts
        $fieldsToCheck = ['title', 'artist', 'year', 'genre', 'album',
            'composer', 'comment', 'copyrightMessage', 'publisher', 'trackNumber', 'lyrics'];
        foreach ($fieldsToCheck as $field) {
            $value = Request::input($field);
            if(!empty($value)) {
                $metadata[$field] = $value;
            }
        }


        $newExtension = Request::input('extension');

        error_log("prepering");

        ChangeMetadata::dispatch($fileInfo, $coverPath, $metadata, $newExtension, $guestId, $isPrivate);

        return response()->json(['message' => 'success']);
    }

    public function videoToAudio(): JsonResponse
    {
        $user = Request::user();

        if(!$user){
            $isPrivate = false;
        } else {
            $isPrivate = true;
        }
        $file = Request::file('file');
        $guestId = Request::input('guestId');
        $fileInfo = $this->getFileInfo($file);


        VideoToAudio::dispatch($fileInfo, $guestId, $isPrivate);

        return \response()->json(['message' => 'success']);

    }

    public function convert(): JsonResponse
    {
        $user = Request::user();

        if(!$user){
            $isPrivate = false;
        } else {
            $isPrivate = true;
        }
        $file = Request::file('file');
        $guestId = Request::input('guestId');
        $extension = Request::input('extension');
        $bitrate = Request::input('bitrate');
        $fileInfo = $this->getFileInfo($file);


        ConvertFile::dispatch($fileInfo, $extension, $bitrate, $guestId, $isPrivate);
        return \response()->json(['message' => 'success']);

    }

    public function recorder(): JsonResponse
    {
        $user = Request::user();

        if(!$user){
            $isPrivate = false;
        } else {
            $isPrivate = true;
        }

        $file = Request::file('recording');
        $file2 = Request::file('background');
        $guestId = Request::input('guestId');
        $path = Storage::putFile($file);
        $path2 = Storage::putFile($file2);
        error_log($path);
        error_log($path2);
        error_log($guestId);
        Recorder::dispatch($path, $path2, $guestId, $isPrivate);

        return \response()->json(['message' => 'success']);
    }

    public function speedup(): JsonResponse
    {
        $user = Request::user();

        if(!$user){
            $isPrivate = false;
        } else {
            $isPrivate = true;
        }

        if(!Request::hasFile('file')){
            return \response()->json(['message' => 'no file']);
        }
        $file = Request::file('file');
        $fileInfo = $this->getFileInfo($file);

        $pitch = Request::input('pitchValue');
        $speed = Request::input('speedValue');
        $guestId = Request::input('guestId');
        error_log($isPrivate);
        error_log($guestId);
        SpeedUpFile::dispatch($fileInfo, $pitch, $speed, $isPrivate, $guestId);

        return \response()->json(['message' => 'success']);
    }

    public function cut(): JsonResponse
    {
        $user = Request::user();

        if(!$user){
            $isPrivate = false;
        } else {
            $isPrivate = true;
        }

        $start = Request::input('start');
        $end = Request::input('end');

        $file = Request::file('file');
        $fileInfo = $this->getFileInfo($file);

        $guestId = Request::input('guestId');
        $af = "";
        if(Request::has('start2') && Request::has('end2')){
            error_log('2 ranges');
            $start2 = Request::input('start2');
            $end2 = Request::input('end2');

            if (($start >= $start2 && $start <= $end2) || ($start2 >= $start && $start2 <= $end)) {
                error_log('overlap');
                error_log(min($start, $start2, $end, $end2));
                error_log(max($start, $start2, $end, $end2));
                $af = "aselect='between(t,".min($start, $start2, $end, $end2).",".max($start, $start2, $end, $end2).")',asetpts=N/SR/TB";
            } else {
                error_log('osobno');
                if($start < $start2){
                    $af = "aselect='between(t,".$start.",".$end.")+between(t,".$start2.",".$end2.")',asetpts=N/SR/TB";
                } else {
                    $af = "aselect='between(t,".$start2.",".$end2.")+between(t,".$start.",".$end.")',asetpts=N/SR/TB";
                }
            }
        } else {
            error_log('1 range');
            $af = "aselect='between(t,".$start.",".$end.")',asetpts=N/SR/TB";
        }
//        ffmpeg -i lol.mp3 -af "aselect='between(t,4,6.5)+between(t,17,26)+between(t,74,91)',asetpts=N/SR/TB" out.mp3

        CutFile::dispatch($af, $fileInfo, $guestId, $isPrivate);
        return response()->json(['message' => 'success']);
    }

    public function merge(): JsonResponse
    {
        if(Auth::check()){
            $isPrivate = true;
        } else {
            $isPrivate = false;
        }

        $fileKeys = array_keys(Request::all());
        error_log(count($fileKeys));
        $guestId = Request::input('guestId');
        $paths = [];
        foreach ($fileKeys as $key){
            if($key == "guestId") {
                error_log('guest lol');
                continue;
            }
            $file = Request::file($key);
            $path = Storage::putFile($file);
            error_log('key: '.$key);
            $paths[] = $path;
        }
        MergeFiles::dispatch($paths, $guestId, $isPrivate);

        return \response()->json(['message' => 'processing started']);
    }
    public function bpmFinder(): JsonResponse
    {
        $user = Request::user();

        if(!$user){
            $isPrivate = false;
        } else {
            $isPrivate = true;
        }

        $guestId = Request::input('guestId');

        if(Request::hasFile('fileRef')) {
            $audioFile = Request::file('fileRef');
            $audioPath = Storage::putFile($audioFile);
            error_log($audioPath);
        } else {
            return response()->json(['error' => 'file is required'], 400);
        }

        if (Request::hasFile('coverRef')) {
            $coverFile = Request::file('coverRef');
            $coverPath = Storage::putFile($coverFile);
        } else {
            $coverPath = null;
        }

        error_log("prepering");

        BPMFinder::dispatch($audioPath, $coverPath, $guestId, $isPrivate);

        return response()->json(['message' => 'success']);
    }

    /**
     * @param array|\Illuminate\Http\UploadedFile|null $file
     * @param bool|string $path
     * @return array
     */
    public function getFileInfo(array|\Illuminate\Http\UploadedFile|null $file): array
    {
        $path = Storage::putFile($file);

        $originalName = $file->getClientOriginalName();
        $originalName = pathinfo($originalName, PATHINFO_FILENAME);
        $originalExt = $file->getClientOriginalExtension();
        return [
            'originalName' => $originalName,
            'originalExt' => $originalExt,
            'path' => $path,
        ];
    }
}
