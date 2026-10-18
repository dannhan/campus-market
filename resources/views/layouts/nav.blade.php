@php($route = request()->route()?->getName())
@php($isHome = $route === 'welcome')
@php($isSearch = in_array($route, ['products.index', 'products.show']))
@php($isSell = in_array($route, ['products.create', 'products.edit']))
@php($isProfile = $route === 'profile' || str_starts_with((string) $route, 'profile.') || str_starts_with((string) $route, 'offers.'))

<!-- Bottom Navigation (Sprint 1 visual design; Cari & Jual live since Sprint 2, Chat follows in Sprint 3) -->
<!-- Active tab is color-only on purpose: a bolder active label changes its width and shifts the neighbouring tabs. -->
<nav class="fixed bottom-0 max-w-md w-full bg-white/95 backdrop-blur-md border-t border-slate-200 flex items-center justify-around py-2.5 px-2 z-20 shadow-lg">
    <!-- Beranda -->
    <a href="{{ route('welcome') }}" class="flex flex-col items-center {{ $isHome ? 'text-teal-600' : 'text-slate-400 hover:text-slate-600' }} transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        <span class="text-[10px] mt-0.5">Beranda</span>
    </a>

    <!-- Cari / Etalase Produk (Sprint 2) -->
    <a href="{{ route('products.index') }}" class="flex flex-col items-center {{ $isSearch ? 'text-teal-600' : 'text-slate-400 hover:text-slate-600' }} transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <span class="text-[10px] mt-0.5">Cari</span>
    </a>

    <!-- Jual / Unggah Produk (Sprint 2) -->
    <a href="{{ route('products.create') }}" class="flex flex-col items-center {{ $isSell ? 'text-teal-600' : 'text-slate-400 hover:text-slate-600' }} transition">
        <div class="w-7 h-7 rounded-full {{ $isSell ? 'bg-teal-600 text-white shadow-md shadow-teal-600/30' : 'bg-teal-600/10 text-teal-700' }} flex items-center justify-center transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
        </div>
        <span class="text-[10px] mt-0.5">Jual</span>
    </a>

    <!-- Chat (Sprint 3) -->
    <div class="flex flex-col items-center text-slate-300 cursor-not-allowed" title="Segera Hadir: Sprint 3">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <span class="text-[10px] mt-0.5">Chat</span>
    </div>

    <!-- Saya / Profil -->
    <a href="{{ route('profile') }}" class="flex flex-col items-center {{ $isProfile ? 'text-teal-600' : 'text-slate-400 hover:text-slate-600' }} transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <span class="text-[10px] mt-0.5">Saya</span>
    </a>
</nav>
