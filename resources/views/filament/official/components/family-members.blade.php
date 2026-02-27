<div class="space-y-3 p-4">
    @if($family->members->isEmpty())
        <p class="text-sm text-gray-400 text-center py-4">No members found.</p>
    @else
        <div class="space-y-2">
            @foreach($family->members->sortBy('date_of_birth') as $member)
                <div class="flex items-center gap-3 p-3 rounded-lg border {{ $member->trashed() ? 'bg-red-50 border-red-200' : 'bg-white border-gray-200' }}">
                    {{-- Gender Icon --}}
                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                        {{ $member->gender === 'Male' ? 'bg-blue-100 text-blue-600' : 'bg-pink-100 text-pink-600' }}">
                        {{ $member->gender === 'Male' ? '♂' : '♀' }}
                    </div>

                    {{-- Name & Details --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold truncate {{ $member->trashed() ? 'text-red-600 line-through' : 'text-gray-800' }}">
                            {{ $member->name }}
                            @if($member->id === $family->father_id)
                                <span class="ml-1 text-[10px] text-blue-500 font-normal bg-blue-50 px-1 rounded border border-blue-200 uppercase tracking-tighter">Father/Head</span>
                            @elseif($member->id === $family->mother_id)
                                <span class="ml-1 text-[10px] text-pink-500 font-normal bg-pink-50 px-1 rounded border border-pink-200 uppercase tracking-tighter">Mother/Spouse</span>
                            @endif
                        </p>
                        <p class="text-[11px] text-gray-400">
                            {{ $member->civil_status }}
                            · Born {{ \Carbon\Carbon::parse($member->date_of_birth)->format('M d, Y') }}
                            · {{ \Carbon\Carbon::parse($member->date_of_birth)->age }} yrs old
                        </p>
                    </div>

                    {{-- Status Badge --}}
                    <div class="flex-shrink-0">
                        @if($member->trashed())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-100 text-red-700">
                                Deceased
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100 text-emerald-700">
                                Living
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-2 border-t border-gray-100">
            <p class="text-[10px] text-gray-400 text-center">
                {{ $family->members->count() }} member(s)
                · {{ $family->members->where('deleted_at', null)->count() }} living
                · {{ $family->members->whereNotNull('deleted_at')->count() }} deceased
            </p>
        </div>
    @endif
</div>
