<div class="fi-section p-6">
    <div class="max-w-4xl mx-auto">
        {{-- Progress Navigation --}}
        <div class="flex items-center justify-center mt-4 mb-16" style="margin-bottom: 2rem;">
            <nav class="flex items-center gap-2 sm:gap-4">
                @foreach([[1, 'Select Details'], [2, 'Scan Resident'], [3, 'Confirm Request']] as [$num, $label])
                    <div class="flex items-center gap-2 sm:gap-4">
                        
                        {{-- Step Indicator Pill --}}
                        <div @class([
                            'flex items-center gap-3 px-4 py-2.5 rounded-full text-sm font-bold transition-all duration-200',
                            'bg-primary-600 text-white shadow-md ring-4 ring-primary-600/10' => $step === $num, // Active Step
                            'bg-success-50 dark:bg-success-500/10 text-success-600 dark:text-success-400 ring-1 ring-success-500/30' => $step > $num, // Completed Step
                            'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 ring-1 ring-gray-200 dark:ring-white/10' => $step < $num, // Upcoming Step
                        ])>
                            {{-- Number / Check Icon Container --}}
                            <div @class([
                                'flex items-center justify-center w-6 h-6 rounded-full text-xs transition-colors',
                                'bg-white/20 text-white' => $step === $num,
                                'bg-success-200 dark:bg-success-500/20 text-success-700 dark:text-success-300' => $step > $num,
                                'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300' => $step < $num,
                            ])>
                                @if($step > $num)
                                    <x-heroicon-m-check class="w-4 h-4" />
                                @else
                                    {{ $num }}
                                @endif
                            </div>
                            
                            {{-- Step Label --}}
                            <span>{{ $label }}</span>
                        </div>

                        {{-- Next Step Arrow (Hidden on the last step) --}}
                        @if(!$loop->last)
                            <div class="flex items-center text-gray-400 dark:text-gray-500">
                                <x-heroicon-m-chevron-right class="w-6 h-6" />
                            </div>
                        @endif

                    </div>
                @endforeach
            </nav>
        </div>

        {{-- Main Card --}}
        <div class="fi-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl overflow-hidden shadow-sm">
            
            {{-- STEP 1: Document Details --}}
            @if($step === 1)
                <div>
                    <x-filament::section class="p-6">
                        <x-slot name="heading">Document Details</x-slot>
                        <x-slot name="description">Choose the document type and purpose for this walk-in request.</x-slot>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                            {{-- Document Type Field --}}
                            <div>
                                <div class="mb-1 flex items-center justify-between">
                                    <label class="text-sm font-medium leading-6 text-gray-950 dark:text-white" for="document_type">
                                        Document Type <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                                    </label>
                                </div>
                                <x-filament::input.wrapper :valid="!$errors->has('document_type')">
                                    <x-filament::input.select wire:model="document_type" id="document_type">
                                        <option value="">Select a document</option>
                                        @foreach($documentTypes as $dt)
                                            <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                                        @endforeach
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>
                                @error('document_type')
                                    <p class="text-sm text-danger-600 dark:text-danger-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Purpose Field --}}
                            <div>
                                <div class="mb-1 flex items-center justify-between">
                                    <label class="text-sm font-medium leading-6 text-gray-950 dark:text-white" for="purpose">
                                        Purpose <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                                    </label>
                                </div>
                                <x-filament::input.wrapper :valid="!$errors->has('purpose')">
                                    <x-filament::input type="text" wire:model="purpose" id="purpose" placeholder="Enter reason..." />
                                </x-filament::input.wrapper>
                                @error('purpose')
                                    <p class="text-sm text-danger-600 dark:text-danger-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Filament Native Footer Action --}}
                        <x-slot name="footerActions">
                            <div class="flex justify-end w-full">
                                <x-filament::button wire:click="proceedToScan" color="primary" icon="heroicon-m-arrow-right" icon-position="after">
                                    Next: Scan Resident Card
                                </x-filament::button>
                            </div>
                        </x-slot>
                    </x-filament::section>
                </div>
            @endif

            {{-- STEP 2: Scan NFC --}}
            <div>
                <x-filament::section class="{{ $step === 2 ? : 'hidden' }}">
                    <x-slot name="heading">Scan Resident ID</x-slot>
                    <x-slot name="description">Tap the resident's NFC card to the reader to verify their identity.</x-slot>
                    
                    {{-- Native Header Actions for Selected Document --}}
                    <x-slot name="headerEnd">
                        <div class="flex items-center gap-3">
                            <x-filament::badge color="info" icon="heroicon-m-document-text">
                                {{ $this->getSelectedDocumentName() }}
                            </x-filament::badge>
                            
                            <x-filament::button color="gray" variant="outline" size="sm" wire:click="backToDocumentSelect" icon="heroicon-m-pencil-square">
                                Change
                            </x-filament::button>
                        </div>
                    </x-slot>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                        <livewire:officials.nfc-listener />
                        <livewire:officials.nfc-resident-lookup />
                    </div>
                </x-filament::section>
            </div>

            {{-- STEP 3: Confirm Details --}}
            @if($step === 3 && $resident)
                <div class="p-6 space-y-6">
                    {{-- Selected Document Header --}}
                    <div class="flex items-center justify-between p-4 bg-primary-50 dark:bg-primary-500/10 rounded-xl border border-primary-100 dark:border-primary-500/20">
                        <div>
                            <p class="text-xs text-primary-600 dark:text-primary-400 font-semibold uppercase tracking-wider">Document to Process</p>
                            <p class="text-base font-bold text-primary-800 dark:text-primary-300 mt-1">{{ $this->getSelectedDocumentName() }}</p>
                        </div>
                        <x-filament::button color="primary" variant="text" size="sm" wire:click="backToScan">
                            Re-scan Card
                        </x-filament::button>
                    </div>

                    {{-- Resident Information Section --}}
                    <x-filament::section compact>
                        <x-slot name="heading">Resident Details</x-slot>

                        <div class="flex flex-col md:flex-row items-start gap-6">
                            <div class="w-24 h-24 rounded-xl overflow-hidden flex-shrink-0 bg-gray-100 dark:bg-gray-800 flex items-center justify-center ring-1 ring-gray-950/10 dark:ring-white/20">
                                @if($resident['profile_photo'] ?? null)
                                    <img src="{{ $resident['profile_photo'] }}" alt="Profile" class="w-full h-full object-cover"/>
                                @else
                                    <span class="text-3xl font-bold text-gray-500 dark:text-gray-400">{{ $this->getInitials() }}</span>
                                @endif
                            </div>

                            <div class="flex-1 w-full">
                                <div class="flex items-center gap-3 flex-wrap mb-1">
                                    <h3 class="text-xl font-bold text-gray-950 dark:text-white">{{ $resident['name'] ?? '—' }}</h3>
                                    <x-filament::badge color="success" icon="heroicon-m-check-circle">
                                        Verified
                                    </x-filament::badge>
                                </div>
                                <p class="text-sm font-mono text-gray-500 dark:text-gray-400 mb-4">{{ $resident['uuid'] ?? $uid }}</p>

                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    @php
                                        $fields = [
                                            ['Birthday',     $resident['birthdate_formal'] ?? $resident['birthdate'] ?? null],
                                            ['Sex',          $resident['sex'] ?? null],
                                            ['Civil Status', $resident['civil_status'] ?? null],
                                            ['Mobile',       $resident['contact_number'] ?? null],
                                            ['Birthplace',   $resident['birth_place'] ?? null],
                                            ['Address',      $resident['address'] ?? null],
                                        ];
                                    @endphp
                                    @foreach($fields as [$label, $value])
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
                                            <p class="text-sm font-medium text-gray-950 dark:text-white truncate">{{ $value ?: '—' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </x-filament::section>

                    {{-- Purpose Details --}}
                    <x-filament::section compact>
                        <x-slot name="heading">Purpose of Request</x-slot>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $purpose }}</p>
                    </x-filament::section>

                    {{-- Dynamic Document Fields Section --}}
                    <x-filament::section compact>
                        <x-slot name="heading">Document Specifics</x-slot>
                        <x-slot name="description">Auto-filled values can be edited before submitting.</x-slot>

                        @if(empty($documentFields))
                            <p class="text-sm text-gray-500 dark:text-gray-400 italic">No additional fields required for this document.</p>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                @foreach($documentFields as $field => $value)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                            {{ $documentFieldLabels[$field] ?? \Illuminate\Support\Str::headline($field) }}
                                            <span class="text-danger-600">*</span>
                                        </label>
                                        
                                        <x-filament::input.wrapper :valid="!$errors->has('documentFields.'.$field)">
                                            <x-filament::input
                                                type="text"
                                                wire:model="documentFields.{{ $field }}"
                                                placeholder="Enter {{ strtolower($documentFieldLabels[$field] ?? \Illuminate\Support\Str::headline($field)) }}"
                                            />
                                        </x-filament::input.wrapper>

                                        @error("documentFields.$field")
                                            <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </x-filament::section>

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-between pt-4">
                        <x-filament::button color="gray" variant="outline" wire:click="backToScan" icon="heroicon-m-arrow-left">
                            Back
                        </x-filament::button>

                        <div class="flex items-center gap-4">
                            <button wire:click="backToScan" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition underline">
                                Wrong resident?
                            </button>
                            
                            <x-filament::button 
                                wire:click="openSubmitConfirmation" 
                                color="primary" 
                                icon="heroicon-m-check-circle"
                                wire:target="openSubmitConfirmation,submit"
                            >
                                <span wire:loading.remove wire:target="openSubmitConfirmation,submit">Submit Request</span>
                                <span wire:loading wire:target="openSubmitConfirmation,submit">Processing...</span>
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Submission Confirmation Modal --}}
    @if($showCardConfirmationModal && $step === 3 && $resident)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/40 transition-opacity p-4">
            <x-filament::modal id="confirm-card-modal" display-classes="block" width="md" alignment="center">
                <x-slot name="heading">
                    Confirm Submission
                </x-slot>

                <x-slot name="description">
                    Please confirm all resident and document details before submitting this walk-in request.
                </x-slot>

                <x-slot name="footerActions">
                    <x-filament::button color="gray" variant="outline" wire:click="closeSubmitConfirmation">
                        Back
                    </x-filament::button>
                    
                    <x-filament::button color="primary" wire:click="submit">
                        Confirm & Submit
                    </x-filament::button>
                </x-slot>
            </x-filament::modal>
        </div>
    @endif

    {{-- Scripts --}}
    <script>
        window.addEventListener('nfc:owner', (e) => {
            $wire.dispatch('nfcUidTapped', { uid: e.detail.uid, resident: e.detail.resident });
        });

        window.addEventListener('walkin:success', () => {
            const NotificationClass = window.FilamentNotification ?? window.Filament?.Notification;

            if (!NotificationClass) {
                return;
            }

            new NotificationClass()
                .title('Success')
                .body('Walk-in request created successfully.')
                .success()
                .send();
        });
    </script>
</div>