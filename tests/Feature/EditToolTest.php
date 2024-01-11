<?php

namespace Tests\Feature;

use App\Jobs\Recorder;
use App\Models\SuccessfulJobs;
use App\Models\TemporarySong;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditToolTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_endpoint_and_job(): void
    {

        Storage::fake();

        $sourcePath = base_path('/tests/testing_files/song.mp3');
        $file1 = new UploadedFile($sourcePath, 'song1.mp3', 'audio/mpeg');

        $sourcePath = base_path('/tests/testing_files/song2.opus');
        $file2 = new UploadedFile($sourcePath, 'song2.opus', 'audio/opus');

        $response = $this->post('/tools/recorder', [
            'recording' => $file1,
            'background' => $file2
        ]);

        $response->assertStatus(200);
    }

    /*
     * podczas sleepa, plik znajduje sie tak gdzie sie powinien znajdowac, temporary file jest w bazie.
     * gdy wszystko sie konczy, temp jest usuwany, plik jest usuwany, tylko logsuccessful dziala
     *
     * */


    public function test_push_job(): void
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

    public function test_job() {
        Storage::fake();
        $sourcePath = base_path('/tests/testing_files/song.mp3');
        $filePath = Storage::putFile($sourcePath);

        $sourcePath2 = base_path('/tests/testing_files/song2.opus');
        $filePath2 = Storage::putFile($sourcePath2);


        (new Recorder($filePath, $filePath2, '43232', false))->handle();


        $this->assertDatabaseCount(TemporarySong::class, 1);
        $this->assertDatabaseCount(SuccessfulJobs::class, 1);
    }

}
