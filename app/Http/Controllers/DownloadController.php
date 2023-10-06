<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class downloadController extends Controller
{
    public function download(Request $request)
{
    $disk = $request->get('disk');
    $path = $request->get('path');

    if (!Storage::disk($disk)->exists($path)) {
        abort(404);
    }

    return Storage::disk($disk)->download($path);
}
}
