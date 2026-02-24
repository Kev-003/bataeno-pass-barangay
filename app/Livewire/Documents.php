<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use App\Models\DocumentTransaction;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
class Documents extends Component
{
    #[Computed]
    public function transactions()
    {
        return DocumentTransaction::where('requester_id', Auth::id())
            ->with('documentTypeProperty')
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.documents');
    }
}
