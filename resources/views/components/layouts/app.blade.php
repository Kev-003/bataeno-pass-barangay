<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Bataeno Pass Barangay') }}</title>

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bevan&display=swap" rel="stylesheet">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        h2,
        h3,
        .font-display {
            font-family: 'Outfit', sans-serif;
        }

        /* Premium Blue Gradient */
        .bg-bataan-premium {
            background: linear-gradient(to bottom, #2563eb, #1e3a8a);
        }
    </style>
</head>

<body class="h-full antialiased text-slate-900 border-t-4 border-blue-600">

    <div x-data="{ mobileMenuOpen: false }" class="min-h-full">
        <!-- Navigation -->
        <nav class="bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
                <div class="flex justify-between h-20">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <div class="bg-blue-600 p-2 rounded-lg shadow-blue-200 shadow-md">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.040A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <span
                                class="ml-3 text-xl font-display font-extrabold tracking-tight text-slate-900 uppercase">
                                Bataeno<span class="text-blue-600">Pass</span>
                                <span class="block text-[10px] font-bold text-slate-400 -mt-1 tracking-[.2em]">BARANGAY
                                    PORTAL</span>
                            </span>
                        </div>
                    </div>

                    <!-- Desktop Menu -->
                    <div class="hidden sm:ml-6 sm:flex sm:items-center space-x-4">
                        @auth
                            <a href="{{ route('dashboard') }}"
                                class="text-slate-600 hover:text-blue-600 px-3 py-2 text-sm font-semibold">Dashboard</a>

                            <!-- User Info -->
                            <div
                                class="ml-4 relative flex items-center bg-slate-100 pl-4 pr-1 py-1 rounded-full border border-slate-200">
                                <span class="text-xs font-bold text-slate-700 mr-2">{{ auth()->user()->first_name }}</span>
                                <img class="h-8 w-8 rounded-full shadow-sm border border-white"
                                    src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->first_name) }}&background=0284c7&color=fff"
                                    alt="">
                            </div>

                            <form method="POST" action="{{ route('logout') }}" class="ml-4">
                                @csrf
                                <button type="submit" class="text-slate-400 hover:text-red-500 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4-4H7m6 4v1h8V7" />
                                    </svg>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('bataeno.login') }}"
                                class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-bold rounded-xl text-white bg-slate-900 hover:bg-slate-800 transition shadow-sm">
                                Login
                            </a>
                        @endauth
                    </div>

                    <!-- Mobile menu button -->
                    <div class="flex items-center sm:hidden">
                        <button @click="mobileMenuOpen = !mobileMenuOpen"
                            class="text-slate-500 hover:text-slate-600 p-2">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path :class="mobileMenuOpen ? 'hidden' : 'inline-flex'" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="mobileMenuOpen ? 'inline-flex' : 'hidden'" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                class="sm:hidden bg-white border-t border-slate-100 shadow-xl overflow-hidden">
                <div class="pt-2 pb-6 space-y-1">
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="block px-6 py-4 text-base font-semibold text-slate-700 hover:bg-slate-50 border-l-4 border-transparent hover:border-blue-600">Dashboard</a>
                        <div class="px-6 py-4 flex items-center bg-slate-50 mx-4 rounded-xl">
                            <img class="h-8 w-8 rounded-full mr-3"
                                src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->first_name) }}" alt="">
                            <span class="text-sm font-bold text-slate-700">{{ auth()->user()->first_name }}</span>
                        </div>
                    @else
                        <div class="px-6 py-4">
                            <a href="{{ route('login') }}"
                                class="flex items-center justify-center px-6 py-4 border border-transparent text-base font-bold rounded-xl text-white bg-slate-900 hover:bg-slate-800 transition">
                                Login
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white">
                <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-12">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Main Content -->
        <main>
            {{ $slot }}
        </main>

        <footer class="bg-slate-900 text-slate-400 py-16 mt-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 text-center">
                <div class="flex justify-center mb-8">
                    <div class="bg-white/10 p-2 rounded-lg">
                        <svg class="w-8 h-8 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.040A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4">Official Barangay Portal</p>
                <p class="text-[11px] font-semibold tracking-[.2em] uppercase opacity-50">&copy; {{ date('Y') }}
                    Provincial Government of Bataan</p>
            </div>
        </footer>
    </div>

    @livewireScriptConfig
</body>

</html>