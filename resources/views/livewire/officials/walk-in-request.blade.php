<div>
    <div class="p-4 bg-white rounded shadow-sm">

        {{-- include scanner UI --}}
        <div class="max-w-5xl mx-auto">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-6 border-b">
                    <div class="flex items-start gap-6">
                        <div class="flex-shrink-0">
                            <div class="w-24 h-24 rounded-lg bg-gray-100 flex items-center justify-center text-2xl font-bold text-gray-500">
                                {{-- avatar placeholder --}}
                                <span class="uppercase">N</span>
                            </div>
                        </div>

                        <div class="flex-1">
                            <h1 class="text-2xl font-semibold text-gray-900">@if($resident){{ $resident['name'] }}@else Walk-in Request @endif</h1>
                            <p class="text-sm text-gray-500 mt-1">@if($uid)UID: <span class="font-mono text-gray-700">{{ $uid }}</span>@else Tap a card to begin @endif</p>
                            @if($resident)
                                <p class="text-xs text-gray-500 mt-1">@if(isset($resident['raw']['uuid']))Bataeno UUID: <span class="font-mono">{{ $resident['raw']['uuid'] }}</span>@endif</p>
                            @endif
                        </div>

                        <div class="text-right">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/50 text-sm font-medium text-gray-700">
                                @if($resident)
                                    <span class="text-emerald-700">Card Verified</span>
                                @elseif($uid)
                                    <span class="text-yellow-600">Scanning…</span>
                                @else
                                    <span class="text-gray-500">Awaiting Scan</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            {{-- Resident Details Box (styled like provided screenshot) --}}
                            <div class="bg-white text-gray-900 p-6 rounded-lg border border-gray-100 shadow-sm">
                                <div class="space-y-4">
                                    <livewire:officials.nfc-listener />
                                    <livewire:officials.nfc-resident-lookup />
                                </div>

                                @if($resident)
                                    <div class="mt-4 text-xs text-gray-600">
                                        <strong class="text-gray-800">Status:</strong> Card verified · Bataeno Pass
                                    </div>
                                @endif
                            </div>


                        </div>

                        {{-- Right column removed to simplify UI; info now shown in the white resident card above. --}}
                    </div>

                    {{-- Request form --}}
                    {{-- keep uid in sync with Livewire component --}}
                    <input type="hidden" wire:model="uid">
                    <div class="mt-6 bg-white border rounded p-4">
                        <h3 class="text-sm font-semibold text-gray-800">Create Request</h3>
                        <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs text-gray-600">Document Type</label>
                                <select wire:model="document_type" class="mt-1 block w-full border rounded p-2">
                                    <option value="">Select</option>
                                    @foreach(DB::table('document_type_properties')->get() as $dt)
                                        <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs text-gray-600">&nbsp;</label>
                                <button wire:click.prevent="submit" class="w-full inline-flex items-center justify-center px-3 py-2 bg-blue-600 text-white rounded">Submit</button>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="block text-xs text-gray-600">Purpose</label>
                            <input wire:model="purpose" class="mt-1 block w-full border rounded p-2" />
                        </div>
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
