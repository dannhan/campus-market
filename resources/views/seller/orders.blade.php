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
            <h1 class="text-base font-bold text-slate-900 leading-tight">Kelola Pesanan Masuk</h1>
            <span class="text-[10px] text-slate-400 font-medium">Manajemen Transaksi Penjual</span>
        </div>
        <div class="w-9"></div>
    </div>

    <!-- Filter Tabs (Horizontal Scrollable) -->
    <div class="px-5 py-3 bg-white border-b border-slate-100 overflow-x-auto no-scrollbar">
        <div class="flex items-center gap-2 text-xs font-semibold shrink-0">
            <a
                href="{{ route('seller.orders', ['status' => 'all']) }}"
                class="px-3.5 py-1.5 rounded-full border transition shrink-0 {{ $statusFilter === 'all' ? 'bg-teal-700 text-white border-teal-700 shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}"
            >
                Semua ({{ $totalCount }})
            </a>
            <a
                href="{{ route('seller.orders', ['status' => 'action_required']) }}"
                class="px-3.5 py-1.5 rounded-full border transition shrink-0 flex items-center gap-1.5 {{ $statusFilter === 'action_required' ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100' }}"
            >
                <span>Perlu Diproses</span>
                @if ($actionRequiredCount > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $statusFilter === 'action_required' ? 'bg-white text-amber-700 font-extrabold' : 'bg-amber-600 text-white font-extrabold' }}">
                        {{ $actionRequiredCount }}
                    </span>
                @endif
            </a>
            <a
                href="{{ route('seller.orders', ['status' => 'processing']) }}"
                class="px-3.5 py-1.5 rounded-full border transition shrink-0 {{ $statusFilter === 'processing' ? 'bg-teal-700 text-white border-teal-700 shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}"
            >
                Sedang Diproses
            </a>
            <a
                href="{{ route('seller.orders', ['status' => 'completed']) }}"
                class="px-3.5 py-1.5 rounded-full border transition shrink-0 {{ $statusFilter === 'completed' ? 'bg-teal-700 text-white border-teal-700 shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}"
            >
                Selesai ({{ $completedCount }})
            </a>
            <a
                href="{{ route('seller.orders', ['status' => 'cancelled']) }}"
                class="px-3.5 py-1.5 rounded-full border transition shrink-0 {{ $statusFilter === 'cancelled' ? 'bg-teal-700 text-white border-teal-700 shadow-xs' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}"
            >
                Dibatalkan
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

        @forelse ($orders as $order)
            @php
                $isActionRequired = $order->status === \App\Models\Transaction::STATUS_PROCESSING
                    || ($order->status === \App\Models\Transaction::STATUS_PENDING && $order->payment_status === \App\Models\Transaction::PAYMENT_STATUS_PAID);

                $statusBadgeClass = match($order->status) {
                    'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    'cancelled' => 'bg-slate-100 text-slate-500 border-slate-200',
                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                };
            @endphp

            <div class="bg-white border {{ $isActionRequired ? 'border-amber-300 ring-2 ring-amber-100' : 'border-slate-200/90' }} rounded-2xl p-4 shadow-xs transition hover:shadow-sm">
                <!-- Order Card Header -->
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-bold text-slate-800">{{ $order->transaction_code }}</span>
                        <span class="text-slate-300">&bull;</span>
                        <span class="text-slate-400 text-[11px]">{{ $order->created_at->diffForHumans() }}</span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        @if ($order->escrowPayment)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 border border-teal-200">
                                Rekber Dijamin
                            </span>
                        @endif
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $statusBadgeClass }}">
                            {{ $order->status_label }}
                        </span>
                    </div>
                </div>

                <!-- Buyer Info Snippet -->
                <div class="flex items-center gap-2.5 mb-3 bg-slate-50/70 p-2.5 rounded-xl border border-slate-100">
                    <img
                        src="{{ $order->buyer->avatar_url }}"
                        alt="{{ $order->buyer->name }}"
                        class="w-7 h-7 rounded-full object-cover shrink-0"
                    >
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-slate-800 truncate">
                            Pembeli: {{ $order->buyer->name }}
                        </p>
                        <p class="text-[10px] text-slate-400 truncate">
                            {{ $order->buyer->student?->university?->name ?? 'Mahasiswa' }}
                        </p>
                    </div>
                    <a
                        href="{{ route('chats.index') }}"
                        class="p-1.5 bg-white text-teal-700 hover:bg-teal-50 border border-slate-200 rounded-lg text-[11px] font-semibold flex items-center gap-1 transition shrink-0"
                        title="Pusat Pesan & Chat"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Chat</span>
                    </a>
                </div>

                <!-- Product Preview -->
                <div class="flex items-center gap-3">
                    <img
                        src="{{ $order->product->cover_image_url }}"
                        alt="{{ $order->product->title }}"
                        class="w-14 h-14 rounded-xl object-cover border border-slate-100 shrink-0"
                    >
                    <div class="min-w-0 flex-1">
                        <h2 class="text-xs font-bold text-slate-900 truncate">{{ $order->product->title }}</h2>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="text-xs font-extrabold text-teal-700 font-mono">{{ $order->formatted_total_amount }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-1.5 text-[10px] text-slate-500">
                            <span class="px-1.5 py-0.5 rounded bg-slate-100 font-medium">
                                {{ $order->delivery_method_label }}
                            </span>
                            <span class="px-1.5 py-0.5 rounded bg-slate-100 font-medium">
                                {{ $order->payment_method_label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Required Indicator -->
                @if ($isActionRequired)
                    <div class="mt-3 p-2.5 bg-amber-50/80 border border-amber-200 rounded-xl flex items-center justify-between text-[11px] text-amber-900">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span class="font-semibold">
                                @if ($order->delivery_method === 'courier' && empty($order->tracking_number))
                                    Menunggu Anda mengirim barang & memasukkan nomor resi
                                @elseif ($order->delivery_method === 'pickup_point' || $order->delivery_method === 'cod')
                                    Menunggu Anda bertemu pembeli & serah terima barang
                                @else
                                    Perlu diproses oleh Anda
                                @endif
                            </span>
                        </div>
                    </div>
                @endif

                <!-- Footer Action Buttons -->
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <div class="text-[11px] text-slate-400">
                        Status: <span class="font-semibold text-slate-600">{{ $order->timeline['status_title'] ?? $order->status_label }}</span>
                    </div>

                    <a
                        href="{{ route('transactions.show', $order) }}"
                        class="px-3.5 py-1.5 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center gap-1"
                    >
                        <span>Kelola Pesanan</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="py-14 text-center bg-white border border-slate-200/90 rounded-2xl p-6">
                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl mb-3">
                    📦
                </div>
                <h3 class="text-xs font-bold text-slate-800">Tidak ada pesanan pada filter ini</h3>
                <p class="text-[11px] text-slate-400 mt-1 max-w-xs mx-auto">
                    @if ($statusFilter === 'action_required')
                        Hebat! Semua pesanan masuk telah Anda proses.
                    @else
                        Belum ada riwayat pesanan yang sesuai dengan kriteria yang dipilih.
                    @endif
                </p>
                @if ($statusFilter !== 'all')
                    <a href="{{ route('seller.orders', ['status' => 'all']) }}" class="inline-block mt-3 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                        Tampilkan Semua Pesanan
                    </a>
                @endif
            </div>
        @endforelse

        <div class="pt-2">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
