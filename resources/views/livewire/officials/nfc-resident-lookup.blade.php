<div class="fi-resident-lookup-wrapper transition-all duration-300">

    {{-- ═══════════════════════════════════════════════════════════════════
    IDLE STATE — Waiting for Tap
    ═══════════════════════════════════════════════════════════════════ --}}
    @if(!$cardUid && !$loading && !$resident && !$error)
        <div
            class="flex flex-col items-center justify-center py-12 text-center
                                                    bg-gray-50 dark:bg-white/5 border-2 border-dashed border-gray-200 dark:border-white/10 !rounded-2xl !overflow-hidden">
            <div
                class="w-16 h-16 rounded-full bg-primary-500/10 dark:bg-primary-500/20 flex items-center justify-center mb-4 ring-1 ring-primary-500/20">
                <x-heroicon-o-rss class="w-8 h-8 text-primary-600 dark:text-primary-400" />
            </div>
            <p class="text-gray-950 dark:text-white font-semibold text-base">Ready to Scan</p>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 max-w-[200px]">Hold the resident's Bataeno Pass card
                near the reader</p>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
    LOADING SKELETON
    ═══════════════════════════════════════════════════════════════════ --}}
    @if($loading)
        <div
            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 !rounded-2xl overflow-hidden p-6 shadow-sm animate-pulse">
            <div class="flex items-start gap-5">
                <div class="w-20 h-20 rounded-xl bg-gray-200 dark:bg-white/5 flex-shrink-0"></div>
                <div class="flex-1 space-y-3 pt-1">
                    <div class="h-4 bg-gray-200 dark:bg-white/5 rounded w-2/5"></div>
                    <div class="h-3 bg-gray-200 dark:bg-white/5 rounded w-3/5"></div>
                </div>
            </div>
            <div class="mt-8 grid grid-cols-2 gap-6">
                @foreach(range(1, 4) as $_)
                    <div class="space-y-2">
                        <div class="h-2 bg-gray-100 dark:bg-white/5 rounded w-1/3"></div>
                        <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-3/4"></div>
                    </div>
                @endforeach
            </div>
            <div
                class="mt-6 flex items-center justify-center gap-2 text-primary-600 dark:text-primary-400 text-xs font-medium uppercase tracking-wider">
                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                Verifying Card...
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
    ERROR STATE
    ═══════════════════════════════════════════════════════════════════ --}}
    @if($error && !$loading)
        <div
            class="flex items-start gap-3 bg-danger-50 dark:bg-danger-500/10 border border-danger-200 dark:border-danger-500/20 text-danger-700 dark:text-danger-400 rounded-xl p-4">
            <x-heroicon-m-exclamation-circle class="w-5 h-5 mt-0.5 flex-shrink-0" />
            <div>
                <p class="font-bold text-sm">Scan Error</p>
                <p class="text-sm opacity-90">{{ $error }}</p>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
    RESIDENT CARD (SUCCESS)
    ═══════════════════════════════════════════════════════════════════ --}}
    @if($resident && !$loading)
        <div
            class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-2xl overflow-hidden shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">

            {{-- Modern Header --}}
            <div
                class="px-6 py-6 flex flex-col sm:flex-row items-center sm:items-start gap-5 bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5">
                {{-- Avatar --}}
                <div
                    class="w-24 h-24 rounded-2xl bg-primary-600 flex items-center justify-center text-3xl font-bold text-white shadow-lg shadow-primary-500/20 overflow-hidden ring-4 ring-white dark:ring-gray-800">
                    @if($resident['profile_photo'] ?? null)
                        <img src="{{ $resident['profile_photo'] }}" class="w-full h-full object-cover">
                    @else
                        {{ $this->getInitials() }}
                    @endif
                </div>

                {{-- Name & Badges --}}
                <div class="flex-1 text-center sm:text-left">
                    <h2 class="text-2xl font-bold text-gray-950 dark:text-white tracking-tight">
                        {{ $resident['name'] ?? '—' }}
                    </h2>
                    <p class="text-xs font-mono text-gray-400 mt-1 uppercase tracking-tighter">UUID:
                        {{ $resident['uuid'] ?? $cardUid }}
                    </p>

                    <div class="flex flex-wrap justify-center sm:justify-start gap-2 mt-3">
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-[10px] font-bold uppercase tracking-wider border border-emerald-200 dark:border-emerald-500/20">
                            <x-heroicon-m-check-badge class="w-3 h-3" />
                            Verified
                        </span>
                        @if($source === 'local')
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full bg-primary-100 dark:bg-primary-500/10 text-primary-700 dark:text-primary-400 text-[10px] font-bold uppercase tracking-wider border border-primary-200 dark:border-primary-500/20">
                                Barangay Resident
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Details Grid --}}
            <div class="px-6 py-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                @php
                    $fields = [
                        ['label' => 'Birthday', 'value' => $resident['birthdate'] ?? '—'],
                        ['label' => 'Sex', 'value' => $resident['sex'] ?? '—'],
                        ['label' => 'Status', 'value' => $resident['civil_status'] ?? '—'],
                        ['label' => 'Contact', 'value' => $resident['contact_number'] ?? '—'],
                        ['label' => 'Address', 'value' => $resident['address'] ?? '—', 'full' => true],
                    ];
                @endphp

                @foreach($fields as $field)
                    <div @class(['sm:col-span-2' => $field['full'] ?? false])>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                            {{ $field['label'] }}
                        </p>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-1 leading-relaxed">
                            {{ $field['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>

            {{-- Footer Info --}}
            <div
                class="px-6 py-3 bg-gray-50 dark:bg-white/5 border-t border-gray-100 dark:border-white/5 flex justify-between items-center text-[10px] text-gray-400 font-medium">
                <span>SYSTEM: {{ strtoupper($source ?? 'BATAENO PASS') }}</span>
                <span>SCANNED: {{ now()->format('h:i A') }}</span>
            </div>
        </div>
    @endif

</div>