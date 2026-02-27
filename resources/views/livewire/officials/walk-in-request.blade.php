<div>
    <div class="p-4 bg-white rounded shadow-sm">

        {{-- include scanner UI --}}
        <div class="max-w-5xl mx-auto">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-4 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-stretch">
                        <div class="md:col-span-3">
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
