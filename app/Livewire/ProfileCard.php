<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BarangayTerm;

class ProfileCard extends Component
{
    public function getPosition()
    {
        $term = auth()->user()->activeTerm()->with('position')->first();
        return $term?->position?->name ?? 'Resident';
    }
    public function render()
    {
        return view('livewire.profile-card', [
            'user' => auth()->user(),
        ]);
    }
}
