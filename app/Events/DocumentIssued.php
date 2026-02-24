<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\DocumentTransaction;

class DocumentIssued implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public DocumentTransaction $transaction;

    public function __construct(DocumentTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Broadcast on a private channel per resident (requester).
     * Channel name: resident.{user_id}.documents
     * Residents listening on this channel are notified when their doc is issued.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('resident.' . $this->transaction->requester_id . '.documents'),
        ];
    }

    /**
     * Data sent to the front-end.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->transaction->id,
            'document_type' => $this->transaction->documentTypeProperty->name ?? 'Document',
            'issued_at' => now()->format('F d, Y h:i A'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'DocumentIssued';
    }
}
