<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// Default Livewire / Filament user channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Officials listen here for new document requests submitted to their barangay.
// Any official whose active barangay term matches the barangay_id can listen.
Broadcast::channel('barangay.{barangayCode}.requests', function ($user, $barangayCode) {
    // Allow if the user is an official of this barangay (has an active BarangayTerm)
    return $user->barangayTerms()
        ->whereHas('barangay', function ($query) use ($barangayCode) {
            $query->where('barangay_code', $barangayCode);
        })
        ->where(function ($q) {
            $q->whereNull('ended_at')->orWhere('ended_at', '>=', now());
        })
        ->exists();
});

// Residents listen here to be notified when their document is issued.
// Only the document's requester (by user id) may subscribe.
Broadcast::channel('resident.{userId}.documents', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
