<?php

namespace App\Http\Controllers;

use App\Events\FileReadyToDownload;
use App\Jobs\CutFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use function Laravel\Prompts\error;

class EditController extends Controller
{
    public function cut(): JsonResponse
    {
        $user = Request::user();

        if(!$user){
            $userId = 123;
            $isPrivate = false;
        } else {
            $userId = $user->id;
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
}
