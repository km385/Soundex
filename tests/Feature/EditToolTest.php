<?php

namespace Tests\Feature;

use App\Jobs\Recorder;
use App\Models\SuccessfulJobs;
use App\Models\TemporarySong;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_endpoint(): void
    {
        Storage::fake();
        Queue::fake();

        $sourcePath = base_path('/tests/testing_files/song.mp3');
        $file1 = new UploadedFile($sourcePath, 'song1.mp3', 'audio/mpeg');

        $sourcePath = base_path('/tests/testing_files/song2.opus');
        $file2 = new UploadedFile($sourcePath, 'song2.opus', 'audio/opus');

        $response = $this->post('/tools/recorder', [
            'recording' => $file1,
            'background' => $file2
        ]);


        Queue::assertPushed(Recorder::class);
    }

    public function test_recorder_job() {
        Storage::fake();

        $sourcePath = base_path('/tests/testing_files/song.mp3');
        $filePath = Storage::putFile($sourcePath);

        $sourcePath2 = base_path('/tests/testing_files/song2.opus');
        $filePath2 = Storage::putFile($sourcePath2);


        (new Recorder($filePath, $filePath2, '43232', false, false))->handle();

        $tempFilePath = TemporarySong::first()->song_path;


        Storage::assertExists($tempFilePath);

        Storage::checksum($sourcePath);
        Storage::checksum($sourcePath2);
        Storage::checksum($tempFilePath);

        $this->assertNotEquals($sourcePath, $tempFilePath);
        $this->assertNotEquals($sourcePath2, $tempFilePath);


        $this->assertDatabaseCount(TemporarySong::class, 1);
        $this->assertDatabaseCount(SuccessfulJobs::class, 1);
    }

}
