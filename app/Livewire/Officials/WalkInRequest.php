<?php

namespace App\Livewire\Officials;

use App\Models\User;
use App\Services\BataenoService;
use App\Services\DocumentRequestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class WalkInRequest extends Component
{
    // ── NFC state ─────────────────────────────────────────────────────────────
    public ?string $uid      = null;
    public ?array  $resident = null;

    // ── Form fields ───────────────────────────────────────────────────────────
    public string $document_type = '';
    public string $purpose       = '';

    // ── Livewire 3 listeners ──────────────────────────────────────────────────
    protected function getListeners(): array
    {
        return [
            'nfcUidTapped'  => 'onNfcUid',
            'nfcUidRemoved' => 'onNfcRemoved',
        ];
    }

    // ── NFC handlers ──────────────────────────────────────────────────────────

    /**
     * Called when NfcResidentLookup dispatches 'nfcUidTapped' after a
     * successful local DB + Bataeno verify-card lookup.
     *
     * Livewire 3 maps dispatch keys directly to parameter names.
     */
    public function onNfcUid(string $uid, array $resident = []): void
    {
        $this->uid      = $uid;
        $this->resident = $resident ?: null;

        Log::info('WalkInRequest: NFC uid received', [
            'uid'  => $uid,
            'name' => $resident['name'] ?? null,
        ]);
    }

    public function onNfcRemoved(): void
    {
        $this->uid           = null;
        $this->resident      = null;
        $this->document_type = '';
        $this->purpose       = '';
    }

    // ── Form submit ───────────────────────────────────────────────────────────

    public function submit(DocumentRequestService $service): void
    {
        $this->validate([
            'uid'           => 'required|string|max:255',
            'document_type' => 'required|exists:document_type_properties,id',
            'purpose'       => 'required|string|min:5',
        ]);

        $user = User::where('uuid', $this->uid)->first();

        if (! $user) {
            $this->dispatch('walkin:error', message: 'Resident not found in the local database.');
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
                $this->dispatch('walkin:success', transaction_id: $transaction->id);
                $this->onNfcRemoved();
            } else {
                $this->dispatch('walkin:error', message: 'Failed to create request.');
            }
        } catch (\Exception $e) {
            Log::error('WalkInRequest submit failed: ' . $e->getMessage());
            $this->dispatch('walkin:error', message: 'System error while creating request.');
        }
    }

    public function render()
    {
        return view('livewire.officials.walk-in-request');
    }
}