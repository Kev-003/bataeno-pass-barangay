<?php

namespace App\Livewire\Officials;

use App\Models\DocumentTransaction;
use App\Models\BarangayTerm;
use App\Services\DocumentApprovalService;
use Livewire\Component;

class DocumentApprovalProcess extends Component
{
    public $pendingTransactions;
    public $documentType = '';
    public $purpose = '';
    public $dynamicFields = [];
    public $selectedDocMetadata = null;
    public $hasValidId = false;
    public $barangay_code;
    public $transactionDetails = [];

    public function mount($barangay_code, $id, DocumentApprovalService $service)
    {
        // Normalize the PSGC code from URL
        $this->barangay_code = \App\Models\Barangay::normalizeCode($barangay_code);

        $this->pendingTransactions = DocumentTransaction::findOrFail($id);
        $this->transactionDetails = $service->getTransactionDetails($this->pendingTransactions);

        $this->documentType = $this->transactionDetails['metadata']->name ?? '';
        $this->purpose = $this->pendingTransactions->purpose;
        $this->dynamicFields = $this->transactionDetails['fields'] ?? [];
        $this->selectedDocMetadata = $this->transactionDetails['metadata'];
        $this->hasValidId = $this->transactionDetails['requester']->hasAnyValidID();
    }

    public function approveAndSign(DocumentApprovalService $service)
    {
        try {
            $pdfPath = $service->generateAndSign(
                $this->pendingTransactions,
                BarangayTerm::where('user_id', auth()->id())->firstOrFail()
            );

            session()->flash('success', 'Document signed and issued successfully!');
            return redirect()->route('official.dashboard', ['barangay_code' => $this->barangay_code]);

        } catch (\Exception $e) {
            $this->addError('error', 'Signing failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.officials.document-approval-process');
    }
}

