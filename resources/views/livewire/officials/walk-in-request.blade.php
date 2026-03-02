<div class="fi-section p-6">
    <div class="max-w-4xl mx-auto">
        {{-- ── Step indicator (Filament Style) ──────────────────────────────── --}}
        <div class="flex items-center justify-center ">
            <nav class="flex items-center gap-4">
                @foreach([[1, 'Select'], [2, 'Scan'], [3, 'Confirm']] as [$num, $label])
                    <div class="flex items-center gap-2">
                        <div @class([
                            'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition',
                            'bg-primary-600 text-white shadow-md' => $step === $num,
                            'bg-emerald-500 text-white' => $step > $num,
                            'bg-gray-200 dark:bg-gray-800 text-gray-500' => $step < $num,
                        ])>
                            @if($step > $num)
                                <x-heroicon-m-check class="w-5 h-5" />
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        <span @class([
                            'text-sm font-medium',
                            'text-primary-600 dark:text-primary-400' => $step === $num,
                            'text-gray-500 dark:text-gray-400' => $step !== $num,
                        ])>{{ $label }}</span>
                        @if(!$loop->last)
                            <div class="w-8 h-px bg-gray-300 dark:bg-gray-700 ml-2"></div>
                        @endif
                    </div>
                @endforeach
            </nav>
        </div>

        <div
            class="fi-card bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl overflow-hidden shadow-sm">

            {{-- STEP 1 — Selection --}}
            @if($step === 1)
                <div class="p-6 space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Document Details</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Choose the document type and purpose for this
                            walk-in request.</p>
                    </div>

                    <div class="grid gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Document
                                Type</label>
                            <select wire:model="document_type"
                                class="fi-input block w-full border-none bg-gray-50 dark:bg-white/5 text-gray-950 dark:text-white ring-1 ring-gray-950/10 dark:ring-white/20 rounded-lg focus:ring-2 focus:ring-primary-600">
                                <option value="">Select a document</option>
                                @foreach($documentTypes as $dt)
                                    <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                                @endforeach
                            </select>
                            @error('document_type') <p class="text-danger-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Purpose</label>
                            <input wire:model="purpose" type="text"
                                class="fi-input block w-full border-none bg-gray-50 dark:bg-white/5 text-gray-950 dark:text-white ring-1 ring-gray-950/10 dark:ring-white/20 rounded-lg"
                                placeholder="Enter reason...">
                            @error('purpose') <p class="text-danger-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button wire:click="proceedToScan"
                            class="fi-btn fi-btn-color-primary bg-primary-600 px-6 py-2 text-white rounded-lg font-semibold hover:bg-primary-500 transition">
                            Next: Scan Resident Card
                        </button>
                    </div>
                </div>
            @endif

            {{-- STEP 2 — NFC Scan --}}
            <div class="{{ $step === 2 ? 'p-6' : 'hidden' }}">
                <div
                    class="flex items-center justify-between mb-6 py-4 bg-primary-50 dark:bg-primary-500/10 rounded-lg">
                    <span class="text-sm font-bold text-primary-700 dark:text-primary-300">Scanning for:
                        {{ $this->getSelectedDocumentName() }}</span>
                    <button wire:click="backToDocumentSelect" class="text-xs text-primary-600 underline">Change</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <livewire:officials.nfc-listener />
                    <livewire:officials.nfc-resident-lookup />
                </div>
            </div>

            {{-- STEP 3 — Confirm --}}
            @if($step === 3 && $resident)
                <div class="p-6">
                    {{-- Call the existing Form Component --}}
                    <livewire:document-request-form :embedded="true" :is-filament="true" :initial-doc-id="$document_type"
                        :target-resident="$resident" :key="'doc-form-' . ($resident['uuid'] ?? 'new')" />

                    <div class="mt-4 flex justify-start">
                        <button wire:click="backToScan" class="text-xs text-gray-500 hover:underline">
                            ← Wrong resident? Scan again
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Script section cleaned for Filament compatibility --}}
@script
<script>
    window.addEventListener('nfc:owner', (e) => {
        $wire.dispatch('nfcUidTapped', { uid: e.detail.uid, resident: e.detail.resident });
    });

    window.addEventListener('walkin:success', (e) => {
        new FilamentNotification()
            .title('Success')
            .body('Walk-in request created successfully.')
            .success()
            .send();
    });
</script>
@endscript