<?php

namespace App\Livewire\Officials;

use App\Models\User;
use App\Services\BataenoService;
use App\Services\DocumentRequestService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\On;
use Filament\Notifications\Notification;
use App\Models\Barangay;

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
    public bool $showCardConfirmationModal = false;
    public bool $useQrScanner = false;
    public bool $useManualLookup = false;

    // Step 4: Editable document fields
    public array $documentFields = [];
    public array $documentFieldLabels = [];

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
        $this->showCardConfirmationModal = false;
        $this->initializeDocumentFields();
    }

    public function backToDocumentSelect(): void
    {
        $this->step = 1;
        $this->uid = null;
        $this->resident = null;
        $this->nfcError = null;
        $this->loading = false;
        $this->showCardConfirmationModal = false;
        $this->documentFields = [];
        $this->documentFieldLabels = [];
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
        $this->showCardConfirmationModal = false;
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
        $this->showCardConfirmationModal = false;

        try {
            $bataeno = app(BataenoService::class);
            $resident = $bataeno->findByCardUid($uid);

            if (!$resident) {
                // Case: Not in local DB. Let's try to verify against Portal and auto-register
                $portalData = $bataeno->verifyCard($uid);
                if ($portalData) {
                    $barangayId = auth()->user()->getActiveBarangayId();
                    if ($barangayId) {
                        $user = $bataeno->registerToBarangay($portalData, $barangayId);
                        // Re-lookup to get the full payload
                        $resident = $bataeno->findByCardUid($uid);
                    }
                }
            }

            if ($resident) {
                $this->resident = $resident;
                // Auto-fill document fields from resident data
                $this->populateDocumentFields($resident);
                $this->step = 3;
            } else {
                $this->nfcError = 'This card is not registered. Please ensure the resident is registered in the Bataan Portal.';
            }
        } catch (\Exception $e) {
            $this->nfcError = 'Card lookup failed: ' . $e->getMessage();
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
            $this->showCardConfirmationModal = false;
            $this->documentFields = [];
            $this->documentFieldLabels = [];
        }
    }
    public function toggleQrScanner(): void
    {
        $this->useQrScanner = !$this->useQrScanner;
        $this->useManualLookup = false;
        $this->uid = null;
        $this->resident = null;
        $this->nfcError = null;
    }

    public function toggleManualLookup(): void
    {
        $this->useManualLookup = !$this->useManualLookup;
        $this->useQrScanner = false;
        $this->uid = null;
        $this->resident = null;
        $this->nfcError = null;
    }

    #[On('resident-selected')]
    public function onResidentSelected($resident): void
    {
        if ($this->step !== 2)
            return;

        $this->resident = $resident;
        $this->uid = $resident['uuid'];
        $this->populateDocumentFields($resident);
        $this->step = 3;
    }

    public function onScanResident($data): void
    {
        if ($this->step !== 2)
            return;

        $this->nfcError = null;
        $this->loading = true;

        try {
            // Try to check if it's a UUID
            if (preg_match('/^[a-f\d]{8}-(?:[a-f\d]{4}-){3}[a-f\d]{12}$/i', $data)) {
                $this->onVerifiedUid($data);
                return;
            }

            $bataeno = app(BataenoService::class);

            // Try the new verify-qr endpoint
            $portalData = null;
            try {
                $portalData = $bataeno->verifyQr($data);
            } catch (\Exception $e) {
            }

            if ($portalData) {
                $barangayId = auth()->user()->getActiveBarangayId();
                if ($barangayId) {
                    $user = $bataeno->registerToBarangay($portalData, $barangayId);
                    if ($user->uuid) {
                        $this->onVerifiedUid($user->uuid);
                        return;
                    }
                }
            }

            $payload = json_decode($data, true);
            if ($payload) {
                // Case: Valid QR data but not in portal yet. Try to register to portal!
                try {
                    $registered = $bataeno->registerToPortal($payload);
                    $portalData = $registered ? ($registered['user'] ?? $registered['data'] ?? $registered) : $payload;

                    $barangayId = auth()->user()->getActiveBarangayId();
                    if ($barangayId) {
                        $user = $bataeno->registerToBarangay($portalData, $barangayId);
                        if ($user->uuid) {
                            $this->onVerifiedUid($user->uuid);
                            return;
                        }
                    }
                } catch (\Exception $e) {
                    throw new \RuntimeException('Portal Registration failed: ' . $e->getMessage());
                }
            }

            $this->nfcError = 'Resident not found or invalid QR. Please ensure they are registered in the Bataan Portal.';
        } catch (\Exception $e) {
            $this->nfcError = 'Scan processing failed: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    // ── Step 3: go back to re-tap ─────────────────────────────────────────────

    public function backToScan(): void
    {
        $this->step = 2;
        $this->uid = null;
        $this->resident = null;
        $this->nfcError = null;
        $this->showCardConfirmationModal = false;
    }

    public function openSubmitConfirmation(): void
    {
        if (!$this->resident) {
            $this->nfcError = 'No verified resident details found.';
            $this->showCardConfirmationModal = false;
            return;
        }

        $this->showCardConfirmationModal = true;
    }

    public function closeSubmitConfirmation(): void
    {
        $this->showCardConfirmationModal = false;
    }

    // ── Step 3/4: Document field management ────────────────────────────────

    public function populateDocumentFields($resident): void
    {
        if (empty($this->documentFields)) {
            $this->initializeDocumentFields();
        }

        if (empty($this->documentFields)) {
            return;
        }

        $fullName = trim(($resident['first_name'] ?? '') . ' ' . ($resident['middle_name'] ?? '') . ' ' . ($resident['last_name'] ?? ''));

        $map = [
            'first_name' => ['first_name'],
            'middle_name' => ['middle_name'],
            'last_name' => ['last_name'],
            'name' => ['name'],
            'date_of_birth' => ['birthdate', 'birthdate_formal'],
            'dob' => ['birthdate', 'birthdate_formal'],
            'sex' => ['sex'],
            'gender' => ['sex'],
            'civil_status' => ['civil_status'],
            'contact_number' => ['contact_number'],
            'mobile_number' => ['contact_number'],
            'phone_number' => ['contact_number'],
            'address' => ['address'],
            'birthplace' => ['birth_place'],
            'place_of_birth' => ['birth_place'],
            'email' => ['email'],
        ];

        if ($fullName !== '') {
            $map['full_name'] = ['name'];
        }

        foreach ($this->documentFields as $field => $value) {
            if (!array_key_exists($field, $map)) {
                continue;
            }

            foreach ($map[$field] as $source) {
                if (is_string($source) && array_key_exists($source, $resident) && filled($resident[$source])) {
                    $this->documentFields[$field] = (string) $resident[$source];
                    break;
                }
            }

            if ($field === 'full_name' && blank($this->documentFields[$field]) && $fullName !== '') {
                $this->documentFields[$field] = $fullName;
            }
        }
    }

    public function initializeDocumentFields(): void
    {
        $this->documentFields = [];
        $this->documentFieldLabels = [];

        if (!$this->document_type) {
            return;
        }

        $modelClass = DB::table('document_type_properties')
            ->where('id', $this->document_type)
            ->value('doc_type_model');

        if (!$modelClass) {
            return;
        }

        if (!str_contains($modelClass, '\\')) {
            $modelClass = "App\\Models\\{$modelClass}";
        }

        if (!class_exists($modelClass)) {
            return;
        }

        $instance = new $modelClass;
        $columns = Schema::getColumnListing($instance->getTable());
        $excluded = ['id', 'created_at', 'updated_at', 'transaction_id'];

        foreach ($columns as $column) {
            if (in_array($column, $excluded, true)) {
                continue;
            }

            $this->documentFields[$column] = '';
            $this->documentFieldLabels[$column] = Str::headline($column);
        }
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
        $this->showCardConfirmationModal = false;

        $rules = [
            'uid' => 'required|string',
            'document_type' => 'required|exists:document_type_properties,id',
            'purpose' => 'required|string|min:5',
        ];

        foreach (array_keys($this->documentFields) as $field) {
            $rules["documentFields.{$field}"] = 'required';
        }

        $this->validate($rules);

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
                dynamicFields: $this->documentFields // Pass the editable fields
            );

            if ($transaction) {
                $this->dispatch('walkin:success', transaction_id: $transaction->id);
                $this->reset(['uid', 'resident', 'document_type', 'purpose', 'nfcError', 'loading', 'documentFields', 'documentFieldLabels', 'showCardConfirmationModal']);
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