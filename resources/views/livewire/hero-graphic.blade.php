<section class="relative w-full min-h-screen flex items-center justify-center bg-bataan-premium">
    <!-- dotgrid -->
    <div class="dot-grid__wrap absolute inset-0 w-full h-full">
        <canvas class="dot-grid__canvas"></canvas>
    </div>

    <div class="flex flex-col gap-10">
        <div class="relative z-10 text-center px-4">
            <h1 class="text-6xl md:text-8xl font-bevan text-white drop-shadow-lg">
                BATAEÑO PASS
            </h1>
            <p class="text-xl md:text-2xl text-white mt-4 font-medium">
                Your digital gateway to the province of Bataan
            </p>
        </div>
        <div class="relative z-10 text-center px-4 flex justify-center gap-8">
            <x-primary-button href="{{ route('bataeno.login') }}">Get Started</x-primary-button>
            <x-secondary-button>About Us</x-secondary-button>
        </div>
    </div>
</section>

@script
<script>
    if (window.initDotGrid) {
        window.initDotGrid();
    }
</script>
@endscript