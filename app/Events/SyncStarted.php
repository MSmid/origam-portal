<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SyncStarted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $dataSourceId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($dataSourceId)
    {
        $this->dataSourceId = $dataSourceId;
    }
}
