<div class="p-4 bg-white rounded shadow-sm">
    <div class="max-w-3xl mx-auto">

        {{-- ── Step indicator ────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-0 mb-6">
            @foreach([
                [1, 'Select Document'],
                [2, 'Scan Card'],
                [3, 'Confirm & Submit'],
            ] as [$num, $label])
                <div class="flex items-center {{ ! $loop->first ? 'flex-1' : '' }}">
                    @if(! $loop->first)
                        <div class="flex-1 h-px {{ $step > $num - 1 ? 'bg-indigo-500' : 'bg-gray-200' }}"></div>
                    @endif
                    <div class="flex items-center gap-2 {{ $loop->first ? '' : 'ml-2' }}">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                            {{ $step === $num ? 'bg-indigo-600 text-white' : ($step > $num ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-500') }}">
                            @if($step > $num)
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        <span class="text-sm font-medium {{ $step === $num ? 'text-indigo-600' : ($step > $num ? 'text-emerald-600' : 'text-gray-400') }}">
                            {{ $label }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

            {{-- ════════════════════════════════════════════════════════════════
                 STEP 1 — Select Document Type
            ════════════════════════════════════════════════════════════════ --}}
            @if($step === 1)
                <div class="px-6 py-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-1">Select Document</h2>
                    <p class="text-sm text-gray-500 mb-6">Ask the resident which document they need, then select it below.</p>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                Document Type <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="document_type"
                                    class="block w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">— Select a document —</option>
                                @foreach($documentTypes as $dt)
                                    <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                                @endforeach
                            </select>
                            @error('document_type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                Purpose <span class="text-red-500">*</span>
                            </label>
                            <input wire:model="purpose"
                                   type="text"
                                   placeholder="e.g. For employment, For school requirements…"
                                   class="block w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                            @error('purpose')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button wire:click="proceedToScan"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                            Next: Scan Card
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- ════════════════════════════════════════════════════════════════
                 STEP 2 — Scan Bataeno Pass Card (shared NFC components)
            ════════════════════════════════════════════════════════════════ --}}
            <div class="{{ $step === 2 ? 'block' : 'hidden' }}">
                <div class="px-6 py-6">

                    {{-- Selected document summary --}}
                    <div class="flex items-center justify-between mb-6 p-3 bg-indigo-50 border border-indigo-100 rounded-lg">
                        <div>
                            <p class="text-xs text-indigo-500 font-semibold uppercase tracking-wide">Selected Document</p>
                            <p class="text-sm font-semibold text-indigo-800 mt-0.5">{{ $this->getSelectedDocumentName() }}</p>
                        </div>
                        <button wire:click="backToDocumentSelect"
                                class="text-xs text-indigo-500 hover:text-indigo-700 font-medium underline">
                            Change
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            {{-- Socket bridge + reader status (centralized) --}}
                            <livewire:officials.nfc-listener />
                        </div>

                        <div>
                            {{-- Resident lookup / verification UI --}}
                            <livewire:officials.nfc-resident-lookup />
                        </div>
                    </div>

                    <div class="mt-4 flex justify-start">
                        <button wire:click="backToDocumentSelect"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:text-gray-800 font-medium rounded-lg border border-gray-300 hover:border-gray-400 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                            </svg>
                            Back
                        </button>
                    </div>
                </div>
       

            {{-- ════════════════════════════════════════════════════════════════
                 STEP 3 — Confirm & Submit
            ════════════════════════════════════════════════════════════════ --}}
            @if($step === 3 && $resident)
                <div class="px-6 py-6">

                    {{-- Document + resident summary header --}}
                    <div class="flex items-center justify-between mb-5 p-3 bg-indigo-50 border border-indigo-100 rounded-lg">
                        <div>
                            <p class="text-xs text-indigo-500 font-semibold uppercase tracking-wide">Document</p>
                            <p class="text-sm font-semibold text-indigo-800 mt-0.5">{{ $this->getSelectedDocumentName() }}</p>
                        </div>
                        <button wire:click="backToScan"
                                class="text-xs text-indigo-500 hover:text-indigo-700 font-medium underline">
                            Re-scan
                        </button>
                    </div>

                    {{-- Resident card --}}
                    <div class="flex items-start gap-5 p-4 bg-gray-50 border border-gray-200 rounded-xl mb-6">

                        {{-- Avatar --}}
                        <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 bg-gradient-to-br from-sky-400 to-indigo-500 flex items-center justify-center shadow">
                            @if($resident['profile_photo'] ?? null)
                                <img src="{{ $resident['profile_photo'] }}" alt="Profile" class="w-full h-full object-cover"/>
                            @else
                                <span class="text-2xl font-bold text-white">{{ $this->getInitials() }}</span>
                            @endif
                        </div>

                        {{-- Details --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-lg font-bold text-gray-900">{{ $resident['name'] ?? '—' }}</h3>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/>
                                    </svg>
                                    Verified
                                </span>
                            </div>
                            <p class="text-xs font-mono text-gray-400 mt-0.5">{{ $resident['uuid'] ?? $uid }}</p>

                            <div class="mt-3 grid grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-2 text-sm">
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
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ $label }}</p>
                                        <p class="font-medium text-gray-700 mt-0.5 truncate">{{ $value ?: '—' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Purpose summary --}}
                    <div class="mb-6 p-3 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                        <span class="text-xs font-bold uppercase tracking-wide text-gray-400">Purpose</span>
                        <p class="text-gray-700 mt-0.5">{{ $purpose }}</p>
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex items-center justify-between">
                        <button wire:click="backToScan"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:text-gray-800 font-medium rounded-lg border border-gray-300 hover:border-gray-400 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                            </svg>
                            Back
                        </button>

                        <button wire:click="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-semibold rounded-lg transition">
                            <span wire:loading.remove wire:target="submit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                            </span>
                            <span wire:loading.remove wire:target="submit">Submit Request</span>
                            <span wire:loading wire:target="submit">Submitting…</span>
                        </button>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- NFC socket bridge handled by <livewire:officials.nfc-listener /> --}}
    
    <script>
        // Listen for the scanner shouting that a card was tapped AND data was found
        window.addEventListener('nfc:owner', (e) => {
            const uid = e.detail?.uid;
            const residentData = e.detail?.resident;

            if (!uid) return;

            try {
                if (window.Livewire) {
                    if (typeof Livewire.dispatch === 'function') {
                        Livewire.dispatch('nfcUidTapped', { uid: uid, resident: residentData });
                    } else if (typeof Livewire.emit === 'function') {
                        Livewire.emit('nfcUidTapped', { uid: uid, resident: residentData });
                    } else if (typeof Livewire.find === 'function') {
                        try {
                            const list = document.querySelectorAll('[wire\\:id]');
                            if (list && list.length) {
                                const id = list[0].getAttribute('wire:id');
                                Livewire.find(id).call('onNfcUid', { uid: uid, resident: residentData });
                            }
                        } catch (ie) {
                            console.debug('Livewire direct call failed', ie);
                        }
                    }
                }
            } catch (ex) {
                console.debug('Livewire emit error', ex);
            }
        });

        window.addEventListener('walkin:success', (e) => {
            alert('Request created: ' + e.detail.transaction_id);
        });

        window.addEventListener('walkin:error', (e) => {
            alert('Error: ' + e.detail.message);
        });

        // Debug: show when Livewire dispatched back to the browser
        window.addEventListener('nfc:received', (e) => {
            console.debug('nfc:received event from Livewire', e.detail);
            let dbg = document.getElementById('nfc-debug');
            if (!dbg) {
                dbg = document.createElement('div');
                dbg.id = 'nfc-debug';
                dbg.className = 'mt-2 text-xs text-gray-500';
                document.querySelector('.px-6.py-6')?.appendChild(dbg);
            }
            dbg.textContent = 'Livewire received UID: ' + (e.detail?.uid || '<none>');
        });
    </script>
</div>