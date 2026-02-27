import http from 'http';
import { Server } from 'socket.io';

const PORT = process.env.NFC_PORT || 8001;

const server = http.createServer(async (req, res) => {
  // Minimal POST /emit support for testing when running in mock mode
  if (req.method === 'POST' && req.url === '/emit') {
    let body = '';
    req.on('data', (chunk) => (body += chunk));
    req.on('end', () => {
      try {
        const json = JSON.parse(body);
        const uid = json.uid || json.data?.uid;
        if (uid) {
          io.emit('verified_uid', uid);
          io.emit('verified-user-detail', uid);
          res.writeHead(200, { 'Content-Type': 'application/json' });
          res.end(JSON.stringify({ ok: true, uid }));
          return;
        }
      } catch (e) {}
      res.writeHead(400);
      res.end(JSON.stringify({ ok: false }));
    });
    return;
  }
  // simple status
  res.writeHead(200, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify({ ok: true, now: Date.now() }));
});

const io = new Server(server, { cors: { origin: '*' } });

io.on('connection', (socket) => {
  console.log('Client connected', socket.id);
});

let usedNative = false;
try {
  // Try dynamic import of native dependency. If not present, run mock mode.
  const mod = await import('nfc-pcsc');
  const { NFC } = mod;
  if (typeof NFC === 'function') {
    usedNative = true;
    const nfc = new NFC();

    nfc.on('reader', (reader) => {
      console.log(`${reader.reader.name} device attached`);

      reader.on('card', (card) => {
  const uid = card.uid || (card.atr && card.atr.toString('hex')) || null;
  if (uid) {
    io.emit('card-uid', uid);
    io.emit('verified_uid', uid);        // ← add this
    io.emit('verified-user-detail', uid); // ← and this (blade listens to both)
  }
});

      reader.on('card.off', (card) => {
        console.log('Card removed', card);
        io.emit('card-removed');
      });

      reader.on('error', (err) => {
        console.error('Reader error', err);
      });

      reader.on('end', () => {
        console.log(`${reader.reader.name} device removed`);
      });
    });

    nfc.on('error', (err) => {
      console.error('NFC error', err);
    });
  }
} catch (err) {
  console.warn('nfc-pcsc not available; running in mock mode. Install nfc-pcsc for real readers.');
}

// If native wasn't used, emit a sample UID periodically to help testing clients.
if (!usedNative) {
  // Only emit sample UID when explicitly requested via POST /emit, not on interval
  // Remove interval-based emission to avoid repeated UID events
}

server.listen(PORT, () => {
  console.log(`NFC socket server listening on http://127.0.0.1:${PORT}`);
  if (!usedNative) console.log('Mock mode: emitting sample UID every 20s.');
});
