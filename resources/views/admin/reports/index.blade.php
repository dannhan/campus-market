@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-12">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('profile') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Profil">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="text-center">
            <div class="text-[10px] font-bold uppercase tracking-wider text-teal-600">Admin Panel</div>
            <h1 class="text-base font-bold text-slate-900 leading-tight">Laporan & Statistik</h1>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="w-9 h-9 -mr-1 rounded-full flex items-center justify-center text-slate-600 hover:bg-slate-100 transition" title="Kelola Kategori">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
        </a>
    </div>

    <!-- Navigation Tabs -->
    <div class="px-5 pt-3 bg-white border-b border-slate-100 flex items-center gap-2 overflow-x-auto">
        <a href="{{ route('admin.categories.index') }}" class="pb-2.5 text-xs font-medium text-slate-400 hover:text-slate-600 transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span>Kategori Produk</span>
        </a>
        <a href="{{ route('admin.reports.index') }}" class="pb-2.5 text-xs font-bold text-teal-700 border-b-2 border-teal-600 flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span>Laporan & Statistik</span>
        </a>
        <a href="{{ route('admin.disputes.index') }}" class="pb-2.5 text-xs font-medium text-slate-400 hover:text-slate-600 transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>Mediasi Sengketa</span>
        </a>
    </div>

    <div class="p-5 space-y-6">
        <!-- 1. Gross Merchandise Value & Transaction Volume Hero Card -->
        <div class="bg-gradient-to-br from-teal-700 to-teal-900 text-white rounded-2xl p-5 shadow-md relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 w-28 h-28 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

            <div class="text-[11px] font-semibold text-teal-200 uppercase tracking-wider flex items-center gap-1.5">
                <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Estimasi Nilai Transaksi & Penawaran (GMV)</span>
            </div>

            <div class="text-2xl font-black mt-1.5 tracking-tight font-mono">
                Rp {{ number_format($totalEstimatedGmv, 0, ',', '.') }}
            </div>

            <div class="mt-4 pt-3 border-t border-teal-600/60 grid grid-cols-2 gap-3 text-xs">
                <div>
                    <div class="text-[10px] text-teal-200">Produk Terjual (Sold)</div>
                    <div class="font-bold text-white mt-0.5">Rp {{ number_format($soldProductsValue, 0, ',', '.') }}</div>
                    <div class="text-[10px] text-teal-300 font-semibold">{{ $soldProducts }} barang</div>
                </div>
                <div>
                    <div class="text-[10px] text-teal-200">Kesepakatan Tawaran (Deals)</div>
                    <div class="font-bold text-white mt-0.5">Rp {{ number_format($acceptedOffersValue, 0, ',', '.') }}</div>
                    <div class="text-[10px] text-teal-300 font-semibold">{{ $acceptedOffersCount }} transaksi</div>
                </div>
            </div>
        </div>

        <!-- 2. Core Metrics KPI Grid -->
        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 px-1">
                Ringkasan Metrik Penggunaan Platform
            </h2>

            <div class="grid grid-cols-2 gap-3">
                <!-- User Growth -->
                <div class="p-3.5 bg-white border border-slate-200/90 rounded-2xl shadow-xs">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-[11px] font-semibold text-slate-500">Pengguna</span>
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ $totalUsers }}</div>
                    <div class="mt-2 text-[10px] text-slate-500 space-y-0.5">
                        <div class="flex justify-between">
                            <span>Mahasiswa Aktif:</span>
                            <span class="font-bold text-emerald-600">{{ $activeStudents }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Alumni:</span>
                            <span class="font-bold text-indigo-600">{{ $alumniUsers }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Email Terverifikasi:</span>
                            <span class="font-bold text-teal-700">{{ $verifiedUsers }}</span>
                        </div>
                    </div>
                </div>

                <!-- Product Catalog Activity -->
                <div class="p-3.5 bg-white border border-slate-200/90 rounded-2xl shadow-xs">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-[11px] font-semibold text-slate-500">Katalog Produk</span>
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ $totalProducts }}</div>
                    <div class="mt-2 text-[10px] text-slate-500 space-y-0.5">
                        <div class="flex justify-between">
                            <span>Aktif di Etalase:</span>
                            <span class="font-bold text-teal-600">{{ $activeProducts }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Terjual:</span>
                            <span class="font-bold text-emerald-600">{{ $soldProducts }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Total Dilihat:</span>
                            <span class="font-bold text-slate-700">{{ number_format($totalViews, 0, ',', '.') }}x</span>
                        </div>
                    </div>
                </div>

                <!-- Tawar-Menawar / Offers -->
                <div class="p-3.5 bg-white border border-slate-200/90 rounded-2xl shadow-xs">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-[11px] font-semibold text-slate-500">Tawar-Menawar</span>
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ $totalOffers }}</div>
                    <div class="mt-2 text-[10px] text-slate-500 space-y-0.5">
                        <div class="flex justify-between">
                            <span>Diterima (Deals):</span>
                            <span class="font-bold text-emerald-600">{{ $acceptedOffersCount }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Menunggu (Pending):</span>
                            <span class="font-bold text-amber-600">{{ $pendingOffersCount }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Ditolak:</span>
                            <span class="font-bold text-rose-600">{{ $rejectedOffersCount }}</span>
                        </div>
                    </div>
                </div>

                <!-- Interaksi & Komunikasi -->
                <div class="p-3.5 bg-white border border-slate-200/90 rounded-2xl shadow-xs">
                    <div class="flex items-center justify-between text-slate-400">
                        <span class="text-[11px] font-semibold text-slate-500">Interaksi Chat & Simpan</span>
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ $totalMessages }} <span class="text-xs font-normal text-slate-400">pesan</span></div>
                    <div class="mt-2 text-[10px] text-slate-500 space-y-0.5">
                        <div class="flex justify-between">
                            <span>Percakapan Aktif:</span>
                            <span class="font-bold text-teal-700">{{ $totalConversations }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Barang di-Wishlist:</span>
                            <span class="font-bold text-rose-600">{{ $totalWishlists }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. University & Category Distributions -->
        <div class="space-y-4">
            <!-- Kampus Teraktif -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Distribusi Mahasiswa per Universitas</span>
                    </h3>
                    <span class="text-[10px] text-slate-400 font-semibold">{{ $universitiesStats->count() }} kampus terdaftar</span>
                </div>

                <div class="space-y-2.5">
                    @forelse ($universitiesStats as $univ)
                        @php
                            $percentage = $totalUsers > 0 ? min(100, round(($univ->students_count / max(1, $totalUsers)) * 100)) : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="font-medium text-slate-800">{{ $univ->name }}</span>
                                <span class="font-bold text-slate-900">{{ $univ->students_count }} mahasiswa ({{ $percentage }}%)</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-teal-600 h-2 rounded-full" style="width: {{ max(4, $percentage) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada data universitas.</p>
                    @endforelse
                </div>
            </div>

            <!-- Kategori Terpopuler -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span>Sebaran Produk per Kategori</span>
                    </h3>
                    <a href="{{ route('admin.categories.index') }}" class="text-[10px] text-teal-600 hover:text-teal-700 font-bold">
                        Kelola Kategori →
                    </a>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    @forelse ($categoriesStats as $cat)
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-700 shrink-0">
                                <x-category-icon :name="$cat->icon" class="w-3.5 h-3.5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="text-[11px] font-bold text-slate-800 truncate">{{ $cat->name }}</div>
                                <div class="text-[10px] text-slate-400 font-semibold">{{ $cat->products_count }} produk</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 col-span-2">Belum ada kategori.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 4. Transaction & Bargaining Log / Report Table -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900">
                        Laporan Riwayat Transaksi & Penawaran
                    </h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Catatan tawar-menawar harga dan kesepakatan antar mahasiswa.</p>
                </div>
            </div>

            <!-- Status Filter Pills -->
            <div class="flex flex-wrap gap-1.5 pt-1">
                @php
                    $filters = [
                        '' => 'Semua (' . $totalOffers . ')',
                        'accepted' => 'Diterima (' . $acceptedOffersCount . ')',
                        'pending' => 'Pending (' . $pendingOffersCount . ')',
                        'countered' => 'Tawar Balik',
                        'rejected' => 'Ditolak (' . $rejectedOffersCount . ')',
                        'withdrawn' => 'Ditarik',
                    ];
                @endphp
                @foreach ($filters as $statusKey => $label)
                    @php
                        $isSelected = ($currentStatus ?? '') === (string) $statusKey;
                    @endphp
                    <a
                        href="{{ route('admin.reports.index', $statusKey ? ['status' => $statusKey] : []) }}"
                        class="px-2.5 py-1 text-[11px] font-semibold rounded-lg transition {{ $isSelected ? 'bg-teal-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <!-- Transaction Items -->
            <div class="space-y-3 pt-2">
                @forelse ($transactions as $offer)
                    @php
                        $badgeClasses = match($offer->status) {
                            'accepted' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'countered' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'rejected' => 'bg-rose-100 text-rose-800 border-rose-200',
                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                        };

                        $statusLabel = match($offer->status) {
                            'accepted' => 'Sepakat / Diterima',
                            'pending' => 'Menunggu Respon',
                            'countered' => 'Ditawar Balik',
                            'rejected' => 'Ditolak',
                            'withdrawn' => 'Dibatalkan',
                            default => ucfirst($offer->status),
                        };
                    @endphp
                    <div class="p-3.5 bg-slate-50/70 border border-slate-200/80 rounded-xl space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="text-[10px] text-slate-400 font-mono">
                                    {{ $offer->created_at->translatedFormat('d M Y, H:i') }}
                                </div>
                                <h4 class="text-xs font-bold text-slate-900 truncate mt-0.5">
                                    {{ $offer->product?->title ?? 'Produk telah dihapus' }}
                                </h4>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $badgeClasses }} shrink-0">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <!-- Parties involved -->
                        <div class="grid grid-cols-2 gap-2 text-[11px] py-1 border-y border-slate-200/60">
                            <div>
                                <span class="text-slate-400 text-[10px] block">Penjual:</span>
                                <span class="font-bold text-slate-800 truncate block">{{ $offer->product?->user?->name ?? 'User' }}</span>
                                <span class="text-[10px] text-teal-700 truncate block">{{ $offer->product?->user?->university?->name ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] block">Penawar:</span>
                                <span class="font-bold text-slate-800 truncate block">{{ $offer->user?->name ?? 'User' }}</span>
                                <span class="text-[10px] text-teal-700 truncate block">{{ $offer->user?->university?->name ?? '-' }}</span>
                            </div>
                        </div>

                        <!-- Pricing Breakdown -->
                        <div class="flex items-center justify-between text-xs pt-0.5">
                            <div class="text-[10px] text-slate-500">
                                Harga Asli: <span class="font-mono text-slate-700">Rp {{ number_format($offer->product?->price ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="font-mono font-bold text-teal-800 text-xs">
                                Tawaran: {{ $offer->formatted_price }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                        <p class="text-xs font-semibold text-slate-500">Tidak ada riwayat transaksi atau penawaran pada filter ini.</p>
                    </div>
                @endforelse

                <!-- Pagination -->
                @if ($transactions->hasPages())
                    <div class="pt-3">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

