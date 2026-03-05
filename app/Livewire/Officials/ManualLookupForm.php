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

        $bataeno = app(BataenoService::class);

        try {

            Log::info('LOOKUP STARTED', [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'birthday' => $this->birthday,
            ]);
            $portalUser = $bataeno->findUserByNameAndBirthday([
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'suffix' => $this->suffix,
                'date_of_birth' => $this->birthday,
            ]);
            Log::info('PORTAL RESULT', ['portalUser' => $portalUser]);

            if ($portalUser) {
                $this->result = $bataeno->mapPortalData($portalUser);
            } else {
                $localUser = User::where('first_name', 'like', "%{$this->first_name}%")
                    ->where('last_name', 'like', "%{$this->last_name}%")
                    ->whereDate('date_of_birth', date('Y-m-d', strtotime($this->birthday)))
                    ->first();
                Log::info('LOCAL RESULT', ['localUser' => $localUser?->toArray()]);

                if ($localUser) {
                    $this->result = [
                        'first_name' => $localUser->first_name,
                        'middle_name' => $localUser->middle_name,
                        'last_name' => $localUser->last_name,
                        'suffix' => $localUser->suffix,
                        'email' => $localUser->email,
                        'uuid' => $localUser->uuid,
                        'date_of_birth' => optional($localUser->date_of_birth)->format('Y-m-d'),
                        'gender' => $localUser->gender,
                        'civil_status' => $localUser->civil_status,
                        'contact_number' => $localUser->contact_number,
                        '_source' => 'local',
                    ];
                } else {
                    $this->error = 'No resident found matching these details.';
                }
            }

            // Dispatch immediately if we got a result — no separate confirm step needed
            if ($this->result) {
                Log::info('DISPATCH FIRING', ['result' => $this->result]);
                $this->dispatch('resident-selected', resident: $this->result);
            }

        } catch (\Exception $e) {
            $this->error = 'Lookup failed: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function selectResult(): void
    {
        if ($this->result) {
            $this->dispatch('resident-selected', resident: $this->result);
        }
    }

    public function render()
    {
        return view('livewire.officials.manual-lookup-form');
    }
}
