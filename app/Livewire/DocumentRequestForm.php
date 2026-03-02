<?php

namespace App\Livewire;

use App\Services\DocumentRequestService;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\DocumentTransaction;
use Illuminate\Support\Facades\Auth;
use App\Events\DocuTypeSelected;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class DocumentRequestForm extends Component
{
    // When a resident card is tapped, this will hold the resident payload
    public ?array $targetResident = null;
    public $step = 1;
    public $documentType = '';
    public $purpose = '';
    public $dynamicFields = [];
    public $selectedDocMetadata = null;
    public $requirements = [];
    public $hasValidId = false;
    public $isValidating = false;
    // When embedded into another page, set this to true to avoid applying a full-page layout
    public bool $embedded = false;

    public bool $isFilament = false; // To toggle the UI style
    public $initialDocId = null;

    public function mount($embedded = false, $isFilament = false, $initialDocId = null, $targetResident = null)
    {
        $this->embedded = $embedded;
        $this->isFilament = $isFilament;

        if ($initialDocId) {
            $this->setDoc($initialDocId);
        }

        if ($targetResident) {
            $this->targetResident = $targetResident;
            $this->onNfcUid(null, $targetResident);
        }
    }


    public function updatedPurpose()
    {
        $this->checkIdentityStatus();
    }

    // Listen for NFC tap events to pre-fill this form for the tapped resident
    protected function getListeners(): array
    {
        return [
            'nfcUidTapped' => 'onNfcUid',
        ];
    }

    public function onNfcUid(?string $uid = null, array $resident = []): void
    {
        // Livewire 3 maps dispatched keys to parameter names; accept `uid` and `resident`.
        // Support legacy single-object payloads as a fallback as well.
        if (is_null($uid) && empty($resident)) {
            return;
        }

        // If $resident was passed as non-array (legacy), normalize it
        if (!is_array($resident)) {
            $resident = (array) $resident;
        }

        // Legacy payload shape handling: if uid is empty but resident contains uid/key
        if (empty($uid) && isset($resident['uid'])) {
            $uid = $resident['uid'];
        }

        if (!$resident)
            return;

        $this->targetResident = $resident;

        // Prefill commonly-used fields into dynamicFields if keys exist
        $map = [
            'first_name' => ['first_name', 'name', 'given_name'],
            'middle_name' => ['middle_name'],
            'last_name' => ['last_name', 'family_name'],
            'date_of_birth' => ['birthdate', 'dob'],
            'place_of_birth' => ['birth_place', 'place_of_birth'],
            'gender' => ['sex', 'gender'],
            'civil_status' => ['civil_status'],
            'email' => ['email'],
            'address' => ['address', 'full_address', 'barangay_name'],
        ];

        foreach ($map as $field => $keys) {
            foreach ($keys as $k) {
                if (isset($resident[$k]) && (!isset($this->dynamicFields[$field]) || $this->dynamicFields[$field] === '')) {
                    $this->dynamicFields[$field] = $resident[$k];
                    break;
                }
            }
        }
    }

    public function checkIdentityStatus()
    {
        $this->isValidating = true;

        // Simulate a small delay or API call if necessary
        $user = auth()->user();
        $data = $user->egov_data; // This would be the JSON from your example

        if (!$data || !isset($data['passport'])) {
            $this->hasValidId = false;
            $this->isValidating = false;
            return;
        }

        $expiryDate = \Carbon\Carbon::parse($data['passport']['expiry_date']);

        // Logic: Has passport AND current date is before expiry
        $this->hasValidId = auth()->user()->hasAnyValidID();
        $this->isValidating = false;
    }

    public function setDoc($id)
    {
        $this->selectedDocMetadata = (array) DB::table('document_type_properties')->where('id', $id)->first();

        if (!$this->selectedDocMetadata)
            return;

        $this->documentType = $id;

        $event = new DocuTypeSelected((string) $id, (int) Auth::id());
        event($event);
        $this->requirements = $event->requirements;

        // 2. Prepare Dynamic Fields (BUT DON'T SHOW THEM YET)
        $modelClass = $this->selectedDocMetadata['doc_type_model'];

        // Ensure fully qualified class name
        if (!str_contains($modelClass, '\\')) {
            $modelClass = "App\\Models\\{$modelClass}";
        }

        if (class_exists($modelClass)) {
            $instance = new $modelClass;
            $columns = Schema::getColumnListing($instance->getTable());

            $excluded = ['id', 'created_at', 'updated_at', 'transaction_id'];

            // Create a simple Key => Value array
            $this->dynamicFields = collect($columns)
                ->reject(fn($column) => in_array($column, $excluded))
                ->mapWithKeys(fn($column) => [$column => ''])
                ->toArray();
        }

        $this->purpose = '';

        // 3. Move to Step 2 ONLY
        $this->step = 2;
    }

    public function validateStep2()
    {
        $this->validate([
            'purpose' => 'required|min:5',
        ]);

        // Proceed to Dynamic Fields
        $this->step = 3;
    }

    public function submit(DocumentRequestService $service)
    {

        $this->validate([
            'dynamicFields.*' => 'required',
            'purpose' => 'required|min:5',
        ]);

        $metadata = $this->selectedDocMetadata;

        try {
            // Determine requester: prefer tapped resident when available
            $requester = auth()->user();
            if ($this->targetResident && is_array($this->targetResident)) {
                $uuid = $this->targetResident['uuid'] ?? $this->targetResident['raw']['uuid'] ?? null;
                $email = $this->targetResident['email'] ?? null;

                $found = null;
                if ($uuid) {
                    $found = User::where('uuid', $uuid)->first();
                }
                if (!$found && $email) {
                    $found = User::where('email', $email)->first();
                }

                if (!$found) {
                    // Create a minimal resident record so we can associate the request
                    $found = User::create([
                        'uuid' => $uuid,
                        'first_name' => $this->targetResident['first_name'] ?? strtok($this->targetResident['name'] ?? '', ' '),
                        'middle_name' => $this->targetResident['middle_name'] ?? null,
                        'last_name' => $this->targetResident['last_name'] ?? (!empty($this->targetResident['name'] ?? '') ? trim(substr(($this->targetResident['name'] ?? ''), strlen(strtok($this->targetResident['name'] ?? '', ' ')))) : null),
                        'date_of_birth' => $this->targetResident['birthdate'] ?? $this->targetResident['dob'] ?? null,
                        'place_of_birth' => $this->targetResident['birth_place'] ?? null,
                        'gender' => $this->targetResident['sex'] ?? $this->targetResident['gender'] ?? null,
                        'civil_status' => $this->targetResident['civil_status'] ?? null,
                        'email' => $email,
                        'email_verified_at' => now(),
                        'password' => Hash::make(bin2hex(random_bytes(8))),
                        'municity_name' => $this->targetResident['municity_name'] ?? $this->targetResident['city'] ?? null,
                        'barangay_name' => $this->targetResident['barangay_name'] ?? $this->targetResident['address'] ?? null,
                    ]);
                }

                $requester = $found;
            }

            // We call the service here. Livewire handles the dependency injection.
            $service->createRequest(
                user: $requester,
                docTypeId: $this->documentType,
                modelClass: $metadata['doc_type_model'],
                purpose: $this->purpose,
                dynamicFields: $this->dynamicFields
            );

            session()->flash('success', 'Your request has been submitted successfully.');
            if ($this->isFilament) {
                $this->dispatch('walkin:success');
                return; // Don't redirect the official to the resident dashboard!
            }

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            logger($e->getMessage());
            $this->addError('submission', 'Error processing request: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $view = view('livewire.document-request-form', [
            'documents' => DB::table('document_type_properties')->get()
        ]);

        if ($this->embedded) {
            return $view;
        }

        return $view->layout('layouts.app');
    }
}
