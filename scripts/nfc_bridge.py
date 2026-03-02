#!/usr/bin/env python3
import argparse
import json
from pathlib import Path
import sys
import time
import urllib.error
import urllib.request

from smartcard.CardMonitoring import CardMonitor, CardObserver


def format_as_uuid(hex_value: str) -> str:
    cleaned = ''.join(ch for ch in hex_value.lower() if ch in '0123456789abcdef')
    if len(cleaned) != 32:
        return cleaned

    data = bytes.fromhex(cleaned)
    guid_ordered = data[0:4][::-1] + data[4:6][::-1] + data[6:8][::-1] + data[8:16]
    canonical = guid_ordered.hex()
    return f"{canonical[:8]}-{canonical[8:12]}-{canonical[12:16]}-{canonical[16:20]}-{canonical[20:32]}"


def normalize_uid_key(value: str | None) -> str | None:
    if not isinstance(value, str):
        return None
    normalized = ''.join(ch for ch in value.lower() if ch in '0123456789abcdef')
    return normalized or None


def load_uid_map(path: str | None) -> dict[str, str]:
    if not path:
        return {}

    map_path = Path(path)
    if not map_path.is_absolute():
        map_path = Path.cwd() / map_path

    if not map_path.exists():
        return {}

    try:
        data = json.loads(map_path.read_text(encoding='utf-8'))
    except Exception as err:
        print(f"[NFC] Warning: failed to read UID map file ({map_path}): {err}")
        return {}

    if not isinstance(data, dict):
        print(f"[NFC] Warning: UID map file must be a JSON object: {map_path}")
        return {}

    output: dict[str, str] = {}
    for key, value in data.items():
        key_norm = normalize_uid_key(key)
        if not key_norm or not isinstance(value, str) or not value.strip():
            continue
        output[key_norm] = value.strip().lower()

    return output


def resolve_uid(raw_uid: str, uid_map: dict[str, str]) -> str:
    key = normalize_uid_key(raw_uid)
    if key and key in uid_map:
        return uid_map[key]
    return raw_uid


def read_uid(card) -> str | None:
    for attempt in range(2):
        connection = card.createConnection()
        try:
            connection.connect()
            data, sw1, sw2 = connection.transmit([0xFF, 0xCA, 0x00, 0x00, 0x00])
            if sw1 == 0x90 and sw2 == 0x00 and data:
                hex_uid = ''.join(f"{byte:02x}" for byte in data)
                return format_as_uuid(hex_uid)
            return None
        except Exception as err:
            if attempt == 0 and '0x8010000B' in str(err):
                time.sleep(0.08)
                continue
            raise
        finally:
            try:
                connection.disconnect()
            except Exception:
                pass

    return None


def emit_uid(emit_url: str, uid: str, timeout: float) -> bool:
    payload = json.dumps({"uid": uid}).encode('utf-8')
    req = urllib.request.Request(
        emit_url,
        data=payload,
        headers={"Content-Type": "application/json"},
        method="POST",
    )
    try:
        with urllib.request.urlopen(req, timeout=timeout) as response:
            return 200 <= response.status < 300
    except urllib.error.URLError:
        return False


class UIDObserver(CardObserver):
    def __init__(self, emit_url: str, cooldown_seconds: float, timeout: float, uid_map: dict[str, str]):
        self.emit_url = emit_url
        self.cooldown_seconds = cooldown_seconds
        self.timeout = timeout
        self.uid_map = uid_map
        self.last_seen: dict[str, float] = {}

    def update(self, observable, actions):
        added_cards, removed_cards = actions

        for card in added_cards:
            try:
                uid = read_uid(card)
                if not uid:
                    print("[NFC] Card detected, but no UID was returned")
                    continue

                resolved_uid = resolve_uid(uid, self.uid_map)

                now = time.time()
                last = self.last_seen.get(resolved_uid, 0.0)
                if now - last < self.cooldown_seconds:
                    continue

                self.last_seen[resolved_uid] = now
                if resolved_uid != uid:
                    print(f"[NFC] UID detected: {uid} -> mapped UUID: {resolved_uid}")
                else:
                    print(f"[NFC] UID detected: {resolved_uid}")

                ok = emit_uid(self.emit_url, resolved_uid, self.timeout)
                if ok:
                    print(f"[NFC] ✓ Emitted to {self.emit_url}")
                else:
                    print(f"[NFC] ✗ Failed to emit to {self.emit_url}")
            except Exception as err:
                print(f"[NFC] Error while processing card: {err}")

        for _ in removed_cards:
            print("[NFC] Card removed")


def main() -> int:
    parser = argparse.ArgumentParser(description="PC/SC NFC bridge for Bataeno Pass")
    parser.add_argument("--server", default="http://127.0.0.1:8001", help="NFC server base URL")
    parser.add_argument("--cooldown", type=float, default=1.0, help="Duplicate UID cooldown in seconds")
    parser.add_argument("--timeout", type=float, default=2.0, help="HTTP emit timeout in seconds")
    parser.add_argument("--uid-map", default="scripts/nfc_uid_map.json", help="Path to JSON map of raw UID to UUID")
    args = parser.parse_args()

    emit_url = args.server.rstrip('/') + '/emit'
    uid_map = load_uid_map(args.uid_map)

    print("=" * 70)
    print("NFC Python Bridge")
    print("Emit URL:", emit_url)
    print("UID Map:", args.uid_map, f"({len(uid_map)} entries)")
    print("Press Ctrl+C to stop")
    print("=" * 70)

    monitor = CardMonitor()
    observer = UIDObserver(emit_url=emit_url, cooldown_seconds=args.cooldown, timeout=args.timeout, uid_map=uid_map)
    monitor.addObserver(observer)

    try:
        while True:
            time.sleep(0.2)
    except KeyboardInterrupt:
        print("\n[NFC] Stopping bridge...")
        monitor.deleteObserver(observer)
        return 0
    except Exception as err:
        print(f"[NFC] Fatal error: {err}")
        monitor.deleteObserver(observer)
        return 1


if __name__ == "__main__":
    sys.exit(main())
