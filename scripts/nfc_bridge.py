#!/usr/bin/env python3
import argparse
import json
import sys
import time
import urllib.error
import urllib.request

from smartcard.CardMonitoring import CardMonitor, CardObserver


def format_as_uuid(hex_value: str) -> str:
    cleaned = ''.join(ch for ch in hex_value.lower() if ch in '0123456789abcdef')
    cleaned = cleaned.ljust(32, '0')[:32]
    return f"{cleaned[:8]}-{cleaned[8:12]}-{cleaned[12:16]}-{cleaned[16:20]}-{cleaned[20:32]}"


def read_uid(card) -> str | None:
    connection = card.createConnection()
    connection.connect()
    data, sw1, sw2 = connection.transmit([0xFF, 0xCA, 0x00, 0x00, 0x00])
    if sw1 == 0x90 and sw2 == 0x00 and data:
        hex_uid = ''.join(f"{byte:02x}" for byte in data)
        return format_as_uuid(hex_uid)
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
    def __init__(self, emit_url: str, cooldown_seconds: float, timeout: float):
        self.emit_url = emit_url
        self.cooldown_seconds = cooldown_seconds
        self.timeout = timeout
        self.last_seen: dict[str, float] = {}

    def update(self, observable, actions):
        added_cards, removed_cards = actions

        for card in added_cards:
            try:
                uid = read_uid(card)
                if not uid:
                    print("[NFC] Card detected, but no UID was returned")
                    continue

                now = time.time()
                last = self.last_seen.get(uid, 0.0)
                if now - last < self.cooldown_seconds:
                    continue

                self.last_seen[uid] = now
                print(f"[NFC] UID detected: {uid}")
                ok = emit_uid(self.emit_url, uid, self.timeout)
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
    args = parser.parse_args()

    emit_url = args.server.rstrip('/') + '/emit'

    print("=" * 70)
    print("NFC Python Bridge")
    print("Emit URL:", emit_url)
    print("Press Ctrl+C to stop")
    print("=" * 70)

    monitor = CardMonitor()
    observer = UIDObserver(emit_url=emit_url, cooldown_seconds=args.cooldown, timeout=args.timeout)
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
