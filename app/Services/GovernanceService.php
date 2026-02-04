<?php

namespace App\Services;

use App\Models\BarangayTerm;
use App\Models\User;

class GovernanceService
{
    /**
     * Check if a user has authority to sign a specific document type.
     */
    public function canSign($user, $documentTypeCode)
    {
        // Your logic here:
        // 1. Get user's active term
        // 2. If Captain -> true
        // 3. If delegated -> true

        if (is_numeric($user) && is_numeric($documentTypeCode)) {
            $user = User::find($user);
        }

        if (!$user instanceof User) {
            return false;
        }

        // Replace the activeTerm logic here:
        $activeTerm = $user->activeTerm;

        if (!$activeTerm) {
            return false;
        }

        // Logic: Captains bypass delegation; others check delegation
        if ($activeTerm->position_type === 'Captain' || $this->isDelegated($user, $documentTypeCode)) {
            return true;
        }

        return false;

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