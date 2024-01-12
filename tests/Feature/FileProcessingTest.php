<?php

namespace Tests\Feature;

use App\Events\FileReadyToDownload;
use App\Jobs\BPMFinder;
use App\Jobs\ChangeMetadata;
use App\Jobs\ChangeVolume;
use App\Jobs\ConvertFile;
use App\Jobs\CutFile;
use App\Jobs\DiagnoseFile;
use App\Jobs\MergeFiles;
use App\Jobs\MixFile;
use App\Jobs\Recorder;
use App\Jobs\SpeedUpFile;
use App\Jobs\VideoToAudio;
use App\Models\SuccessfulJobs;
use App\Models\TemporarySong;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\FFProbe;
use Tests\TestCase;

class FileProcessingTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_speedup_job() {
        Storage::fake();

        $sourcePath = base_path('/tests/testing_files/song.mp3');
        $filePath = Storage::putFile($sourcePath);

        $fileInfo = [
            'originalName' => 'song',
            'originalExt' => 'mp3',
            'path' => $filePath,
        ];

        (new SpeedUpFile($fileInfo, 1.2, 1.3, false, '123', false))->handle();

        $this->assertDatabaseCount(TemporarySong::class, 1);
        $this->assertDatabaseCount(SuccessfulJobs::class, 1);

        $tempFile = TemporarySong::first();

        $FFProbe = FFProbe::create()
            ->getFFProbeDriver()
            ->command([
                '-v', 'quiet',
                '-print_format', 'json',
                '-show_format',
                Storage::path($tempFile->song_path)
            ]);
        $arr = json_decode($FFProbe, 1);
        $referenceFilePath = Storage::putFile($sourcePath);
        $FFProbe = FFProbe::create()
            ->getFFProbeDriver()
            ->command([
                '-v', 'quiet',
                '-print_format', 'json',
                '-show_format',
                Storage::path($referenceFilePath)
            ]);
        $refArr = json_decode($FFProbe, 1);

        $this->assertLessThan($refArr['format']['duration'], $arr['format']['duration']);
    }

    public function test_convert_job() {
        Storage::fake();

        $sourcePath = base_path('/tests/testing_files/song2.opus');
        $filePath = Storage::putFile($sourcePath);

        $fileInfo = [
            'originalName' => 'song',
            'originalExt' => 'opus',
            'path' => $filePath,
        ];

        (new ConvertFile($fileInfo, 'mp3', '192', '123', false, false))->handle();

        $this->assertDatabaseCount(TemporarySong::class, 1);
        $this->assertDatabaseCount(SuccessfulJobs::class, 1);

        $tempFile = TemporarySong::first();

        $FFProbe = FFProbe::create()
            ->getFFProbeDriver()
            ->command([
                '-v', 'quiet',
                '-print_format', 'json',
                '-show_format',
                '-show_streams',
                Storage::path($tempFile->song_path)
            ]);
        $arr = json_decode($FFProbe, 1);

        $this->assertEquals('mp3', $arr['streams'][0]['codec_name']);
        $this->assertEquals('192000', $arr['streams'][0]['bit_rate']);
    }

    public function test_change_metadata_job() {
        Storage::fake();

        $sourcePath = base_path('/tests/testing_files/song.mp3');
        $filePath = Storage::putFile($sourcePath);

        $fileInfo = [
            'originalName' => 'song',
            'originalExt' => 'mp3',
            'path' => $filePath,
        ];

        $newExtension = 'mp3';
        $newCoverPath = null;

        $metadata = [
            'title' => 'My Song',
            'artist' => 'Lil Nas X',
            'year' => 2022,
            'genre' => 'Pop',
            'album' => 'Greatest Hits',
            'composer' => 'Jane Smith',
            'comment' => 'Awesome song!',
            'copyrightMessage' => 'All rights reserved',
            'publisher' => 'Music Publishing Inc.',
            'trackNumber' => 1,
            'lyrics' => 'La la la...'
        ];

        (new ChangeMetadata($fileInfo, $newCoverPath, $metadata, $newExtension, '123', false, false))->handle();

        $this->assertDatabaseCount(TemporarySong::class, 1);
        $this->assertDatabaseCount(SuccessfulJobs::class, 1);

        $tempFile = TemporarySong::first();

        $FFProbe = FFProbe::create()
            ->getFFProbeDriver()
            ->command([
                '-v', 'quiet',
                '-print_format', 'json',
                '-show_format',
                '-show_streams',
                Storage::path($tempFile->song_path)
            ]);
        $arr = json_decode($FFProbe, 1);
        unset($arr['format']['tags']['encoder']);

        $this->assertEquals($metadata, $arr['format']['tags']);
    }

    public function test_video_to_audio_job() {
        Storage::fake();

        $sourcePath = base_path('/tests/testing_files/video.webm');
        $filePath = Storage::putFile($sourcePath);

        $fileInfo = [
            'originalName' => 'video',
            'originalExt' => 'webm',
            'path' => $filePath,
        ];

        (new VideoToAudio($fileInfo, '123', false, false))->handle();

        $this->assertDatabaseCount(TemporarySong::class, 1);
        $this->assertDatabaseCount(SuccessfulJobs::class, 1);

        $tempFile = TemporarySong::first();

        $FFProbe = FFProbe::create()
            ->getFFProbeDriver()
            ->command([
                '-v', 'quiet',
                '-print_format', 'json',
                '-show_format',
                '-show_streams',
                Storage::path($tempFile->song_path)
            ]);
        $arr = json_decode($FFProbe, 1);

        $this->assertEquals('mp3', $arr['streams'][0]['codec_name']);
        $this->assertEquals('audio', $arr['streams'][0]['codec_type']);

    }

    public function test_cutFile_job() {
        Storage::fake();

        $sourcePath = base_path('/tests/testing_files/song.mp3');
        $filePath = Storage::putFile($sourcePath);

        $fileInfo = [
            'originalName' => 'song',
            'originalExt' => 'mp3',
            'path' => $filePath,
        ];

        $start = 32;
        $end = 84;
        $params = [
            'start' => $start,
            'end' => $end,
            'start2' => null,
            'end2' => null,
        ];

        (new CutFile($params, $fileInfo, '123', false, false))->handle();

        $this->assertDatabaseCount(TemporarySong::class, 1);
        $this->assertDatabaseCount(SuccessfulJobs::class, 1);

        $tempFile = TemporarySong::first();

        $FFProbe = FFProbe::create()
            ->getFFProbeDriver()
            ->command([
                '-v', 'quiet',
                '-print_format', 'json',
                '-show_format',
                '-show_streams',
                Storage::path($tempFile->song_path)
            ]);
        $arr = json_decode($FFProbe, 1);

        $this->assertEqualsWithDelta($end - $start, (float)$arr['streams'][0]['duration'], 1.0);
    }

    public function test_change_volume_job() {
        Storage::fake();

        $sourcePath = base_path('/tests/testing_files/song.mp3');
        $filePath = Storage::putFile($sourcePath);

        $fileInfo = [
            'originalName' => 'song',
            'originalExt' => 'mp3',
            'path' => $filePath,
        ];

        (new ChangeVolume($fileInfo, '0.8', '123', false , false))->handle();

        $this->assertDatabaseCount(TemporarySong::class, 1);
        $this->assertDatabaseCount(SuccessfulJobs::class, 1);
    }

    public function test_bpm_job() {
        $initialDispatcher = Event::getFacadeRoot();
        Event::fake();
        Model::setEventDispatcher($initialDispatcher);
        Storage::fake();

        $sourcePath = base_path('/tests/testing_files/song.mp3');
        $filePath = Storage::putFile($sourcePath);

        $fileInfo = [
            'originalName' => 'song',
            'originalExt' => 'mp3',
            'path' => $filePath,
        ];

        (new BPMFinder($fileInfo, '123', false, false))->handle();
        $this->assertDatabaseCount(TemporarySong::class, 1);
        $this->assertDatabaseCount(SuccessfulJobs::class, 1);


        Event::assertDispatched(function (FileReadyToDownload $event) {
            return $event->bpmArray[0]['BPM'] === 120.0 && $event->bpmArray[1]['BPM'] === 120 && $event->user == "123";
        });
    }

    public function test_diagnosis_job() {
        $initialDispatcher = Event::getFacadeRoot();
        Event::fake();
        Model::setEventDispatcher($initialDispatcher);
        Storage::fake();

        $sourcePath = base_path('/tests/testing_files/song.mp3');
        $filePath = Storage::putFile($sourcePath);

        $fileInfo = [
            'originalName' => 'song',
            'originalExt' => 'mp3',
            'path' => $filePath,
        ];

        (new DiagnoseFile($fileInfo, '123', false))->handle();

        $this->assertDatabaseCount(SuccessfulJobs::class, 1);
        Event::assertDispatched(function (FileReadyToDownload $event) {
            $res = json_decode($event->fileName);

            return $res->numberOfErrors === 0 &&
                $res->name === "song" &&
                $res->extension === "mp3" &&
                $res->bitrate === "128000" &&
                $res->duration === "145.632000" &&
                $res->sample_rate === "48000";
        });
    }

    public function test_merge_job() {
        Storage::fake();

        $sourcePath = base_path('/tests/testing_files/song.mp3');
        $filePath = Storage::putFile($sourcePath);

        $sourcePath2 = base_path('/tests/testing_files/song2.opus');
        $filePath2 = Storage::putFile($sourcePath2);

        $paths = [
            $filePath, $filePath2
        ];

        (new MergeFiles($paths, '123', false, false))->handle();

        $this->assertDatabaseCount(TemporarySong::class, 1);
        $this->assertDatabaseCount(SuccessfulJobs::class, 1);

        $tempFile = TemporarySong::first();

        $FFProbe = FFProbe::create()
            ->getFFProbeDriver()
            ->command([
                '-v', 'quiet',
                '-print_format', 'json',
                '-show_format',
                '-show_streams',
                Storage::path($tempFile->song_path)
            ]);
        $arr = json_decode($FFProbe, 1);
        $filePath = Storage::putFile($sourcePath);
        $filePath2 = Storage::putFile($sourcePath2);

        $FFProbe = FFProbe::create()
            ->getFFProbeDriver()
            ->command([
                '-v', 'quiet',
                '-print_format', 'json',
                '-show_format',
                '-show_streams',
                Storage::path($filePath)
            ]);
        $ref1 = json_decode($FFProbe, 1);

        $FFProbe = FFProbe::create()
            ->getFFProbeDriver()
            ->command([
                '-v', 'quiet',
                '-print_format', 'json',
                '-show_format',
                '-show_streams',
                Storage::path($filePath2)
            ]);
        $ref2 = json_decode($FFProbe, 1);

        $file1Duration = $ref1['streams'][0]['duration'];
        $file2Duration = $ref2['streams'][0]['duration'];
        $outputFileDuration = $arr['streams'][0]['duration'];

        $this->assertEqualsWithDelta($file1Duration + $file2Duration, $outputFileDuration, 1);

    }

    public function test_layer_mixer_job() {
        Storage::fake();

        $sourcePath = base_path('/tests/testing_files/song.mp3');
        $foregroundPath = Storage::putFile($sourcePath);

        $sourcePath2 = base_path('/tests/testing_files/song2.opus');
        $backgroundPath = Storage::putFile($sourcePath2);

        (new MixFile($backgroundPath, $foregroundPath, '123', false, false))->handle();

        $this->assertDatabaseCount(TemporarySong::class, 1);
        $this->assertDatabaseCount(SuccessfulJobs::class, 1);

        $tempFile = TemporarySong::first();

        $FFProbe = FFProbe::create()
            ->getFFProbeDriver()
            ->command([
                '-v', 'quiet',
                '-print_format', 'json',
                '-show_format',
                '-show_streams',
                Storage::path($tempFile->song_path)
            ]);
        $arr = json_decode($FFProbe, 1);

        $filePath = Storage::putFile($sourcePath2);

        $FFProbe = FFProbe::create()
            ->getFFProbeDriver()
            ->command([
                '-v', 'quiet',
                '-print_format', 'json',
                '-show_format',
                '-show_streams',
                Storage::path($filePath)
            ]);
        $ref1 = json_decode($FFProbe, 1);

        $outputFileDuration = (float)$arr['streams'][0]['duration'];
        $referenceFileDuration = (float)$ref1['streams'][0]['duration'];
        $this->assertEqualsWithDelta($referenceFileDuration, $outputFileDuration, 1);

    }

}
