<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use App\Models\Song;

class SongController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $songs = $user->songs;

        return Inertia::render('Database/Index', [
            'songs' => $songs,
        ]);
    }
    public function destroy(Song $song)
    {
        if ($song->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        try {
            Storage::disk($song->disk)->delete($song->song_path);
        } catch (\Exception $e) {
            return response()->json(['message' => 'File not found or unable to delete'], 500);
        }
        $song->delete();
        return Redirect::route('Database')->with('message', 'Song deleted successfully');
    }

    public function post($songId)
    {
        $song = Song::find($songId);
        
        if (!$song || $song->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        $expiration = now()->addHour();
        $temporaryUrl = URL::temporarySignedRoute(
            'musicplayer-song', // Create a named route for downloading the file
            $expiration,
            ['disk' => $song->disk == null ? "" : $song->disk, 'path' => $song->song_path]
        );
    
        return response()->json(['songURL' => $temporaryUrl]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

}