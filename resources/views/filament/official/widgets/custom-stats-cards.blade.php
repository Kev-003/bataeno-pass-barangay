<x-filament-widgets::widget>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($this->getStats() as $stat)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $stat['title'] }}</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stat['value'] }}</p>
                    </div>
                    <div
                        class="w-12 h-12 flex items-center justify-center rounded-full bg-{{ $stat['color'] }}-100 dark:bg-{{ $stat['color'] }}-900">
                        <x-filament::icon icon="heroicon-o-chart-bar"
                            class="w-6 h-6 text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400" />
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-widgets::widget>