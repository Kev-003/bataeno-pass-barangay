<x-layouts.app>
    <x-slot name="header">
        <h1 class="font-bold text-3xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h1>
    </x-slot>

    <div class="py-12">
        <div x-data="grainient({ color1: '#0a132e', color2: '#1e3a8a', color3: '#2563eb', zoom: 1.2 })"
            x-ref="container"
            class="relative max-w-7xl mx-auto sm:px-6 lg:px-8 min-h-[400px] overflow-hidden rounded-3xl shadow-2xl">
            <div class="relative z-10 w-full h-full p-8 lg:p-12">
                <livewire:profile-card />
            </div>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sm:p-8 lg:p-12">
                <a href="{{ route('document.request') }}"
                    class="group relative flex h-48 w-full flex-col items-start justify-end overflow-hidden rounded-2xl bg-orange-500 p-6 transition-all hover:scale-[1.02] hover:shadow-lg sm:w-64">

                    <div class="absolute inset-0 opacity-10"
                        style="background-image: radial-gradient(circle, #fff 1.5px, transparent 1.5px); background-size: 15px 15px;">
                    </div>

                    <div
                        class="mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-white/20 backdrop-blur-md">
                        <span class="text-xl font-bold text-white">RD</span>
                    </div>

                    <h3 class="relative z-10 text-lg font-semibold text-white">Request Document</h3>
                    <p class="relative z-10 text-xs text-white/80">Process barangay clearances & certs</p>

                    <div class="absolute right-4 top-4 text-white/50 group-hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                </a>
            </div>
        </div>
    </div>

</x-layouts.app>