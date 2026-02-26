<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <x-slot name="header">
        <h1 class="text-3xl font-bold text-gray-800 leading-tight">
            {{ __('Household Profiles')}}
        </h1>
        <p class="mt-2 text-sm text-gray-500">
            {{ __('Manage your residency across different households in the province.')}}
        </p>
    </x-slot>

    <div class="flex items-center justify-between mb-8">
        @if(session()->has('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                class="bg-emerald-50 border border-emerald-100 text-emerald-800 px-4 py-2 rounded-xl text-sm font-medium animate-in fade-in slide-in-from-right-4 duration-300">
                {{ session('success') }}
            </div>
        @endif
    </div>

    {{-- Pending/Rejected Requests Section --}}
    @if($pendingRequests->isNotEmpty())
        <div class="mb-10">
            <h2 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Residency Applications</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($pendingRequests as $request)
                    <div @class([
                        'rounded-3xl p-6 relative overflow-hidden border transition-all duration-300',
                        'bg-amber-50/50 border-amber-200' => $request->status === 'Pending',
                        'bg-red-50/50 border-red-200' => $request->status === 'Rejected',
                    ])>
                        <div class="absolute top-0 right-0 p-3">
                            @if($request->status === 'Pending')
                                <span class="animate-pulse flex h-2 w-2">
                                    <span class="absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                </span>
                            @else
                                <div class="w-2 h-2 rounded-full bg-red-500"></div>
                            @endif
                        </div>
                        <div class="flex items-center gap-4 mb-4">
                            <div @class([
                                'w-10 h-10 rounded-xl flex items-center justify-center',
                                'bg-amber-100 text-amber-600' => $request->status === 'Pending',
                                'bg-red-100 text-red-600' => $request->status === 'Rejected',
                            ])>
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($request->status === 'Pending')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    @endif
                                </svg>
                            </div>
                            <div>
                                <p @class([
                                    'text-xs font-bold uppercase tracking-tight',
                                    'text-amber-700' => $request->status === 'Pending',
                                    'text-red-700' => $request->status === 'Rejected',
                                ])>Status: {{ $request->status }}</p>
                                <p class="text-[10px] text-gray-500">Applied {{ $request->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <h3 class="font-bold text-gray-800 truncate">{{ $request->street }}</h3>
                        <p class="text-xs text-gray-500 italic mb-2">Brgy. {{ $request->barangay->name }}</p>

                        @if($request->status === 'Rejected' && $request->rejection_reason)
                            <div class="mt-4 p-3 bg-red-100/50 rounded-2xl border border-red-100">
                                <p class="text-[10px] font-bold text-red-800 uppercase tracking-widest mb-1">Reason for Rejection
                                </p>
                                <p class="text-xs text-red-700 leading-relaxed">{{ $request->rejection_reason }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($profiles as $profile)
            <div @class([
                'relative group overflow-hidden rounded-3xl transition-all duration-500',
                'bg-white border-2 border-primary-500 shadow-xl shadow-primary-500/10 scale-[1.02] z-10' => $profile->presence_status === 'Present',
                'bg-white/60 backdrop-blur-md border border-gray-100 shadow-sm hover:shadow-md hover:scale-[1.01]' => $profile->presence_status !== 'Present'
            ])>
                {{-- Status Ribbon/Badge --}}
                @if($profile->presence_status === 'Present')
                    <div class="absolute top-0 right-0">
                        <div
                            class="bg-primary-500 text-white text-[10px] font-bold uppercase tracking-widest px-8 py-1 rotate-45 translate-x-[24px] translate-y-[10px] shadow-sm">
                            Primary
                        </div>
                    </div>
                @endif

                <div class="p-6">
                    {{-- Header: Barangay & Icon --}}
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center text-primary-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a11 11 0 003 3h8a1 1 0 001-1V10M19 10l2 2m-2-2a11 11 0 01-1-1V5a1 1 0 00-1-1H9a1 1 0 00-1 1v2.5a1 1 0 01-1 1" />
                            </svg>
                        </div>

                        <span @class([
                            'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider',
                            'bg-primary-100 text-primary-700' => $profile->presence_status === 'Present',
                            'bg-gray-100 text-gray-500' => $profile->presence_status !== 'Present'
                        ])>
                            {{ $profile->presence_status }}
                        </span>
                    </div>

                    {{-- Address Details --}}
                    <div class="space-y-4 mb-8">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 line-clamp-1">
                                {{ $profile->household->house->street ?? 'Main Street' }}
                            </h3>
                            <p class="text-sm text-primary-600 font-semibold tracking-wide">
                                Brgy. {{ $profile->household->house->linkedBarangay->name ?? 'Unknown' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Role</p>
                                <p class="text-sm font-semibold text-gray-700">{{ $profile->role }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Ownership</p>
                                <p class="text-sm font-semibold text-gray-700">{{ $profile->household->ownership ?? '—' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="space-y-3">
                        @if($profile->presence_status === 'Present')
                            <div class="flex items-center gap-2 text-primary-600 py-2 justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-sm font-bold">Currently Active</span>
                            </div>
                        @else
                            <button wire:click="switchPresence({{ $profile->id }})" wire:loading.attr="disabled"
                                class="w-full bg-white border-2 border-gray-200 text-gray-700 py-3 rounded-2xl text-sm font-bold hover:bg-gray-50 active:scale-95 transition-all duration-300 flex items-center justify-center gap-2">
                                <svg wire:loading.remove wire:target="switchPresence({{ $profile->id }})" class="w-4 h-4"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <span wire:loading wire:target="switchPresence({{ $profile->id }})"
                                    class="loading loading-spinner loading-xs text-primary-500"></span>
                                Set as Present
                            </button>
                        @endif

                        @php
                            $isHead = $profile->role === 'Head' || $profile->id === ($profile->household->household_head_id ?? null);
                        @endphp

                        @if($isHead)
                            <button wire:click="openAddMemberModal({{ $profile->household_id }})"
                                class="w-full bg-primary-50 text-primary-600 py-3 rounded-2xl text-sm font-bold hover:bg-primary-100 active:scale-95 transition-all duration-300 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                Add Member
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Interactive Hover Glow (Inactive cards only) --}}
                @if($profile->presence_status !== 'Present')
                    <div
                        class="absolute inset-0 bg-primary-100 opacity-0 group-hover:opacity-5 pointer-events-none transition-all duration-300">
                    </div>
                @endif
            </div>
        @endforeach

        {{-- Add New Household --}}
        <div wire:click="$set('showRequestModal', true)"
            class="rounded-3xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center p-8 bg-gray-50/50 hover:bg-white hover:border-primary-400 transition-all group cursor-pointer shadow-sm">
            <div
                class="w-12 h-12 rounded-full border-2 border-gray-300 flex items-center justify-center text-gray-400 group-hover:text-primary-500 group-hover:border-primary-300 transition-colors mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <p class="text-sm font-bold text-gray-500 group-hover:text-primary-600">Register New Residence</p>
        </div>
    </div>

    {{-- Request Modal --}}
    @if($showRequestModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                    wire:click="$set('showRequestModal', false)"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100">
                    <div class="bg-white p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900" id="modal-title">New Residency Request</h3>
                                <p class="text-sm text-gray-500">Provide details for your new household.</p>
                            </div>
                            <button wire:click="$set('showRequestModal', false)"
                                class="p-2 rounded-xl hover:bg-gray-100 transition-colors">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form wire:submit.prevent="submitRequest" class="space-y-6">
                            {{-- Request Type Toggle --}}
                            <div class="bg-gray-50 p-1 rounded-2xl flex gap-1">
                                <button type="button" 
                                    wire:click="$set('request_type', 'new')"
                                    @class([
                                        'flex-1 py-2 px-4 rounded-xl text-sm font-bold transition-all',
                                        'bg-white shadow-sm text-primary-600' => $request_type === 'new',
                                        'text-gray-500 hover:text-gray-700' => $request_type !== 'new'
                                    ])>
                                    New Household
                                </button>
                                <button type="button" 
                                    wire:click="$set('request_type', 'join')"
                                    @class([
                                        'flex-1 py-2 px-4 rounded-xl text-sm font-bold transition-all',
                                        'bg-white shadow-sm text-primary-600' => $request_type === 'join',
                                        'text-gray-500 hover:text-gray-700' => $request_type !== 'join'
                                    ])>
                                    Join Existing
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="col-span-2">
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Select Municipality</label>
                                    <select wire:model.live="municipality_id" class="w-full bg-gray-50 border-0 rounded-2xl px-4 py-3 text-gray-700 focus:ring-2 focus:ring-primary-500 transition-all outline-none">
                                        <option value="">Select a municipality...</option>
                                        @foreach($municipalities as $municipality)
                                            <option value="{{ $municipality->id }}">{{ $municipality->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-span-2">
                                    <label @class([
                                        'block text-xs font-bold uppercase tracking-widest mb-2 transition-colors',
                                        'text-gray-400' => $municipality_id,
                                        'text-gray-300' => !$municipality_id,
                                    ])>Select Barangay</label>
                                    <select
                                        wire:model.live="barangay_id"
                                        @disabled(!$municipality_id)
                                        @class([
                                            'w-full border-0 rounded-2xl px-4 py-3 transition-all outline-none',
                                            'bg-gray-50 text-gray-700 focus:ring-2 focus:ring-primary-500' => $municipality_id,
                                            'bg-gray-100 text-gray-400 cursor-not-allowed' => !$municipality_id,
                                        ])
                                    >
                                        <option value="">{{ $municipality_id ? 'Select a barangay...' : 'Please select a municipality first' }}</option>
                                        @foreach($barangays as $barangay)
                                            <option value="{{ $barangay->id }}">{{ $barangay->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('barangay_id') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                                </div>

                                @if($request_type === 'join')
                                    <div class="col-span-2">
                                        <label @class([
                                            'block text-xs font-bold uppercase tracking-widest mb-2',
                                            'text-gray-400' => $barangay_id,
                                            'text-gray-300' => !$barangay_id,
                                        ])>Select Existing Household</label>
                                        <select 
                                            wire:model="household_id"
                                            @disabled(!$barangay_id)
                                            @class([
                                                'w-full border-0 rounded-2xl px-4 py-3 transition-all outline-none',
                                                'bg-gray-50 text-gray-700 focus:ring-2 focus:ring-primary-500' => $barangay_id,
                                                'bg-gray-100 text-gray-400 cursor-not-allowed' => !$barangay_id,
                                            ])
                                        >
                                            <option value="">{{ $barangay_id ? 'Select household...' : 'Please select a barangay first' }}</option>
                                            @foreach($households as $h)
                                                <option value="{{ $h->id }}">
                                                    {{ $h->house->street }} - Head: {{ $h->headOfHousehold->user->name ?? 'Unknown' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('household_id') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                                    </div>
                                @else
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Street</label>
                                        <input type="text" wire:model="street" placeholder="e.g. Rizal St."
                                            class="w-full bg-gray-50 border-0 rounded-2xl px-4 py-3 text-gray-700 focus:ring-2 focus:ring-primary-500 transition-all outline-none">
                                        @error('street') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label
                                            class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Subdivision</label>
                                        <input type="text" wire:model="subdivision" placeholder="e.g. Phase 1 (Optional)"
                                            class="w-full bg-gray-50 border-0 rounded-2xl px-4 py-3 text-gray-700 focus:ring-2 focus:ring-primary-500 transition-all outline-none">
                                    </div>

                                    <div>
                                        <label
                                            class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Housing
                                            Unit</label>
                                        <input type="text" wire:model="housing_unit" placeholder="e.g. Blk 1 Lot 2"
                                            class="w-full bg-gray-50 border-0 rounded-2xl px-4 py-3 text-gray-700 focus:ring-2 focus:ring-primary-500 transition-all outline-none">
                                    </div>
                                @endif

                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Role</label>
                                    <select wire:model="role"
                                        class="w-full bg-gray-50 border-0 rounded-2xl px-4 py-3 text-gray-700 focus:ring-2 focus:ring-primary-500 transition-all outline-none">
                                        <option value="Member">Member</option>
                                        <option value="Head">Head of Household</option>
                                        <option value="Boarder">Boarder</option>
                                    </select>
                                </div>

                                <div class="col-span-2">
                                    <label
                                        class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Ownership
                                        Status</label>
                                    <select wire:model="ownership"
                                        class="w-full bg-gray-50 border-0 rounded-2xl px-4 py-3 text-gray-700 focus:ring-2 focus:ring-primary-500 transition-all outline-none">
                                        <option value="Owned">Owned</option>
                                        <option value="Rented">Rented</option>
                                        <option value="Living with Parents/Relatives">Living with Relatives</option>
                                    </select>
                                </div>
                            </div>

                            <div class="pt-6">
                                <button type="submit"
                                    class="w-full bg-primary-600 text-white font-bold py-4 rounded-2xl hover:bg-primary-700 shadow-xl shadow-primary-500/30 active:scale-95 transition-all">
                                    Submit Registration Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    {{-- Add Member Modal --}}
    @if($showAddMemberModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showAddMemberModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">
                    <div class="bg-white p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">Add Member</h3>
                                <p class="text-sm text-gray-500">Search for a user to add to your home.</p>
                            </div>
                            <button wire:click="$set('showAddMemberModal', false)" class="p-2 rounded-xl hover:bg-gray-100 transition-colors">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Search Resident Name</label>
                                <div class="relative">
                                    <input type="text" 
                                        wire:model.live.debounce.300ms="search_user_query" 
                                        placeholder="Type at least 3 characters..."
                                        class="w-full bg-gray-50 border-0 rounded-2xl px-4 py-3 pl-11 text-gray-700 focus:ring-2 focus:ring-primary-500 transition-all outline-none">
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            @if(strlen($search_user_query) >= 3)
                                <div class="space-y-2">
                                    @forelse($searchResults as $user)
                                        <button 
                                            wire:click="selectUser({{ $user->id }})"
                                            @class([
                                                'w-full flex items-center gap-3 p-3 rounded-2xl transition-all border',
                                                'bg-primary-50 border-primary-200 ring-2 ring-primary-500/20' => $target_user_id === $user->id,
                                                'bg-white border-gray-100 hover:bg-gray-50' => $target_user_id !== $user->id
                                            ])>
                                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div class="text-left">
                                                <p class="text-sm font-bold text-gray-900">{{ $user->name }}</p>
                                                <p class="text-xs text-gray-500">ID: #{{ $user->id }}</p>
                                            </div>
                                            @if($target_user_id === $user->id)
                                                <div class="ml-auto text-primary-500">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </button>
                                    @empty
                                        <p class="text-center py-4 text-sm text-gray-400">No matching residents found.</p>
                                    @endforelse
                                </div>
                            @endif

                            <div class="pt-4">
                                <button 
                                    wire:click="inviteMember"
                                    @disabled(!$target_user_id)
                                    class="w-full bg-primary-600 text-white font-bold py-4 rounded-2xl hover:bg-primary-700 shadow-xl shadow-primary-500/30 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                    Send Residency Invitation
                                </button>
                                <p class="text-[10px] text-center text-gray-400 mt-3 uppercase tracking-widest font-bold">
                                    Subject to Official Barangay Verification
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>