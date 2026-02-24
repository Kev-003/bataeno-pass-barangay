@php
    $color = $styles['color'];
    // Tailwinds classes are hard to generate dynamically with strings, 
    // so we map the base colors to full class names here.
    $bgClass = "bg-{$color}-50";
    $textClass = "text-{$color}-600";
    $borderClass = "border-{$color}-100";
@endphp

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col h-full">
    <div class="p-5 flex-grow">
        <div class="flex justify-between items-start mb-4">
            <div class="{{ $bgClass }} p-3 rounded-lg {{ $textClass }}">
                {{-- Dynamic Heroicon --}}
                @svg($styles['icon'], 'w-7 h-7')
            </div>

            <x-filament::badge :color="$transaction->status === 'issued' ? 'success' : 'warning'">
                {{ strtoupper($transaction->status) }}
            </x-filament::badge>
        </div>

        <h3 class="text-lg font-bold text-gray-900 leading-tight">
            {{ $transaction->documentTypeProperty->name }}
        </h3>
        <p class="text-xs text-gray-500 mt-1">Ref: {{ $transaction->id }}</p>

        <div class="mt-6 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Requested:</span>
                <span class="font-medium text-gray-800">{{ $transaction->created_at }}</span>
            </div>
            @if($transaction->issued_at)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Issued:</span>
                    <span class="font-medium text-gray-800">{{ $transaction->issued_at }}</span>
                </div>
            @endif
            @if($transaction->expiry_date)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Expires:</span>
                    <span class="font-medium text-red-600">{{ $transaction->expiry_date }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Action Area --}}
    <div class="bg-gray-50 px-5 py-3 border-t border-gray-100 flex items-center justify-between">
        <button class="text-sm font-semibold text-gray-600 hover:text-blue-600">
            View Details
        </button>

        @if($transaction->file_path)
            <a href="{{ $transaction->getTemporaryDownloadUrl() }}" target="_blank"
                class="inline-flex items-center gap-1 text-sm font-bold text-blue-600 hover:text-blue-700">
                <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                Download
            </a>
        @endif
    </div>
</div>