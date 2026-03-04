<div class="space-y-5">
    <div class="text-center mb-2">
        <h3 class="text-sm font-bold text-gray-950 dark:text-white">Manual Portal Lookup</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Enter the resident's identity details to search the Bataan Portal.</p>
    </div>

    <form wire:submit="lookup" class="space-y-4">
        <div class="grid grid-cols-2 gap-6">
            {{-- First Name --}}
            <div class="space-y-2">
                <label for="first_name" class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    First Name <span class="text-danger-600">*</span>
                </label>
                <x-filament::input.wrapper :valid="!$errors->has('first_name')">
                    <x-filament::input type="text" id="first_name" wire:model="first_name" placeholder="e.g. JUAN" required />
                </x-filament::input.wrapper>
                @error('first_name') <p class="text-xs text-danger-600">{{ $message }}</p> @enderror
            </div>

            {{-- Last Name --}}
            <div class="space-y-2">
                <label for="last_name" class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    Last Name <span class="text-danger-600">*</span>
                </label>
                <x-filament::input.wrapper :valid="!$errors->has('last_name')">
                    <x-filament::input type="text" id="last_name" wire:model="last_name" placeholder="e.g. DELA CRUZ" required />
                </x-filament::input.wrapper>
                @error('last_name') <p class="text-xs text-danger-600">{{ $message }}</p> @enderror
            </div>

            {{-- Middle Name --}}
            <div class="space-y-2">
                <label for="middle_name" class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    Middle Name
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" id="middle_name" wire:model="middle_name" placeholder="e.g. SANTOS" />
                </x-filament::input.wrapper>
            </div>

            {{-- Suffix --}}
            <div class="space-y-2">
                <label for="suffix" class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    Suffix
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input type="text" id="suffix" wire:model="suffix" placeholder="e.g. Jr., Sr., III" />
                </x-filament::input.wrapper>
            </div>

            {{-- Birthday --}}
            <div class="space-y-2">
                <label for="birthday" class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    Birthday <span class="text-danger-600">*</span>
                </label>
                <x-filament::input.wrapper :valid="!$errors->has('birthday')">
                    <x-filament::input type="date" id="birthday" wire:model="birthday" required />
                </x-filament::input.wrapper>
                @error('birthday') <p class="text-xs text-danger-600">{{ $message }}</p> @enderror
            </div>

            {{-- Submit --}}
            <div class="flex items-end mb-1">
                <x-filament::button type="submit" class="w-full" wire:loading.attr="disabled" wire:target="lookup"
                    icon="heroicon-m-magnifying-glass">
                    <span wire:loading.remove wire:target="lookup">Search Portal</span>
                    <span wire:loading wire:target="lookup">Searching...</span>
                </x-filament::button>
            </div>
        </div>
    </form>

    {{-- Error Display --}}
    @if($error)
        <div class="p-4 bg-danger-50 dark:bg-danger-500/10 border border-danger-200 dark:border-danger-500/20 rounded-xl">
            <div class="flex items-start gap-3">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-danger-500 shrink-0 mt-0.5" />
                <p class="text-sm text-danger-700 dark:text-danger-400">{{ $error }}</p>
            </div>
        </div>
    @endif

    {{-- Result Display --}}
    @if($result)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-success-50 dark:bg-success-500/10">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-check-circle class="w-5 h-5 text-success-500" />
                    <span class="text-sm font-bold text-success-700 dark:text-success-400">Resident Found</span>
                </div>
            </div>

            <div class="p-4 space-y-3">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</span>
                        <p class="font-semibold text-gray-950 dark:text-white">
                            {{ $result['first_name'] ?? '' }} {{ $result['middle_name'] ?? '' }}
                            {{ $result['last_name'] ?? '' }} {{ $result['suffix'] ?? '' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</span>
                        <p class="text-gray-700 dark:text-gray-300">{{ $result['email'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Birthday</span>
                        <p class="text-gray-700 dark:text-gray-300">
                            {{ $result['date_of_birth'] ?? $result['birthday'] ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gender</span>
                        <p class="text-gray-700 dark:text-gray-300">{{ $result['gender'] ?? $result['sex'] ?? 'N/A' }}</p>
                    </div>
                    @if(!empty($result['uuid']))
                        <div class="col-span-2">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">UUID</span>
                            <p class="text-xs font-mono text-gray-500 dark:text-gray-500 truncate">{{ $result['uuid'] }}</p>
                        </div>
                    @endif
                </div>

                <x-filament::button wire:click="selectResult" class="w-full" color="success" icon="heroicon-m-check">
                    Use This Resident
                </x-filament::button>
            </div>
        </div>
    @endif
</div>