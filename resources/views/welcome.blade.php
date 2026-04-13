<x-layouts.app>
    <div class="relative min-h-[90vh] flex items-center justify-center overflow-hidden bg-slate-900">
        <!-- Background decorative elements -->
        <div class="absolute inset-0 z-0">
            <div class="absolute top-0 -left-4 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
            <div class="absolute top-0 -right-4 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-8 left-20 w-72 h-72 bg-emerald-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
        </div>

        <div class="relative z-10 max-w-6xl mx-auto px-6 py-12 text-center">
            <div class="mb-8 flex justify-center">
                <span class="inline-flex items-center rounded-full bg-blue-500/10 px-3 py-1 text-sm font-medium text-blue-400 ring-1 ring-inset ring-blue-500/20">
                    Bataeno Pass Portfolio Demo
                </span>
            </div>
            
            <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-6">
                Explore the <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-emerald-400">Bataan Digital System</span>
            </h1>
            
            <p class="text-lg text-slate-400 mb-12 max-w-2xl mx-auto">
                Select an access level below to explore the different perspectives of the multi-tiered governance system.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Resident Card -->
                <a href="{{ route('demo.resident') }}" class="group relative flex flex-col p-6 bg-slate-800/50 backdrop-blur-xl rounded-3xl border border-slate-700 hover:border-blue-500/50 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-400 group-hover:bg-blue-500 group-hover:text-white transition-colors duration-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2 text-left">Resident</h3>
                    <p class="text-slate-400 text-xs text-left mb-6">View personal dashboard, request documents, and track status.</p>
                    <div class="mt-auto flex items-center text-blue-400 text-xs font-bold group-hover:translate-x-1 transition-transform">
                        Enter Resident Side
                        <svg class="ml-2 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

                <!-- Barangay Official Card -->
                <a href="{{ route('demo.official') }}" class="group relative flex flex-col p-6 bg-slate-800/50 backdrop-blur-xl rounded-3xl border border-slate-700 hover:border-emerald-500/50 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-400 group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2 text-left">Official</h3>
                    <p class="text-slate-400 text-xs text-left mb-6">Manage resident records and approve local data/requests.</p>
                    <div class="mt-auto flex items-center text-emerald-400 text-xs font-bold group-hover:translate-x-1 transition-transform">
                        Enter Official Side
                        <svg class="ml-2 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

                <!-- City Admin Card -->
                <a href="{{ route('demo.city.admin') }}" class="group relative flex flex-col p-6 bg-slate-800/50 backdrop-blur-xl rounded-3xl border border-slate-700 hover:border-amber-500/50 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-400 group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2 text-left">City Admin</h3>
                    <p class="text-slate-400 text-xs text-left mb-6">Oversee municipality data and barangay level metrics.</p>
                    <div class="mt-auto flex items-center text-amber-400 text-xs font-bold group-hover:translate-x-1 transition-transform">
                        Enter City Side
                        <svg class="ml-2 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

                <!-- Master Admin Card -->
                <a href="{{ route('demo.admin') }}" class="group relative flex flex-col p-6 bg-slate-800/50 backdrop-blur-xl rounded-3xl border border-slate-700 hover:border-purple-500/50 transition-all duration-300 transform hover:-translate-y-1">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-500/10 text-purple-400 group-hover:bg-purple-500 group-hover:text-white transition-colors duration-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2 text-left">Admin</h3>
                    <p class="text-slate-400 text-xs text-left mb-6">Master view for entire system analytics and global settings.</p>
                    <div class="mt-auto flex items-center text-purple-400 text-xs font-bold group-hover:translate-x-1 transition-transform">
                        Enter Master Side
                        <svg class="ml-2 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>