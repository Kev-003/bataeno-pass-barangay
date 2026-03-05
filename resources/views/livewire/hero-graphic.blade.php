<section x-data="grainient({ 
    color1: '#0f172a', 
    color2: '#1e3a8a', 
    color3: '#020617',
    timeSpeed: 0,
    warpStrength: 0
})" x-ref="container"
    class="relative w-full min-h-screen flex items-center justify-center overflow-hidden bg-slate-950">
    <!-- dotgrid -->
    <div class="dot-grid__wrap absolute inset-0 w-full h-full pointer-events-none z-[1]">
        <canvas class="dot-grid__canvas"></canvas>
    </div>

    <div class="flex flex-col gap-5 md:gap-10">
        <div class="relative z-10 flex justify-center px-4">
            <img src="{{ asset('storage/logos/BataanSeal.png') }}" alt="Bataan Logo"
                class="w-24 h-24 md:w-48 md:h-48 mx-2 md:mx-5 mb-4" />
            <x-application-logo class="w-24 h-24 md:w-48 md:h-48 mx-2 md:mx-5 mb-4" />
        </div>
        <div class="text-center px-4 z-20">
            <h1 class="text-6xl md:text-8xl font-bevan text-white">
                Bataeño Pass
            </h1>
            <p class="text-xl md:text-2xl text-white mt-4 font-medium opacity-90">
                Your digital gateway to the province of Bataan
            </p>
        </div>
        <div class="text-center px-20 md:px-4 flex flex-col md:flex-row justify-center gap-4 md:gap-8 z-20">
            <x-primary-button href="{{ route('bataeno.login') }}" class="text-center px-12 py-3 md:py-4 text-lg">
                Get Started
            </x-primary-button>
            <x-secondary-button
                class="text-center px-12 py-3 md:py-4 text-lg border-white/20 hover:bg-white/10 text-white">
                About Us
            </x-secondary-button>
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