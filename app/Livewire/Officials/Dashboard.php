<?php

namespace App\Livewire\Officials;

use Livewire\Component;
use App\Models\User;
use App\Models\Barangay;
use App\Models\DocumentTransaction;
use Livewire\WithPagination;


class Dashboard extends Component
{
    use WithPagination;
    public array $stats = [];
    public string $psgc_code;
    public int $barangay_id;

    public function mount($barangay_code)
    {
        $this->psgc_code = Barangay::normalizeCode($barangay_code);

        $barangay = Barangay::where('barangay_code', $this->psgc_code)->firstOrFail();
        $this->barangay_id = $barangay->id;

        $this->loadStats();
    }

    public function loadStats()
    {
        // Use the numeric ID for filtering transactions
        $this->stats = [
            [
                'title' => 'Total Residents',
                'value' => User::where('barangay_code', $this->psgc_code)->count(),
                'color' => 'blue'
            ],
            [
                'title' => 'Total Requests',
                'value' => DocumentTransaction::where('barangay_code', $this->barangay_id)->count(),
                'color' => 'green'
            ],
            [
                'title' => 'Pending Requests',
                'value' => DocumentTransaction::where('barangay_code', $this->barangay_id)
                    ->where('status', 'pending')
                    ->count(),
                'color' => 'red'
            ],
        ];
    }
    public function render()
    {
        return view('livewire.officials.dashboard', [
            'transactions' => DocumentTransaction::where('barangay_code', $this->barangay_id)
                ->with(['requester', 'documentTypeProperty']) // Eager load for performance
                ->latest()
                ->paginate(5)
        ]);
    }

    public function updateStatus($id, $status)
    {
        $transaction = DocumentTransaction::findOrFail($id);
        $transaction->update(['status' => $status]);

        // Refresh stats after update
        $this->loadStats();
        session()->flash('message', 'Request updated to ' . $status);
    }
}
