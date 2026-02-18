@props(['image', 'width' => '100px', 'height' => '100px'])

<div class="relative inline-block z-10 opacity-90">
    @if($image)
        <img src="{{ $image }}" alt="Official Seal"
            style="width: {{ $width }}; height: {{ $height }}; object-fit: contain;">
    @endif
</div>