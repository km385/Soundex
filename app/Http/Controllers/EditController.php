<?php

namespace App\Http\Controllers;

use App\Events\FileReadyToDownload;
use App\Jobs\ChangeMetadata;
use App\Jobs\CutFile;
use App\Jobs\MergeFiles;
use App\Jobs\MixFile;
use App\Jobs\Recorder;
use App\Jobs\SpeedUpFile;
use App\Models\Song;
use App\Models\TemporaryFile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function Laravel\Prompts\error;

class EditController extends Controller
{
    public function downloadFile ($token): StreamedResponse|JsonResponse
    {
        if(!Request::hasValidSignature(false)){
            error_log('expired url');
            return response()->json(['message' => 'expired url'], 500);
        }
        $file = TemporaryFile::where('token', $token)->first();
        if($file) {
            $filePath = $file->filePath;
            error_log($filePath);
            return Storage::disk('')->download($filePath);
        }
        error_log('no file');

        return response()->json(['message' => 'no file']);

    }

    public function saveToLibrary(): JsonResponse
    {
        $user = Auth::user();
        if(!$user){
            return response()->json(['message' => 'Unauthorized'], 401);
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
        $file = TemporaryFile::where('token', $token)->first();
        $song = new Song();
        $song->name = 'tytul';
        $song->songPath = $file->filePath;
        $user->songs()->save($song);
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

    public function metachange() {
        $user = Request::user();

        if(!$user){
            $isPrivate = false;
        } else {
            $isPrivate = true;
        }

        $guestId = Request::input('guestId');
        $author = Request::input('author');
        $genre = Request::input('genre');
        $title = Request::input('title');
        $year = Request::input('year');

        $audioFile = Request::file('fileRef');

        if (Request::hasFile('coverRef')) {
            $coverFile = Request::file('coverRef');
            $coverPath = Storage::putFile($coverFile);
        } else {
            $coverPath = null;
        }


        $audioPath = Storage::putFile($audioFile);

        $metadata = [];

        if (!empty($title)) {
            $metadata['title'] = $title;
        }

        if (!empty($author)) {
            $metadata['artist'] = $author;
        }

        // TODO windows context menu używa 'date' jako year
        if (!empty($year)) {
            $metadata['year'] = $year;
        }

        if (!empty($genre)) {
            $metadata['genre'] = $genre;
        }
        error_log("prepering");

        ChangeMetadata::dispatch($audioPath, $coverPath, $metadata, $guestId, $isPrivate);

        return response()->json(['message' => 'success']);
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

        // todo move to jobs
        if(!Request::hasFile('file')){
            return \response()->json(['message' => 'no file']);
        }
        $file = Request::file('file');
        $path = Storage::putFile($file);
        $tempo = Request::input('tempoValue');
        $speed = Request::input('speedValue');
        $guestId = Request::input('guestId');
        error_log($isPrivate);
        error_log($guestId);
        SpeedUpFile::dispatch($path, $tempo, $speed, $isPrivate, $guestId);

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
        $path = Storage::putFile($file);
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

        CutFile::dispatch($af, $path, $guestId, $isPrivate);
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
}
