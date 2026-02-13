<?php

namespace App\Services;

use App\Models\DocumentTransaction;
use App\Models\User;
use App\Notifications\DocumentRequestReceived;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class DocumentRequestService
{
    public function createRequest(User $user, string $docTypeId, string $modelClass, string $purpose, array $dynamicFields)
    {
        return DB::transaction(function () use ($user, $docTypeId, $modelClass, $purpose, $dynamicFields) {

            $transaction = DocumentTransaction::create([
                'requester_id' => $user->id,
                'barangay_id' => $user->barangay_code,
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
        $officials = User::officialsForBarangay($transaction->barangay_id)->get();

        if ($officials->count() > 0) {
            Notification::send($officials, new DocumentRequestReceived($transaction));
        }
    }
}