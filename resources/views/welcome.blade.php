<x-layouts.app>
    <div class="min-h-[90vh] bg-slate-50">

        <!-- Ribbon -->
        <div class="border-b border-slate-800" style="background-color:#020617;">
            <div class="max-w-5xl mx-auto px-6 py-3 flex items-center justify-between flex-wrap gap-2">
                <span class="font-['Bevan'] font-semibold text-xs tracking-[0.14em] uppercase text-white">
                    Bataeno Pass
                </span>
                <span class="font-['Inter'] font-medium text-xs tracking-[0.14em] uppercase text-slate-400">
                    Portfolio Demo
                </span>
            </div>
        </div>

        <!-- Hero -->
        <div class="max-w-5xl mx-auto px-6 pt-12">
            <div class="hero-gradient rounded-3xl px-8 py-14 md:px-14 md:py-16 relative overflow-hidden"
                 style="background-color:#020617; background-image: radial-gradient(circle at top right, #082f49, #312e81);">

                <span class="inline-block font-['Inter'] font-semibold text-xs tracking-[0.14em] uppercase px-3 py-1 rounded-full mb-6 bg-white/10 text-emerald-300 ring-1 ring-emerald-400/30">
                    04 access tiers · one civic record
                </span>

                <h1 class="font-['Bevan'] font-normal text-[2.25rem] md:text-[3.25rem] leading-[1.1] text-white max-w-2xl">
                    Barangay-level records.<br />Four vantage points.
                </h1>

                <p class="font-['Inter'] text-base md:text-lg text-slate-300 max-w-xl mt-6 leading-relaxed">
                    Bataeno Pass models how a single civic record moves through a local
                    government — from the resident who owns it, to the official who
                    processes it, up to the admins who oversee it at scale. Pick a tier
                    to preview that vantage point.
                </p>
            </div>
        </div>

        <!-- Tier grid -->
        <div class="max-w-5xl mx-auto px-6 pt-20 pb-20">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- 01 Resident -->
                <a href="{{ route('demo.resident') }}"
                   class="group relative flex flex-col bg-white rounded-2xl p-7 ring-1 ring-slate-200
                          shadow-sm transition-all duration-200 ease-out
                          hover:shadow-lg hover:-translate-y-0.5 hover:ring-blue-300
                          focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-500 focus-visible:outline-offset-2">
                    <div class="flex items-start justify-between mb-6">
                        <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl font-['Inter'] font-bold text-sm bg-blue-50 text-blue-700">
                            01
                        </span>
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-slate-50 text-slate-400 transition-all duration-200 group-hover:bg-blue-600 group-hover:text-white">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                    <h2 class="font-['Bevan'] font-normal text-xl text-slate-900 mb-2">Resident</h2>
                    <p class="font-['Inter'] text-sm text-slate-500 leading-relaxed">
                        View your dashboard, request documents, track status.
                    </p>
                </a>

                <!-- 02 Official -->
                <a href="{{ route('demo.official') }}"
                   class="group relative flex flex-col bg-white rounded-2xl p-7 ring-1 ring-slate-200
                          shadow-sm transition-all duration-200 ease-out
                          hover:shadow-lg hover:-translate-y-0.5 hover:ring-blue-300
                          focus-visible:outline focus-visible:outline-2 focus-visible:outline-blue-500 focus-visible:outline-offset-2">
                    <div class="flex items-start justify-between mb-6">
                        <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl font-['Inter'] font-bold text-sm bg-blue-600 text-white">
                            02
                        </span>
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-slate-50 text-slate-400 transition-all duration-200 group-hover:bg-blue-600 group-hover:text-white">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                    <h2 class="font-['Bevan'] font-normal text-xl text-slate-900 mb-2">Official</h2>
                    <p class="font-['Inter'] text-sm text-slate-500 leading-relaxed">
                        Manage resident records, approve local requests.
                    </p>
                </a>

                <!-- 03 City Admin -->
                <a href="{{ route('demo.city.admin') }}"
                   class="group relative flex flex-col bg-white rounded-2xl p-7 ring-1 ring-slate-200
                          shadow-sm transition-all duration-200 ease-out
                          hover:shadow-lg hover:-translate-y-0.5 hover:ring-emerald-300
                          focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-500 focus-visible:outline-offset-2">
                    <div class="flex items-start justify-between mb-6">
                        <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl font-['Inter'] font-bold text-sm bg-emerald-50 text-emerald-700">
                            03
                        </span>
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-slate-50 text-slate-400 transition-all duration-200 group-hover:bg-emerald-600 group-hover:text-white">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                    <h2 class="font-['Bevan'] font-normal text-xl text-slate-900 mb-2">City Admin</h2>
                    <p class="font-['Inter'] text-sm text-slate-500 leading-relaxed">
                        Oversee municipality data and barangay-level metrics.
                    </p>
                </a>

                <!-- 04 Master Admin -->
                <a href="{{ route('demo.admin') }}"
                   class="group relative flex flex-col rounded-2xl p-7 overflow-hidden
                          shadow-sm transition-all duration-200 ease-out
                          hover:shadow-lg hover:-translate-y-0.5
                          focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-400 focus-visible:outline-offset-2"
                   style="background-color:#020617; background-image: radial-gradient(circle at top right, #082f49, #312e81);">
                    <div class="flex items-start justify-between mb-6">
                        <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl font-['Inter'] font-bold text-sm bg-emerald-500 text-white">
                            04
                        </span>
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/10 text-white/60 transition-all duration-200 group-hover:bg-emerald-500 group-hover:text-white">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                    <h2 class="font-['Bevan'] font-normal text-xl text-white mb-2">Admin</h2>
                    <p class="font-['Inter'] text-sm text-slate-300 leading-relaxed">
                        Master view of system-wide analytics and global settings.
                    </p>
                </a>

            </div>
        </div>

        <!-- Footer note -->
        <div class="border-t border-slate-200 bg-white">
            <div class="max-w-5xl mx-auto px-6 py-4">
                <p class="font-['Inter'] font-medium text-xs tracking-[0.08em] uppercase text-slate-400">
                    Demo data only — no real resident records are shown
                </p>
            </div>
        </div>

    </div>
</x-layouts.app>