@php
    $navItems = [
        'officials' => [
            'label' => 'Officials',
            'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>',
        ],
        'delegations' => [
            'label' => 'Authority Delegations',
            'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>',
        ],
    ];
@endphp

<div>
    <x-slot:title>Officials Management</x-slot:title>

    <x-sidebar-layout :navItems="$navItems" defaultTab="officials">
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div x-show="activeTab === 'officials'">
                <div class="flex justify-between px-6 py-4 border-b border-gray-200">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Officials</h2>
                        <p class="text-sm text-gray-500 mt-1">Manage officials in {{ $barangay_code }}</p>
                    </div>
                    <a
                        class="px-4 py-2 bg-blue-600 text-white mb-6 text-sm font-medium rounded-md hover:bg-blue-700 transition">
                        Register Official
                    </a>
                </div>
                <div class="p-6">
                    {{ $this->table }}
                </div>
            </div>

            <div x-show="activeTab === 'delegations'" style="display: none;">
                <div class="flex justify-between px-6 py-4 border-b border-gray-200">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Authority Delegations</h2>
                        <p class="text-sm text-gray-500 mt-1">Manage authority delegations in {{ $barangay_code }}</p>
                    </div>
                    <button wire:click="$set('showDelegationModal', true)"
                        class="px-4 py-2 bg-blue-600 text-white mb-6 text-sm font-medium rounded-md hover:bg-blue-700 transition">
                        Delegate Authority
                    </button>
                </div>

                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($delegations as $delegation)
                            <div x-data="{ editingExpiry: false }"
                                class="flex justify-between items-center p-4 border border-gray-100 rounded-xl hover:border-blue-200 hover:bg-blue-50/5 transition group">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                        {{ substr($delegation->delegateTerm->user->first_name, 0, 1) }}{{ substr($delegation->delegateTerm->user->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 leading-tight">
                                            {{ $delegation->delegateTerm->user->name }}
                                        </h3>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <p class="text-xs text-gray-500 italic">
                                                General Signing Authority Granted
                                            </p>
                                            @if($delegation->expires_at)
                                                <span
                                                    class="text-[10px] px-1.5 py-0.5 bg-yellow-100 text-yellow-700 rounded-md font-medium">
                                                    Expires {{ $delegation->expires_at->diffForHumans() }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <div x-show="editingExpiry"
                                        class="flex items-center gap-2 bg-white p-1 rounded-lg border border-gray-100 shadow-sm"
                                        style="display: none;">
                                        <input type="date" class="text-[11px] border-none focus:ring-0 p-1"
                                            x-on:change="$wire.setExpiration({{ $delegation->id }}, $event.target.value); editingExpiry = false">
                                        <button @click="editingExpiry = false"
                                            class="p-1 text-gray-400 hover:text-gray-600">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <button @click="editingExpiry = !editingExpiry"
                                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                        title="Set Expiration">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>

                                    <button wire:click="revoke({{ $delegation->id }})"
                                        wire:confirm="Are you sure you want to revoke this signing authority?"
                                        class="px-3 py-1.5 text-xs font-bold text-red-600 bg-red-50 rounded-lg opacity-0 group-hover:opacity-100 hover:bg-red-100 transition duration-200">
                                        Revoke
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 border-2 border-dashed border-gray-100 rounded-2xl">
                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <h3 class="mt-4 text-sm font-bold text-gray-800">No active delegations</h3>
                                <p class="mt-1 text-xs text-gray-500">You haven't delegated your signing authority to anyone
                                    yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </x-sidebar-layout>

    <!-- Delegate Authority Modal Overlay -->
    @if($showDelegationModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">Delegate Authority</h3>
                    <button wire:click="$set('showDelegationModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveDelegation" class="p-6">
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Select Official</label>
                        <p class="text-xs text-gray-500 mb-4">Choose an official from your barangay to grant signing
                            authority.</p>

                        <select wire:model="selectedDelegateId"
                            class="w-full rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500 transition">
                            <option value="">Select an official...</option>
                            @foreach($potentialDelegates as $official)
                                <option value="{{ $official->id }}">{{ $official->user->name }}
                                    ({{ $official->role_name ?? $official->position->name }})</option>
                            @endforeach
                        </select>
                        @error('selectedDelegateId') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-3 justify-end">
                        <button type="button" wire:click="$set('showDelegationModal', false)"
                            class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition">
                            Confirm Delegation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>