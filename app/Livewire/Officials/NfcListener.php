<?php

namespace App\Livewire\Officials;

use Livewire\Component;

class NfcListener extends Component
{
    public bool $connected = false;
    public ?string $cardUid = null;
    public ?string $verifiedUid = null;
    public array $readerStatus = [];
    public array $readErrors = [];

    // Livewire 3 listeners (dispatched from JS via Livewire.dispatch())
    protected function getListeners(): array
    {
        return [
            'nfc:connect'           => 'onConnect',
            'nfc:disconnect'        => 'onDisconnect',
            'nfc:cardUid'           => 'onCardUid',
            'nfc:verifiedUid'       => 'onVerifiedUid',
            'nfc:readerConnect'     => 'onReaderConnect',
            'nfc:readerDisconnect'  => 'onReaderDisconnect',
            'nfc:readError'         => 'onReadError',
        ];
    }

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
    }

    public function onVerifiedUid(string $uid): void
    {
        $this->verifiedUid = $uid;
    }

    public function onReaderConnect(string $name): void
    {
        $this->readerStatus[] = "{$name} connected";
    }

    public function onReaderDisconnect(string $name): void
    {
        $this->readerStatus[] = "{$name} disconnected";
    }

    public function onReadError(string $err): void
    {
        $this->readErrors[] = $err;
    }

    public function render()
    {
        return view('livewire.officials.nfc-listener');
    }
}