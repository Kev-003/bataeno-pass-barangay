<?php

namespace App\Livewire\Officials;

use App\Services\BataenoService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class NfcResidentLookup extends Component
{
    // ── Socket / NFC state ────────────────────────────────────────────────────
    public bool $connected = false;
    public ?string $cardUid = null;
    public ?string $verifiedUid = null;

    // ── Lookup state ──────────────────────────────────────────────────────────
    public bool $loading = false;
    public ?array $resident = null;
    public ?string $error = null;
    public ?string $source = null;   // 'bataeno' | 'local' | 'cache'

    // ── Listeners (Livewire 3) ────────────────────────────────────────────────
    protected function getListeners(): array
    {
        return [
            'nfc:connect' => 'onConnect',
            'nfc:disconnect' => 'onDisconnect',
            'nfc:cardUid' => 'onCardUid',
            'nfc:verifiedUid' => 'onVerifiedUid',
            'nfc:cardRemoved' => 'onCardRemoved',
        ];
    }

    // ── Event handlers ────────────────────────────────────────────────────────

    public function onConnect(): void
    {
        $this->connected = true;
    }

    public function onDisconnect(): void
    {
        $this->connected = false;
    }

    public function onCardUid(string $uid): void
    {
        $this->cardUid = $uid;
        // A raw UID arrived — clear stale resident data while we wait
        $this->resident = null;
        $this->error = null;
        $this->source = null;
    }

    /**
     * Fired when the socket server has verified the card and emits the UID.
     * This is the primary trigger for the resident lookup.
     */
    public function onVerifiedUid(string $uid): void
    {
        $this->verifiedUid = $uid;
        $this->cardUid = $uid;
        $this->lookupResident($uid);
    }

    public function onCardRemoved(): void
    {
        $this->cardUid = null;
        $this->verifiedUid = null;
        $this->resident = null;
        $this->error = null;
        $this->source = null;
        $this->loading = false;
    }

    // ── Core lookup ───────────────────────────────────────────────────────────

    public function lookupResident(string $uid): void
    {
        $this->loading = true;
        $this->resident = null;
        $this->error = null;

        try {
            /** @var BataenoService $service */
            $service = app(BataenoService::class);
            $resident = $service->findByCardUid($uid);

            if ($resident) {
                $this->resident = $resident;
                $this->source = $resident['_source'] ?? 'bataeno';
            } else {
                $this->error = 'No resident found for this card. The card may not be registered.';
            }
        } catch (\Exception $e) {
            $this->error = 'Lookup failed: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getInitials(): string
    {
        if (!$this->resident)
            return '?';

        $first = $this->resident['first_name'] ?? '';
        $last = $this->resident['last_name'] ?? '';

        return strtoupper(substr($first, 0, 1) . substr($last, 0, 1)) ?: '?';
    }

    public function render()
    {
        return view('livewire.officials.nfc-resident-lookup');
    }
}
