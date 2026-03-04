<?php

namespace App\Services;

use App\Models\DocumentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Models\Barangay;


class DocumentRequestService
{
    public function createRequest(User $user, string $docTypeId, string $modelClass, string $purpose, array $dynamicFields)
    {
        return DB::transaction(function () use ($user, $docTypeId, $modelClass, $purpose, $dynamicFields) {

            $transaction = DocumentTransaction::create([
                'requester_id' => $user->id,
                'barangay_id' => $user->barangay_id,
                'document_type_id' => $docTypeId,
                'purpose' => $purpose,
                'status' => 'pending',
                'request_origin' => 'web',
            ]);

            $modelClass::create(array_merge($dynamicFields, [
                'transaction_id' => $transaction->id,
            ]));

            $this->notifyOfficials($transaction);

            return $transaction;
        });
    }

    protected function notifyOfficials($transaction)
    {
        $officials = User::officialsForBarangay($transaction->barangay->barangay_code)->get();

        if ($officials->isEmpty())
            return;

        // 1. Notify officials
        foreach ($officials as $official) {
            Notification::make()
                ->title('New Document Request')
                ->body(
                    ($transaction->requester->name ?? 'Resident') .
                    ' requested a ' .
                    ($transaction->documentType->name ?? 'Document')
                )
                ->icon('heroicon-o-document-text')
                ->warning()
                ->actions([
                    Action::make('view')
                        ->label('View Request')
                        ->url(route('filament.official.resources.document-transactions.index', [
                            'tenant' => $transaction->barangay->barangay_code,
                        ]))
                        ->markAsRead(),
                ])
                ->sendToDatabase($official)
                ->broadcast($official);
        }

        // 2. Notify resident — also uses Filament's Notification, not Laravel's facade
        Notification::make()
            ->title('Document Request Sent')
            ->body(
                'Your request for ' .
                ($transaction->documentType->name ?? 'Document') .
                ' has been received.'
            )
            ->icon('heroicon-o-document-text')
            ->warning()
            ->actions([
                Action::make('view')
                    ->label('View Request')
                    ->url(route('filament.resident.resources.document-transactions.index', [
                        'tenant' => $transaction->barangay->barangay_code,
                    ]))
                    ->markAsRead(),
            ])
            ->sendToDatabase($transaction->requester)
            ->broadcast($transaction->requester);
    }
}