<section
    wire:poll.5s="refreshConnectionStatus"
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

    {{-- NFC socket bridge (via @brynrgnzls/nfc-listener from resources/js/app.js) --}}
    <script>
        (async () => {
            const SOCKET_URL = "{{ env('NFC_SOCKET_URL', 'http://127.0.0.1:8001') }}";
            const FALLBACK_URLS = [SOCKET_URL, 'http://127.0.0.1:8001', 'http://localhost:8001'];

            const createDispatcher = () => {
                return (event, params = {}) => {
                    try {
                        if (window.Livewire) {
                            if (typeof window.Livewire.dispatch === 'function') return window.Livewire.dispatch(event, params);
                            if (typeof window.Livewire.emit === 'function') return window.Livewire.emit(event, params);
                        }
                        // fallback: dispatch DOM event for any listeners
                        window.dispatchEvent(new CustomEvent(event, { detail: params }));
                    } catch (e) {
                        console.debug('[NFC] dispatch failed', e);
                    }
                };
            };

            const dispatch = createDispatcher();

            const healthUrls = [...new Set(FALLBACK_URLS.map((url) => `${String(url).replace(/\/$/, '')}/health`))];
            let lastHealthConnected = window.__BATAENO_NFC_CONNECTION_READY === true;

            const probeHealth = async () => {
                for (const healthUrl of healthUrls) {
                    try {
                        const controller = new AbortController();
                        const timeout = setTimeout(() => controller.abort(), 1200);
                        const response = await fetch(healthUrl, {
                            method: 'GET',
                            signal: controller.signal,
                            cache: 'no-store',
                        });
                        clearTimeout(timeout);

                        if (!response.ok) continue;

                        window.__BATAENO_NFC_CONNECTION_READY = true;
                        if (!lastHealthConnected) {
                            dispatch('nfc:connect');
                            lastHealthConnected = true;
                        }
                        return true;
                    } catch (_) {}
                }

                window.__BATAENO_NFC_CONNECTION_READY = false;
                if (lastHealthConnected) {
                    dispatch('nfc:disconnect');
                    lastHealthConnected = false;
                }
                return false;
            };

            const startHealthMonitor = () => {
                if (window.__BATAENO_NFC_HEALTH_TIMER) {
                    return;
                }

                probeHealth();
                window.__BATAENO_NFC_HEALTH_TIMER = window.setInterval(probeHealth, 5000);
            };

            startHealthMonitor();

            if (window.__BATAENO_NFC_CONNECTION_READY) {
                dispatch('nfc:connect');
                return;
            }

            const NfcHandler = window.NfcHandler;
            if (NfcHandler) {
                const nfc = new NfcHandler(SOCKET_URL);
                nfc.onConnect(() => {
                    window.__BATAENO_NFC_CONNECTION_READY = true;
                    dispatch('nfc:connect');
                });
                nfc.onDisconnect(() => {
                    window.__BATAENO_NFC_CONNECTION_READY = false;
                    dispatch('nfc:disconnect');
                });
                nfc.onCardUid((uid) => dispatch('nfc:cardUid', { uid }));
                nfc.onVerifiedUid((uid) => dispatch('nfc:verifiedUid', { uid }));

                nfc.open();
                window.__BATAENO_NFC_HANDLER = nfc;
                return;
            }

            function loadSocketIo() {
                return new Promise((resolve, reject) => {
                    if (typeof window.io !== 'undefined') return resolve(window.io);
                    const s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/socket.io-client@4.8.3/dist/socket.io.min.js';
                    s.async = true;
                    s.onload = () => resolve(window.io);
                    s.onerror = () => reject(new Error('Failed to load socket.io client'));
                    document.head.appendChild(s);
                });
            }

            try {
                await loadSocketIo();
            } catch (e) {
                dispatch('nfc:readError', { err: 'NFC client unavailable (NfcHandler/socket.io).' });
                return;
            }

            for (const endpoint of [...new Set(FALLBACK_URLS)]) {
                try {
                    const socket = window.io(endpoint, {
                        transports: ['websocket', 'polling'],
                        reconnection: true,
                    });

                    const connected = await new Promise((resolve) => {
                        const timer = setTimeout(() => resolve(false), 1200);
                        socket.once('connect', () => {
                            clearTimeout(timer);
                            resolve(true);
                        });
                        socket.once('connect_error', () => {
                            clearTimeout(timer);
                            resolve(false);
                        });
                    });

                    if (!connected) {
                        socket.disconnect();
                        continue;
                    }

                    window.__BATAENO_NFC_CONNECTION_READY = true;
                    window.__BATAENO_NFC_SOCKET = socket;
                    dispatch('nfc:connect');

                    socket.on('disconnect', () => {
                        window.__BATAENO_NFC_CONNECTION_READY = false;
                        dispatch('nfc:disconnect');
                    });
                    socket.on('reader_status', (payload = {}) => {
                        const readerName = payload?.name || 'Reader';
                        if (payload?.online) {
                            dispatch('nfc:readerConnect', { name: readerName });
                            dispatch('nfc:connect');
                        } else {
                            dispatch('nfc:readerDisconnect', { name: readerName });
                            dispatch('nfc:disconnect');
                        }
                    });
                    socket.on('reader-connect', (payload = {}) => {
                        dispatch('nfc:readerConnect', { name: payload?.name || 'Reader' });
                        dispatch('nfc:connect');
                    });
                    socket.on('reader-disconnect', (payload = {}) => {
                        dispatch('nfc:readerDisconnect', { name: payload?.name || 'Reader' });
                        dispatch('nfc:disconnect');
                    });
                    socket.on('card-uid', (uid) => dispatch('nfc:cardUid', { uid }));
                    socket.on('card_uid', (uid) => dispatch('nfc:cardUid', { uid }));
                    socket.on('verified_uid', (uid) => dispatch('nfc:verifiedUid', { uid }));
                    socket.on('verified-user-detail', (uid) => dispatch('nfc:verifiedUid', { uid }));
                    return;
                } catch (_) {}
            }

            dispatch('nfc:disconnect');
            dispatch('nfc:readError', { err: 'Could not connect to NFC server on port 8001.' });
        })();
    </script>
</section>