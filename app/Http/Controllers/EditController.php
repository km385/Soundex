<?php

namespace App\Http\Controllers;

use App\Events\FileReadyToDownload;
use App\Jobs\CutFile;
use App\Jobs\MergeFiles;
use App\Models\Song;
use App\Models\TemporaryFile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function Laravel\Prompts\error;

class EditController extends Controller
{
    public function downloadFile ($token): StreamedResponse|JsonResponse
    {
        $file = TemporaryFile::where('token', $token)->first();
        if($file) {
            $filePath = $file->filePath;
            error_log($filePath);
            return Storage::disk('')->download($filePath);
        }
        error_log('no file');

        return response()->json(['message' => 'no file']);

    }

    public function saveToLibrary() {
        $user = Auth::user();
        if(!$user){
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $token = Request::input('token');
        $file = TemporaryFile::where('token', $token)->first();
        $song = new Song();
        $song->name = 'tytul';
        $song->songPath = $file->filePath;
        $user->songs()->save($song);
        return response()->json(['message' => 'success']);


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
