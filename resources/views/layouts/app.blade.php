<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'CampusMarket — Dari Mahasiswa, Untuk Mahasiswa' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 antialiased font-sans">
    <div class="min-h-screen flex flex-col justify-between max-w-md mx-auto bg-white shadow-lg relative">

        <!-- Flash Messages (floating overlay: no layout shift) -->
        @if (session('status') || session('error'))
            <div class="absolute inset-x-0 top-0 z-30 p-4 space-y-2 pointer-events-none">
                @if (session('status'))
                    <div class="p-4 bg-teal-50 border-l-4 border-teal-600 rounded-r-lg shadow-md flex items-start gap-3">
                        <svg class="w-5 h-5 text-teal-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-xs text-teal-800 leading-relaxed font-medium">
                            {{ session('status') }}
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-lg shadow-md flex items-start gap-3">
                        <svg class="w-5 h-5 text-rose-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-xs text-rose-800 leading-relaxed font-medium">
                            {{ session('error') }}
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Validation errors are rendered inline next to their form fields. -->

        <!-- Main Content -->
        @php($showNav = auth()->check() && auth()->user()->hasVerifiedEmail())
        <main class="flex-1 flex flex-col {{ $showNav ? 'pb-16' : '' }}">
            @yield('content')
        </main>

        @if ($showNav)
            @include('layouts.nav')
        @endif

    </div>
</body>
</html>
