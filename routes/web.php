<?php

use App\Http\Controllers\EditController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SongController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DownloadController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/files/{fileName}', [EditController::class, 'downloadFile'])->name('downloadFile');

Route::post('/savetolibrary', [EditController::class, 'saveToLibrary']);

// Tools routes
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

    Route::get('/bpmFinder', function () {
        return Inertia::render('Tools/BPMFinder');
    })->name('BPM Finder');
    Route::post('/bpmFinder', [EditController::class, 'bpmFinder']);

    Route::get('/videotoaudio', function () {
        return Inertia::render('Tools/VideoToAudio');
    })->name('videotoaudio');
    Route::post('/videotoaudio', [EditController::class, 'videoToAudio']);
});

//Database routes
Route::middleware(['auth'])->group(function () {
    Route::get('/database', [SongController::class, 'index'])->name('Database');
    Route::delete('/database/songs/{song}', [SongController::class, 'destroy'])->name('Database.destroy');

});
//
Route::post('/database/songs/{songId}', [SongController::class, 'post']);
Route::get('/musicplayer-song', [DownloadController::class, 'download'])->name('musicplayer-song');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
