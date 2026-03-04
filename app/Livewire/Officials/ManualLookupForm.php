<?php

namespace App\Livewire\Officials;

use App\Models\User;
use App\Services\BataenoService;
use Livewire\Component;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ManualLookupForm extends Component
{
    public string $first_name = '';
    public string $middle_name = '';
    public string $last_name = '';
    public string $suffix = '';
    public string $birthday = '';

    public bool $loading = false;
    public ?array $result = null;
    public ?string $error = null;

    public function lookup(): void
    {
        $this->validate([
            'first_name' => 'required|string|min:2',
            'last_name' => 'required|string|min:2',
            'birthday' => 'required|date',
        ]);

        $this->loading = true;
        $this->result = null;
        $this->error = null;

        try {
            // First check local DB
            $localUser = User::where('first_name', 'like', "%{$this->first_name}%")
                ->where('last_name', 'like', "%{$this->last_name}%")
                ->whereDate('date_of_birth', date('Y-m-d', strtotime($this->birthday)))
                ->first();

            if ($localUser) {
                $bataeno = app(BataenoService::class);
                $enriched = $bataeno->findByCardUid($localUser->uuid);

                $this->result = $enriched ?? [
                    'first_name' => $localUser->first_name,
                    'middle_name' => $localUser->middle_name,
                    'last_name' => $localUser->last_name,
                    'suffix' => $localUser->suffix,
                    'email' => $localUser->email,
                    'uuid' => $localUser->uuid,
                    'birthdate' => $localUser->date_of_birth,
                    'sex' => $localUser->gender,
                    'civil_status' => $localUser->civil_status,
                    '_source' => 'local',
                ];
                return;
            }

            // Not found locally — try Portal API using POST /api/user
            $bataeno = app(BataenoService::class);
            $portalUser = $bataeno->findUserByNameAndBirthday([
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'suffix' => $this->suffix,
                'date_of_birth' => $this->birthday,
            ]);

            if ($portalUser) {
                $mapped = $bataeno->mapPortalData($portalUser);
                $this->result = $mapped;
                return;
            }

            $this->error = 'No resident found matching these details in local database or Bataan Portal.';
        } catch (\Exception $e) {
            $this->error = 'Lookup failed: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function selectResult(): void
    {
        if (!$this->result)
            return;

        $this->dispatch('resident-selected', resident: $this->result);

        Notification::make()
            ->title('Resident Found')
            ->success()
            ->body('Resident data has been pre-filled from the lookup.')
            ->send();
    }

    public function render()
    {
        return view('livewire.officials.manual-lookup-form');
    }
}
