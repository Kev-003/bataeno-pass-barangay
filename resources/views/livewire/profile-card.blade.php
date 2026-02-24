<div class="overflow-hidden shadow-sm sm:rounded-lg sm:p-8 lg:p-12">
    <div class="flex flex-col md:flex-row items-center gap-8 text-white">
        <div
            class="relative w-32 h-32 rounded-3xl overflow-hidden border-4 border-white/30 shadow-xl bg-white/10 backdrop-blur-md">
            @if($user->profile_photos)
                <img src="{{ Storage::url($user->profile_photos) }}" alt="{{ $user->name }}"
                    class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center bg-white/5">
                    <svg class="w-16 h-16 text-white/20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
            @endif
        </div>

        <div class="flex-1 text-center md:text-left">
            <div class="flex flex-col md:flex-row md:items-center gap-3 mb-2">
                <h2 class="text-4xl font-bold tracking-tight">{{ $user->name }}</h2>
                @if($user->isOfficial())
                    <div
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-500/20 backdrop-blur-md rounded-full border border-blue-400/30 text-blue-200 text-sm font-semibold">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.64.304 1.25.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        {{ $this->getPosition() }}
                    </div>
                @endif
            </div>
            <p class="text-white/70 text-lg mb-6">{{ $user->email }}</p>

            <div class="flex flex-wrap items-center justify-center md:justify-start gap-4">
                <div class="px-4 py-2 bg-white/10 backdrop-blur-md rounded-xl border border-white/10">
                    <span class="text-xs uppercase tracking-widest text-white/50 block">Barangay</span>
                    <span class="font-semibold">{{ $user->barangay_name }}</span>
                </div>
                <div class="px-4 py-2 bg-white/10 backdrop-blur-md rounded-xl border border-white/10">
                    <span class="text-xs uppercase tracking-widest text-white/50 block">Municipality</span>
                    <span class="font-semibold">{{ $user->municity_name }}</span>
                </div>
            </div>
        </div>
    </div>
</div>