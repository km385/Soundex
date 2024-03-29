<?php

use App\Http\Controllers\EditController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\SuccessfulJobsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DownloadController;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

// Q&A
Route::get('/help', function () {
    return Inertia::render('Help');
});

//Tools Files Managment
Route::get('/files/{fileName}', [EditController::class, 'downloadFile'])->name('downloadFile');
Route::get('/file/{fileName}', [EditController::class, 'downloadDiagnosticFile'])->name('downloadDiagnoseFile');
Route::post('/savetolibrary', [EditController::class, 'saveToLibrary']);
Route::get('/songs', [SongController::class, 'getSongs']);
Route::get('/songs/{id}', [SongController::class, 'getSongById']);
Route::get('/jobs', [SuccessfulJobsController::class, 'show']);
// Tools
Route::prefix('tools')->name('tools.')->group(function () {

    Route::get('/', function () {
        return redirect()->route('tools.cutter');
    });

    Route::get('/cutter', function () {
        return Inertia::render('Tools/Cutter');
    })->name('cutter');
    Route::post('/cutFile', [EditController::class, 'cut']);

    Route::get('/merge', function () {
        return Inertia::render('Tools/Merge');
    })->name('merge');
    Route::post('/merge', [EditController::class, 'merge']);

    Route::get('/converter', function () {
        return Inertia::render('Tools/Converter');
    })->name('converter');
    Route::post('/converter', [EditController::class, 'convert']);

    Route::get('/speedup', function () {
        return Inertia::render('Tools/SpeedUp');
    })->name('speedup');
    Route::post('/speedup', [EditController::class, 'speedup']);

    Route::get('/recorder', function () {
        return Inertia::render('Tools/Recorder');
    })->name('recorder');
    Route::post('/recorder', [EditController::class, 'recorder']);

    Route::get('/tageditor', function () {
        return Inertia::render('Tools/TagEditor');
    })->name('tagEditor');
    Route::post('/tageditor', [EditController::class, 'tageditor']);

    Route::get('/layermixer', function () {
        return Inertia::render('Tools/LayerMixer');
    })->name('layerMixer');
    Route::post('/layermixer', [EditController::class, 'layerMixer']);

    Route::get('/volumechanger', function () {
        return Inertia::render('Tools/VolumeChanger');
    })->name('volumechanger');
    Route::post('/volumechanger', [EditController::class, 'volumeChanger']);

    Route::get('/bpmFinder', function () {
        return Inertia::render('Tools/BPMFinder');
    })->name('BPM Finder');
    Route::post('/bpmFinder', [EditController::class, 'bpmFinder']);

    Route::get('/diagnosis', function () {
        return Inertia::render('Tools/Diagnosis');
    })->name('BPM Finder');
    Route::post('/diagnosis', [EditController::class, 'diagnosis']);

    Route::get('/videotoaudio', function () {
        return Inertia::render('Tools/VideoToAudio');
    })->name('videotoaudio');
    Route::post('/videotoaudio', [EditController::class, 'videoToAudio']);

    Route::get('/index', function () {
        return Inertia::render('Tools/Index');
    })->name('index');
});

//User Profile
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/edit', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/edit', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//Admin Panel
Route::get('/users', [ProfileController::class, 'getAllUsers'])->name('getAllUsers');
Route::delete('/users/{id}', [ProfileController::class, 'destroyUsers'])->name('deleteUser');
Route::patch('/users/{id}', [ProfileController::class, 'updateUsers'])->name('updateUser');
Route::get('/jobStatistics', [SuccessfulJobsController::class, 'statistics']);
Route::get('/jobsIndex', [SuccessfulJobsController::class, 'index']);
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/adminPanel', function () {
        return Inertia::render('AdminPanel');
    })->name('adminPanel');;
});

//Database 
Route::middleware(['auth'])->group(function () {
    Route::get('/database', [SongController::class, 'index'])->name('Database');
    Route::delete('/database/songs/{song}', [SongController::class, 'destroy'])->name('song.destroy');
    Route::post('/database/upload', [SongController::class, 'songUpload'])->name('song.upload');
    Route::get('/database/get', [SongController::class, 'getSongs'])->name('song.upload');
});
//Route::get('/song-covers/{filename}', [SongController::class, 'getCover']);
Route::get('/musicplayer-song', [SongController::class, 'download'])->name('musicplayer-song');
Route::post('/database/songs/{songId}', [SongController::class, 'downloadSong'])->name('generateUrlForSong');
Route::post('/database/songs/change/{songId}', [SongController::class, 'changeMetadata']);

require __DIR__ . '/auth.php';


