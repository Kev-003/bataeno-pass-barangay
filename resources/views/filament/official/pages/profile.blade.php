<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Profile Card with Grainient Background --}}
        <div x-data="grainient({ color1: '#0a132e', color2: '#121d3d', color3: '#1e3a8a', zoom: 1.1 })"
            x-ref="container"
            class="relative overflow-hidden shadow rounded-xl p-4 sm:p-8 min-h-[300px] flex items-center bg-[#0a132e]">
            <div class="relative z-10 w-full">
                <livewire:profile-card />
            </div>
            {{-- Grainy Overlay --}}
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none"
                style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.65%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E');">
            </div>
        </div>

        @php
            $currentSignaturePath = auth()->user()->digital_signature;
            $signatureBase64 = null;
            if ($currentSignaturePath && \Illuminate\Support\Facades\Storage::disk('local')->exists($currentSignaturePath)) {
                $fileData = \Illuminate\Support\Facades\Storage::disk('local')->get($currentSignaturePath);
                $mimeType = \Illuminate\Support\Facades\Storage::disk('local')->mimeType($currentSignaturePath);
                $signatureBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($fileData);
            }
        @endphp

        @if($signatureBase64)
            <x-filament::section>
                <x-slot name="heading">
                    Current Digital Signature
                </x-slot>

                <div
                    class="flex items-center justify-center p-4 bg-gray-50 dark:bg-gray-900 border border-dashed rounded-xl overflow-hidden">
                    <img src="{{ $signatureBase64 }}" alt="Digital Signature" class="max-h-48 shadow-sm">
                </div>
            </x-filament::section>
        @endif

        <form wire:submit="save">
            {{ $this->form }}

            <div class="mt-6">
                <x-filament::actions :actions="$this->getFormActions()" />
            </div>
        </form>
    </div>
</x-filament-panels::page>