import http from 'http';
import { Server } from 'socket.io';

const PORT = Number(process.env.NFC_PORT || 8001);

function normalizeUid(rawUid) {
  if (typeof rawUid !== 'string') return null;
  const value = rawUid.trim().replace(/^{|}$/g, '').toLowerCase();
  if (!value.length) return null;

  const compact = value.replace(/-/g, '');
  if (/^[0-9a-f]{32}$/.test(compact)) {
    return `${compact.slice(0, 8)}-${compact.slice(8, 12)}-${compact.slice(12, 16)}-${compact.slice(16, 20)}-${compact.slice(20)}`;
  }

  return value;
}

function broadcastUid(io, uid) {
  io.emit('card-uid', uid);
  io.emit('card_uid', uid);
  io.emit('verified_uid', uid);
  io.emit('verified-user-detail', uid);
}

const server = http.createServer((req, res) => {
  if (req.method === 'POST' && req.url === '/emit') {
    let body = '';
    req.on('data', (chunk) => {
      body += chunk;
    });

    req.on('end', () => {
      try {
        const payload = JSON.parse(body || '{}');
        const uid = normalizeUid(payload.uid || payload.data?.uid);

        if (!uid) {
          res.writeHead(400, { 'Content-Type': 'application/json' });
          res.end(JSON.stringify({ ok: false, error: 'uid is required' }));
          return;
        }

        broadcastUid(io, uid);
        console.log(`[NFC] UID received and broadcast: ${uid}`);

        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ ok: true, uid }));
      } catch (error) {
        res.writeHead(400, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ ok: false, error: 'invalid json payload' }));
      }
    });

    return;
  }

  if (req.method === 'GET' && req.url === '/health') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ ok: true, service: 'nfc-socket-hub', port: PORT }));
    return;
  }

  res.writeHead(404, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify({ ok: false, error: 'not found' }));
});

const io = new Server(server, {
  cors: { origin: '*' },
});

io.on('connection', (socket) => {
  console.log(`[NFC] Client connected: ${socket.id}`);
  socket.on('disconnect', (reason) => {
    console.log(`[NFC] Client disconnected: ${socket.id} (${reason})`);
  });
});

server.listen(PORT, () => {
  console.log('='.repeat(70));
  console.log(`NFC Socket Hub listening on http://127.0.0.1:${PORT}`);
  console.log('Waiting for real hardware bridge events at POST /emit');
  console.log('='.repeat(70));
});
