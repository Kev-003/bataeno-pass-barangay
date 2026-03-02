<?php

namespace App\Livewire\Officials;

use App\Models\User;
use App\Services\BataenoService;
use App\Services\DocumentRequestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\On; // <-- Required for Livewire 3!

class WalkInRequest extends Component
{
    public int $step = 1;

    public string  $document_type = '';
    public string  $purpose       = '';

    public bool    $connected  = false;
    public ?string $uid        = null;
    public ?array  $resident   = null;
    public bool    $loading    = false;
    public ?string $nfcError   = null;
    public bool    $showCardConfirmationModal = false;
    
    // Step 4: Editable document fields
    public array   $documentFields = [];
    public bool    $isEditingFields = false;

    // ── Step 1 actions ────────────────────────────────────────────────────────

    public function proceedToScan(): void
    {
        $this->validate([
            'document_type' => 'required|exists:document_type_properties,id',
            'purpose'       => 'required|string|min:5',
        ]);

        $this->step = 2;
        $this->uid      = null;
        $this->resident = null;
        $this->nfcError = null;
        $this->showCardConfirmationModal = false;
    }

    public function backToDocumentSelect(): void
    {
        $this->step     = 1;
        $this->uid      = null;
        $this->resident = null;
        $this->nfcError = null;
        $this->loading  = false;
        $this->showCardConfirmationModal = false;
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
        if ($this->step !== 2) return;

        $this->uid      = $uid;
        $this->resident = null;
        $this->nfcError = null;
        $this->showCardConfirmationModal = false;
    }

    #[On('nfc:verifiedUid')]
    public function onVerifiedUid($uid = null): void
    {
        if ($this->step !== 2) return;

        $this->uid      = $uid;
        $this->loading  = true;
        $this->nfcError = null;
        $this->resident = null;
        $this->showCardConfirmationModal = false;

        try {
            $resident = app(BataenoService::class)->findByCardUid($uid);

            if ($resident) {
                $this->resident = $resident;
                // Auto-fill document fields from resident data
                $this->populateDocumentFields($resident);
                $this->showCardConfirmationModal = true;
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
            $this->uid      = null;
            $this->resident = null;
            $this->nfcError = null;
            $this->loading  = false;
            $this->showCardConfirmationModal = false;
        }
    }

    // ── Step 3: go back to re-tap ─────────────────────────────────────────────

    public function backToScan(): void
    {
        $this->step     = 2;
        $this->uid      = null;
        $this->resident = null;
        $this->nfcError = null;
        $this->showCardConfirmationModal = false;
    }

    public function confirmResidentLookup(): void
    {
        if (! $this->resident) {
            $this->nfcError = 'No verified resident details found.';
            $this->showCardConfirmationModal = false;
            return;
        }

        $this->showCardConfirmationModal = false;
        $this->step = 3;
    }

    public function cancelResidentLookup(): void
    {
        $this->showCardConfirmationModal = false;
        $this->resident = null;
        $this->uid = null;
        $this->documentFields = [];
    }

    // ── Step 3/4: Document field management ────────────────────────────────

    public function populateDocumentFields($resident): void
    {
        // Auto-fill with resident data - map resident fields to common document fields
        $this->documentFields = [
            'first_name'    => $resident['first_name'] ?? '',
            'middle_name'   => $resident['middle_name'] ?? '',
            'last_name'     => $resident['last_name'] ?? '',
            'date_of_birth' => $resident['birthdate'] ?? $resident['birthdate_formal'] ?? '',
            'sex'           => $resident['sex'] ?? '',
            'civil_status'  => $resident['civil_status'] ?? '',
            'contact_number' => $resident['contact_number'] ?? '',
            'address'       => $resident['address'] ?? '',
            'birthplace'    => $resident['birth_place'] ?? '',
        ];
    }

    public function toggleEditMode(): void
    {
        $this->isEditingFields = !$this->isEditingFields;
    }

    public function confirmAndProceed(): void
    {
        // Move to step 4 if editing, or proceed with submission
        if ($this->isEditingFields) {
            // User confirmed edits, reset mode
            $this->isEditingFields = false;
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getInitials(): string
    {
        return strtoupper(
            substr($this->resident['first_name'] ?? '', 0, 1) .
            substr($this->resident['last_name']  ?? '', 0, 1)
        ) ?: '?';
    }

    public function getSelectedDocumentName(): string
    {
        if (! $this->document_type) return '';
        return DB::table('document_type_properties')
            ->where('id', $this->document_type)
            ->value('name') ?? '';
    }

    // ── Submit ────────────────────────────────────────────────────────────────

    public function submit(DocumentRequestService $service): void
    {
        $this->validate([
            'uid'           => 'required|string',
            'document_type' => 'required|exists:document_type_properties,id',
            'purpose'       => 'required|string|min:5',
        ]);

        $user = User::where('uuid', $this->uid)->first();

        if (! $user) {
            $this->nfcError = 'Resident not found in the local database.';
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
                dynamicFields: $this->documentFields // Pass the editable fields
            );

            if ($transaction) {
                $this->dispatch('walkin:success', transaction_id: $transaction->id);
                $this->reset(['uid', 'resident', 'document_type', 'purpose', 'nfcError', 'loading', 'documentFields', 'isEditingFields', 'showCardConfirmationModal']);
                $this->step = 1;
            } else {
                $this->dispatch('walkin:error', message: 'Failed to create request.');
            }
        } catch (\Exception $e) {
            Log::error('WalkIn submission failed: ' . $e->getMessage());
            $this->dispatch('walkin:error', message: 'System error: ' . $e->getMessage());
        }
    }

    

    public function render()
    {
        return view('livewire.officials.walk-in-request', [
            'documentTypes' => DB::table('document_type_properties')->get(),
        ]);
    }
}