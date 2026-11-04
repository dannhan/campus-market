@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-12" x-data="{ tab: 'purchases' }">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('profile') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Profil">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-base font-bold text-slate-900 leading-tight">Riwayat Transaksi & Pembayaran</h1>
        <div class="w-9"></div>
    </div>

    <!-- Segmented Tabs -->
    <div class="px-5 pt-3 pb-2 bg-white border-b border-slate-100">
        <div class="grid grid-cols-2 bg-slate-100 p-1 rounded-xl text-xs font-bold text-slate-600">
            <button
                type="button"
                @click="tab = 'purchases'"
                :class="tab === 'purchases' ? 'bg-white text-teal-700 shadow-xs' : 'hover:text-slate-900'"
                class="py-2 rounded-lg transition text-center"
            >
                Pembelian Saya ({{ $purchases->total() }})
            </button>
            <button
                type="button"
                @click="tab = 'sales'"
                :class="tab === 'sales' ? 'bg-white text-teal-700 shadow-xs' : 'hover:text-slate-900'"
                class="py-2 rounded-lg transition text-center"
            >
                Penjualan Masuk ({{ $sales->total() }})
            </button>
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

        <!-- Filter bar -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <a
                href="{{ route('transactions.index') }}"
                class="px-3 py-1.5 rounded-full border transition shrink-0 {{ empty($methodFilter) ? 'bg-teal-600 text-white border-teal-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300' }}"
            >
                Semua Metode
            </a>
            <a
                href="{{ route('transactions.index', ['method' => 'cod']) }}"
                class="px-3 py-1.5 rounded-full border transition shrink-0 {{ $methodFilter === 'cod' ? 'bg-teal-600 text-white border-teal-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300' }}"
            >
                💵 Tunai (COD)
            </a>
            <a
                href="{{ route('transactions.index', ['method' => 'bank_transfer']) }}"
                class="px-3 py-1.5 rounded-full border transition shrink-0 {{ $methodFilter === 'bank_transfer' ? 'bg-teal-600 text-white border-teal-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300' }}"
            >
                🏦 Transfer Bank
            </a>
            <a
                href="{{ route('transactions.index', ['method' => 'e_wallet']) }}"
                class="px-3 py-1.5 rounded-full border transition shrink-0 {{ $methodFilter === 'e_wallet' ? 'bg-teal-600 text-white border-teal-600 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300' }}"
            >
                📱 E-Wallet
            </a>
        </div>

        <!-- 1. Purchases Tab -->
        <div x-show="tab === 'purchases'" class="space-y-3">
            @forelse ($purchases as $trx)
                <a href="{{ route('transactions.show', $trx) }}" class="block bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs hover:border-teal-300 transition">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 text-[11px]">
                        <span class="font-mono text-slate-400 font-medium">{{ $trx->transaction_code }}</span>
                        @php
                            $badge = match($trx->status) {
                                'pending' => 'bg-amber-100 text-amber-800',
                                'processing' => 'bg-blue-100 text-blue-800',
                                'completed' => 'bg-emerald-100 text-emerald-800',
                                'cancelled' => 'bg-slate-100 text-slate-500',
                                default => 'bg-slate-100 text-slate-700',
                            };
                        @endphp
                        <span class="font-bold px-2.5 py-0.5 rounded-full {{ $badge }}">
                            {{ $trx->status_label }}
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <img src="{{ $trx->product->cover_image_url }}" alt="{{ $trx->product->title }}" class="w-12 h-12 rounded-xl object-cover border border-slate-100 shrink-0">
                        <div class="min-w-0 flex-1">
                            <h2 class="text-xs font-bold text-slate-900 truncate">{{ $trx->product->title }}</h2>
                            <p class="text-[11px] text-slate-500 truncate">Penjual: {{ $trx->seller->name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs font-extrabold text-teal-700 font-mono">{{ $trx->formatted_amount }}</span>
                                <span class="text-[10px] text-slate-400">·</span>
                                <span class="text-[10px] text-slate-500 font-medium">
                                    {{ $trx->payment_method_label }}
                                </span>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
            @empty
                <div class="py-12 text-center bg-white border border-slate-200/90 rounded-2xl p-6">
                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl mb-3">
                        🛍️
                    </div>
                    <p class="text-xs font-bold text-slate-700">Belum ada transaksi pembelian.</p>
                    <p class="text-[11px] text-slate-400 mt-1">Jelajahi etalase produk kampus untuk menemukan kebutuhan akademik Anda.</p>
                    <a href="{{ route('products.index') }}" class="inline-block mt-3 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl transition">
                        Jelajahi Etalase
                    </a>
                </div>
            @endforelse
            {{ $purchases->links() }}
        </div>

        <!-- 2. Sales Tab -->
        <div x-show="tab === 'sales'" x-cloak class="space-y-3">
            @forelse ($sales as $trx)
                <a href="{{ route('transactions.show', $trx) }}" class="block bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs hover:border-teal-300 transition">
                    <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100 text-[11px]">
                        <span class="font-mono text-slate-400 font-medium">{{ $trx->transaction_code }}</span>
                        @php
                            $badge = match($trx->status) {
                                'pending' => 'bg-amber-100 text-amber-800',
                                'processing' => 'bg-blue-100 text-blue-800',
                                'completed' => 'bg-emerald-100 text-emerald-800',
                                'cancelled' => 'bg-slate-100 text-slate-500',
                                default => 'bg-slate-100 text-slate-700',
                            };
                        @endphp
                        <span class="font-bold px-2.5 py-0.5 rounded-full {{ $badge }}">
                            {{ $trx->status_label }}
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <img src="{{ $trx->product->cover_image_url }}" alt="{{ $trx->product->title }}" class="w-12 h-12 rounded-xl object-cover border border-slate-100 shrink-0">
                        <div class="min-w-0 flex-1">
                            <h2 class="text-xs font-bold text-slate-900 truncate">{{ $trx->product->title }}</h2>
                            <p class="text-[11px] text-slate-500 truncate">Pembeli: {{ $trx->buyer->name }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs font-extrabold text-teal-700 font-mono">{{ $trx->formatted_amount }}</span>
                                <span class="text-[10px] text-slate-400">·</span>
                                <span class="text-[10px] text-slate-500 font-medium">
                                    {{ $trx->payment_method_label }}
                                </span>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
            @empty
                <div class="py-12 text-center bg-white border border-slate-200/90 rounded-2xl p-6">
                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl mb-3">
                        📦
                    </div>
                    <p class="text-xs font-bold text-slate-700">Belum ada pesanan penjualan masuk.</p>
                    <p class="text-[11px] text-slate-400 mt-1">Unggah produk kebutuhan mahasiswa yang ingin Anda jual.</p>
                    <a href="{{ route('products.create') }}" class="inline-block mt-3 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl transition">
                        Unggah Produk
                    </a>
                </div>
            @endforelse
            {{ $sales->links() }}
        </div>
    </div>
</div>
@endsection

