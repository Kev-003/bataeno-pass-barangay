<div class="space-y-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800">
    <div class="flex items-center gap-2">
        <x-filament::input.wrapper class="flex-1">
            <x-filament::input type="text" wire:model.live.debounce.300ms="search"
                placeholder="Search by name or email..." class="w-full" icon="heroicon-m-magnifying-glass" />
        </x-filament::input.wrapper>
    </div>

    @if(!empty($results))
        <div class="space-y-2">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Results</p>
            <div
                class="divide-y divide-gray-200 dark:divide-gray-800 border rounded-xl overflow-hidden bg-white dark:bg-gray-800 shadow-sm transition-all duration-300">
                @foreach($results as $res)
                    <div wire:click="selectResident('{{ $res['uuid'] }}')"
                        class="p-4 flex items-center justify-between group cursor-pointer hover:bg-primary-50 dark:hover:bg-primary-500/10 transition-colors">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-500/20 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold">
                                {{ substr($res['name'], 0, 1) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4
                                        class="text-sm font-bold text-gray-950 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400">
                                        {{ $res['name'] }}
                                    </h4>
                                    <span
                                        class="text-[10px] px-1.5 py-0.5 rounded-full {{ $res['source'] === 'Bataan Portal' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' }}">
                                        {{ $res['source'] }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $res['email'] ?: 'No email' }}</p>
                                <p class="text-[10px] font-mono text-gray-400 dark:text-gray-600 truncate max-w-[200px]">
                                    {{ $res['uuid'] }}
                                </p>
                            </div>
                        </div>
                        <x-heroicon-m-chevron-right
                            class="w-5 h-5 text-gray-300 dark:text-gray-600 group-hover:text-primary-500 transition-transform group-hover:translate-x-1" />
                    </div>
                @endforeach
            </div>
        </div>
    @elseif(strlen($search) >= 3)
        <div
            class="p-8 text-center bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
            <div class="mx-auto w-12 h-12 rounded-full bg-gray-50 dark:bg-gray-900 flex items-center justify-center mb-4">
                <x-heroicon-o-user-plus class="w-6 h-6 text-gray-400" />
            </div>
            <p class="text-sm font-medium text-gray-950 dark:text-white">No residents found matching your query.</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Make sure the name or email is spelled correctly.</p>
        </div>
    @else
        <div
            class="p-8 text-center bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
            <div class="mx-auto w-12 h-12 rounded-full bg-gray-50 dark:bg-gray-900 flex items-center justify-center mb-4">
                <x-heroicon-o-magnifying-glass class="w-6 h-6 text-gray-300" />
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Enter at least 3 characters to search the resident database.
            </p>
        </div>
    @endif
</div>