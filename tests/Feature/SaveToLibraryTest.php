<?php

namespace Tests\Feature;

use App\Models\Song;
use App\Models\TemporarySong;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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
}
