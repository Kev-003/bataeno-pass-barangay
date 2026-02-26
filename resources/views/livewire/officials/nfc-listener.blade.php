<main>
    <h1>NFC Listener Standalone Page</h1>
    <p>Status: {{ $connected ? 'Connected' : 'Disconnected' }}</p>
    <p>Last Card UID: {{ $cardUid ?? 'None' }}</p>
    <p>Last Verified UID: {{ $verifiedUid ?? 'None' }}</p>

    <h2>Reader Status</h2>
    @if(empty($readerStatus))
        <p>No reader events</p>
    @else
        <ul>
            @foreach($readerStatus as $entry)
                <li>{{ $entry }}</li>
            @endforeach
        </ul>
    @endif

    <h2>Read Errors</h2>
    @if(empty($readErrors))
        <p>No errors</p>
    @else
        <ul>
            @foreach($readErrors as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    @endif

    {{-- NFC socket bridge --}}
    <script src="https://cdn.jsdelivr.net/npm/socket.io-client@4.8.3/dist/socket.io.min.js"></script>
    <script>
        (() => {
            const SOCKET_FALLBACKS = [
                "{{ env('NFC_SOCKET_URL', 'http://127.0.0.1:3000') }}",
                'http://localhost:3000',
                'http://127.0.0.1:8001'
            ];

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

            // Attempt connections to multiple endpoints sequentially for diagnostics
            async function tryConnect(endpoints) {
                for (const endpoint of endpoints) {
                    try {
                        console.debug('[NFC] trying socket endpoint', endpoint);
                        const socket = io(endpoint, {
                            transports: ['websocket'],
                            reconnection: true,
                            reconnectionAttempts: Infinity,
                            reconnectionDelay: 1000,
                            reconnectionDelayMax: 5000,
                        });

                        socket.on('connect', () => {
                            console.debug('[NFC] connected to', endpoint, socket.id);
                            // wire up events
                            dispatch('nfc:connect');
                        });

                        socket.on('connect_error', (err) => {
                            console.debug('[NFC] connect_error', endpoint, err?.message || err);
                        });

                        socket.on('disconnect', (reason) => {
                            console.debug('[NFC] disconnected from', endpoint, reason);
                            dispatch('nfc:disconnect');
                        });

                        socket.on('card-uid',   (uid)   => { console.debug('[NFC] card-uid', uid); dispatch('nfc:cardUid', { uid }); });
                        socket.on('card_uid',   (uid)   => { console.debug('[NFC] card_uid', uid); dispatch('nfc:cardUid', { uid }); });

                        socket.on('verified_uid',         (uid) => { console.debug('[NFC] verified_uid', uid); dispatch('nfc:verifiedUid', { uid }); });
                        socket.on('verified-user-detail', (uid) => { console.debug('[NFC] verified-user-detail', uid); dispatch('nfc:verifiedUid', { uid }); });

                        socket.on('reader-connect',    (name) => { console.debug('[NFC] reader-connect', name); dispatch('nfc:readerConnect', { name }); });
                        socket.on('reader_connected',  (name) => { console.debug('[NFC] reader_connected', name); dispatch('nfc:readerConnect', { name }); });
                        socket.on('reader-disconnect', (name) => { console.debug('[NFC] reader-disconnect', name); dispatch('nfc:readerDisconnect', { name }); });
                        socket.on('reader_removed',    (name) => { console.debug('[NFC] reader_removed', name); dispatch('nfc:readerDisconnect', { name }); });

                        socket.on('read-error', (err)  => { console.debug('[NFC] read-error', err); dispatch('nfc:readError', { err }); });
                        socket.on('error',      (err)  => { console.debug('[NFC] socket error', err); dispatch('nfc:readError', { err: String(err) }); });

                        // Wait briefly to see if we connected (or error) before trying next endpoint
                        const connected = await new Promise((resolve) => {
                            const t = setTimeout(() => resolve(false), 800);
                            socket.once('connect', () => { clearTimeout(t); resolve(true); });
                            socket.once('connect_error', () => { clearTimeout(t); resolve(false); });
                        });

                        if (connected) {
                            // Keep this socket active and stop trying others
                            window.__BATAENO_NFC_SOCKET = socket;
                            return socket;
                        } else {
                            try { socket.close?.(); socket.disconnect?.(); } catch (e) {}
                        }
                    } catch (err) {
                        console.debug('[NFC] tryConnect error for', endpoint, err?.message || err);
                    }
                }
                return null;
            }

            // Start connection attempts
            tryConnect(SOCKET_FALLBACKS).then((sock) => {
                if (!sock) console.debug('[NFC] no socket endpoints reachable');
            });
        })();
    </script>
</main>