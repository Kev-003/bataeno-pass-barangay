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
    @livewireStyles

    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
    @livewireScripts
    @auth
        @livewire('notifications')
    @endauth

    <div x-data="{ mobileMenuOpen: false }" class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <livewire:layout.navigation />

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

</body>

</html>