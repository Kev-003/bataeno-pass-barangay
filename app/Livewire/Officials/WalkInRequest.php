<?php

namespace App\Livewire\Officials;

use Livewire\Component;
use App\Models\User;
use App\Services\DocumentRequestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalkInRequest extends Component
{
    public $uid;
    public $document_type = '';
    public $purpose = '';
    public $resident = null;

    // Replace $listeners with:
protected function getListeners()
{
    return [
        'nfcUidTapped' => 'onNfcUid',
        'nfcUidRemoved' => 'onNfcRemoved',
    ];
}

    public function onNfcUid($uidOrPayload, $residentData = null)
    {
        // Support multiple payload shapes depending on Livewire version / JS call:
        // - (uid, residentData)
        // - ({ uid: ..., resident: {...} }) delivered as a single object via Livewire.dispatch
        $uid = null;

        if (is_array($uidOrPayload) || (is_object($uidOrPayload) && ! is_string($uidOrPayload))) {
            $payload = (array) $uidOrPayload;
            $uid = $payload['uid'] ?? null;
            $residentData = $payload['resident'] ?? $payload['data'] ?? $residentData;
        } else {
            $uid = $uidOrPayload;
        }

        $this->uid = $uid;
        
        // Log for server-side tracing
        
        try {
            \Illuminate\Support\Facades\Log::info('Livewire onNfcUid invoked', ['uid' => $this->uid, 'hasResident' => (bool) $residentData]);
        } catch (\Exception $e) {
            // ignore logging errors
        }

        // Log raw payload for debugging Livewire 3 named-arg wrapping
        try {
            \Illuminate\Support\Facades\Log::info('onNfcUid raw payload', ['raw' => is_scalar($uidOrPayload) ? (string)$uidOrPayload : json_encode($uidOrPayload)]);
        } catch (\Exception $e) {
            // ignore
        }

        if ($residentData) {
            $this->resident = $residentData;
            // Notify the browser for easier debugging/confirmation
            $this->dispatch('nfc:received', uid: $this->uid, resident: $this->resident);
            return;
        }

        // Fallback: If Javascript only sent the UID, look them up in the local DB
        $this->lookupLocalResident();

        // After local lookup completed, dispatch an event so front-end knows
        $this->dispatch('nfc:received', uid: $this->uid, resident: $this->resident);
    }

    public function onNfcRemoved()
    {
        // Clear the screen if the card is pulled away
        $this->uid = null;
        $this->resident = null;
        $this->document_type = '';
        $this->purpose = '';
    }

    public function lookupLocalResident()
    {
        if (! $this->uid) return;

        $user = User::where('uuid', $this->uid)
            ->orWhere('nfc_uid', $this->uid)
            ->orWhere('card_uid', $this->uid)
            ->first();

        if ($user) {
            $this->resident = [
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'last_name' => $user->last_name,
                'name' => trim(($user->first_name ?? '') . ' ' . ($user->middle_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email,
                'address' => ($user->barangay_name ? $user->barangay_name . ', ' : '') . ($user->municity_name ?? ''),
                'birthdate' => $user->date_of_birth ?? $user->birthdate ?? null,
                'contact_number' => $user->contact_number ?? $user->phone ?? $user->email ?? null,
            ];
        } else {
            $this->resident = null;
        }
    }

    public function submit(DocumentRequestService $service)
    {
        $this->validate([
            'uid' => 'required|string|max:255',
            'document_type' => 'required|exists:document_type_properties,id',
            'purpose' => 'required|string|min:5',
        ]);

        // Find the user in the local database to attach the document to
        $user = User::where('uuid', $this->uid)
            ->orWhere('nfc_uid', $this->uid)
            ->orWhere('card_uid', $this->uid)
            ->first();

        // 🚨 IMPORTANT: If the resident exists in the Bataeno Pass API but NOT in 
        // your local MySQL database, they cannot submit a document yet.
        if (! $user) {
            $this->dispatch('nfc:received', uid: $this->uid, resident: $this->resident);
            return;
        }

        try {
            $modelClass = DB::table('document_type_properties')
                ->where('id', $this->document_type)
                ->value('doc_type_model');

            $transaction = $service->createRequest(
                user: $user,
                docTypeId: $this->document_type,
                modelClass: $modelClass,
                purpose: $this->purpose,
                dynamicFields: []
            );

            if ($transaction) {
                $this->dispatch('nfc:received', uid: $this->uid, resident: $this->resident);
                // Reset form on success
                $this->onNfcRemoved(); 
            } else {
                $this->dispatch('nfc:received', uid: $this->uid, resident: $this->resident);
            }
        } catch (\Exception $e) {
            Log::error('Walk-in request submission failed: ' . $e->getMessage());
            $this->dispatch('nfc:received', uid: $this->uid, resident: $this->resident);
        }
    }

    public function render()
    {
        return view('livewire.officials.walk-in-request');
    }
}