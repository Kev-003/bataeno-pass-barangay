<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\DocumentTransaction;

class DocumentRequestReceived extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected DocumentTransaction $request)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }




    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Action Required: New Document Request')
            ->greeting('Hello Official,')
            ->line('A new request for ' . $this->request->documentType->name . ' has been received.') // ✅ fixed
            ->line('Requester: ' . $this->request->requester->name) // use ->name if that's the accessor
            ->action('View in Dashboard', route('official.dashboard', ['barangay_code' => $this->request->barangay->barangay_code]))
            ->line('Please review this at your earliest convenience.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Document Request',
            'body' => ($this->request->requester->name ?? 'Resident') . ' requested a ' . ($this->request->documentType->name ?? 'Document'), // ✅ fixed
            'transaction_id' => $this->request->id,
            'type' => 'document_request',
            'icon' => 'heroicon-o-document-text',   // optional but good for Filament UI
            'color' => 'warning',                    // optional but good for Filament UI
        ];
    }

    public function toBroadcast(object $notifiable): array
    {
        return [
            'title' => 'New Document Request',
            'body' => ($this->request->requester->name ?? 'Resident') . ' requested a ' . ($this->request->documentType->name ?? 'Document'), // ✅ fixed
            'transaction_id' => $this->request->id,
            'type' => 'document_request',
            'icon' => 'heroicon-o-document-text',   // optional but good for Filament UI
            'color' => 'warning',                    // optional but good for Filament UI
        ];
    }
}
