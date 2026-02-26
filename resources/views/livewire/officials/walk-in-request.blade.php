<div>
    <div class="p-4 bg-white rounded shadow-sm">

        {{-- include scanner UI --}}
        <div class="max-w-5xl mx-auto">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-4 py-4 border-b">
                    <div class="flex items-start gap-6">

                        

                    </div>
                </div>

                <div class="px-4 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-stretch">
                        <div class="md:col-span-3">
                            {{-- Resident Details Box (white-themed layout similar to screenshot) --}}
                            <div class="bg-white text-gray-900 p-4 rounded-lg border border-gray-100 shadow-sm h-full flex flex-col justify-between min-h-[220px]">
                                <div class="flex items-center gap-6">
                                    <div class="flex-shrink-0">
                                        <div class="w-40 h-40 md:w-48 md:h-48 rounded-lg bg-gray-100 flex items-center justify-center text-5xl md:text-6xl font-bold text-gray-500">
                                            {{-- Avatar placeholder/initials --}}
                                            <span class="uppercase">{{ strtoupper(substr(data_get($resident, 'first_name', 'N'), 0, 1)) }}</span>
                                        </div>
                                    </div>

                                        <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-4">
                                                <div>
                                                    <h2 class="text-3xl font-semibold text-gray-900 leading-tight">
                                                        {{ data_get($resident, 'name') ?? trim((data_get($resident,'first_name','')) . ' ' . (data_get($resident,'last_name',''))) ?: 'Unknown Resident' }}
                                                    </h2>
                                                    <p class="text-xs text-gray-500 mt-1 font-mono">{{ data_get($resident, 'raw.uuid') ?? $uid ?? '' }}</p>
                                                </div>

                                                <div>
                                                    @if($resident)
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-sm font-medium">
                                                            Card Verified
                                                        </span>
                                                    @elseif($uid)
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-sm font-medium">
                                                            Scanning…
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-sm font-medium">
                                                            Awaiting Scan
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-700">
                                            <div>
                                                <div class="mb-2">
                                                    <div class="text-xs text-gray-500 uppercase">Birthday</div>
                                                    <div class="font-medium">{{ data_get($resident,'birthdate') ?? data_get($resident,'date_of_birth') ?? '-' }}</div>
                                                </div>

                                                <div class="mb-2">
                                                    <div class="text-xs text-gray-500 uppercase">Mobile Number</div>
                                                    <div class="font-medium">{{ data_get($resident,'contact_number') ?? data_get($resident,'phone') ?? '-' }}</div>
                                                </div>

                                                <div class="mb-2">
                                                    <div class="text-xs text-gray-500 uppercase">Address</div>
                                                    <div class="font-medium">{{ data_get($resident,'address') ?? (data_get($resident,'barangay_name') ? data_get($resident,'barangay_name') . ', ' . (data_get($resident,'municity_name') ?? '') : (data_get($resident,'city') ?? '-')) }}</div>
                                                </div>
                                            </div>

                                            <div>
                                                <div class="mb-2">
                                                    <div class="text-xs text-gray-500 uppercase">Birthplace</div>
                                                    <div class="font-medium">{{ data_get($resident,'birth_place') ?? '-' }}</div>
                                                </div>

                                                <div class="mb-2">
                                                    <div class="text-xs text-gray-500 uppercase">Civil Status</div>
                                                    <div class="font-medium">{{ data_get($resident,'civil_status') ?? '-' }}</div>
                                                </div>

                                                <div class="mb-3">
                                                    <div class="text-xs text-gray-500 uppercase">Sex</div>
                                                    <div class="font-medium">{{ data_get($resident,'sex') ?? data_get($resident,'gender') ?? '-' }}</div>
                                                </div>

                                                <div class="mb-3">
                                                    <div class="text-xs text-gray-500 uppercase">Email Address</div>
                                                    <div class="font-medium">{{ data_get($resident,'email') ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Keep invisible Livewire listener components for socket/UI hooks --}}
                                <div class="mt-4 space-y-2">
                                    <livewire:officials.nfc-listener />
                                    <livewire:officials.nfc-resident-lookup />
                                </div>
                            </div>


                        </div>

                        {{-- Right column removed to simplify UI; info now shown in the white resident card above. --}}
                    </div>

                    {{-- Request form: embed the DocumentRequestForm Livewire component for inline document requests --}}
                    {{-- keep uid in sync with Livewire component --}}
                    <input type="hidden" wire:model="uid">
                    <div class="mt-6">
                        <livewire:document-request-form :embedded="true" />
                    </div>
                </div>
            </div>
        </div>

      <script>
                // Listen for the scanner shouting that a card was tapped AND data was found
                window.addEventListener('nfc:owner', (e) => {
                    const uid = e.detail?.uid;
                    const residentData = e.detail?.resident;

                    if (!uid) return;

                    // Emit a single-object payload to Livewire (preferred) and
                    // provide fallbacks for older Livewire APIs and direct calls.
                    try {
                        if (window.Livewire) {
                            if (typeof Livewire.dispatch === 'function') {
                                // Livewire 3 syntax
                                Livewire.dispatch('nfcUidTapped', { uid: uid, resident: residentData });
                            } else if (typeof Livewire.emit === 'function') {
                                // Livewire 2+ syntax: emit a single object (works with our component)
                                Livewire.emit('nfcUidTapped', { uid: uid, resident: residentData });
                            } else if (typeof Livewire.find === 'function') {
                                // Best-effort direct call to the first Livewire component on page
                                try {
                                    const list = document.querySelectorAll('[wire\:id]');
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
                    // optionally show a small visible trace
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
