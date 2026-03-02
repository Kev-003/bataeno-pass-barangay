import { NfcHandler } from '@brynrgnzls/nfc-listener';

const SOCKET_URL = process.env.NFC_SOCKET_URL || 'http://127.0.0.1:8001';
const BACKEND_URL = process.env.BACKEND_URL || 'http://127.0.0.1:8000';

console.log('\n' + '='.repeat(70));
console.log('NFC Client Starting');
console.log('='.repeat(70));
console.log('Socket Server:', SOCKET_URL);
console.log('Backend:', BACKEND_URL);
console.log('='.repeat(70) + '\n');

let isConnected = false;

// Create the NFC Handler client
const handler = new NfcHandler(SOCKET_URL);

handler.onConnect((id) => {
  console.log('[✓] Socket connected:', id);
  isConnected = true;
});

handler.onDisconnect((reason) => {
  console.log('[✗] Socket disconnected:', reason);
  isConnected = false;
});

handler.onCardUid((uid) => {
  console.log('[NFC] Raw card UID detected:', uid);
});

handler.onVerifiedUid(async (uid) => {
  console.log('[NFC] ✓ Verified UID received:', uid);
  
  // Optional: Look up resident in backend
  if (BACKEND_URL) {
    try {
      const url = new URL('/residents/lookup', BACKEND_URL);
      url.searchParams.set('uid', uid);

      const res = await fetch(url.toString(), {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
      });

      if (res.ok) {
        const payload = await res.json().catch(() => null);
        console.log('[✓] Resident found:', payload?.resident?.name || 'Unknown');
      } else {
        console.warn('[!] Lookup returned status:', res.status);
      }
    } catch (err) {
      console.error('[!] Resident lookup error:', err.message);
    }
  }
});

// Connect to the socket server
console.log('[...] Connecting to socket server...');
handler.open();

// Keep the process alive
process.on('SIGINT', () => {
  console.log('\n[...] Closing NFC client...');
  try { handler.close(); } catch (e) {}
  process.exit(0);
});

// Simple command loop
console.log('[INFO] Commands:');
console.log('  - Type "exit" to quit\n');

// Simple stdin listener
process.stdin.setRawMode(true);
process.stdin.on('data', async (chunk) => {
  const input = chunk.toString().trim();
  
  if (input.toLowerCase() === 'exit') {
    console.log('[...] Exiting...');
    process.exit(0);
  }
});

console.log('[...] Ready. Waiting for real NFC card events...\n');
