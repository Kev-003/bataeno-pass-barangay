<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\DocumentTransaction;

class DocumentRequestCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public DocumentTransaction $transaction;

    public function __construct(DocumentTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Broadcast on a private channel per barangay.
     * Channel name: barangay.{barangay_id}.requests
     * Officials listening on this channel get notified of new requests.
     */
    public function broadcastOn(): array
    {
        // Use the PSGC code from the related barangay model
        $psgc = $this->transaction->barangay->barangay_code;
        return [
            new PrivateChannel('barangay.' . $psgc . '.requests'),
        ];
    }

    /**
     * Data sent to the front-end in the event payload.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->transaction->id,
            'document_type' => $this->transaction->documentTypeProperty->name ?? 'Document',
            'requester' => $this->transaction->requester->name ?? 'Resident',
            'created_at' => $this->transaction->created_at->diffForHumans(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'DocumentRequestCreated';
    }
}
