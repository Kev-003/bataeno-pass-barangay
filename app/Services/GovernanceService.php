<?php

namespace App\Services;

use App\Models\BarangayTerm;
use App\Models\User;
use App\Models\DocumentTransaction;

class GovernanceService
{
    /**
     * Check if a user has authority to sign a specific document type.
     */
    public function canSign($user, $transactionId)
    {
        if (!$user instanceof User)
            return false;

        // Load transaction with the property (using the now-fixed relationship name)
        $transaction = DocumentTransaction::with('documentTypeProperty')->find($transactionId);

        if (!$transaction || !$transaction->documentTypeProperty) {
            return false;
        }

        // 1. Jurisdictional Check
        // Compare User's active barangay against the transaction's specific barangay_id
        if ($user->getActiveBarangayId() !== $transaction->barangay_id) {
            return false;
        }

        // 2. Official Authority Check
        $activeTerm = $user->activeTerm;
        if (!$activeTerm)
            return false;

        // 3. Captain Override
        if ($activeTerm->position_type === 'Captain') {
            return true;
        }

        // 4. Delegation Check
        // We pass the document_type_id (Integer) to match the database column
        return $this->isDelegated($user, $transaction->document_type_id);
    }

    public function isDelegated($user, $documentTypeCode)
    {
        // Use the User ID if an object was passed
        $userId = $user instanceof User ? $user->id : $user;

        $activeTerm = BarangayTerm::where('user_id', $userId)
            ->where('ended_at', '>', now())
            ->first();

        if (!$activeTerm)
            return false;

        return \App\Models\Delegation::where('delegate_term_id', $activeTerm->id)
            ->where('document_type_id', $documentTypeCode)
            // Ensure we handle nullable expires_at if it means "forever"
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->exists();
    }
}