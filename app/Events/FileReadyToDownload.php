<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FileReadyToDownload implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $fileName;
    public $user;
    public $bpmArray;

    /**
     * Create a new event instance.
     *
     * @param string $fileName
     * @param int $user
     * @param array|null $bpmArray
     */
    public function __construct($fileName, $user, $bpmArray = null)
    {
        $this->fileName = $fileName;
        $this->user = $user;
        $this->bpmArray = $bpmArray;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('fileUpload.'.$this->user),
        ];
    }
}
