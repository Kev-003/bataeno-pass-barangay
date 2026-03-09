<?php

namespace App\Jobs;

use App\Models\DocumentTransaction;
use App\Models\User;
use App\Events\DocumentRequestCreated;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDocumentRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        public DocumentTransaction $transaction
    ) {
    }

    public function handle(): void
    {
        // 1. Broadcast to officials via Reverb
        DocumentRequestCreated::dispatch($this->transaction);

        // 2. Notify officials
        $officials = User::officialsForBarangay(
            $this->transaction->barangay->barangay_code
        )->get();

        foreach ($officials as $official) {
            Notification::make()
                ->title('New Document Request')
                ->body(
                    ($this->transaction->requester->name ?? 'Resident') .
                    ' requested a ' .
                    ($this->transaction->documentType->name ?? 'Document')
                )
                ->icon('heroicon-o-document-text')
                ->warning()
                ->actions([
                    Action::make('view')
                        ->label('View Request')
                        ->url(route('filament.official.resources.document-transactions.index', [
                            'tenant' => $this->transaction->barangay->barangay_code,
                        ]))
                        ->markAsRead(),
                ])
                ->sendToDatabase($official)
                ->broadcast($official);
        }

        // 3. Notify resident
        Notification::make()
            ->title('Document Request Sent')
            ->body(
                'Your request for ' .
                ($this->transaction->documentType->name ?? 'Document') .
                ' has been received.'
            )
            ->icon('heroicon-o-document-text')
            ->warning()
            ->actions([
                Action::make('view')
                    ->label('View Request')
                    ->url(route('documents'))
                    ->markAsRead(),
            ])
            ->sendToDatabase($this->transaction->requester)
            ->broadcast($this->transaction->requester);
    }

    public function failed(\Throwable $e): void
    {
        \Illuminate\Support\Facades\Log::error('ProcessDocumentRequest job failed', [
            'transaction_id' => $this->transaction->id,
            'error' => $e->getMessage(),
        ]);
    }
}