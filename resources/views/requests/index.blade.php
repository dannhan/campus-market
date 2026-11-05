@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-14">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('welcome') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Beranda">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="text-center">
            <h1 class="text-base font-bold text-slate-900 leading-tight">Papan Cari Barang</h1>
            <span class="text-[10px] text-teal-700 font-semibold uppercase tracking-wider">Wanted Board Mahasiswa</span>
        </div>
        <a href="{{ route('requests.create') }}" class="w-9 h-9 -mr-1 rounded-full bg-teal-600/10 text-teal-700 hover:bg-teal-600 hover:text-white transition flex items-center justify-center" title="Pasang Permintaan Barang">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
        </a>
    </div>

    <!-- Hero / Explainer Banner -->
    <div class="px-5 pt-4 pb-2">
        <div class="bg-gradient-to-r from-teal-800 via-teal-700 to-teal-900 rounded-2xl p-4 text-white shadow-md relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 w-28 h-28 bg-white/10 rounded-full pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-teal-200">Beli Cepat & Tepat</span>
                    <span class="text-[10px] bg-white/20 px-2 py-0.5 rounded-full font-bold">{{ $openCount }} Permintaan Aktif</span>
                </div>
                <h2 class="text-sm font-bold text-white leading-snug">
                    Belum nemu barang di etalase? Pasang di Papan Cari Barang!
                </h2>
                <p class="text-[11px] text-teal-100 mt-1 leading-relaxed">
                    Tulis barang yang Anda butuhkan. Mahasiswa yang punya barang akan langsung menawarkan kepada Anda.
                </p>
                <div class="mt-3">
                    <a href="{{ route('requests.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-400 hover:bg-amber-300 text-amber-950 text-xs font-black rounded-xl transition shadow-xs">
                        <span>+ Pasang Permintaan Baru</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="px-5 py-2">
        <form method="GET" action="{{ route('requests.index') }}" class="relative">
            @if (request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            @if (request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            @if (request('scope'))
                <input type="hidden" name="scope" value="{{ request('scope') }}">
            @endif
            <input
                type="text"
                name="q"
                value="{{ $searchQuery }}"
                placeholder="Cari barang yang sedang dicari mahasiswa..."
                class="w-full pl-10 pr-24 py-2.5 bg-white border border-slate-200 rounded-2xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 shadow-xs"
            >
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="absolute inset-y-1.5 right-1.5 px-3 bg-teal-700 hover:bg-teal-800 text-white rounded-xl text-xs font-bold transition">
                Cari
            </button>
        </form>
    </div>

    <!-- Scope / Status Tabs -->
    <div class="px-5 py-2 bg-white border-b border-slate-100 overflow-x-auto no-scrollbar">
        <div class="flex items-center gap-2 text-xs font-semibold shrink-0">
            <a
                href="{{ route('requests.index', array_filter(['q' => $searchQuery, 'category' => $currentCategory, 'status' => 'open'])) }}"
                class="px-3.5 py-1.5 rounded-full border transition shrink-0 {{ $statusFilter === 'open' && $scope !== 'my' ? 'bg-teal-700 text-white border-teal-700 shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}"
            >
                Sedang Dicari ({{ $openCount }})
            </a>
            <a
                href="{{ route('requests.index', array_filter(['q' => $searchQuery, 'category' => $currentCategory, 'status' => 'fulfilled'])) }}"
                class="px-3.5 py-1.5 rounded-full border transition shrink-0 {{ $statusFilter === 'fulfilled' && $scope !== 'my' ? 'bg-teal-700 text-white border-teal-700 shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}"
            >
                Sudah Ditemukan ({{ $fulfilledCount }})
            </a>
            @auth
                <a
                    href="{{ route('requests.index', ['scope' => 'my']) }}"
                    class="px-3.5 py-1.5 rounded-full border transition shrink-0 flex items-center gap-1.5 {{ $scope === 'my' ? 'bg-teal-700 text-white border-teal-700 shadow-xs' : 'bg-teal-50 text-teal-800 border-teal-200 hover:bg-teal-100' }}"
                >
                    <span>Permintaan Saya</span>
                    @if ($myRequestsCount > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $scope === 'my' ? 'bg-white text-teal-800 font-extrabold' : 'bg-teal-700 text-white font-extrabold' }}">
                            {{ $myRequestsCount }}
                        </span>
                    @endif
                </a>
            @endauth
        </div>
    </div>

    <!-- Category Filter Pills -->
    <div class="px-5 py-2.5 bg-slate-50 border-b border-slate-100 overflow-x-auto no-scrollbar">
        <div class="flex items-center gap-1.5 text-xs shrink-0">
            <a
                href="{{ route('requests.index', array_filter(['q' => $searchQuery, 'status' => $statusFilter, 'scope' => $scope])) }}"
                class="px-3 py-1 rounded-full text-xs font-semibold transition shrink-0 {{ empty($currentCategory) ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-slate-300' }}"
            >
                Semua Kategori
            </a>
            @foreach ($categories as $cat)
                <a
                    href="{{ route('requests.index', array_filter(['q' => $searchQuery, 'category' => $cat->slug, 'status' => $statusFilter, 'scope' => $scope])) }}"
                    class="px-3 py-1 rounded-full text-xs font-semibold transition shrink-0 {{ $currentCategory === $cat->slug ? 'bg-teal-700 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-slate-300' }}"
                >
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Request Listings -->
    <div class="p-5 space-y-4">
        <!-- Flash Notice -->
        @if (session('status'))
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @forelse ($requests as $req)
            @php
                $urgencyBadge = match($req->urgency) {
                    'urgent' => 'bg-rose-50 text-rose-700 border-rose-200',
                    'low' => 'bg-slate-100 text-slate-600 border-slate-200',
                    default => 'bg-amber-50 text-amber-700 border-amber-200',
                };
                $statusBadge = match($req->status) {
                    'fulfilled' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'closed' => 'bg-slate-100 text-slate-600 border-slate-200',
                    default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                };
            @endphp

            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs hover:border-teal-300 hover:shadow-sm transition">
                <!-- Card Header: Badges & Time -->
                <div class="flex items-center justify-between gap-2 pb-2.5 mb-2.5 border-b border-slate-100 text-[11px]">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="font-bold px-2 py-0.5 rounded-full border {{ $statusBadge }}">
                            {{ $req->status_label }}
                        </span>
                        @if ($req->urgency === 'urgent')
                            <span class="font-extrabold px-2 py-0.5 rounded-full border {{ $urgencyBadge }} flex items-center gap-1">
                                <span>⚡</span>
                                <span>Mendesak</span>
                            </span>
                        @endif
                        <span class="text-slate-400">&bull;</span>
                        <span class="text-slate-500 font-medium">{{ $req->category?->name ?? 'Kategori' }}</span>
                    </div>
                    <span class="text-slate-400 shrink-0">{{ $req->created_at->diffForHumans() }}</span>
                </div>

                <!-- Title & Description -->
                <a href="{{ route('requests.show', $req) }}" class="block group">
                    <h2 class="text-sm font-bold text-slate-900 group-hover:text-teal-700 transition leading-snug">
                        {{ $req->title }}
                    </h2>
                    <p class="text-xs text-slate-600 line-clamp-2 mt-1 leading-relaxed">
                        {{ $req->description }}
                    </p>
                </a>

                <!-- Meta Specs: Budget & Condition -->
                <div class="grid grid-cols-2 gap-2 mt-3 p-2.5 bg-slate-50/80 rounded-xl border border-slate-100 text-xs">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 block uppercase">Budget Pembeli:</span>
                        <span class="font-black text-slate-900 font-mono text-xs block mt-0.5">{{ $req->formatted_budget }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 block uppercase">Preferensi Kondisi:</span>
                        <span class="font-semibold text-slate-700 text-xs block mt-0.5">{{ $req->condition_label }}</span>
                    </div>
                </div>

                <!-- Footer: Requester info & CTA -->
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <img
                            src="{{ $req->user->avatar_url }}"
                            alt="{{ $req->user->name }}"
                            class="w-6 h-6 rounded-full object-cover shrink-0"
                        >
                        <div class="min-w-0 truncate">
                            <span class="font-bold text-slate-800 text-[11px] truncate block">{{ $req->user->name }}</span>
                            <span class="text-[10px] text-slate-400 truncate block">{{ $req->university?->name ?? 'Mahasiswa' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @if ($req->offers_count > 0)
                            <span class="text-[10px] font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded-full border border-teal-200">
                                {{ $req->offers_count }} tawaran
                            </span>
                        @endif

                        <a
                            href="{{ route('requests.show', $req) }}"
                            class="px-3 py-1.5 bg-teal-700 hover:bg-teal-800 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center gap-1"
                        >
                            <span>{{ $req->user_id === auth()->id() ? 'Kelola' : 'Tawarkan' }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-14 text-center bg-white border border-slate-200/90 rounded-2xl p-6">
                <div class="w-12 h-12 mx-auto rounded-full bg-teal-50 text-teal-600 flex items-center justify-center text-xl mb-3">
                    🔍
                </div>
                <h3 class="text-xs font-bold text-slate-800">Belum ada barang yang dicari</h3>
                <p class="text-[11px] text-slate-400 mt-1 max-w-xs mx-auto">
                    Sedang butuh buku, modul, alat lab, atau barang kos? Jadilah yang pertama memasang permintaan barang!
                </p>
                <a href="{{ route('requests.create') }}" class="inline-block mt-4 px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold rounded-xl transition shadow-xs">
                    + Pasang Permintaan Barang
                </a>
            </div>
        @endforelse

        <div class="pt-2">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection
