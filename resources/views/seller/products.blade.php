@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-14">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('seller.dashboard') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Seller Center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="text-center">
            <h1 class="text-base font-bold text-slate-900 leading-tight">Etalase & Inventaris</h1>
            <span class="text-[10px] text-slate-400 font-medium">Kelola Katalog Produk Anda</span>
        </div>
        <div class="flex items-center gap-1.5 -mr-1">
            <a href="{{ route('seller.products.bulk') }}" class="h-9 px-2.5 rounded-full bg-slate-100 hover:bg-teal-50 text-slate-700 hover:text-teal-700 border border-slate-200 text-xs font-bold transition flex items-center gap-1" title="Upload Massal via CSV">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Upload CSV</span>
            </a>
            <a href="{{ route('products.create') }}" class="w-9 h-9 rounded-full bg-teal-600/10 text-teal-700 hover:bg-teal-600 hover:text-white transition flex items-center justify-center" title="Tambah Produk Baru">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Filter Tabs (Horizontal Scrollable) -->
    <div class="px-5 py-3 bg-white border-b border-slate-100 overflow-x-auto no-scrollbar">
        <div class="flex items-center gap-2 text-xs font-semibold shrink-0">
            <a
                href="{{ route('seller.products', ['status' => 'all']) }}"
                class="px-3.5 py-1.5 rounded-full border transition shrink-0 {{ $filter === 'all' ? 'bg-teal-700 text-white border-teal-700 shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}"
            >
                Semua ({{ $totalCount }})
            </a>
            <a
                href="{{ route('seller.products', ['status' => 'active']) }}"
                class="px-3.5 py-1.5 rounded-full border transition shrink-0 {{ $filter === 'active' ? 'bg-teal-700 text-white border-teal-700 shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}"
            >
                Aktif ({{ $activeCount }})
            </a>
            <a
                href="{{ route('seller.products', ['status' => 'sold']) }}"
                class="px-3.5 py-1.5 rounded-full border transition shrink-0 {{ $filter === 'sold' ? 'bg-teal-700 text-white border-teal-700 shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}"
            >
                Terjual ({{ $soldCount }})
            </a>
            <a
                href="{{ route('seller.products', ['status' => 'withdrawn']) }}"
                class="px-3.5 py-1.5 rounded-full border transition shrink-0 {{ $filter === 'withdrawn' ? 'bg-teal-700 text-white border-teal-700 shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}"
            >
                Ditarik Sementara ({{ $withdrawnCount }})
            </a>
        </div>
    </div>

    <div class="p-5 space-y-4">
        <!-- Flash Alert -->
        @if (session('status'))
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('bulk_warnings') && is_array(session('bulk_warnings')))
            <div class="p-3 bg-amber-50 border border-amber-200 text-amber-900 text-xs rounded-xl space-y-1">
                <span class="font-bold flex items-center gap-1.5 text-amber-800">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Catatan Upload Massal (Beberapa baris dilewati):
                </span>
                <ul class="list-disc list-inside text-[11px] text-amber-700 space-y-0.5">
                    @foreach (session('bulk_warnings') as $warn)
                        <li>{{ $warn }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @forelse ($products as $product)
            @php
                $statusBadge = match($product->status) {
                    'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'sold' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'withdrawn' => 'bg-slate-100 text-slate-600 border-slate-200',
                    default => 'bg-slate-100 text-slate-600 border-slate-200',
                };
                $statusLabel = match($product->status) {
                    'active' => 'Aktif di Etalase',
                    'sold' => 'Sudah Terjual',
                    'withdrawn' => 'Ditarik Sementara',
                    default => ucfirst($product->status),
                };
            @endphp

            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs transition hover:shadow-sm">
                <div class="flex items-start gap-3.5">
                    <img
                        src="{{ $product->cover_image_url }}"
                        alt="{{ $product->title }}"
                        class="w-20 h-20 rounded-xl object-cover border border-slate-100 shrink-0 bg-slate-50"
                    >

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $statusBadge }}">
                                {{ $statusLabel }}
                            </span>
                            <span class="text-[10px] text-slate-400">
                                {{ $product->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <h2 class="text-xs font-bold text-slate-900 mt-1 truncate" title="{{ $product->title }}">
                            {{ $product->title }}
                        </h2>

                        <div class="text-xs font-extrabold text-teal-700 font-mono mt-0.5">
                            {{ $product->formatted_price }}
                        </div>

                        <div class="flex items-center gap-2 mt-2 text-[11px] text-slate-500">
                            <!-- Views -->
                            <div class="flex items-center gap-1" title="Jumlah Dilihat">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>{{ $product->views_count ?? 0 }}</span>
                            </div>

                            <span class="text-slate-300">&bull;</span>

                            <!-- Category -->
                            <span class="truncate max-w-[120px]">{{ $product->category?->name ?? 'Kategori' }}</span>

                            @if (($product->offers_count ?? 0) > 0)
                                <span class="text-slate-300">&bull;</span>
                                <a href="{{ route('offers.index') }}" class="flex items-center gap-1 text-amber-700 font-semibold bg-amber-50 px-1.5 py-0.5 rounded" title="Penawaran Masuk">
                                    <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    <span>{{ $product->offers_count }} tawaran</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Product Control Buttons -->
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5">
                        <a
                            href="{{ route('products.show', $product) }}"
                            class="px-2.5 py-1 text-slate-600 hover:text-teal-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg text-xs font-semibold transition flex items-center gap-1"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Lihat</span>
                        </a>

                        @if ($product->status !== 'sold')
                            <a
                                href="{{ route('products.edit', $product) }}"
                                class="px-2.5 py-1 text-slate-600 hover:text-teal-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg text-xs font-semibold transition flex items-center gap-1"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit</span>
                            </a>
                        @endif
                    </div>

                    <div>
                        @if ($product->status === 'active')
                            <form method="POST" action="{{ route('seller.products.status', $product) }}" onsubmit="return confirm('Tarik produk ini sementara dari etalase? Pembeli tidak akan bisa melihat produk ini.');">
                                @csrf
                                <button
                                    type="submit"
                                    class="px-2.5 py-1 text-amber-800 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-lg text-xs font-semibold transition"
                                >
                                    Tarik Sementara
                                </button>
                            </form>
                        @elseif ($product->status === 'withdrawn')
                            <form method="POST" action="{{ route('seller.products.status', $product) }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="px-2.5 py-1 text-teal-800 hover:text-teal-900 bg-teal-50 hover:bg-teal-100 border border-teal-200 rounded-lg text-xs font-semibold transition"
                                >
                                    Aktifkan ke Etalase
                                </button>
                            </form>
                        @else
                            <span class="text-[11px] text-slate-400 font-medium">Transaksi Rampung</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="py-14 text-center bg-white border border-slate-200/90 rounded-2xl p-6">
                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl mb-3">
                    🏷️
                </div>
                <h3 class="text-xs font-bold text-slate-800">Belum ada barang di kategori ini</h3>
                <p class="text-[11px] text-slate-400 mt-1 max-w-xs mx-auto">
                    Mulai jual barang bekas kuliah, perlengkapan kos, atau buku akademik Anda ke sesama mahasiswa.
                </p>
                <a href="{{ route('products.create') }}" class="inline-block mt-4 px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold rounded-xl transition shadow-xs">
                    + Jual Produk Sekarang
                </a>
            </div>
        @endforelse

        <div class="pt-2">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection

