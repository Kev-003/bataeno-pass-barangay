@php
    $navItems = [
        'residents' => [
            'label' => 'Residents',
            'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>',
        ],
        'households' => [
            'label' => 'Households',
            'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>',
        ],
    ];
@endphp

<div>
    <x-slot:title>Residents Management</x-slot:title>

    <x-sidebar-layout :navItems="$navItems" defaultTab="residents">
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div x-show="activeTab === 'residents'">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Residents</h2>
                    <p class="text-sm text-gray-500 mt-1">Manage residents in {{ $barangay_code }}</p>
                </div>

                <div class="p-6">
                    {{ $this->table }}
                </div>
            </div>

            <div x-show="activeTab === 'households'" style="display: none;">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Households</h2>
                    <p class="text-sm text-gray-500 mt-1">Manage households in {{ $barangay_code }}</p>
                </div>

                <div class="p-6">
                    <div class="text-center py-12 border-2 border-dashed border-gray-200 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                            </path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Households table coming soon</h3>
                        <p class="mt-1 text-sm text-gray-500">Household management will be available here.</p>
                    </div>
                </div>
            </div>
        </div>
    </x-sidebar-layout>
</div>