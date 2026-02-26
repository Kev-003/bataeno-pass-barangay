import { NfcHandler } from '@brynrgnzls/nfc-listener';

const SOCKET_URL = process.env.NFC_SOCKET_URL || 'http://127.0.0.1:8001';
const BACKEND_URL = process.env.BACKEND_URL || 'http://127.0.0.1:8000';

console.log('NFC client connecting to', SOCKET_URL, 'backend', BACKEND_URL);

const handler = new NfcHandler(SOCKET_URL);

handler.onConnect((id) => console.log('Socket connected', id));
handler.onDisconnect((reason) => console.log('Socket disconnected', reason));

handler.onCardUid((uid) => {
  console.log('card_uid:', uid);
});

handler.onVerifiedUid(async (uid) => {
  console.log('verified_uid:', uid);
  try {
    const url = new URL('/residents/lookup', BACKEND_URL);
    url.searchParams.set('uid', uid);

    const res = await fetch(url.toString(), {
      method: 'GET',
      headers: { 'Accept': 'application/json' },
    });

    if (!res.ok) {
      console.warn('Lookup returned', res.status);
      const text = await res.text().catch(() => '');
      console.log('Lookup body:', text);
      return;
    }

    const payload = await res.json().catch(() => null);
    console.log('Lookup success:', JSON.stringify(payload, null, 2));
    // Push the resident payload to the Laravel app so the blade can pick it up
    try {
      await fetch(new URL('/nfc/set', BACKEND_URL).toString(), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ uid, resident: payload?.resident || payload })
      });
      console.log('Posted resident to /nfc/set');
    } catch (err) {
      console.error('Failed to POST resident to backend', err);
    }
  } catch (err) {
    console.error('Lookup error:', err);
  }
});

handler.open();

process.on('SIGINT', () => {
  try { handler.close(); } catch (e) {}
  process.exit();
});
