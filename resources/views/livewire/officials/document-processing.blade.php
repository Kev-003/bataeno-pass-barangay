@php
    $navItems = [
        'pending' => [
            'label' => 'Pending Requests',
            'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        ],
        'processing' => [
            'label' => 'In Processing',
            'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>',
        ],
        'completed' => [
            'label' => 'Completed',
            'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
        ],
    ];

    $currentTab = request()->query('tab', 'pending');
@endphp

<div>
    <x-slot:title>Document Processing</x-slot:title>

    <x-sidebar-layout :navItems="$navItems" defaultTab="pending">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div x-show="activeTab === 'pending'">
                {{-- Pending Requests Content --}}
                <h2 class="text-xl font-bold text-gray-900 mb-6">Pending Requests</h2>

                @forelse($pendingTransactions as $transaction)
                    <div class="mb-4 p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">
                                            {{ $transaction->documentTypeProperty->name ?? 'Unknown Document' }}
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            From {{ $transaction->requester->name ?? 'Unknown Resident' }} •
                                            {{ $transaction->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                    Pending
                                </span>
                                <a href="{{ route('official.document-approval-process', ['barangay_code' => $psgc_code, 'id' => $transaction->id]) }}"
                                    wire:navigate
                                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition">
                                    Process
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 border-2 border-dashed border-gray-200 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No pending requests</h3>
                        <p class="mt-1 text-sm text-gray-500">All requests have been processed.</p>
                    </div>
                @endforelse
            </div>

            <div x-show="activeTab === 'processing'" style="display: none;">
                {{-- Processing Requests Content --}}
                <h2 class="text-xl font-bold text-gray-900 mb-6">In Processing</h2>

                @forelse($processingTransactions as $transaction)
                    <div class="mb-4 p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">
                                            {{ $transaction->documentTypeProperty->name ?? 'Unknown Document' }}
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            From {{ $transaction->requester->name ?? 'Unknown Resident' }} •
                                            {{ $transaction->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                    Processing
                                </span>
                                <a href="{{ route('official.document-approval-process', ['barangay_code' => $psgc_code, 'id' => $transaction->id]) }}"
                                    wire:navigate
                                    class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition">
                                    Complete
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 border-2 border-dashed border-gray-200 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No requests in processing</h3>
                        <p class="mt-1 text-sm text-gray-500">No documents are currently being processed.</p>
                    </div>
                @endforelse
            </div>

            <div x-show="activeTab === 'completed'" style="display: none;">
                {{-- Completed Requests Content --}}
                <h2 class="text-xl font-bold text-gray-900 mb-6">Completed Requests</h2>

                @forelse($completedTransactions as $transaction)
                    <div class="mb-4 p-4 border border-gray-200 rounded-lg hover:border-green-300 transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">
                                            {{ $transaction->documentTypeProperty->name ?? 'Unknown Document' }}
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            From {{ $transaction->requester->name ?? 'Unknown Resident' }} •
                                            {{ $transaction->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                    Completed
                                </span>
                                <a href="{{ route('official.document-approval-process', ['barangay_code' => $psgc_code, 'id' => $transaction->id]) }}"
                                    wire:navigate
                                    class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 transition">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 border-2 border-dashed border-gray-200 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No completed requests</h3>
                        <p class="mt-1 text-sm text-gray-500">Completed documents will appear here.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </x-sidebar-layout>

</div>