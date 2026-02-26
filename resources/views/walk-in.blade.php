@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Walk-in Document Request</h1>

    {{-- NFC listener Livewire component --}}
    <livewire:officials.nfc-listener />

    <div class="mt-6 bg-white p-4 rounded border">
        <h2 class="font-semibold">Create Request</h2>

        <form id="walkin-form" onsubmit="return false;">
            <input type="hidden" id="walkin-uid" name="uid" />

            <div class="mt-3">
                <label class="block text-sm font-medium">Document Type</label>
                <select id="doc-type" name="document_type" class="mt-1 block w-full border rounded p-2">
                    @foreach(\DB::table('document_type_properties')->get() as $dt)
                        <option value="{{ $dt->id }}">{{ $dt->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-3">
                <label class="block text-sm font-medium">Purpose</label>
                <input id="purpose" name="purpose" class="mt-1 block w-full border rounded p-2" />
            </div>

            <div class="mt-3">
                <button id="submit-walkin" class="px-4 py-2 bg-blue-600 text-white rounded mt-2">Submit Walk-in Request</button>
            </div>

            <div id="walkin-result" class="mt-3 text-sm"></div>
        </form>
    </div>
 </div>

<script type="module">
document.addEventListener('DOMContentLoaded', () => {
    const uidEl = document.getElementById('walkin-uid');
    const submitBtn = document.getElementById('submit-walkin');
    const resultEl = document.getElementById('walkin-result');

    // Listen for resident resolved earlier by the included nfc-scanner
    window.addEventListener('nfc:owner', (e) => {
        const data = e.detail;
        // If your nfc-scanner dispatches resident details, use its uid
        if (data && data.uid) {
            uidEl.value = data.uid;
        }
    });

    // Fallback: use the verified uid element from the included scanner
    const verifiedEl = document.getElementById('nfc-verified-uid');
    const cardUidEl = document.getElementById('nfc-card-uid');
    const lookup = async (uid) => {
        if (!uid) return;
        // populate hidden uid
        uidEl.value = uid;
        // Optionally auto-select or populate other fields by calling /residents/lookup
        try {
            const res = await fetch('/residents/lookup', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ uid })
            });
            if (res.ok) {
                const json = await res.json();
                // dispatch to form or populate UI fields
                window.dispatchEvent(new CustomEvent('nfc:owner', { detail: { uid, resident: json.resident } }));
            }
        } catch (err) {
            console.error(err);
        }
    };

    // When verified UID updates, auto-lookup
    const observer = new MutationObserver(() => {
        const uid = verifiedEl?.textContent?.trim() || cardUidEl?.textContent?.trim();
        if (uid && uid !== 'None') lookup(uid);
    });
    if (verifiedEl) observer.observe(verifiedEl, { childList: true, characterData: true, subtree: true });

    // Poll the server for latest NFC resident pushed by an external client
    let lastPolledUid = null;
    async function pollLatest() {
        try {
            const res = await fetch('/nfc/latest', { cache: 'no-store' });
            if (res.status === 204) return;
            if (!res.ok) return;
            const data = await res.json();
            const uid = data.uid || (data.resident && (data.resident.uuid || data.resident.id));
            if (!uid || uid === lastPolledUid) return;
            lastPolledUid = uid;
            // dispatch same event used by the rest of the page
            window.dispatchEvent(new CustomEvent('nfc:owner', { detail: { uid, resident: data.resident || data } }));
        } catch (err) {
            // ignore polling errors
        }
    }
    setInterval(pollLatest, 1200);

    submitBtn.addEventListener('click', async () => {
        const uid = uidEl.value;
        const document_type = document.getElementById('doc-type').value;
        const purpose = document.getElementById('purpose').value;
        if (!uid) { resultEl.textContent = 'No UID present. Tap an NFC card first.'; return; }
        resultEl.textContent = 'Submitting...';
        try {
            const res = await fetch('/walk-in/submit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ uid, document_type, purpose })
            });
            const json = await res.json();
            if (res.ok) {
                resultEl.textContent = 'Request submitted. Transaction: ' + json.transaction_id;
            } else {
                resultEl.textContent = json.message || 'Submission failed';
            }
        } catch (err) {
            resultEl.textContent = 'Error: ' + err.message;
        }
    });
});
</script>

@endsection
