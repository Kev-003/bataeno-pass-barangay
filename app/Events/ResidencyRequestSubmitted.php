<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResidencyRequestSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $request;
    public $residentName;

    public function __construct($request)
    {
        $this->request = $request;
        $this->residentName = $request->user->name;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('barangay.' . $this->request->barangay_id . '.requests'),
        ];
    }

    public function broadcastAs()
    {
        return 'ResidencyRequestSubmitted';
    }

    public function broadcastWith()
    {
        return [
            'request' => $this->request,
            'residentName' => $this->residentName,
        ];
    }
}
