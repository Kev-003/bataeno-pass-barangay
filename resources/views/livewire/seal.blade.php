<div class="relative inline-block">
    @if($image)
        <img src="{{ $image }}" alt="Official Seal" style="width: {{ $width }}; height: {{ $height }}; object-fit: contain;"
            class="opacity-90">
    @else
        <div style="width: {{ $width }}; height: {{ $height }};"
            class="bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center text-xs text-gray-400 text-center p-2 rounded">
            No Seal<br>Uploaded
        </div>
    @endif
</div>