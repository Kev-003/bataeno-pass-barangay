<div class="min-h-screen bg-gray-50/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="lg:flex lg:gap-x-8">
            {{-- Sidebar Navigation --}}
            <aside class="hidden lg:block lg:w-64 lg:flex-none">
                <nav class="sticky top-8 space-y-1 bg-white rounded-lg shadow-sm p-4">
                    @foreach($navItems as $slug => $item)
                                    <button wire:click="setActiveTab('{{ $slug }}')" class="w-full text-left flex items-center px-3 py-2 text-sm font-medium rounded-md transition-all duration-200 ease-in-out
                                                {{ $activeTab === $slug
                        ? 'text-blue-700 bg-blue-50'
                        : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">

                                        @if(isset($item['icon']))
                                            <div class="mr-3 h-5 w-5 flex-shrink-0">
                                                {!! $item['icon'] !!}
                                            </div>
                                        @endif

                                        <span class="truncate">{{ $item['label'] }}</span>
                                    </button>
                    @endforeach
                </nav>
            </aside>

            {{-- Main Content Area - Passed via slot --}}
            <main class="flex-1 min-w-0 mt-8 lg:mt-0">
                {{ $slot }}
            </main>
        </div>
    </div>
</div>