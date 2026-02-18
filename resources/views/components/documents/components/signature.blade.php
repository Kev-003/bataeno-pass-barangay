@props(['image', 'width' => '150px', 'height' => '80px'])

<div class="relative inline-block text-center">
    @if($image)
        <img src="{{ $image }}" alt="Official Signature"
            style="max-width: {{ $width }}; max-height: {{ $height }}; object-fit: contain;" class="mx-auto">
    @endif
</div>