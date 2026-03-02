<div
    x-data
    class="font-sans antialiased"
>


    {{-- ═══════════════════════════════════════════════════════════════════
         IDLE STATE — waiting for tap
    ═══════════════════════════════════════════════════════════════════ --}}
    @if(! $cardUid && ! $loading && ! $resident && ! $error)
        <div class="flex flex-col items-center justify-center py-16 text-center
                    bg-white border-2 border-dashed border-gray-200 rounded-2xl">
            <div class="w-20 h-20 rounded-full bg-sky-100 flex items-center justify-center mb-5">
                {{-- NFC / tap icon --}}
                <svg class="w-9 h-9 text-sky-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 0 1 1.06 0Z" />
                </svg>
            </div>
            <p class="text-sky-700 font-semibold text-base">Tap a Bataeno Pass card</p>
            <p class="text-sky-400 text-sm mt-1">Hold the card near the reader to look up resident details</p>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
         LOADING SKELETON
    ═══════════════════════════════════════════════════════════════════ --}}
    @if($loading)
        <div class="bg-gradient-to-br from-sky-900 to-sky-800 rounded-2xl p-6 animate-pulse">
            <div class="flex items-start gap-5">
                <div class="w-20 h-20 rounded-xl bg-sky-700 flex-shrink-0"></div>
                <div class="flex-1 space-y-3 pt-1">
                    <div class="h-4 bg-sky-700 rounded w-2/5"></div>
                    <div class="h-3 bg-sky-700 rounded w-3/5"></div>
                    <div class="h-3 bg-sky-700 rounded w-1/3"></div>
                </div>
            </div>
            <div class="mt-6 grid grid-cols-2 gap-4">
                @foreach(range(1,4) as $_)
                    <div class="space-y-1.5">
                        <div class="h-2.5 bg-sky-700 rounded w-1/3"></div>
                        <div class="h-3.5 bg-sky-700 rounded w-3/4"></div>
                    </div>
                @endforeach
            </div>
        </div>
        <p class="text-center text-sm text-sky-500 mt-3 flex items-center justify-center gap-2">
            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
            Looking up resident on Bataeno Pass…
        </p>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
         ERROR STATE
    ═══════════════════════════════════════════════════════════════════ --}}
    @if($error && ! $loading)
        <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4">
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <div>
                <p class="font-semibold text-sm">Lookup Failed</p>
                <p class="text-sm mt-0.5 text-red-600">{{ $error }}</p>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
         RESIDENT CARD
    ═══════════════════════════════════════════════════════════════════ --}}
    @if($resident && ! $loading)
        <div class="bg-gradient-to-br from-sky-900 via-sky-800 to-sky-900 rounded-2xl overflow-hidden shadow-xl">
            {{-- Header --}}
            <div class="px-6 pt-6 pb-5 flex items-start gap-5 border-b border-white/10">
                {{-- Avatar --}}
                <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-sky-500 to-sky-700
                            flex items-center justify-center text-2xl font-bold text-white flex-shrink-0 shadow-lg">
                    {{ $this->getInitials() }}
                </div>

                {{-- Name & status --}}
                <div class="flex-1 min-w-0 pt-1">
                    <h2 class="text-white font-bold text-xl leading-tight truncate">
                        {{ $resident['name'] ?? '—' }}
                    </h2>
                    @if($resident['uuid'] ?? null)
                        <p class="text-slate-400 text-xs font-mono mt-1">UUID: {{ $resident['uuid'] }}</p>
                    @endif
                    <div class="flex items-center gap-2 mt-2.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full
                                     bg-lime-500/20 text-lime-300 text-xs font-semibold border border-lime-500/30">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/>
                            </svg>
                            Current Bataeno Pass Verified
                        </span>
                        @if($source === 'local')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full
                                         bg-sky-500/10 text-sky-300 text-xs font-semibold border border-sky-500/30">
                                Local DB
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Details grid --}}
            <div class="px-6 py-5 grid grid-cols-2 gap-x-8 gap-y-5">

                @php
                    $raw = $resident['raw'] ?? [];
                    $fields = [
                        ['label' => 'BIRTHDAY',      'value' => $resident['birthdate'] ?? $raw['birthday'] ?? null],
                        ['label' => 'BIRTHPLACE',    'value' => $resident['birth_place'] ?? $raw['birth_place'] ?? $raw['place_of_birth'] ?? null],
                        ['label' => 'SEX',           'value' => $resident['sex'] ?? $raw['sex'] ?? $raw['gender'] ?? null],
                        ['label' => 'CIVIL STATUS',  'value' => $resident['civil_status'] ?? $raw['civil_status'] ?? null],
                        ['label' => 'MOBILE NUMBER', 'value' => $resident['contact_number'] ?? $raw['mobile'] ?? $raw['phone'] ?? null],
                        ['label' => 'EMAIL ADDRESS', 'value' => $resident['email'] ?? $raw['email'] ?? null],
                        ['label' => 'ADDRESS',       'value' => $resident['address'] ?? $raw['barangay_name'] ?? $raw['address'] ?? null],
                    ];
                @endphp

                @foreach($fields as $field)
                    <div>
                        <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest">
                            {{ $field['label'] }}
                        </p>
                        <p class="text-white text-sm font-medium mt-1 leading-snug">
                            {{ $field['value'] ?? '—' }}
                        </p>
                    </div>
                @endforeach

            </div>

            {{-- Footer --}}
            <div class="px-6 py-3 bg-black/20 border-t border-white/5 flex items-center justify-between">
                <p class="text-sky-200 text-xs">
                    Source: <span class="text-sky-100 font-medium">{{ $source ?? 'bataeno' }}</span>
                </p>
                <p class="text-sky-200 text-xs">
                    {{ now()->format('M d, Y · g:i A') }}
                </p>
            </div>
        </div>
    @endif

</div>