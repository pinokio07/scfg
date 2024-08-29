<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScanHouse implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $jenis, $house, $status, $info;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($jenis, $house, $status, $info)
    {
        $this->jenis = $jenis;
        $this->house = $house;
        $this->status = $status;
        $this->info = $info;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('scan-'.$this->jenis);
    }

    public function broadcastWith(): array
    {
        return [
          'id' => $this->house,
          'status' => $this->status,
          'info' => $this->info,
        ];
    }
}
