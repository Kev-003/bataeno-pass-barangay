<div class="p-6 gap-y-8">
    @if($house->households->isEmpty())
        <div
            class="flex flex-col items-center justify-center p-12 text-center bg-gray-50 dark:bg-gray-900 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800">
            <x-heroicon-o-home-modern class="w-16 h-16 text-gray-300 mb-4" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Empty House</h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-sm">No households are currently registered at this address.</p>
        </div>
    @else
        @foreach($house->households as $index => $household)
            <div class="space-y-4 {{ $index > 0 ? 'pt-6 mt-6 border-t border-gray-100 dark:border-gray-800' : '' }}">
                {{-- Household Header --}}
                <div class="flex items-center justify-between pb-3">
                    <div class="flex items-center gap-3">
                        <div class="bg-emerald-100 dark:bg-emerald-900/30 p-2 rounded-lg">
                            <x-heroicon-o-user-group class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                Household #{{ $index + 1 }}
                                @if($household->headOfHousehold?->user)
                                    <span class="text-xs font-normal text-gray-400 dark:text-gray-500">— Headed by
                                        {{ $household->headOfHousehold->user->name }}</span>
                                @endif
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $household->members->count() }} registered
                                {{ Str::plural('member', $household->members->count()) }}
                            </p>
                        </div>
                    </div>

                    @if($household->ownership)
                        <span
                            class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                            {{ ucfirst($household->ownership) }}
                        </span>
                    @endif
                </div>

                {{-- Members Grid --}}
                @if($household->members->isEmpty())
                    <p class="text-sm italic text-gray-500 dark:text-gray-400 pl-11">No members registered in this household.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pl-0 md:pl-10">
                        @foreach($household->members as $member)
                            @php $user = $member->user; @endphp
                            @if($user)
                                <div
                                    class="group flex items-center gap-4 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm transition-all hover:shadow-md hover:border-emerald-500/40 dark:hover:border-emerald-500/40">
                                    <div class="relative shrink-0">
                                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                                            class="w-11 h-11 rounded-full object-cover border-2 border-white dark:border-gray-700 shadow-sm">
                                        @if($household->household_head_id === $member->id)
                                            <div class="absolute -top-1 -right-1 p-0.5 bg-yellow-400 rounded-full border border-white dark:border-gray-800 shadow-xs"
                                                title="Head of Household">
                                                <x-heroicon-s-star class="w-2.5 h-2.5 text-white" />
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                                {{ $user->name }}
                                            </h4>
                                            @if($member->role)
                                                <span
                                                    class="px-1.5 py-0.5 rounded text-xs font-bold uppercase tracking-wider bg-gray-50 text-gray-500 dark:bg-gray-900 dark:text-gray-400 border border-gray-100 dark:border-gray-800">
                                                    {{ $member->role }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400">
                                                {{ $user->gender }}
                                            </span>
                                            <span class="text-[10px] text-gray-300 dark:text-gray-600">•</span>
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400">
                                                {{ $user->civil_status ?? 'Single' }}
                                            </span>
                                        </div>
                                    </div>
                                    <a href="{{ \App\Filament\Official\Resources\ResidentResource::getUrl('edit', ['record' => $user]) }}"
                                        class="p-2 text-gray-400 hover:text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-lg transition-all"
                                        title="View Profile">
                                        <x-heroicon-m-arrow-right-circle class="w-6 h-6" />
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>