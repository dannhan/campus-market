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

        <!-- Toast notifications: fixed, click-through, and never restored from Turbo's page cache. -->
        <!-- Only flash-level notices spawn a toast; validation errors stay inline next to their fields. -->
        @if (session('status') || session('error'))
            <div class="pointer-events-none fixed inset-x-0 top-16 z-50 flex flex-col items-center gap-2 px-4" data-turbo-temporary>
                @if (session('status'))
                    <x-toast :message="session('status')" />
                @endif

                @if (session('error'))
                    <x-toast type="error" :message="session('error')" />
                @endif
            </div>
        @endif

        <!-- Validation errors are rendered inline next to their form fields. -->

        <!-- Main Content -->
        @php($showNav = auth()->check() && auth()->user()->hasVerifiedEmail())
        @php($hasBottomBar = trim($__env->yieldContent('bottom_bar')) !== '')
        @php($mainBottomPadding = match (true) {
            $showNav && $hasBottomBar => 'pb-40',
            $hasBottomBar => 'pb-28',
            $showNav => 'pb-16',
            default => '',
        })
        <main class="flex-1 flex flex-col {{ $mainBottomPadding }}">
            @yield('content')
        </main>

        @if ($hasBottomBar)
            <!-- Sticky page action bar: sits above the bottom nav, below toasts. -->
            <div class="fixed inset-x-0 {{ $showNav ? 'bottom-16' : 'bottom-0' }} z-30 mx-auto w-full max-w-md border-t border-slate-200 bg-white/95 px-4 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] shadow-[0_-4px_16px_rgba(15,23,42,0.06)] backdrop-blur-md">
                @yield('bottom_bar')
            </div>
        @endif

        @if ($showNav)
            @include('layouts.nav')
        @endif

    </div>
</body>
</html>
