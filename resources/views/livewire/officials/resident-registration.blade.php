<div class="space-y-6">
    {{-- Mode Selection --}}
    <div class="flex items-center justify-between border-b pb-4 dark:border-white/5">
        <div class="flex items-center gap-2">
            <x-filament::button size="sm" color="{{ $this->useQrScanner ? 'primary' : 'gray' }}"
                variant="{{ $this->useQrScanner ? 'filled' : 'outline' }}" icon="heroicon-m-qr-code"
                wire:click="toggleQrScanner">
                QR Scan
            </x-filament::button>

            <x-filament::button size="sm" color="{{ $this->useManualLookup ? 'primary' : 'gray' }}"
                variant="{{ $this->useManualLookup ? 'filled' : 'outline' }}" icon="heroicon-m-magnifying-glass"
                wire:click="toggleManualLookup">
                Manual Search
            </x-filament::button>
        </div>

        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">
            Registration Mode
        </span>
    </div>

    {{-- scanner / Search Interface --}}
    @if($this->useManualLookup)
        <div class="max-w-xl mx-auto">
            <livewire:officials.manual-lookup-form />

            <p class="text-xs text-gray-500 mt-4 text-center">
                Search the resident database by name or email to auto-fill registration fields.
            </p>
        </div>
    @else
        <div class="max-w-md mx-auto">
            <x-qr-scanner on-scan="$wire.processPhilId(data)" />

            <p class="text-xs text-gray-500 mt-4 text-center">
                Scan a PhilID QR code or Bataan Portal ID to automatically fetch resident information.
            </p>
        </div>
    @endif
</div>