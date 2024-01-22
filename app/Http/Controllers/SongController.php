<?php

namespace App\Http\Controllers;

use App\Http\UtilityClasses\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use App\Models\Song;
use function Laravel\Prompts\error;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use DateTime;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use FFMpeg\Filters\Audio\AudioFilters;

class SongController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $songs = $user->songs;

        return Inertia::render('Database', [
            'songs' => $songs,
        ]);
    }

    public function changeMetadata($songId, Request $request)
    {
        $song = Song::find($songId);
    
        if ($song->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    
        $updatedMetadata = $request->all();
        $metadata = [];
    
        $existingMetadata = [
            'title' => $song->title,
            'artist' => $song->artist,
            'year' => $song->year,
            'genre' => $song->genre,
            'album' => $song->album,
            'composer' => $song->composer,
            'comment' => $song->comment,
            'copyrightMessage' => $song->copyrightMessage,
            'publisher' => $song->publisher,
            'trackNumber' => $song->trackNumber,
            'lyrics' => $song->lyrics,
        ];
    
        $metadata = [];
    
        $fieldsToCheck = [
            'title',
            'artist',
            'year',
            'genre',
            'album',
            'composer',
            'comment',
            'copyrightMessage',
            'publisher',
            'trackNumber',
            'lyrics'
        ];
    
        foreach ($fieldsToCheck as $field) {
            $metadata[$field] = isset($updatedMetadata[$field]) ? $updatedMetadata[$field] : $existingMetadata[$field];
        }
        try {
            FFMpeg::fromDisk('')
                ->open("user_files/" .auth()->id(). '/'.$song->song_path)
                ->export()
                ->addFilter(function (AudioFilters $filters) use ($metadata) {
                    $filters->addMetadata($metadata);
                })
                ->save("user_files/" .auth()->id(). '/'.$song->song_path.'.mp3');

            $song->update($updatedMetadata);
    
        } catch (\Exception $e) {
            error_log($e);
        }
    }
    

    public function songUpload(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($user->files_stored >= 50) {
            error('no space left');
            return response()->json(['message' => 'number of files limit reached'], 1);
        }
        $file = $request->file('file');

        $tempPath = Storage::putFile($file);

        try {
            $tags = FileService::extractMetadata($tempPath);
            if (!isset($tags['tags']['year']) || !DateTime::createFromFormat('Y-m-d', $tags['tags']['year']) !== false) {
                $tags['tags']['year'] = null;
            }

            if (!isset($tags['size'])) {
                error('no size info');
                throw new \Exception();
            }
        } catch (\Exception $e) {
            error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], 3);
        }

        if (234800 - $user->storage_used <= ($tags['size'] / 1024)) {
            error('no storage left');
            return response()->json(['message' => 'no space left'], 4);
        }

        try {
            $song = new Song();
            $song->song_path = $tempPath;
            //change
            $song->extension = "mp3";
            $song->disk = "";
            $song->size_kb = $tags['size'] / 1024;
            $song->title = $tags['tags']['title'] ?? null;
            $song->album = $tags['tags']['album'] ?? null;
            $song->year = $tags['tags']['year'] ?? null;
            $song->duration_sec = $tags['duration'] ?? null;
            $song->artist = $tags['tags']['artist'] ?? null;
            ;
            $song->genre = $tags['tags']['genre'] ?? null;
            ;

            $user->songs()->save($song);
            $user->storage_used = $user->storage_used + $tags['size'] / 1024;
            $user->files_stored = $user->files_stored + 1;
            $user->save();
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        $directory = "user_files" . DIRECTORY_SEPARATOR . $user->id . DIRECTORY_SEPARATOR;
        if (Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        Storage::copy($tempPath, $directory . $tempPath);
        // Storage::Remove($tempPath);

        return response()->json(['message' => 'success']);
    }
    public function download(Request $request)
    {
        $disk = $request->get('disk');
        $path = $request->get('path');

        if (!Storage::disk($disk)->exists($path)) {
            abort(404);
        }
        return Storage::disk($disk)->download($path);
    }
    public function getSongs()
    {
        $user = auth()->user();
        if (!$user) {
            abort(404);
        }
        $songs = $user->songs;

        return response()->json(['songs' => $songs]);
    }

    public function getSongById($songId)
    {

        $user = auth()->user();
        $song = $user->songs()->where('id', $songId)->first();

        if (!$song) {
            abort(404);
        }

        $file_path = 'user_files' . DIRECTORY_SEPARATOR . $user->id . DIRECTORY_SEPARATOR . $song->song_path;

        return Storage::download($file_path);
    }

    public function destroy(Song $song)
    {
        if ($song->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
        try {
            Storage::disk($song->disk)->delete($song->song_path);
        } catch (\Exception $e) {
            return response()->json(['message' => 'File not found or unable to delete'], 6);
        }
        $song->delete();
        return response()->json(['message' => 'removed'], 200);
    }

    public function downloadSong($songId)
    {
        $song = Song::find($songId);

        try {
            if (!$song || $song->user_id !== auth()->id()) {
                error('unauthorized');
                return response()->json(['message' => 'not authorized'], 405);
            }

            $expiration = now()->addHour();

            $songPath = "user_files/" . auth()->id() . "/" . $song->song_path;

            $temporaryUrl = URL::temporarySignedRoute(
                'musicplayer-song',
                $expiration,
                ['path' => $songPath]
            );

            return response()->json(['songURL' => $temporaryUrl]);

        } catch (\Exception $e) {
            error_log('$error' . $e);
            return response()->json(['message' => 'not authorized'], 405);
        }
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
