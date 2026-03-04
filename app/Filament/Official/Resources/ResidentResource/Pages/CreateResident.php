<?php

namespace App\Filament\Official\Resources\ResidentResource\Pages;

use App\Filament\Official\Resources\ResidentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\View;
use Livewire\Attributes\On;
use App\Services\BataenoService;
use Illuminate\Support\Facades\Log;

class CreateResident extends CreateRecord
{
    use HasWizard;

    protected static string $resource = ResidentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password'] = bcrypt(\Illuminate\Support\Str::random(16));
        $data['registered_at'] = now();

        return $data;
    }

    public bool $useQrScanner = true;
    public bool $useManualLookup = false;

    public function toggleQrScanner(): void
    {
        $this->useQrScanner = !$this->useQrScanner;
        $this->useManualLookup = false;
    }

    public function toggleManualLookup(): void
    {
        $this->useManualLookup = !$this->useManualLookup;
        $this->useQrScanner = false;
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Scan PhilID')
                ->description('Scan the resident\'s PhilID QR Code')
                ->schema([
                    View::make('livewire.officials.resident-registration'),
                ]),
            Step::make('Review & Input')
                ->description('Verify and complete resident data')
                ->schema(
                    $this->getResource()::getFormSchema()
                ),
        ];
    }

    #[On('resident-selected')]
    public function onResidentSelected($resident): void
    {
        Log::info('Resident selected via manual lookup', ['data' => $resident]);

        $this->populateFromPortal($resident);

        // Explicitly fill the form state. In Filament 3 wizards, this is crucial
        // to sync the nested data property to the UI components.
        $this->form->fill($this->data);

        Notification::make()
            ->title('Resident Selected')
            ->success()
            ->body('Existing data has been pre-filled. Please review and complete the registration.')
            ->send();

        $this->dispatch('next-wizard-step');
    }

    public function processPhilId($rawString)
    {
        $bataeno = app(BataenoService::class);
        $mappedData = [];

        Log::info('Processing QR scan', ['payload' => $rawString]);

        // 1. Try to decode the JSON first (PhilID VDS or eGovPH JSON Export)
        $data = json_decode($rawString, true);

        $hasError = false;
        try {
            if ($data) {
                Log::info('JSON decoded from QR', ['json' => $data]);

                // Map immediately to get names for API search
                $tempMapped = $bataeno->mapPortalData($data);
                $govData = null;

                if (!empty($tempMapped['first_name']) && !empty($tempMapped['last_name'])) {
                    Log::info('Prioritizing Portal search (Name/Bday)', [
                        'name' => $tempMapped['first_name'] . ' ' . $tempMapped['last_name'],
                        'bday' => $tempMapped['date_of_birth'] ?? null
                    ]);
                    $govData = $bataeno->findUserByNameAndBirthday($tempMapped);
                }

                if (!$govData) {
                    Log::info('Portal search failed. Trying direct verify-qr fallback.');
                    $govData = $bataeno->verifyQr($rawString);
                }

                if ($govData) {
                    Log::info('Portal User found/resolved', ['data' => $govData]);
                    $mappedData = $bataeno->mapPortalData($govData);
                } else {
                    // Case B: Not in portal. Auto-register!
                    Log::info('Resident not in portal. Attempting auto-registration to Portal.');
                    $registered = $bataeno->registerToPortal($data);

                    if ($registered) {
                        Notification::make()
                            ->title('Bataan Portal Registration')
                            ->info()
                            ->body('This resident has been automatically registered to the Bataan Portal.')
                            ->send();

                        $mappedData = $bataeno->mapPortalData($registered['user'] ?? $registered['data'] ?? $registered);
                    } else {
                        // Fallback to raw data if all else fails
                        Log::info('All portal resolution failed. Falling back to raw scan data.');
                        $mappedData = $tempMapped;
                    }
                }
            } else {
                // Case C: Try the new verify-qr endpoint directly for non-JSON payloads
                $govData = $bataeno->verifyQr($rawString);

                if ($govData) {
                    Log::info('Portal Verify-QR success', ['data' => $govData]);
                    $mappedData = $bataeno->mapPortalData($govData);
                } elseif (preg_match('/^[a-f\d]{8}-(?:[a-f\d]{4}-){3}[a-f\d]{12}$/i', $rawString)) {
                    // Case D: Raw UUID fallback (verify-card)
                    Log::info('UUID detected, attempting verify-card', ['uuid' => $rawString]);
                    $govData = $bataeno->verifyCard($rawString);
                    if ($govData) {
                        $mappedData = $bataeno->mapPortalData($govData);
                    } else {
                        $this->data['uuid'] = $rawString;
                        $this->form->fill($this->data);
                    }
                }
            }
        } catch (\Exception $e) {
            $hasError = true;
            Log::error('Portal API Error during scan', ['message' => $e->getMessage()]);
            Notification::make()
                ->title('Portal Connection Error')
                ->danger()
                ->body($e->getMessage())
                ->persistent()
                ->send();

            // Still try to map whatever we had if it was JSON
            if ($data) {
                $mappedData = $bataeno->mapPortalData($data);
            }
        }

        // Check if we actually mapped anything useful (at least a name and birthday)
        $hasSignificantData = !empty($mappedData['first_name']) && !empty($mappedData['last_name']) && !empty($mappedData['date_of_birth']);

        if ($hasSignificantData) {
            Log::info('Successful mapping from QR', ['mapped' => $mappedData]);
            $this->populateFromPortal($mappedData);
            $this->form->fill($this->data);

            if (!$hasError) {
                Notification::make()
                    ->title('Fields Pre-filled')
                    ->success()
                    ->body('Scanned data has been mapped to the registration form.')
                    ->send();
            }

            $this->dispatch('next-wizard-step');
        } else {
            Log::warning('QR scan failed to map significant data');

            if (!$hasError) {
                Notification::make()
                    ->title('Identification Failed')
                    ->warning()
                    ->body('We couldn\'t retrieve enough information from this QR code. You may need to fill the form manually.')
                    ->send();
            }

            // Still advance if it's a UUID at least? 
            if (!empty($this->data['uuid'])) {
                $this->dispatch('next-wizard-step');
            }
        }
    }

    protected function populateFromPortal(array $mappedData): void
    {
        // Handle Date format parsing
        if (!empty($mappedData['date_of_birth'])) {
            try {
                $mappedData['date_of_birth'] = \Carbon\Carbon::parse($mappedData['date_of_birth'])->format('Y-m-d');
            } catch (\Exception $e) {
                $mappedData['date_of_birth'] = null;
            }
        }

        // Normalise Gender strings to Match Select Options
        if (!empty($mappedData['gender'])) {
            $g = strtolower($mappedData['gender']);
            if (str_starts_with($g, 'm'))
                $mappedData['gender'] = 'Male';
            elseif (str_starts_with($g, 'f'))
                $mappedData['gender'] = 'Female';
        }

        // Inject into the Filament form state ($this->data)
        foreach ($mappedData as $key => $value) {
            if ($value !== '' && $value !== null) {
                $this->data[$key] = $value;
            }
        }
    }
}
