# Bataeno Pass NFC Hardware Bridge

This setup uses a real PC/SC NFC reader and forwards detected card UUIDs into the Laravel walk-in flow.

## Components

- `scripts/nfc-server.js` — Socket hub (`/emit` + Socket.IO broadcast)
- `scripts/nfc_bridge.py` — real hardware reader bridge (PC/SC via pyscard)
- `resources/js/app.js` + `@brynrgnzls/nfc-listener` — frontend listener used by Livewire

## Hardware-only flow

1. Run the socket hub:

```powershell
npm run nfc-server
```

2. Run the Python bridge (real reader):

```powershell
python -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r scripts\requirements.txt
npm run nfc-bridge
```

3. Run the app UI:

```powershell
npm run dev
php artisan serve
```

4. In walk-in request, choose document, tap physical card, verify resident, then submit.

## Notes

- No mock flow is required.
- Ensure NFC reader drivers are installed and PC/SC service is running.
- Default socket URL is `http://127.0.0.1:8001`; set `NFC_SOCKET_URL` in `.env` if needed.
