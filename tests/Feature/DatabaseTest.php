<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Song;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\UploadedFile;

class DatabaseTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_database_is_not_accessible_to_guest(): void
    {
        $response = $this
            ->get('/database');

        $response
            ->assertStatus(302)
            ->assertRedirect('/login');
    }
    public function test_database_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/database');

        $response
            ->assertStatus(200);
    }

    public function test_songs_can_be_played()
    {
        Storage::fake();
        $user = User::factory()->create();
        $this->actingAs($user);

        $sourcePath = base_path('/tests/testing_files/song.mp3');
        $destinationPath = "user_files/{$user->id}/song.mp3";
        
        Storage::copy($sourcePath, $destinationPath);
        
        $song = Song::create([
            'title' => 'song',
            'user_id' => $user->id,
            'extension' => 'mp3',
            'size_kb' => '1500',
            'song_path' => 'song.mp3',
        ]);
        
       $response = $this
       ->actingAs($user)
       ->json('POST', route('generateUrlForSong', ['songId' => $song->id]));
 
       $response->assertStatus(200);
    }
    public function test_songs_metadata_is_extracted_when_uploaded()
    {

        $user = User::factory()->create();
        $sourcePath = base_path('/tests/testing_files/song.mp3');
        $file = new UploadedFile($sourcePath, 'song.mp3', 'audio/mpeg', null, true);
    
        $response = $this
        ->actingAs($user)
        ->json('POST', '/database/upload', ['file' => $file]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('songs', 1);
        $this->assertDatabaseHas('songs', [
            'user_id' => $user->id,
            'album' => 'testAlbum',
        ]);
    }
    public function test_song_title_can_be_changed()
    {
        $user = User::factory()->create();
        $song = Song::create([
            'title' => 'original_title',
            'user_id' => $user->id,
            'extension' => 'mp3',
            'size_kb' => '1500',
            'song_path' => 'original_title.mp3',
        ]);

        $response = $this
        ->actingAs($user)
        ->post("/database/songs/change/{$song->id}", [
            'title' => 'new_title',
        ]);
        $response->assertStatus(200);
        $updatedSong = $song->fresh();
        $this->assertEquals('original_title', $updatedSong->title);
    }

    public function test_user_cant_remove_not_his_song()
    {

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $song = Song::create([
            'title' => 'song',
            'user_id' => $user1->id,
            'extension' => 'mp3',
            'size_kb' => '1500',
            'song_path' => 'song.mp3',
        ]);
        
        $response = $this
        ->actingAs($user2)
        ->delete(route('song.destroy', $song));

        $response->assertStatus(403);
        $this->assertDatabaseHas('songs', ['id' => $song->id]);
    }

    public function test_user_can_remove_his_song()
    {
        $user = User::factory()->create();

        $song = Song::create([
            'title' => 'song',
            'user_id' => $user->id,
            'extension' => 'mp3',
            'size_kb' => '1500',
            'song_path' => 'song.mp3',
        ]);

        $this->assertDatabaseHas('songs', ['id' => $song->id]);
        $response = $this
        ->actingAs($user)
        ->delete(route('song.destroy', $song));
        $response->assertStatus(200);
        $this->assertDatabaseCount('songs', 0);
    }

}

