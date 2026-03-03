<div>
    <div class="flex items-center justify-center">
        {{-- The 'group' class is key for hover nesting --}}
        <div class="relative group w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-lg bg-slate-200">

            {{-- 1. The Actual Image --}}
            <img src="{{ auth()->user()->profile_photo_url }}"
                class="w-full h-full object-cover transition duration-300 group-hover:scale-110">

            {{-- 2. Hover Overlay --}}
            <div
                class="absolute inset-0 bg-black/50 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                <x-heroicon-o-camera class="w-8 h-8 text-white mb-1" />
                <span class="text-[10px] text-white font-bold uppercase tracking-wider">Change Photo</span>
            </div>

            {{-- 3. The Invisible Input --}}
            {{-- We place this last and keep it inset-0 so the entire circle is clickable --}}
            <input type="file" wire:model="photo" accept="image/*"
                class="absolute inset-0 opacity-0 cursor-pointer z-10">

            {{-- 4. Loading State (Optional but recommended) --}}
            <div wire:loading wire:target="photo"
                class="absolute inset-0 bg-blue-600/80 flex items-center justify-center z-20">
                <svg class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Error Handling --}}
    @error('photo')
        <p class="text-center text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
    @enderror
</div>