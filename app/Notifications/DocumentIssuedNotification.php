<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\DocumentTransaction;

class DocumentIssuedNotification extends Notification
{
    use Queueable;

    public DocumentTransaction $transaction;

    public function __construct(DocumentTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Document Issued!',
            'body' => 'Your ' . ($this->transaction->documentTypeProperty->name ?? 'Document') . ' has been issued and is ready for download.',
            'transaction_id' => $this->transaction->id,
            'type' => 'document_issued',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
