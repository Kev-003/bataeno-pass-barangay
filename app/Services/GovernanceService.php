<?php

namespace App\Services;

use App\Models\User;
use App\Models\DocumentTransaction;
use App\Models\Delegation;

class GovernanceService
{
    /**
     * Check if a user has authority to sign a specific document type.
     */
    public function canSign(User $user, $transactionId): bool
    {
        // 1. Load Transaction with Context
        $transaction = DocumentTransaction::find($transactionId);
        if (!$transaction)
            return false;

        // 2. Jurisdictional Check
        // We trust the User model to tell us where this user belongs.
        // If the Official is from Barangay A, they cannot sign for Barangay B.
        if ($user->getActiveBarangayId() !== $transaction->barangay->barangay_code && $user->getActiveBarangayId() !== $transaction->barangay_id) {
            return false;
        }

        // 3. Official Status Check
        // We trust the User model's relationship.
        $term = $user->activeTerm;
        if (!$term)
            return false;

        // 4. Role-Based Authority
        // Captains have absolute signing power.
        if ($term->position_type === 'Captain') {
            return true;
        }

        // 5. Check for Specific Delegation
        return $this->hasDelegation($term->id, $transaction->document_type_id);
    }

    /**
     * Check the delegation table for a specific term and document type.
     */
    protected function hasDelegation(int $termId, int $documentTypeId): bool
    {
        return Delegation::where('delegate_term_id', $termId)
            ->where('document_type_id', $documentTypeId)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->exists();
    }
}