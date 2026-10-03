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

        <!-- Top Alerts / Flash Messages -->
        @if (session('status'))
            <div class="p-4 m-4 mb-0 bg-teal-50 border-l-4 border-teal-600 rounded-r-lg shadow-xs flex items-start gap-3">
                <svg class="w-5 h-5 text-teal-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-xs text-teal-800 leading-relaxed font-medium">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 m-4 mb-0 bg-rose-50 border-l-4 border-rose-500 rounded-r-lg shadow-xs flex items-start gap-3">
                <svg class="w-5 h-5 text-rose-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-xs text-rose-800 leading-relaxed font-medium">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 m-4 mb-0 bg-rose-50 border-l-4 border-rose-500 rounded-r-lg shadow-xs">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="text-xs font-semibold text-rose-800">Mohon periksa kesalahan berikut:</span>
                </div>
                <ul class="list-disc list-inside text-xs text-rose-700 space-y-0.5 ml-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Content -->
        <main class="flex-1 flex flex-col">
            @yield('content')
        </main>

    </div>
</body>
</html>
