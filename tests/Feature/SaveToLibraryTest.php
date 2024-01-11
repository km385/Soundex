<?php

namespace Tests\Feature;

use App\Models\Song;
use App\Models\TemporarySong;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class SaveToLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_temporary_file_can_be_saved(): void
    {
        $user = User::factory()->create();
        Storage::fake();
        $fakePath = Str::uuid();

        $tempFile = TemporarySong::create([
            'title' => 'song',
            'extension' => 'mp3',
            'size_kb' => '34',
            'song_path' => $fakePath,
        ]);
        $token = $tempFile->token;

        $response = $this
            ->actingAs($user)
            ->post('/savetolibrary', ['token' => $token.'?fdf']);

        $this->assertDatabaseHas('songs', [
            'title' => 'song',
            'extension' => 'mp3',
            'size_kb' => '34',
            'song_path' => $fakePath,
        ])->count(1);


        $response->assertStatus(200);
    }

    public function test_no_space_left() {
        $user = User::factory()->create();
        $user->update([
            'files_stored' => 60,
        ]);
        $fakePath = Str::uuid();

        $tempFile = TemporarySong::create([
            'title' => 'song',
            'extension' => 'mp3',
            'size_kb' => '34',
            'song_path' => $fakePath,
        ]);
        $token = $tempFile->token;

        $response = $this
            ->actingAs($user)
            ->post('/savetolibrary', ['token' => $token.'?fdf']);

        $this->assertDatabaseMissing('songs', [
            'title' => 'song',
            'extension' => 'mp3',
            'size_kb' => '34',
            'song_path' => $fakePath,
        ])->count(0);


        $response->assertStatus(401);

    }

    public function test_download_file() {
        Storage::fake();
        $sourcePath = base_path('/tests/testing_files/song.mp3');
        $filePath = Storage::putFile($sourcePath);

        $temporaryFile = TemporarySong::create([
            'title' => 'song',
            'extension' => 'mp3',
            'size_kb' => '34',
            'song_path' => $filePath,
        ]);

        $temporaryUrl = URL::temporarySignedRoute(
            'downloadFile',
            now()->addHours(1),
            ['fileName' => $temporaryFile->token],
            false
        );
        $response = $this->get($temporaryUrl);

        $response
            ->assertStatus(200)
            ->assertHeader('content-type', 'audio/mpeg')
            ->assertHeader('content-disposition', 'attachment; filename='.$filePath);

        Storage::delete($filePath);

    }

    public function test_download_when_wrong_signed_url() {
        $response = $this->get('/files/song.mp3');

        $response
            ->assertStatus(500)
            ->assertHeader('content-type', 'application/json')
            ->assertContent('{"message":"expired url"}');

    }

    public function test_download_when_no_file_found() {
        Storage::fake();
        $sourcePath = base_path('/tests/testing_files/song.mp3');
        $filePath = Storage::putFile($sourcePath);

        $temporaryFile = TemporarySong::create([
            'title' => 'song',
            'extension' => 'mp3',
            'size_kb' => '34',
            'song_path' => $filePath,
        ]);


        $temporaryUrl = URL::temporarySignedRoute(
            'downloadFile',
            now()->addHours(1),
            ['fileName' => $temporaryFile->token],
            false
        );

        $temporaryFile->delete();
        $response = $this->get($temporaryUrl);

        $response
            ->assertStatus(404)
            ->assertHeader('content-type', 'application/json');

        Storage::delete($filePath);
    }
}
