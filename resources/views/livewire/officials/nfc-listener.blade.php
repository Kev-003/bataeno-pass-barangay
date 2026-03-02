<section
    class="fi-section p-6 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

        {{-- Left Column: Connection & UIDs --}}
        <div class="space-y-6">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                    System Status
                </p>
                <div class="mt-2 flex items-center gap-3">
                    <span class="relative flex h-3 w-3 items-center justify-center">
                        @if($connected)
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        @else
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                        @endif
                    </span>

                    <div>
                        <div @class([
                            'text-lg font-bold leading-none transition',
                            'text-emerald-600 dark:text-emerald-400' => $connected,
                            'text-gray-500 dark:text-gray-400' => !$connected,
                        ])>
                            {{ $connected ? 'Reader Online' : 'Reader Offline' }}
                        </div>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                            {{ $connected ? 'Ready for resident tap' : 'Check local bridge service' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Card UID
                    </p>
                    <div class="mt-1">
                        <code
                            class="rounded bg-gray-100 px-2 py-1 text-xs font-mono text-gray-700 dark:bg-white/5 dark:text-gray-300 border border-gray-200 dark:border-white/10">
                            {{ $cardUid ?? 'Waiting...' }}
                        </code>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Verified
                        ID</p>
                    <div class="mt-1">
                        <code @class([
                            'rounded px-2 py-1 text-xs font-mono border transition',
                            'bg-primary-50 text-primary-700 border-primary-200 dark:bg-primary-400/10 dark:text-primary-400 dark:border-primary-400/20' => $verifiedUid,
                            'bg-gray-50 text-gray-400 border-gray-200 dark:bg-white/5 dark:text-gray-500 dark:border-white/10' => !$verifiedUid,
                        ])>
                            {{ $verifiedUid ?? 'None' }}
                        </code>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Logs & Errors --}}
        <div
            class="space-y-6 border-t border-gray-100 pt-6 md:border-l md:border-t-0 md:pl-6 md:pt-0 dark:border-white/5">
            <div>
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Live Activity</h3>
                <div class="mt-2 max-h-24 overflow-y-auto rounded-lg bg-gray-50 p-3 dark:bg-black/20">
                    @if(empty($readerStatus))
                        <p class="text-xs italic text-gray-400">Scanning for events...</p>
                    @else
                        <ul class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
                            @foreach(array_reverse($readerStatus) as $entry)
                                <li class="flex items-center gap-2">
                                    <span class="h-1 w-1 rounded-full bg-gray-400"></span>
                                    {{ $entry }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            @if(!empty($readErrors))
                <div>
                    <h3 class="text-sm font-semibold text-danger-600 dark:text-danger-400">Hardware Warnings</h3>
                    <ul class="mt-2 space-y-1 text-xs text-danger-500">
                        @foreach($readErrors as $err)
                            <li class="flex items-start gap-2">
                                <x-heroicon-m-exclamation-triangle class="h-3 w-3 mt-0.5 flex-shrink-0" />
                                {{ $err }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    {{-- NFC Socket Bridge --}}
    <script src="https://cdn.jsdelivr.net/npm/socket.io-client@4.8.3/dist/socket.io.min.js"></script>
    @script
    <script>
        (() => {
            const SOCKET_FALLBACKS = [
                "{{ env('NFC_SOCKET_URL', 'http://127.0.0.1:3000') }}",
                'http://localhost:3000'
            ];

            async function tryConnect(endpoints) {
                for (const endpoint of endpoints) {
                    try {
                        const socket = io(endpoint, { transports: ['websocket'] });

                        socket.on('connect', () => $wire.dispatch('nfc:connect'));
                        socket.on('disconnect', () => $wire.dispatch('nfc:disconnect'));

                        // Normalized event listeners
                        const handleUid = (uid) => $wire.dispatch('nfc:cardUid', { uid });
                        socket.on('card-uid', handleUid);
                        socket.on('card_uid', handleUid);

                        const handleVerified = (uid) => $wire.dispatch('nfc:verifiedUid', { uid });
                        socket.on('verified_uid', handleVerified);

                        socket.on('read-error', (err) => $wire.dispatch('nfc:readError', { err }));

                        const connected = await new Promise((resolve) => {
                            const t = setTimeout(() => resolve(false), 800);
                            socket.once('connect', () => { clearTimeout(t); resolve(true); });
                        });

                        if (connected) return socket;
                        socket.close();
                    } catch (e) { }
                }
                return null;
            }

            tryConnect(SOCKET_FALLBACKS);
        })();
    </script>
    @endscript
</section>