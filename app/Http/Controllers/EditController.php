<?php

namespace App\Http\Controllers;

use App\Events\FileReadyToDownload;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use function Laravel\Prompts\error;

class EditController extends Controller
{
    public function cut(): JsonResponse
    {
        $user = Request::user();

        if(!$user){
            $userId = 123;
        } else {
            $userId = $user->id;
        }
        error_log($userId);
        event(new FileReadyToDownload('plik', $userId));
        error_log('po evencie');
        return response()->json(['message' => 'success']);
    }
}
