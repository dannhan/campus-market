@php($route = request()->route()?->getName())
@php($isHome = $route === 'welcome')
@php($isSearch = in_array($route, ['products.index', 'products.show']))
@php($isSell = in_array($route, ['products.create', 'products.edit']))
@php($isChat = $route === 'chats.index' || str_starts_with((string) $route, 'chats.'))
@php($isProfile = $route === 'profile' || str_starts_with((string) $route, 'profile.') || str_starts_with((string) $route, 'offers.') || str_starts_with((string) $route, 'wishlist.'))

<!-- Bottom Navigation (Sprint 1 visual design; all five destinations are live, including Chat since SRS-016) -->
<!-- Active tab is color-only on purpose: a bolder active label changes its width and shifts the neighbouring tabs. -->
<nav aria-label="Navigasi utama" class="fixed bottom-0 max-w-md w-full h-16 bg-white/95 backdrop-blur-md border-t border-slate-200 flex items-center justify-around px-2 z-20 shadow-lg">
    <!-- Beranda -->
    <a href="{{ route('welcome') }}"
       @if ($isHome) aria-current="page" @endif
       class="flex flex-1 flex-col items-center justify-center min-h-11 min-w-11 px-1 {{ $isHome ? 'text-teal-600' : 'text-slate-400 hover:text-slate-600' }} transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        <span class="text-[10px] mt-0.5">Beranda</span>
    </a>

    <!-- Jelajahi / Etalase Produk (Sprint 2) -->
    <a href="{{ route('products.index') }}"
       @if ($isSearch) aria-current="page" @endif
       class="flex flex-1 flex-col items-center justify-center min-h-11 min-w-11 px-1 {{ $isSearch ? 'text-teal-600' : 'text-slate-400 hover:text-slate-600' }} transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <span class="text-[10px] mt-0.5">Jelajahi</span>
    </a>

    <!-- Jual / Unggah Produk (Sprint 2) -->
    <a href="{{ route('products.create') }}"
       @if ($isSell) aria-current="page" @endif
       class="flex flex-1 flex-col items-center justify-center min-h-11 min-w-11 px-1 {{ $isSell ? 'text-teal-600' : 'text-slate-400 hover:text-slate-600' }} transition">
        <div class="w-7 h-7 rounded-full {{ $isSell ? 'bg-teal-600 text-white shadow-md shadow-teal-600/30' : 'bg-teal-600/10 text-teal-700' }} flex items-center justify-center transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
        </div>
        <span class="text-[10px] mt-0.5">Jual</span>
    </a>

    <!-- Chat (Sprint 3 / SRS-016) -->
    <a href="{{ route('chats.index') }}"
       @if ($isChat) aria-current="page" @endif
       class="relative flex flex-1 flex-col items-center justify-center min-h-11 min-w-11 px-1 {{ $isChat ? 'text-teal-600' : 'text-slate-400 hover:text-slate-600' }} transition">
        @php($unreadNavCount = auth()->user()?->unreadMessagesCount() ?? 0)
        @if ($unreadNavCount > 0)
            <span class="absolute -top-0.5 right-1 w-2.5 h-2.5 bg-teal-600 rounded-full ring-2 ring-white"></span>
        @endif
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <span class="text-[10px] mt-0.5">Chat</span>
    </a>

    <!-- Saya / Profil -->
    <a href="{{ route('profile') }}"
       @if ($isProfile) aria-current="page" @endif
       class="flex flex-1 flex-col items-center justify-center min-h-11 min-w-11 px-1 {{ $isProfile ? 'text-teal-600' : 'text-slate-400 hover:text-slate-600' }} transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <span class="text-[10px] mt-0.5">Saya</span>
    </a>
</nav>
