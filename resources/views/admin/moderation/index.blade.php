@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-10">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('profile') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Profil">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="text-center">
            <div class="text-[10px] font-bold uppercase tracking-wider text-teal-600">Admin Panel</div>
            <h1 class="text-base font-bold text-slate-900 leading-tight">Moderasi &amp; Verifikasi Produk</h1>
        </div>
        <div class="w-9"></div>
    </div>

    <!-- Navigation Tabs -->
    <div class="px-5 pt-3 bg-white border-b border-slate-100 flex items-center gap-2 overflow-x-auto">
        <a href="{{ route('admin.categories.index') }}" class="pb-2.5 text-xs font-medium text-slate-400 hover:text-slate-600 transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span>Kategori</span>
        </a>
        <a href="{{ route('admin.reports.index') }}" class="pb-2.5 text-xs font-medium text-slate-400 hover:text-slate-600 transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span>Laporan</span>
        </a>
        <a href="{{ route('admin.disputes.index') }}" class="pb-2.5 text-xs font-medium text-slate-400 hover:text-slate-600 transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Mediasi</span>
        </a>
        <a href="{{ route('admin.escrows.index') }}" class="pb-2.5 text-xs font-medium text-slate-400 hover:text-slate-600 transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span>Rekber</span>
        </a>
        <a href="{{ route('admin.rewards.index') }}" class="pb-2.5 text-xs font-medium text-slate-400 hover:text-slate-600 transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Reward</span>
        </a>
        <a href="{{ route('admin.moderation.index') }}" class="pb-2.5 text-xs font-bold text-teal-700 border-b-2 border-teal-600 flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span>Moderasi Produk</span>
            @if ($metrics['flagged'] > 0 || $metrics['reports'] > 0)
                <span class="px-1.5 py-0.2 bg-rose-500 text-white rounded-full text-[10px] font-bold">{{ $metrics['flagged'] + $metrics['reports'] }}</span>
            @endif
        </a>
    </div>

    <div class="p-5 space-y-5">
        <!-- Flash Alerts -->
        @if (session('status'))
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Summary Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-gradient-to-br from-teal-700 to-teal-900 text-white rounded-2xl p-4 shadow-xs">
                <span class="text-[10px] uppercase font-bold tracking-wider text-teal-200 block">Total Produk</span>
                <div class="text-xl font-extrabold font-mono mt-1">{{ number_format($metrics['total']) }}</div>
                <span class="text-[10px] text-teal-200 mt-0.5 block">Di seluruh kampus</span>
            </div>

            <div class="bg-white border border-rose-200 rounded-2xl p-4 shadow-xs">
                <span class="text-[10px] uppercase font-bold tracking-wider text-rose-600 block">Dilaporkan / Bermasalah</span>
                <div class="text-xl font-extrabold font-mono text-rose-700 mt-1">{{ number_format($metrics['flagged']) }}</div>
                <span class="text-[10px] text-rose-500 mt-0.5 block">{{ $metrics['reports'] }} aduan mahasiswa</span>
            </div>

            <div class="bg-white border border-emerald-200 rounded-2xl p-4 shadow-xs">
                <span class="text-[10px] uppercase font-bold tracking-wider text-emerald-600 block">Terverifikasi Kampus</span>
                <div class="text-xl font-extrabold font-mono text-emerald-700 mt-1">{{ number_format($metrics['verified']) }}</div>
                <span class="text-[10px] text-emerald-600 mt-0.5 block">Badge resmi aktif</span>
            </div>

            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs">
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Ditolak / Dicabut</span>
                <div class="text-xl font-extrabold font-mono text-slate-700 mt-1">{{ number_format($metrics['rejected']) }}</div>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Dicegah dari etalase</span>
            </div>
        </div>

        <!-- Filter Status Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto text-xs pb-1">
            <a href="{{ route('admin.moderation.index', array_merge(request()->query(), ['status' => 'all'])) }}" class="px-3 py-1.5 rounded-full font-bold shrink-0 {{ $statusFilter === 'all' ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua Produk ({{ $metrics['total'] }})
            </a>
            <a href="{{ route('admin.moderation.index', array_merge(request()->query(), ['status' => 'flagged'])) }}" class="px-3 py-1.5 rounded-full font-bold shrink-0 {{ $statusFilter === 'flagged' ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                🚨 Dilaporkan ({{ $metrics['flagged'] }})
            </a>
            <a href="{{ route('admin.moderation.index', array_merge(request()->query(), ['status' => 'verified'])) }}" class="px-3 py-1.5 rounded-full font-bold shrink-0 {{ $statusFilter === 'verified' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                ✓ Terverifikasi ({{ $metrics['verified'] }})
            </a>
            <a href="{{ route('admin.moderation.index', array_merge(request()->query(), ['status' => 'rejected'])) }}" class="px-3 py-1.5 rounded-full font-bold shrink-0 {{ $statusFilter === 'rejected' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Ditolak ({{ $metrics['rejected'] }})
            </a>
            <a href="{{ route('admin.moderation.index', array_merge(request()->query(), ['status' => 'approved'])) }}" class="px-3 py-1.5 rounded-full font-bold shrink-0 {{ $statusFilter === 'approved' ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Disetujui
            </a>
        </div>

        <!-- Search Bar -->
        <form action="{{ route('admin.moderation.index') }}" method="GET" class="flex gap-2">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            <input type="text" name="q" value="{{ $search }}" placeholder="Cari judul produk, penjual, atau deskripsi..." class="flex-1 px-3.5 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-teal-500 focus:border-teal-500">
            <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl transition">
                Cari
            </button>
            @if ($search)
                <a href="{{ route('admin.moderation.index', ['status' => $statusFilter]) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl transition">
                    Reset
                </a>
            @endif
        </form>

        <!-- Product Moderation List -->
        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
            <div class="px-4 py-3 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Daftar Produk untuk Moderasi</span>
                <span class="text-[11px] text-slate-400 font-medium">{{ $products->total() }} produk</span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($products as $p)
                    <div class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-slate-50/70 transition">
                        <div class="flex items-start gap-3.5 min-w-0 flex-1">
                            <!-- Thumbnail -->
                            <img src="{{ $p->cover_image_url }}" alt="{{ $p->title }}" class="w-16 h-16 rounded-xl object-cover shrink-0 border border-slate-200 bg-slate-100">

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ $p->category?->name ?? 'Kategori' }}
                                    </span>

                                    @if ($p->is_verified)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                            <span>✓</span>
                                            <span>Terverifikasi</span>
                                        </span>
                                    @endif

                                    @if ($p->isFlagged())
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 flex items-center gap-1">
                                            <span>🚨</span>
                                            <span>Dilaporkan ({{ $p->pending_reports_count ?? 1 }})</span>
                                        </span>
                                    @elseif ($p->isRejected())
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700">
                                            Tayang
                                        </span>
                                    @endif
                                </div>

                                <a href="{{ route('admin.moderation.show', $p) }}" class="font-bold text-sm text-slate-900 hover:text-teal-700 transition mt-1 block truncate">
                                    {{ $p->title }}
                                </a>

                                <div class="mt-1 flex items-center gap-3 text-xs text-slate-500 flex-wrap">
                                    <span class="font-mono font-bold text-teal-800">{{ $p->formatted_price }}</span>
                                    <span>•</span>
                                    <span>Penjual: <strong class="text-slate-700">{{ $p->user?->name }}</strong></span>
                                    @if ($p->user?->student?->university)
                                        <span>•</span>
                                        <span>{{ $p->user->student->university->name }}</span>
                                    @endif
                                </div>

                                @if ($p->moderation_notes)
                                    <div class="mt-1.5 text-[11px] text-rose-700 bg-rose-50/80 px-2 py-1 rounded-lg border border-rose-100">
                                        Catatan Moderasi: {{ $p->moderation_notes }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Moderation Action Buttons -->
                        <div class="flex items-center gap-2 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                            <a href="{{ route('admin.moderation.show', $p) }}" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                                Tinjau
                            </a>

                            @if (! $p->is_verified)
                                <form action="{{ route('admin.moderation.verify', $p) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-2xs" title="Beri badge resmi kampus">
                                        ✓ Verifikasi
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.moderation.unverify', $p) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1.5 text-xs font-semibold rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 transition" title="Cabut verifikasi">
                                        Cabut Badge
                                    </button>
                                </form>
                            @endif

                            @if (! $p->isRejected())
                                <button type="button" onclick="document.getElementById('reject-form-{{ $p->id }}').classList.toggle('hidden');" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition">
                                    Tolak
                                </button>
                            @else
                                <form action="{{ route('admin.moderation.approve', $p) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold rounded-xl bg-teal-600 hover:bg-teal-700 text-white transition shadow-2xs">
                                        Setujui Kembali
                                    </button>
                                </form>
                            @endif
                        </div>

                        <!-- Inline Reject Reason Form (Hidden by default) -->
                        <div id="reject-form-{{ $p->id }}" class="hidden w-full pt-3 border-t border-slate-100">
                            <form action="{{ route('admin.moderation.reject', $p) }}" method="POST" class="flex gap-2">
                                @csrf
                                <input type="text" name="reason" placeholder="Tuliskan alasan penolakan/pelanggaran produk..." required class="flex-1 px-3 py-1.5 text-xs border border-rose-300 rounded-xl focus:ring-1 focus:ring-rose-500">
                                <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-2xs">
                                    Konfirmasi Tolak
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-500">
                        Tidak ada produk yang memerlukan tindakan moderasi untuk filter ini.
                    </div>
                @endforelse
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
