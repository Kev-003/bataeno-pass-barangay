<?php

namespace App\Livewire\Officials;

use App\Models\User;
use App\Services\BataenoService;
use App\Services\DocumentRequestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\On;
use Filament\Notifications\Notification;

class WalkInRequest extends Component
{
    public int $step = 1;

    public string $document_type = '';
    public string $purpose = '';

    public bool $connected = false;
    public ?string $uid = null;
    public ?array $resident = null;
    public bool $loading = false;
    public ?string $nfcError = null;

    // ── Step 1 actions ────────────────────────────────────────────────────────

    public function proceedToScan(): void
    {
        $this->validate([
            'document_type' => 'required|exists:document_type_properties,id',
            'purpose' => 'required|string|min:5',
        ]);

        $this->step = 2;
        $this->uid = null;
        $this->resident = null;
        $this->nfcError = null;
    }

    public function backToDocumentSelect(): void
    {
        $this->step = 1;
        $this->uid = null;
        $this->resident = null;
        $this->nfcError = null;
        $this->loading = false;
    }

    // ── NFC event handlers (only active on Step 2) ────────────────────────────

    #[On('nfc:connect')]
    public function onNfcConnect(): void
    {
        $this->connected = true;
    }

    #[On('nfc:disconnect')]
    public function onNfcDisconnect(): void
    {
        $this->connected = false;
    }

    #[On('nfc:cardUid')]
    public function onCardUid($uid = null): void
    {
        if ($this->step !== 2)
            return;

        $this->uid = $uid;
        $this->resident = null;
        $this->nfcError = null;
    }

    #[On('nfc:verifiedUid')]
    public function onVerifiedUid($uid = null): void
    {
        if ($this->step !== 2)
            return;

        $this->uid = $uid;
        $this->loading = true;
        $this->nfcError = null;
        $this->resident = null;

        try {
            $resident = app(BataenoService::class)->findByCardUid($uid);

            if ($resident) {
                $this->resident = $resident;
                $this->step = 3;
                Log::info('WalkIn: resident found', ['uid' => $uid, 'name' => $resident['name'] ?? null]);
            } else {
                $this->nfcError = 'This card is not registered at this barangay. The resident must log in first to register.';
                Log::info('WalkIn: UUID not in local DB', ['uid' => $uid]);
            }
        } catch (\Exception $e) {
            $this->nfcError = 'Card lookup failed: ' . $e->getMessage();
            Log::error('WalkIn NFC error', ['uid' => $uid, 'error' => $e->getMessage()]);
        } finally {
            $this->loading = false;
        }
    }

    #[On('nfc:cardRemoved')]
    public function onCardRemoved(): void
    {
        if ($this->step === 2) {
            $this->uid = null;
            $this->resident = null;
            $this->nfcError = null;
            $this->loading = false;
        }
    }

    #[On('nfc:fakeResident')]
    public function onFakeResident($uid = null, $resident = null): void
    {
        if ($this->step !== 2)
            return;

        $this->uid = $uid;
        $this->resident = $resident;
        $this->nfcError = null;
        $this->loading = false;
        $this->step = 3;
    }

    // ── Step 3: go back to re-tap ─────────────────────────────────────────────

    public function backToScan(): void
    {
        $this->step = 2;
        $this->uid = null;
        $this->resident = null;
        $this->nfcError = null;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getInitials(): string
    {
        return strtoupper(
            substr($this->resident['first_name'] ?? '', 0, 1) .
            substr($this->resident['last_name'] ?? '', 0, 1)
        ) ?: '?';
    }

    public function getSelectedDocumentName(): string
    {
        if (!$this->document_type)
            return '';
        return DB::table('document_type_properties')
            ->where('id', $this->document_type)
            ->value('name') ?? '';
    }

    // ── Submit ────────────────────────────────────────────────────────────────

    public function submit(DocumentRequestService $service): void
    {
        $this->validate([
            'uid' => 'required|string',
            'document_type' => 'required|exists:document_type_properties,id',
            'purpose' => 'required|string|min:5',
        ]);

        $user = User::where('uuid', $this->uid)->first();

        if (!$user) {
            Notification::make()->title('Resident Not Found')->danger()->send();
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
                Notification::make()
                    ->title('Request Created')
                    ->body("Transaction ID: {$transaction->id}")
                    ->success()
                    ->send();

                $this->reset(['uid', 'resident', 'document_type', 'purpose', 'nfcError', 'loading']);
                $this->step = 1;
            }
        } catch (\Exception $e) {
            Notification::make()->title('System Error')->body($e->getMessage())->danger()->send();
        }
    }



    public function render()
    {
        return view('livewire.officials.walk-in-request', [
            'documentTypes' => DB::table('document_type_properties')->get(),
        ]);
    }
}