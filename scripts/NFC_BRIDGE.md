# Bataeno Pass NFC Bridge

This script bridges a PC/SC NFC reader to a Socket.IO server so a Laravel Blade frontend can receive card UID events.

Files added:
- `scripts/nfc_bridge.py` — main bridge server
- `scripts/requirements.txt` — Python dependencies

Quick start (Windows):

```powershell
python -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r scripts\requirements.txt
python scripts\nfc_bridge.py
```

Notes:
- Ensure PC/SC reader drivers are installed and the reader is connected.
- The script emits `card_uid`, `verified_uid`, and `card_removed` Socket.IO events.
