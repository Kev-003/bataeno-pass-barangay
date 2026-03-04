@props([
    'onScan' => null, // Script expression to run on success, e.g. "$wire.handleScan(data)"
    'id' => 'qr-scanner-' . str()->random(4),
])

<div id="{{ $id }}"
     x-data="{
        scanner: null,
        cameras: [],
        activeCamera: 'environment',
        isScanning: false,

        async init() {
            // Wait for QrScanner to be available globally (loaded via CDN/Script hook)
            let attempts = 0;
            while (typeof QrScanner === 'undefined' && attempts < 20) {
                await new Promise(resolve => setTimeout(resolve, 100));
                attempts++;
            }

            if (typeof QrScanner === 'undefined') {
                console.error('QrScanner not found. Ensure it is included in your layout.');
                return;
            }

            this.cameras = await QrScanner.listCameras(true);
            
            this.scanner = new QrScanner(
                this.$refs.video,
                result => this.handleSuccess(result.data),
                { 
                    highlightScanRegion: true,
                    preferredCamera: this.activeCamera 
                }
            );
        },

        async switchCamera(deviceId) {
            this.activeCamera = deviceId;
            await this.scanner.setCamera(deviceId);
        },

        toggle() {
            if (this.isScanning) {
                this.scanner.stop();
            } else {
                this.scanner.start();
            }
            this.isScanning = !this.isScanning;
        },

        handleSuccess(data) {
            this.scanner.stop();
            this.isScanning = false;
            
            @if($onScan)
                // Execute the provided callback expression
                // We provide 'data' variable to the scope
                (new Function('data', '$wire', '{{ $onScan }}'))(data, this.$wire);
            @else
                this.$dispatch('scan-detected', { data });
            @endif
        }
     }" 
     x-init="init()"
     class="space-y-4 p-4">

    <div class="flex items-center gap-2">
        <x-filament::input.wrapper class="flex-1">
            <x-filament::input.select x-model="activeCamera" @change="switchCamera($event.target.value)">
                <template x-for="camera in cameras" :key="camera.id">
                    <option :value="camera.id" x-text="camera.label"></option>
                </template>
            </x-filament::input.select>
        </x-filament::input.wrapper>

        <div x-show="!isScanning">
            <x-filament::button @click="toggle()" color="primary" icon="heroicon-m-camera" type="button">
                Scan
            </x-filament::button>
        </div>
        <div x-show="isScanning" x-cloak>
            <x-filament::button @click="toggle()" color="danger" icon="heroicon-m-stop" type="button">
                Stop
            </x-filament::button>
        </div>
    </div>

    <div class="relative overflow-hidden rounded-xl bg-black aspect-video border-2 border-gray-800 shadow-inner">
        <video x-ref="video" class="w-full h-full object-cover"></video>

        <template x-if="!isScanning">
            <div class="absolute inset-0 flex items-center justify-center bg-black/60 text-white text-sm">
                Camera is standby...
            </div>
        </template>
    </div>
</div>
