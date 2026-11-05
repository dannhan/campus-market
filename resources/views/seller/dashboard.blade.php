@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-14">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('profile') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Profil">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="text-center">
            <h1 class="text-base font-bold text-slate-900 leading-tight">Seller Center</h1>
            <span class="text-[10px] text-teal-700 font-semibold uppercase tracking-wider">Dashboard Penjual Mahasiswa</span>
        </div>
        <a href="{{ route('products.create') }}" class="w-9 h-9 -mr-1 rounded-full bg-teal-600/10 text-teal-700 hover:bg-teal-600 hover:text-white transition flex items-center justify-center" title="Jual Produk Baru">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
        </a>
    </div>

    <div class="p-5 space-y-5">
        <!-- Flash Status Notice -->
        @if (session('status'))
            <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Merchant Profile Card -->
        <div class="bg-gradient-to-r from-teal-800 via-teal-700 to-teal-900 rounded-2xl p-4 text-white shadow-md relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/5 rounded-full pointer-events-none"></div>

            <div class="flex items-start justify-between gap-3 relative z-10">
                <div class="flex items-center gap-3">
                    <img
                        src="{{ $user->avatar_url }}"
                        alt="{{ $user->name }}"
                        class="w-12 h-12 rounded-xl object-cover border-2 border-white/20 shadow-xs"
                    >
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <h2 class="text-sm font-bold text-white truncate">{{ $user->name }}</h2>
                            @if ($user->is_student_verified)
                                <span class="w-4 h-4 rounded-full bg-emerald-400 text-teal-900 inline-flex items-center justify-center text-[10px] font-bold" title="Terverifikasi">✓</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-teal-100 truncate">
                            {{ $user->student?->university?->name ?? 'Mahasiswa' }}
                            @if ($user->student?->major)
                                &bull; {{ $user->student->major }}
                            @endif
                        </p>
                    </div>
                </div>

                <a href="{{ route('users.reviews', $user) }}" class="bg-white/10 hover:bg-white/20 transition px-2.5 py-1 rounded-lg text-right shrink-0">
                    <div class="flex items-center gap-1 justify-end">
                        <svg class="w-3.5 h-3.5 fill-amber-300 text-amber-300" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        <span class="text-xs font-extrabold text-white">{{ $user->seller_rating !== null ? number_format((float) $user->seller_rating, 1) : '-' }}</span>
                    </div>
                    <span class="text-[9px] text-teal-200 block">{{ $user->seller_review_count }} Ulasan</span>
                </a>
            </div>

            <!-- Payment Account Readiness Warning -->
            <div class="mt-3 pt-3 border-t border-white/10 flex items-center justify-between text-xs">
                @if ($hasPaymentSettings)
                    <div class="flex items-center gap-1.5 text-teal-100 text-[11px]">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Rekening Bank dan E-Wallet Siap Menerima Pembayaran</span>
                    </div>
                @else
                    <div class="flex items-center gap-1.5 text-amber-200 text-[11px]">
                        <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Rekening pencairan dana belum diatur</span>
                    </div>
                @endif
                <a href="{{ route('profile.payments.edit') }}" class="text-[11px] font-semibold text-white underline hover:text-teal-200 transition">
                    {{ $hasPaymentSettings ? 'Ubah' : 'Atur Sekarang' }}
                </a>
            </div>
        </div>

        <!-- Financial Summary & Revenue Cards -->
        <div class="grid grid-cols-2 gap-3">
            <!-- Total Revenue -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Total Pendapatan</span>
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="text-base font-extrabold text-slate-900 font-mono">
                    {{ $formattedTotalRevenue }}
                </div>
                <span class="text-[10px] text-slate-400 mt-1 block">Dari {{ $completedOrdersCount }} transaksi selesai</span>
            </div>

            <!-- Escrow Held Balance -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs">
                <div class="flex items-center justify-between text-slate-400 mb-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider">Saldo Rekber Aktif</span>
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div class="text-base font-extrabold text-teal-700 font-mono">
                    {{ $formattedEscrowPendingBalance }}
                </div>
                <span class="text-[10px] text-slate-400 mt-1 block">Aman di penampung kampus</span>
            </div>
        </div>

        <!-- Action Center: Orders Requiring Action Alert -->
        @if ($actionRequiredCount > 0)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 shadow-xs flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 font-bold shadow-xs">
                        {{ $actionRequiredCount }}
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-amber-900">Pesanan Perlu Diproses!</h3>
                        <p class="text-[11px] text-amber-700 mt-0.5">Ada {{ $actionRequiredCount }} pesanan yang menunggu pengiriman atau konfirmasi Anda</p>
                    </div>
                </div>
                <a href="{{ route('seller.orders', ['status' => 'action_required']) }}" class="py-1.5 px-3 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-xs shrink-0">
                    Proses
                </a>
            </div>
        @endif

        <!-- Quick Navigation Grid -->
        <div class="grid grid-cols-3 gap-2 text-center text-xs">
            <a href="{{ route('seller.orders') }}" class="p-3 bg-white border border-slate-200/90 rounded-2xl shadow-xs hover:border-teal-300 transition block">
                <div class="w-8 h-8 mx-auto rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center mb-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <span class="font-bold text-slate-800 block">Pesanan Masuk</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $actionRequiredCount }} perlu diproses</span>
            </a>

            <a href="{{ route('seller.products') }}" class="p-3 bg-white border border-slate-200/90 rounded-2xl shadow-xs hover:border-teal-300 transition block">
                <div class="w-8 h-8 mx-auto rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center mb-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <span class="font-bold text-slate-800 block">Etalase Toko</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $activeProductsCount }} barang aktif</span>
            </a>

            <a href="{{ route('offers.index') }}" class="p-3 bg-white border border-slate-200/90 rounded-2xl shadow-xs hover:border-teal-300 transition block">
                <div class="w-8 h-8 mx-auto rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <span class="font-bold text-slate-800 block">Negosiasi</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $pendingOffersCount }} tawaran baru</span>
            </a>
        </div>

        <!-- Inventory & Listing Analytics Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Performa Etalase Produk</h3>
                <a href="{{ route('seller.products') }}" class="text-[11px] font-semibold text-teal-700 hover:text-teal-800 hover:underline">
                    Lihat Semua
                </a>
            </div>

            <div class="grid grid-cols-3 gap-2 text-center text-xs">
                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Produk Aktif</span>
                    <strong class="text-sm text-slate-800 block mt-0.5">{{ $activeProductsCount }}</strong>
                </div>
                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Total Terjual</span>
                    <strong class="text-sm text-emerald-700 block mt-0.5">{{ $soldProductsCount }}</strong>
                </div>
                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-[10px] text-slate-400 block uppercase font-bold">Penayangan</span>
                    <strong class="text-sm text-slate-800 block mt-0.5 font-mono">{{ $totalProductViews }}</strong>
                </div>
            </div>
        </div>

        <!-- Pending Incoming Offers -->
        @if ($pendingOffers->count() > 0)
            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Penawaran Menunggu Respons</h3>
                    </div>
                    <a href="{{ route('offers.index') }}" class="text-[11px] font-semibold text-teal-700 hover:text-teal-800 hover:underline">
                        Buka Kotak Tawaran
                    </a>
                </div>

                <div class="space-y-2">
                    @foreach ($pendingOffers as $offer)
                        <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80 flex items-center justify-between gap-3 text-xs">
                            <div class="min-w-0 flex-1">
                                <h4 class="font-bold text-slate-800 truncate">{{ $offer->product->title }}</h4>
                                <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-0.5">
                                    <span>Oleh: <strong>{{ $offer->user->name }}</strong></span>
                                    <span>&bull;</span>
                                    <span class="text-amber-700 font-bold font-mono">Tawar Rp {{ number_format($offer->offered_price, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <a href="{{ route('offers.index') }}" class="py-1 px-2.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-[11px] font-bold rounded-lg transition shrink-0 shadow-2xs">
                                Respons
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Recent Orders Feed -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Pesanan Terbaru</h3>
                <a href="{{ route('seller.orders') }}" class="text-[11px] font-semibold text-teal-700 hover:text-teal-800 hover:underline">
                    Semua Pesanan
                </a>
            </div>

            @if ($recentOrders->count() > 0)
                <div class="space-y-2.5">
                    @foreach ($recentOrders as $order)
                        <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80 space-y-2 text-xs">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <img
                                        src="{{ $order->product->cover_image_url }}"
                                        alt="{{ $order->product->title }}"
                                        class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0"
                                    >
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono font-bold text-slate-700 text-[10px]">{{ $order->transaction_code }}</span>
                                        </div>
                                        <h4 class="font-bold text-slate-900 truncate">{{ $order->product->title }}</h4>
                                        <span class="text-[10px] text-slate-400 block">Pembeli: {{ $order->buyer->name }} ({{ $order->buyer->student?->university?->name ?? 'Mahasiswa' }})</span>
                                    </div>
                                </div>

                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border shrink-0 {{ $order->status === 'completed' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : ($order->status === 'cancelled' ? 'bg-rose-50 text-rose-800 border-rose-200' : 'bg-amber-50 text-amber-800 border-amber-200') }}">
                                    {{ $order->status_label }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between pt-1 border-t border-slate-200/60 text-[11px]">
                                <span class="font-mono font-bold text-slate-700">{{ $order->formatted_total_amount }}</span>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('chats.index') }}" class="text-teal-700 font-semibold hover:underline">
                                        Chat
                                    </a>
                                    <span class="text-slate-300">|</span>
                                    <a href="{{ route('transactions.show', $order) }}" class="text-teal-700 font-bold hover:underline">
                                        Detail Pesanan &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-6 text-center text-slate-400 text-xs">
                    <p>Belum ada transaksi penjualan yang masuk.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
