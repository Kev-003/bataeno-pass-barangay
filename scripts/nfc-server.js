import http from 'http';
import { Server } from 'socket.io';
import { NFC } from 'nfc-pcsc';

const PORT = process.env.NFC_PORT || 8001;

const server = http.createServer((req, res) => {
  // Minimal POST /emit support for testing when running in mock mode
  if (req.method === 'POST' && req.url === '/emit') {
    let body = '';
    req.on('data', (chunk) => (body += chunk));
    req.on('end', () => {
      try {
        const json = JSON.parse(body);
        const uid = json.uid || json.data?.uid;
        if (uid) {
          console.log('[API] /emit received UID:', uid);
          io.emit('verified_uid', uid);
          io.emit('verified-user-detail', uid);
          res.writeHead(200, { 'Content-Type': 'application/json' });
          res.end(JSON.stringify({ ok: true, uid }));
          return;
        }
      } catch (e) {
        console.error('[API] /emit parse error:', e.message);
      }
      res.writeHead(400);
      res.end(JSON.stringify({ ok: false }));
    });
    return;
  }
  
  // GET /status for debugging
  if (req.method === 'GET' && req.url === '/status') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ ok: true, running: true, port: PORT, nfcMode: global.nfcMode || 'unknown', timestamp: Date.now() }));
    return;
  }
  
  // simple status for root
  res.writeHead(200, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify({ ok: true, now: Date.now() }));
});

const io = new Server(server, { cors: { origin: '*' } });

io.on('connection', (socket) => {
  console.log('[Socket.io] Client connected:', socket.id);
  socket.emit('nfc:status', { connected: true, mode: global.nfcMode, timestamp: Date.now() });
});

// Initialize NFC with real hardware support
(async () => {
  let nfcActive = false;
  global.nfcMode = 'initializing';

  try {
    console.log('[NFC] Initializing NFC PCSC reader...');
    const nfc = new NFC();
    
    nfc.on('reader', (reader) => {
      console.log(`[NFC] ✓ Reader detected: ${reader.name}`);
      
      reader.on('card', (card) => {
        const uid = card.uid || (card.data && card.data.uid) || card.id;
        if (uid) {
          console.log(`[NFC] ✓ Card detected on ${reader.name}`);
          console.log(`[NFC] ✓ Card UID: ${uid}`);
          console.log(`[NFC] ℹ Card type: ${card.type || 'Unknown'}`);
          
          // Emit to all connected Socket.io clients
          io.emit('card-uid', uid);
          io.emit('verified_uid', uid);
          io.emit('verified-user-detail', uid);
          console.log('[NFC] ✓ UID emitted to all connected clients');
        } else {
          console.warn('[NFC] ⚠ Card detected but UID not found');
        }
      });

      reader.on('card.off', () => {
        console.log(`[NFC] ℹ Card removed from ${reader.name}`);
      });

      reader.on('error', (err) => {
        // ISO 14443-4 AID error is expected and can be ignored for UID reading
        if (err.message && err.message.includes('AID was not set')) {
          console.log(`[NFC] ℹ ISO 14443-4 tag (AID not required for UID reading)`);
        } else {
          console.error(`[NFC] ✗ Reader error (${reader.name}):`, err.message);
        }
      });

      reader.on('end', () => {
        console.log(`[NFC] ℹ Reader disconnected: ${reader.name}`);
      });
    });

    nfc.on('error', (err) => {
      console.error('[NFC] ✗ NFC Error:', err.message);
    });

    nfc.on('end', () => {
      console.log('[NFC] ℹ NFC service ended');
    });

    console.log('[NFC] ✓ NFC service initialized - REAL mode active');
    nfcActive = true;
    global.nfcMode = 'real';
    console.log('[NFC] ✓ Waiting for NFC readers to be connected...');

  } catch (err) {
    console.warn('[NFC] ⚠ NFC initialization issue:', err.message || err);
    console.log('[NFC] - Error code:', err.code);
    console.log('[NFC] - Error type:', err.constructor.name);
    console.log('[NFC] ℹ Falling back to MOCK mode');
    console.log('[NFC] ℹ Use POST /emit to simulate card taps for testing');
    global.nfcMode = 'mock';
  }
})();

server.listen(PORT, () => {
  console.log(`\n${'='.repeat(70)}`);
  console.log(`NFC Server listening on http://127.0.0.1:${PORT}`);
  console.log(`${'='.repeat(70)}`);
  console.log('\nDEBUG COMMANDS:');
  console.log('  Status: curl http://127.0.0.1:8001/status');
  console.log('  Mock UID: curl -X POST http://127.0.0.1:8001/emit -H "Content-Type: application/json" -d \'{"uid":"8421ece2-a06b-45da-9f74-cbf9affa3f90"}\'');
  console.log('\nServer is ready to receive Socket.io connections.\n');
});
