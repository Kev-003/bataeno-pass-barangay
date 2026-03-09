<?php

namespace App\Jobs;

use App\Models\DocumentTransaction;
use App\Models\BarangayTerm;
use App\Events\DocumentIssued;
use App\Services\DocumentApprovalService;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDocumentApproval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        public DocumentTransaction $transaction,
        public BarangayTerm $official,
        public string $signatureMode = 'esign'
    ) {
    }

    public function handle(DocumentApprovalService $service): void
    {
        // 1. Generate and sign the document
        $service->generateAndSign(
            $this->transaction,
            $this->official,
            $this->signatureMode
        );

        // 2. Notify resident
        Notification::make()
            ->title('Document Ready')
            ->body(
                'Your ' .
                ($this->transaction->documentType->name ?? 'Document') .
                ' has been approved and is ready for download.'
            )
            ->icon('heroicon-o-document-check')
            ->success()
            ->actions([
                Action::make('view')
                    ->label('View Document')
                    ->url(route('documents'))
                    ->markAsRead(),
            ])
            ->sendToDatabase($this->transaction->requester)
            ->broadcast($this->transaction->requester);
    }

    public function failed(\Throwable $e): void
    {
        \Illuminate\Support\Facades\Log::error('ProcessDocumentApproval job failed', [
            'transaction_id' => $this->transaction->id,
            'error' => $e->getMessage(),
        ]);

        // Revert status so official can retry
        $this->transaction->update(['status' => 'pending']);
    }
}