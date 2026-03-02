<?php

namespace App\Livewire\Officials;

use Livewire\Component;
use Livewire\Attributes\On; // <-- Required for Livewire 3!
use Illuminate\Support\Facades\Http;

class NfcListener extends Component
{
    public bool $connected = false;
    public ?string $cardUid = null;
    public ?string $verifiedUid = null;
    public array $readerStatus = [];
    public array $readErrors = [];

    public function mount(): void
    {
        $this->refreshConnectionStatus();
    }

    public function refreshConnectionStatus(): void
    {
        $baseUrl = rtrim((string) config('services.nfc.socket_url', env('NFC_SOCKET_URL', 'http://127.0.0.1:8001')), '/');
        $healthUrl = $baseUrl . '/health';

        try {
            $response = Http::timeout(1)->acceptJson()->get($healthUrl);
            $this->connected = $response->ok() && (bool) $response->json('reader_online', false);
        } catch (\Throwable $e) {
            $this->connected = false;
        }
    }

    #[On('nfc:connect')]
    public function onConnect(): void
    {
        $this->connected = true;
    }

    #[On('nfc:disconnect')]
    public function onDisconnect(): void
    {
        $this->connected = false;
    }

    #[On('nfc:cardUid')]
    public function onCardUid($uid = null): void
    {
        $this->connected = true;
        $this->cardUid = $uid;
    }

    #[On('nfc:verifiedUid')]
    public function onVerifiedUid($uid = null): void
    {
        $this->connected = true;
        $this->verifiedUid = $uid;
    }

    #[On('nfc:readerConnect')]
    public function onReaderConnect($name = null): void
    {
        $this->connected = true;
        $this->readerStatus[] = "{$name} connected";
        $this->readerStatus = array_slice($this->readerStatus, -20);
    }

    #[On('nfc:readerDisconnect')]
    public function onReaderDisconnect($name = null): void
    {
        $this->connected = false;
        $this->readerStatus[] = "{$name} disconnected";
        $this->readerStatus = array_slice($this->readerStatus, -20);
    }

    #[On('nfc:readError')]
    public function onReadError($err = null): void
    {
        $this->readErrors[] = $err;
        $this->readErrors = array_slice($this->readErrors, -20);
    }

    public function render()
    {
        return view('livewire.officials.nfc-listener');
    }
}