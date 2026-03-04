<?php

namespace App\Livewire\Officials;

use App\Models\User;
use App\Services\BataenoService;
use Livewire\Component;
use Filament\Notifications\Notification;

class ManualLookupForm extends Component
{
    public string $search = '';
    public array $results = [];

    public function updatedSearch()
    {
        if (strlen($this->search) < 3) {
            $this->results = [];
            return;
        }

        // Local search
        $localResults = User::query()
            ->where(function ($query) {
                $query->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'uuid' => $user->uuid,
                    'source' => 'Local Database',
                ];
            })
            ->toArray();

        // Portal search
        $bataeno = app(BataenoService::class);
        $portalResults = collect($bataeno->searchUsers($this->search))
            ->take(5)
            ->map(function ($res) {
                return [
                    'id' => null,
                    'name' => $res['full_name'] ?? ($res['first_name'] . ' ' . $res['last_name']),
                    'email' => $res['email'] ?? null,
                    'uuid' => $res['uuid'] ?? null,
                    'source' => 'Bataan Portal',
                    'raw' => $res,
                ];
            })
            ->toArray();

        // Merge and unique by email
        $this->results = collect($localResults)
            ->merge($portalResults)
            ->unique('email')
            ->toArray();
    }

    public function selectResident(string $uuid)
    {
        $user = User::where('uuid', $uuid)->first();
        $bataeno = app(BataenoService::class);

        // If found locally, enrich we portal data if possible
        if ($user) {
            $resident = $bataeno->findByCardUid($uuid);
            if ($resident) {
                $this->dispatch('resident-selected', resident: $resident);
            } else {
                $this->dispatch('resident-selected', resident: [
                    'first_name' => $user->first_name,
                    'middle_name' => $user->middle_name,
                    'last_name' => $user->last_name,
                    'name' => $user->name,
                    'email' => $user->email,
                    'uuid' => $user->uuid,
                    'birthdate' => $user->date_of_birth,
                    'sex' => $user->gender,
                    'civil_status' => $user->civil_status,
                    'address' => $user->location ?? null,
                    '_source' => 'local',
                ]);
            }
            return;
        }

        // If not found locally, check our portal results
        $portalMatch = collect($this->results)->firstWhere('uuid', $uuid);
        if ($portalMatch && isset($portalMatch['raw'])) {
            $this->dispatch('resident-selected', resident: $bataeno->mapPortalData($portalMatch['raw']));
            return;
        }

        Notification::make()
            ->title('Error')
            ->body('Resident record not found.')
            ->danger()
            ->send();
    }

    public function render()
    {
        return view('livewire.officials.manual-lookup-form');
    }
}
