@php
    $navItems = [
        'pending' => [
            'label' => 'Pending Requests',
            'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        ],
        'completed' => [
            'label' => 'Completed',
            'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
        ],
    ];
@endphp

<div>
    <x-slot:title>Review Document</x-slot:title>

    <x-sidebar-layout :navItems="$navItems" defaultTab="pending">
        @if (session()->has('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @error('error')
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                {{ $message }}
            </div>
        @enderror

        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-8">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('official.document-processing', ['barangay_code' => $barangay_code]) }}"
                            wire:navigate
                            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ $documentType }}</h2>
                            <p class="text-gray-500 mt-1">Reviewing request for <span
                                    class="font-bold text-gray-700">{{ $pendingTransactions->requester->name }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        @if($pendingTransactions && $pendingTransactions->status !== 'issued')
                            <span
                                class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm font-bold uppercase tracking-wider">
                                Pending Review
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Requester Information --}}
                    <div class="space-y-6">
                        <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Resident Information</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Full Name</p>
                                <p class="font-medium">{{ $transactionDetails['requester']->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Gender</p>
                                <p class="font-medium">{{ $transactionDetails['requester']->gender }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Civil Status</p>
                                <p class="font-medium">{{ $transactionDetails['requester']->civil_status }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="font-medium">{{ $transactionDetails['requester']->email }}</p>
                            </div>
                        </div>

                        <div
                            class="mt-4 p-4 rounded-xl {{ $hasValidId ? 'bg-green-50 border border-green-100' : 'bg-red-50 border border-red-100' }}">
                            <div class="flex items-center gap-3">
                                @if($hasValidId)
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-sm font-medium text-green-800">Verified Identification Found</p>
                                @else
                                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                        </path>
                                    </svg>
                                    <p class="text-sm font-medium text-red-800">No Valid ID Found on eGovPH</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Document Specific Details --}}
                    <div class="space-y-6">
                        <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Request Details</h3>
                        <div>
                            <p class="text-sm text-gray-500 italic">Purpose of Request</p>
                            <p class="font-medium text-lg text-blue-800">{{ $purpose ?: 'Not specified' }}</p>
                        </div>

                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 space-y-4">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Document Data Fields
                            </p>
                            <div class="space-y-3">
                                @foreach($dynamicFields as $key => $value)
                                    @if(!in_array($key, ['id', 'transaction_id', 'created_at', 'updated_at']))
                                        <div class="flex justify-between items-center py-1">
                                            <span
                                                class="text-sm text-gray-600">{{ str($key)->replace('_', ' ')->title() }}</span>
                                            <span class="text-sm font-bold text-gray-900">{{ $value }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @if($pendingTransactions && $pendingTransactions->status !== 'issued')
                    <div class="mt-12 flex justify-end gap-4 p-6 bg-slate-50 -m-8 mt-8 border-t">
                        <button wire:click="approveAndSign" wire:loading.attr="disabled"
                            class="px-8 py-3 bg-blue-600 text-white rounded-xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 hover:scale-105 transition active:scale-95 flex items-center gap-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            <span wire:loading.remove>Approve & Sign Document</span>
                            <span wire:loading>Signing...</span>
                        </button>
                    </div>
                @elseif($pendingTransactions && $pendingTransactions->status === 'issued')
                    <div class="mt-12 flex justify-end p-6 bg-slate-50 -m-8 mt-8 border-t">
                        <span
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-800 text-sm font-semibold rounded-full">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Document Already Issued
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </x-sidebar-layout>
</div>