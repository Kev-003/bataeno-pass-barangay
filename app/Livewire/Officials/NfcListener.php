<?php

namespace App\Livewire\Officials;

use Livewire\Component;
use Livewire\Attributes\On; // <-- Required for Livewire 3!

class NfcListener extends Component
{
    public bool $connected = false;
    public ?string $cardUid = null;
    public ?string $verifiedUid = null;
    public array $readerStatus = [];
    public array $readErrors = [];

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
        $this->cardUid = $uid;
    }

    #[On('nfc:verifiedUid')]
    public function onVerifiedUid($uid = null): void
    {
        $this->verifiedUid = $uid;
    }

    #[On('nfc:readerConnect')]
    public function onReaderConnect($name = null): void
    {
        $this->readerStatus[] = "{$name} connected";
    }

    #[On('nfc:readerDisconnect')]
    public function onReaderDisconnect($name = null): void
    {
        $this->readerStatus[] = "{$name} disconnected";
    }

    #[On('nfc:readError')]
    public function onReadError($err = null): void
    {
        $this->readErrors[] = $err;
    }

    public function render()
    {
        return view('livewire.officials.nfc-listener');
    }
}