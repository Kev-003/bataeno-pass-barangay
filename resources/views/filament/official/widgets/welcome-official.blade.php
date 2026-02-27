<x-filament-widgets::widget class="fi-wi-account">
    <div x-data="grainient({ color1: '#0a132e', color2: '#121d3d', color3: '#1e3a8a', zoom: 1.1 })" x-ref="container"
        class="relative overflow-hidden shadow rounded-xl p-6 sm:p-8 min-h-[120px] flex items-center bg-[#0a132e]">


        <div class="relative z-10 flex items-center gap-x-6 w-full">
            {{-- User Avatar --}}
            <div class="flex-shrink-0">
                <x-filament-panels::avatar.user size="lg" class="h-16 w-16 border-2 border-white/20 shadow-xl" />
            </div>

            <div class="flex-1">
                <h2 class="text-xl font-bold leading-tight text-white tracking-tight">
                    {{ __('filament-panels::widgets/account-widget.welcome', ['name' => auth()->user()->getFilamentName()]) }}
                </h2>

                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span
                        class="px-2.5 py-0.5 rounded-full text-white text-xs font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        {{ auth()->user()->activeTerm?->position?->name ?? 'Barangay Official' }}
                    </span>
                    <span class="text-sm text-gray-400 flex items-center gap-1">
                        <x-filament::icon icon="heroicon-m-map-pin" class="w-4 h-4" />
                        {{ filament()->getTenant()->name }}
                    </span>
                </div>
            </div>

            {{-- Logout Button --}}
            <form action="{{ filament()->getLogoutUrl() }}" method="post" class="my-auto">
                @csrf
                <x-filament::button color="gray" icon="heroicon-m-arrow-left-on-rectangle"
                    icon-alias="panels::widgets.account.logout-button" labeled-from="sm" tag="button" type="submit"
                    class="!bg-white/10 !text-white border-white/20 hover:!bg-white/20 backdrop-blur-md">
                    {{ __('filament-panels::widgets/account-widget.actions.logout.label') }}
                </x-filament::button>
            </form>
        </div>

        {{-- Grainy Overlay for texture --}}
        <div class="absolute inset-0 opacity-[0.03] pointer-events-none"
            style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noiseFilter%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.65%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noiseFilter)%22/%3E%3C/svg%3E');">
        </div>
    </div>
</x-filament-widgets::widget>