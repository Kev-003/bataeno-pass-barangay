<?php

namespace App\Livewire\Officials;

use Livewire\Component;
use App\Models\DocumentTransaction;
use App\Models\Barangay;

class DocumentProcessing extends Component
{
    public $psgc_code;
    public $barangay_id;

    public function mount($barangay_code)
    {
        $this->psgc_code = $barangay_code;
        $barangay = Barangay::where('barangay_code', $this->psgc_code)->firstOrFail();
        $this->barangay_id = $barangay->id;
    }

    public function render()
    {
        return view('livewire.officials.document-processing', [
            'pendingTransactions' => DocumentTransaction::where('barangay_id', $this->barangay_id)
                ->where('status', 'pending')
                ->with(['requester', 'documentTypeProperty'])
                ->latest()
                ->get(),
            'processingTransactions' => DocumentTransaction::where('barangay_id', $this->barangay_id)
                ->where('status', 'processing')
                ->with(['requester', 'documentTypeProperty'])
                ->latest()
                ->get(),
            'completedTransactions' => DocumentTransaction::where('barangay_id', $this->barangay_id)
                ->where('status', 'issued') // Mapping 'completed' in UI to 'issued' in DB
                ->with(['requester', 'documentTypeProperty'])
                ->latest()
                ->get()
        ]);
    }
}
