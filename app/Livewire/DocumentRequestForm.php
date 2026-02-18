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
    public $step = 1;
    public $documentType = '';
    public $purpose = '';
    public $dynamicFields = [];
    public $selectedDocMetadata = null;
    public $requirements = [];
    public $hasValidId = false;
    public $isValidating = false;


    public function updatedPurpose()
    {
        $this->checkIdentityStatus();
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
        $this->selectedDocMetadata = DB::table('document_type_properties')->find($id);

        if (!$this->selectedDocMetadata)
            return;

        $this->documentType = $id;

        $event = new DocuTypeSelected((string) $id, (int) Auth::id());
        event($event);
        $this->requirements = $event->requirements;

        // 2. Prepare Dynamic Fields (BUT DON'T SHOW THEM YET)
        $modelClass = $this->selectedDocMetadata->doc_type_model;

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

        $metadata = (array) $this->selectedDocMetadata;

        try {
            // We call the service here. Livewire handles the dependency injection.
            $service->createRequest(
                user: auth()->user(),
                docTypeId: $this->documentType,
                modelClass: $metadata['doc_type_model'],
                purpose: $this->purpose,
                dynamicFields: $this->dynamicFields
            );

            session()->flash('success', 'Your request has been submitted successfully.');
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            logger($e->getMessage());
            $this->addError('submission', 'There was an error processing your request.');
        }
    }

    public function render()
    {
        return view('livewire.document-request-form', [
            'documents' => DB::table('document_type_properties')->get()
        ])->layout('layouts.app');
    }
}
