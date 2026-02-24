<?php

namespace App\Livewire\Officials;

use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Models\BarangayTerm;
use App\Models\Delegation;
use Filament\Tables\Columns\TextColumn;

class OfficialManagement extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $barangay_code;
    public $showDelegationModal = false;
    public $selectedDelegateId = null;

    public function mount($barangay_code)
    {
        $this->barangay_code = \App\Models\Barangay::normalizeCode($barangay_code);
    }

    public function revoke($delegationId)
    {
        $delegation = Delegation::find($delegationId);
        if ($delegation) {
            $delegation->update(['expires_at' => now()]);
            session()->flash('success', 'Authority revoked successfully.');
        }
    }

    public function saveDelegation()
    {
        $this->validate([
            'selectedDelegateId' => 'required|exists:barangay_terms,id',
        ]);

        $granterTerm = auth()->user()->activeTerm;

        if (!$granterTerm) {
            session()->flash('error', 'You do not have an active official term.');
            return;
        }

        $delegateTerm = BarangayTerm::with('user')->find($this->selectedDelegateId);

        Delegation::create([
            'granter_term_id' => $granterTerm->id,
            'delegate_term_id' => $this->selectedDelegateId,
        ]);

        if ($delegateTerm && $delegateTerm->user) {
            $delegateTerm->user->assignRole('Delegate');
        }

        $this->showDelegationModal = false;
        $this->selectedDelegateId = null;
        session()->flash('success', 'Authority delegated successfully.');
    }

    public function setExpiration($delegationId, $date)
    {
        $delegation = Delegation::find($delegationId);
        if ($delegation) {
            $delegation->update(['expires_at' => $date]);
            session()->flash('success', 'Expiration date updated.');
        }
    }

    public function table(Table $table)
    {
        return $table
            ->query(
                BarangayTerm::query()
                    ->join('users', 'barangay_terms.user_id', '=', 'users.id')
                    ->join('roles', 'barangay_terms.position_id', '=', 'roles.id')
                    ->where('users.barangay_code', $this->barangay_code)
                    // Select specific columns to avoid ID conflicts
                    ->select('barangay_terms.*', 'users.first_name', 'users.last_name', 'roles.name as role_name')
            )
            ->columns([
                TextColumn::make('first_name')->label('First Name')->sortable(),
                TextColumn::make('last_name')->label('Last Name')->sortable(),
                TextColumn::make('role_name')->label('Position'),
            ]);
    }
    public function render()
    {
        $delegations = Delegation::whereHas('granterTerm', function ($query) {
            $query->where('barangay_code', function ($sub) {
                $sub->select('id')->from('barangays')->where('barangay_code', $this->barangay_code);
            });
        })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with(['delegateTerm.user'])
            ->get();

        // Get officials of the same barangay for delegation dropdown (excluding current user)
        $potentialDelegates = BarangayTerm::where('barangay_code', function ($sub) {
            $sub->select('id')->from('barangays')->where('barangay_code', $this->barangay_code);
        })
            ->where('user_id', '!=', auth()->id())
            ->where(function ($q) {
                $q->whereNull('ended_at')->orWhere('ended_at', '>', now());
            })
            ->with('user')
            ->get();

        return view('livewire.officials.official-management', [
            'delegations' => $delegations,
            'potentialDelegates' => $potentialDelegates
        ]);
    }
}
