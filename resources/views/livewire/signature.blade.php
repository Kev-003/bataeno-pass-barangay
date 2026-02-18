<div class="relative inline-block text-center">
    @if($image)
        <img src="{{ $image }}" alt="Official Signature"
            style="max-width: {{ $width }}; max-height: {{ $height }}; object-fit: contain;" class="mx-auto">
    @else
        <div style="width: {{ $width }}; height: {{ $height }};"
            class="bg-gray-50 border border-gray-200 flex items-center justify-center text-xs text-gray-400 italic rounded">
            No Signature Found
        </div>
    @endif
</div>