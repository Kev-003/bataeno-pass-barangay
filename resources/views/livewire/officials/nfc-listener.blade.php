<main class="bp-panel p-6 rounded-2xl font-sans bg-sky-50 border border-sky-100">
    <div class="px-6 py-5 grid grid-cols-2 gap-x-8 gap-y-5">
        <div>
            <p class="text-sky-500 text-[10px] font-bold uppercase tracking-widest">Status</p>
            <div class="mt-2 flex items-center gap-3">
                <span class="relative inline-flex h-4 w-4 items-center justify-center flex-shrink-0">
                    @if($connected)
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-lime-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-lime-500"></span>
                    @else
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-gray-300"></span>
                    @endif
                </span>

                <div>
                    <div class="text-sky-800 font-bold text-lg leading-none">{{ $connected ? 'Connected' : 'Disconnected' }}</div>
                    <div class="text-sky-400 text-xs mt-0.5">{{ $connected ? 'NFC reader is online' : 'NFC reader is offline' }}</div>
                </div>
            </div>

            <p class="text-sky-500 text-[10px] font-bold uppercase tracking-widest mt-4">Current Card UID</p>
            <p class="mt-1"><span class="text-xs font-mono px-2 py-1 bg-white border border-sky-200 rounded">{{ $cardUid ?? 'None' }}</span></p>

            <p class="text-sky-500 text-[10px] font-bold uppercase tracking-widest mt-4">Current Verified UID</p>
            <p class="mt-1"><span class="text-xs font-mono px-2 py-1 bg-white border border-sky-200 rounded">{{ $verifiedUid ?? 'None' }}</span></p>
        </div>

        <div>
            <h2 class="text-sky-800 font-semibold">Reader Status</h2>
            @if(empty($readerStatus))
                <p class="text-sky-500 mt-1">No reader events</p>
            @else
                <ul class="list-disc pl-5 mt-1 text-sky-700">
                    @foreach($readerStatus as $entry)
                        <li class="py-0.5">{{ $entry }}</li>
                    @endforeach
                </ul>
            @endif

            <h2 class="text-sky-800 font-semibold mt-4">Read Errors</h2>
            @if(empty($readErrors))
                <p class="text-sky-500 mt-1">No errors</p>
            @else
                <ul class="list-disc pl-5 mt-1 text-red-700">
                    @foreach($readErrors as $err)
                        <li class="py-0.5">{{ $err }}</li>
                    @endforeach
                </ul>
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
                            if (typeof Livewire.dispatch === 'function') return Livewire.dispatch(event, params);
                            if (typeof Livewire.emit === 'function') return Livewire.emit(event, params);
                        }
                        // fallback: dispatch DOM event for any listeners
                        window.dispatchEvent(new CustomEvent(event, { detail: params }));
                    } catch (e) {
                        console.debug('[NFC] dispatch failed', e);
                    }
                };
            };

            const dispatch = createDispatcher();

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
</main>