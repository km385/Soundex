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
    public function downloadFile ($token): StreamedResponse|JsonResponse
    {
        if(!Request::hasValidSignature(false)){
            return response()->json(['message' => 'expired url'], 500);
        }
        $file = TemporarySong::where('token', $token)->first();
        if($file) {
            $filePath = $file->song_path;
            return Storage::disk('')->download($filePath);
        }

        return response()->json(['message' => 'no file'], 404);

    }

    public function downloadDiagnosticFile($path): StreamedResponse|JsonResponse
    {
        if(!Request::hasValidSignature(false)){
            return response()->json(['message' => 'expired url'], 500);
        }

        if(Storage::exists("/diagnose_files/".$path)) {
            return Storage::disk('')->download('diagnose_files'.DIRECTORY_SEPARATOR.$path);
        } else {
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
            return response()->json(['message' => 'number of files limit reached'], 403);
        }
        $link = Request::input('token');

        $fullLink = "http://localhost/files/".$link;
        if(!URL::isValidUrl($fullLink)){
            return response()->json(['message' => 'not valid'], 401);
        }

        $parts = explode("?", $link);
        $token = $parts[0];
        $file = TemporarySong::where('token', $token)->first();

        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        if(Song::where('song_path', $file->song_path)->first()) {
            return response()->json(['message' => 'already saved'], 403);
        }

        if(234800 - $user->storage_used <= $file->size_kb) {
            return response()->json(['message' => 'no space left'], 403);
        }

        try {
            $song = new Song();
            $song->song_path = $file->song_path;
            $song->extension = $file->extension;
            $song->size_kb = $file->size_kb;
            $song->title = $file->title;
            $song->album = $file->album;
            $song->year = $file->year;
            $song->artist = $file->artist;
            $song->genre = $file->genre;

            $user->songs()->save($song);
            $user->storage_used = $user->storage_used + $file->size_kb;
            $user->files_stored = $user->files_stored + 1;
            $user->save();
        } catch (\Exception $e) {
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
        $fieldsToCheck = ['title', 'artist', 'year', 'genre', 'album',
            'composer', 'comment', 'copyrightMessage', 'publisher', 'trackNumber', 'lyrics'];
        foreach ($fieldsToCheck as $field) {
            $value = Request::input($field);
            if(!empty($value)) {
                $metadata[$field] = $value;
            }
        }


        $newExtension = Request::input('extension');

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
        if(!$file || !$file2) {
            return \response()->json(['message' => 'no file'], 404);
        }
        $guestId = Request::input('guestId');
        $path = Storage::putFile($file);
        $path2 = Storage::putFile($file2);
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
        if($start && $end) {
            $params = [
                'start' => $start,
                'end' => $end,
                'start2' => Request::input('start2'),
                'end2' => Request::input('end2'),
            ];
        } else {
            return response()->json(['message' => 'error'], 401);
        }

        CutFile::dispatch($params, $fileInfo, $guestId, $isPrivate);
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
        $guestId = Request::input('guestId');
        $paths = [];
        foreach ($fileKeys as $key){
            if($key == "guestId") {
                continue;
            }
            $file = Request::file($key);
            $path = Storage::putFile($file);
            $paths[] = $path;
        }
        MergeFiles::dispatch($paths, $guestId, $isPrivate);

        return \response()->json(['message' => 'processing started']);
    }
    public function bpmFinder(): JsonResponse {

        $user = Request::user();
        $isPrivate = $user ? true : false;
        $guestId = Request::input('guestId');

        if (!request()->hasFile('file')) {
            return response()->json(['error' => 'File not found.'], 400);
        }
        $file = Request::file('file');
        $fileInfo = $this->getFileInfo($file);

        BPMFinder::dispatch($fileInfo, $guestId, $isPrivate);
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
