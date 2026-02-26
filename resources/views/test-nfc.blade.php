{{-- resources/views/test-nfc.blade.php --}}
{{-- TEMPORARY TEST VIEW — REMOVE IN PRODUCTION --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFC Lookup Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 p-8">

    <div class="max-w-2xl mx-auto space-y-4">

        {{-- Test controls --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-3">NFC Test Controls</h2>
            <div class="flex gap-2">
                <input
                    id="test-uid"
                    type="text"
                    value="{{ $uid }}"
                    class="flex-1 font-mono text-sm border border-gray-300 rounded px-3 py-2"
                    placeholder="Paste a card UID"
                />
                <button
                    onclick="simulateTap()"
                    class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded hover:bg-indigo-700"
                >
                    ✅ Simulate Tap
                </button>
                <button
                    onclick="simulateRemove()"
                    class="px-4 py-2 bg-red-500 text-white text-sm font-semibold rounded hover:bg-red-600"
                >
                    🚫 Remove
                </button>
            </div>
        </div>

        {{-- The actual Livewire component you want to test --}}
        <livewire:nfc-resident-lookup />

    </div>

    @livewireScripts

    <script>
        function simulateTap() {
            const uid = document.getElementById('test-uid').value.trim();
            if (!uid) return;

            // Dispatch directly into Livewire — bypasses the socket entirely
            if (window.Livewire) {
                Livewire.dispatch('nfc:connect');
                Livewire.dispatch('nfc:cardUid',   { uid });
                Livewire.dispatch('nfc:verifiedUid', { uid });
            }
        }

        function simulateRemove() {
            if (window.Livewire) {
                Livewire.dispatch('nfc:cardRemoved');
            }
        }

        // Auto-simulate the tap on page load if uid is in the URL
        const urlUid = new URLSearchParams(window.location.search).get('uid');
        if (urlUid) {
            document.getElementById('test-uid').value = urlUid;
            window.addEventListener('livewire:initialized', () => simulateTap());
        }
    </script>
</body>
</html>