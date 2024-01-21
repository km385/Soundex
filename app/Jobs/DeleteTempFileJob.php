<?php

namespace App\Jobs;

use App\Models\TemporarySong;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use function Laravel\Prompts\error;

class DeleteTempFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $id)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $tempFile = TemporarySong::find($this->id);

        if ($tempFile) {
            Storage::delete($tempFile->song_path);
            $tempFile->delete();

        }


    }
}
