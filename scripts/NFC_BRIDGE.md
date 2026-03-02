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
- The Python bridge normalizes 16-byte card IDs into canonical UUID text. If a card emits GUID little-endian bytes, they are converted to standard UUID order before emit.
- Short hardware UIDs (for example 4/7-byte tags) are emitted as lowercase hex and are not padded into fake UUID strings.
- For hardware IDs that should resolve to a known resident UUID, configure `scripts/nfc_uid_map.json`.
- The bridge auto-loads this file via `--uid-map scripts/nfc_uid_map.json`.
