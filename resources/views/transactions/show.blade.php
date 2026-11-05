@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-12" x-data="{
    copied: false,
    copiedResi: false,
    copyText(text) {
        navigator.clipboard.writeText(text);
        this.copied = true;
        setTimeout(() => this.copied = false, 2500);
    },
    copyResi(text) {
        navigator.clipboard.writeText(text);
        this.copiedResi = true;
        setTimeout(() => this.copiedResi = false, 2500);
    }
}">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('transactions.index') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Daftar Transaksi">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="text-center">
            <div class="text-[10px] font-mono text-slate-400">{{ $transaction->transaction_code }}</div>
            <h1 class="text-base font-bold text-slate-900 leading-tight">Detail Transaksi</h1>
        </div>
        <div class="w-9"></div>
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
        @if (session('error'))
            <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Visual Transaction Status Timeline -->
        @include('transactions._timeline', ['transaction' => $transaction])

        <!-- Quick Summary Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-2 text-xs">
            <div class="flex items-center justify-between text-slate-500">
                <span>Metode Pengiriman:</span>
                <span class="font-bold text-slate-800 flex items-center gap-1">
                    @if ($transaction->delivery_method === 'cod')
                        <span>🤝 COD / Ketemu Langsung</span>
                    @elseif ($transaction->delivery_method === 'pickup')
                        <span>🏫 Pickup di Kampus</span>
                    @else
                        <span>🚚 Kurir ({{ $transaction->courier_name_label }})</span>
                    @endif
                </span>
            </div>

            <div class="flex items-center justify-between text-slate-500 pt-1.5 border-t border-slate-100">
                <span>Metode Pembayaran:</span>
                <span class="font-bold text-slate-800 flex items-center gap-1">
                    @if ($transaction->payment_method === 'cod')
                        <span>💵 Bayar Tunai (COD)</span>
                    @elseif ($transaction->payment_method === 'bank_transfer')
                        <span>🏦 Transfer Bank ({{ $transaction->payment_channel_label }})</span>
                    @else
                        <span>📱 E-Wallet ({{ $transaction->payment_channel_label }})</span>
                    @endif
                </span>
            </div>

            <div class="flex items-center justify-between text-slate-500 pt-1.5 border-t border-slate-100">
                <span>Waktu Pemesanan:</span>
                <span class="text-slate-700 font-mono text-[11px]">{{ $transaction->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
            </div>
        </div>

        <!-- Delivery Details Section -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">
                        @if ($transaction->delivery_method === 'cod') 🤝 @elseif ($transaction->delivery_method === 'pickup') 🏫 @else 🚚 @endif
                    </span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-950">
                        Informasi Pengiriman & Penyerahan
                    </h3>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                    {{ $transaction->delivery_method_label }}
                </span>
            </div>

            @if ($transaction->delivery_method === 'cod')
                <!-- COD Meeting Spot Details -->
                <div class="p-3 bg-emerald-50/70 border border-emerald-200 rounded-xl space-y-1.5 text-xs">
                    <span class="text-[10px] font-bold text-emerald-800 uppercase block">Lokasi Janji Temu COD di Kampus:</span>
                    <p class="font-bold text-slate-900 text-xs">{{ $transaction->meeting_location ?? 'Area kampus yang disepakati bersama' }}</p>
                    <p class="text-[11px] text-emerald-700 leading-relaxed pt-1">
                        Silakan hubungi lawan transaksi via chat untuk menyepakati hari dan jam temu serah terima barang.
                    </p>
                </div>

            @elseif ($transaction->delivery_method === 'pickup')
                <!-- Campus Pickup Details -->
                <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl space-y-1.5 text-xs">
                    <span class="text-[10px] font-bold text-amber-800 uppercase block">Titik Pengambilan di Kampus (Pickup):</span>
                    <p class="font-bold text-slate-900 text-xs">{{ $transaction->pickup_location ?? 'Titik kampus yang ditentukan penjual' }}</p>
                    <p class="text-[11px] text-amber-800 leading-relaxed pt-1">
                        Pembeli dapat mengambil barang secara mandiri di lokasi kampus yang tertera di atas sesuai jam operasional atau kesepakatan bersama.
                    </p>
                </div>

            @else
                <!-- Courier Delivery Details -->
                <div class="space-y-3">
                    @if ($transaction->originUniversity || $transaction->destinationUniversity)
                        <!-- Campus Shipping Route Card -->
                        <div class="p-3 bg-gradient-to-r from-blue-50/80 to-indigo-50/80 border border-blue-200/90 rounded-xl space-y-1 text-xs">
                            <span class="text-[10px] font-bold text-blue-900 uppercase block">Rute Pengiriman Antar-Kampus:</span>
                            <div class="font-bold text-slate-900 flex items-center gap-1.5 flex-wrap">
                                <span>{{ $transaction->originUniversity?->name ?? 'Kampus Penjual' }}</span>
                                <span class="text-blue-600 font-extrabold">→</span>
                                <span>{{ $transaction->destinationUniversity?->name ?? 'Kampus Pembeli' }}</span>
                            </div>
                            <div class="flex items-center gap-3 pt-1 text-[11px] text-slate-600">
                                @if ($transaction->distance_km !== null)
                                    <span>📍 Jarak: <strong class="font-mono text-slate-800">{{ $transaction->distance_km <= 0.05 ? '< 1 km (Satu Kampus)' : number_format($transaction->distance_km, 1, ',', '.') . ' km' }}</strong></span>
                                @endif
                                @if ($transaction->estimated_delivery_days)
                                    <span>⏱️ Estimasi Tiba: <strong class="text-teal-700">{{ $transaction->estimated_delivery_days }}</strong></span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[10px] font-semibold text-slate-400 block uppercase">Kurir Ekspedisi</span>
                            <span class="font-bold text-slate-800 mt-0.5 block">{{ $transaction->courier_name_label }}</span>
                        </div>
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[10px] font-semibold text-slate-400 block uppercase">Ongkos Kirim</span>
                            <span class="font-bold font-mono text-slate-800 mt-0.5 block">{{ $transaction->formatted_shipping_cost }}</span>
                        </div>
                    </div>

                    <!-- Tracking Number Display -->
                    <div class="p-3 bg-blue-50/80 border border-blue-200 rounded-xl space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-blue-900 uppercase">Nomor Resi Pengiriman:</span>
                            @if ($transaction->shipped_at)
                                <span class="text-[10px] text-blue-700 font-mono">Dikirim: {{ $transaction->shipped_at->translatedFormat('d M, H:i') }} WIB</span>
                            @endif
                        </div>

                        @if ($transaction->tracking_number)
                            <div class="flex items-center justify-between bg-white p-2.5 rounded-lg border border-blue-200">
                                <span class="font-mono text-sm font-extrabold text-blue-900 tracking-wider">
                                    {{ $transaction->tracking_number }}
                                </span>
                                <button
                                    type="button"
                                    @click="copyResi('{{ $transaction->tracking_number }}')"
                                    class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-800 text-[11px] font-semibold rounded-md border border-blue-200 transition flex items-center gap-1"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                    <span x-text="copiedResi ? 'Tersalin!' : 'Salin Resi'"></span>
                                </button>
                            </div>
                        @else
                            <p class="text-xs text-blue-800">
                                Penjual sedang menyiapkan barang. Nomor resi pengiriman akan tertera di sini setelah paket diserahkan ke kurir.
                            </p>
                        @endif
                    </div>

                    <!-- Shipping Address Details -->
                    <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl space-y-1 text-xs">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Alamat Tujuan Pengiriman:</span>
                        <div class="font-bold text-slate-800">{{ $transaction->shipping_recipient_name ?? $transaction->buyer->name }} ({{ $transaction->shipping_phone ?? '-' }})</div>
                        <p class="text-slate-600 text-[11px] leading-relaxed whitespace-pre-line mt-0.5">{{ $transaction->shipping_address ?? 'Alamat kos/tempat tinggal pembeli' }}</p>
                    </div>

                    <!-- Seller Resi Input / Update Form -->
                    @if ($isSeller && $transaction->status !== 'completed' && $transaction->status !== 'cancelled')
                        <form method="POST" action="{{ route('transactions.ship', $transaction) }}" class="p-3 bg-indigo-50/60 border border-indigo-200 rounded-xl space-y-2.5">
                            @csrf
                            <div class="flex items-center gap-1.5 text-xs font-bold text-indigo-950">
                                <svg class="w-4 h-4 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Perbarui Resi Pengiriman (Sebagai Penjual)</span>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-600 mb-1">Kurir</label>
                                    <select name="courier_name" class="w-full px-2.5 py-1.5 text-xs bg-white border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        @foreach (\App\Models\Transaction::COURIERS as $ckey => $cname)
                                            <option value="{{ $ckey }}" @selected($transaction->courier_name === $ckey)>{{ $cname }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-600 mb-1">Nomor Resi <span class="text-rose-500">*</span></label>
                                    <input
                                        type="text"
                                        name="tracking_number"
                                        placeholder="Nomor resi paket"
                                        value="{{ old('tracking_number', $transaction->tracking_number) }}"
                                        required
                                        class="w-full px-2.5 py-1.5 text-xs bg-white border border-slate-200 rounded-lg font-mono focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                    >
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition"
                            >
                                Simpan Nomor Resi Pengiriman
                            </button>
                        </form>
                    @endif
                </div>
            @endif

            <!-- Delivered Confirmation Status -->
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Penerimaan Barang:</span>
                @if ($transaction->delivered_at)
                    <span class="text-emerald-700 font-semibold flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Diterima ({{ $transaction->delivered_at->translatedFormat('d M, H:i') }} WIB)</span>
                    </span>
                @else
                    <span class="text-amber-700 font-semibold">Dalam Proses Pengiriman / Penyerahan</span>
                @endif
            </div>

            @if (! $transaction->delivered_at && $transaction->status !== 'cancelled' && $transaction->status !== 'pending')
                <!-- Mark Delivered Action -->
                <form method="POST" action="{{ route('transactions.delivered', $transaction) }}">
                    @csrf
                    <button
                        type="submit"
                        onclick="return confirm('Konfirmasi bahwa barang sudah diterima dengan baik?')"
                        class="w-full py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-semibold rounded-xl transition flex items-center justify-center gap-1.5"
                    >
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Konfirmasi Barang Sudah Diterima</span>
                    </button>
                </form>
            @endif
        </div>

        <!-- Payment Details Section -->
        @if ($transaction->payment_method === 'cod')
            <div class="bg-emerald-50/70 border border-emerald-200 rounded-2xl p-4 shadow-xs space-y-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">💵</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-900">Petunjuk Pembayaran Tunai (COD)</h3>
                </div>

                <div class="text-xs text-emerald-950 space-y-2 pt-1 leading-relaxed">
                    <div class="p-2.5 bg-white/80 rounded-xl border border-emerald-200">
                        <span class="text-[10px] font-bold text-emerald-700 uppercase block">Total Bayar Tunai Pas:</span>
                        <div class="text-base font-extrabold text-teal-700 font-mono">{{ $transaction->formatted_total_amount }}</div>
                    </div>

                    <div class="text-[11px] text-emerald-800 flex items-start gap-1.5 pt-1">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Tips Mahasiswa: Selalu periksa kondisi fisik barang secara teliti sebelum menyerahkan uang tunai kepada penjual.</span>
                    </div>
                </div>
            </div>
        @else
            <!-- Bank Transfer / E-Wallet Destination Account -->
            @php
                $escrow = $transaction->escrowPayment;
                $details = $transaction->payment_details ?? [];

                if ($escrow) {
                    $accNumber = $escrow->holding_account_number;
                    $accName = $escrow->holding_account_name;
                    $channelName = strtoupper($escrow->holding_channel).' (Rekber Resmi CampusMarket)';
                } else {
                    $accNumber = $transaction->payment_method === 'bank_transfer'
                        ? ($details['bank_account_number'] ?? $transaction->seller->bank_account_number ?? '-')
                        : ($details['ewallet_account_number'] ?? $transaction->seller->ewallet_account_number ?? '-');
                    $accName = $transaction->payment_method === 'bank_transfer'
                        ? ($details['bank_account_name'] ?? $transaction->seller->bank_account_name ?? $transaction->seller->name)
                        : ($details['ewallet_account_name'] ?? $transaction->seller->ewallet_account_name ?? $transaction->seller->name);
                    $channelName = $transaction->payment_channel_label;
                }
            @endphp

            <div class="bg-white border-2 border-teal-500/40 rounded-2xl p-4 shadow-sm space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-teal-600 text-white flex items-center justify-center text-xs font-bold">
                            {{ $transaction->payment_method === 'bank_transfer' ? '🏦' : '📱' }}
                        </span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-teal-900">
                            Tujuan Pembayaran {{ $transaction->payment_method_label }}
                        </h3>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 border border-teal-200">
                        {{ $channelName }}
                    </span>
                </div>

                @if ($escrow)
                    <!-- Rekber Escrow Status Card -->
                    <div class="p-3 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-blue-900 uppercase flex items-center gap-1">
                                <span>🛡️ Proteksi Rekber CampusMarket</span>
                            </span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $escrow->status_badge_class }}">
                                {{ $escrow->status_label }}
                            </span>
                        </div>
                        <div class="text-[11px] text-blue-950 leading-relaxed">
                            @if ($escrow->status === \App\Models\EscrowPayment::STATUS_PENDING_DEPOSIT)
                                Silakan transfer dana ke rekening penampung resmi CampusMarket di bawah ini. Dana Anda akan diamankan di sistem sebelum penjual mengirimkan barang.
                            @elseif ($escrow->status === \App\Models\EscrowPayment::STATUS_HELD)
                                Dana sebesar <strong class="font-mono text-teal-800">{{ $escrow->formatted_total_amount }}</strong> telah diamankan di Rekber CampusMarket. Dana baru akan diteruskan ke penjual setelah pembeli menerima barang dan mengonfirmasi.
                            @elseif ($escrow->status === \App\Models\EscrowPayment::STATUS_RELEASED)
                                Dana rekber telah resmi dicairkan ke rekening penjual pada {{ $escrow->released_at?->translatedFormat('d M Y, H:i') }} WIB.
                            @elseif ($escrow->status === \App\Models\EscrowPayment::STATUS_REFUNDED)
                                Dana rekber telah dikembalikan kepada pembeli pada {{ $escrow->refunded_at?->translatedFormat('d M Y, H:i') }} WIB.
                            @elseif ($escrow->status === \App\Models\EscrowPayment::STATUS_DISPUTED)
                                Dana rekber sedang dibekukan sementara karena adanya sengketa transaksi yang sedang dimediasi admin.
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Account Box -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-semibold text-slate-400 block uppercase">
                                {{ $escrow ? 'Rekening Penampung Resmi Rekber' : ($transaction->payment_method === 'bank_transfer' ? 'Nomor Rekening' : 'Nomor Akun / HP E-Wallet') }}
                            </span>
                            <span class="text-base font-extrabold font-mono text-slate-900 tracking-wider">
                                {{ $accNumber }}
                            </span>
                        </div>
                        @if ($accNumber !== '-')
                            <button
                                type="button"
                                @click="copyText('{{ $accNumber }}')"
                                class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-100 text-teal-700 text-xs font-semibold rounded-lg shadow-2xs transition flex items-center gap-1"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                            </button>
                        @endif
                    </div>

                    <div class="pt-1.5 border-t border-slate-200/60 flex items-center justify-between text-xs">
                        <span class="text-slate-500">Atas Nama:</span>
                        <strong class="text-slate-800">{{ $accName }}</strong>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Total Pembayaran:</span>
                        <strong class="text-teal-700 font-mono text-sm">{{ $transaction->formatted_total_amount }}</strong>
                    </div>
                </div>

                <!-- Payment Proof Status / Upload -->
                @if ($transaction->payment_proof)
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-emerald-800 uppercase flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Bukti Pembayaran Diunggah
                            </span>
                            @if ($transaction->paid_at)
                                <span class="text-[10px] text-emerald-700 font-mono">{{ $transaction->paid_at->translatedFormat('d M Y, H:i') }} WIB</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ $transaction->payment_proof_url }}" target="_blank" class="block w-16 h-16 rounded-lg overflow-hidden border border-emerald-300 shrink-0 hover:opacity-90 transition">
                                <img src="{{ $transaction->payment_proof_url }}" alt="Bukti Transfer" class="w-full h-full object-cover">
                            </a>
                            <div class="text-[11px] text-emerald-900 leading-relaxed flex-1">
                                <p class="font-medium">Struk pembayaran tersimpan di sistem.</p>
                                <a href="{{ $transaction->payment_proof_url }}" target="_blank" class="text-teal-700 font-bold underline mt-1 inline-block">Lihat Foto Struk Penuh</a>
                            </div>
                        </div>
                    </div>
                @elseif ($isBuyer && $transaction->status !== 'cancelled')
                    <!-- Buyer Upload Proof Form -->
                    <form method="POST" action="{{ route('transactions.proof', $transaction) }}" enctype="multipart/form-data" class="pt-2 space-y-2.5">
                        @csrf
                        <div class="text-xs font-semibold text-slate-800 flex items-center gap-1">
                            <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Unggah Bukti Struk Transfer:</span>
                        </div>
                        <input
                            type="file"
                            name="payment_proof"
                            id="payment_proof"
                            accept="image/*"
                            required
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer"
                        >
                        @error('payment_proof')
                            <p class="text-[11px] text-rose-500">{{ $message }}</p>
                        @enderror
                        <button
                            type="submit"
                            class="w-full py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-xs transition"
                        >
                            Kirim Bukti Pembayaran
                        </button>
                    </form>
                @endif
            </div>
        @endif

        <!-- Product & Price Breakdown Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">Barang Transaksi & Tagihan</h3>

            <div class="flex items-center gap-3">
                <img src="{{ $transaction->product->cover_image_url }}" alt="{{ $transaction->product->title }}" class="w-14 h-14 rounded-xl object-cover border border-slate-100 shrink-0">
                <div class="min-w-0 flex-1">
                    <a href="{{ route('products.show', $transaction->product) }}" class="text-xs font-bold text-slate-900 hover:text-teal-700 block truncate">
                        {{ $transaction->product->title }}
                    </a>
                    <span class="text-[10px] text-slate-500 block truncate">{{ $transaction->product->category->name ?? 'Kategori' }}</span>
                    <span class="text-xs font-bold text-slate-700 mt-1 block font-mono">
                        {{ $transaction->formatted_amount }}
                    </span>
                </div>
            </div>

            <!-- Detailed Breakdown -->
            <div class="pt-3 border-t border-slate-100 space-y-1.5 text-xs text-slate-600">
                <div class="flex justify-between">
                    <span>Subtotal Produk:</span>
                    <span class="font-mono">{{ $transaction->formatted_amount }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Biaya Pengiriman ({{ $transaction->delivery_method_label }}):</span>
                    <span class="font-mono {{ $transaction->shipping_cost > 0 ? 'text-slate-900' : 'text-emerald-600' }}">
                        {{ $transaction->shipping_cost > 0 ? $transaction->formatted_shipping_cost : 'Rp 0 (Gratis)' }}
                    </span>
                </div>
                <div class="flex justify-between pt-1.5 border-t border-slate-100 font-bold text-slate-900 text-sm">
                    <span>Total Tagihan:</span>
                    <span class="font-mono text-teal-700 font-extrabold text-base">{{ $transaction->formatted_total_amount }}</span>
                </div>
            </div>

            @if ($transaction->buyer_notes)
                <div class="p-2.5 bg-slate-50 rounded-xl text-xs text-slate-600 leading-relaxed border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Catatan Pembeli:</span>
                    "{{ $transaction->buyer_notes }}"
                </div>
            @endif
        </div>

        @php
            $currentUser = auth()->user();
            $myReview = $transaction->getReviewBy($currentUser);
            $counterpartyReview = $transaction->getReviewFor($currentUser);
            $counterparty = $isBuyer ? $transaction->seller : $transaction->buyer;
            $counterpartyRole = $isBuyer ? 'Penjual' : 'Pembeli';
        @endphp

        <!-- Rating & Review Section (Active on Completed Transactions) -->
        @if ($transaction->status === \App\Models\Transaction::STATUS_COMPLETED)
            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-4" id="rating-review-section">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4 fill-amber-400 stroke-amber-500" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-slate-900">Rating & Ulasan</h3>
                            <p class="text-[10px] text-slate-500">Penilaian dua arah untuk transparansi & reputasi mahasiswa</p>
                        </div>
                    </div>

                    @if ($counterparty)
                        <a href="{{ route('users.reviews', $counterparty) }}" class="text-[11px] font-semibold text-teal-700 hover:text-teal-800 hover:underline inline-flex items-center gap-1">
                            <span>Profil {{ $counterpartyRole }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endif
                </div>

                @if (session('success'))
                    <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- 1. My Review Form or Display --}}
                <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-200/80 space-y-3">
                    @if ($myReview)
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded-full uppercase tracking-wider inline-block">Ulasan Anda</span>
                                <h4 class="text-xs font-semibold text-slate-800 mt-1">Ulasan untuk {{ $counterpartyRole }}: {{ $counterparty?->name }}</h4>
                            </div>
                            <div class="flex items-center gap-1 bg-white px-2 py-1 rounded-lg border border-slate-200 text-xs font-bold text-slate-800 shadow-2xs">
                                <svg class="w-4 h-4 fill-amber-400 stroke-amber-400" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                <span>{{ $myReview->rating }}/5</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            @for ($s = 1; $s <= 5; $s++)
                                <svg class="w-4 h-4 {{ $s <= $myReview->rating ? 'fill-amber-400 text-amber-400' : 'fill-slate-200 text-slate-200' }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            @endfor
                            <span class="text-[11px] text-slate-500 ml-1.5">({{ $myReview->rating_label }})</span>
                        </div>

                        @if ($myReview->tags && count($myReview->tags) > 0)
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($myReview->tags as $tag)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold bg-white border border-slate-200 text-teal-800 px-2 py-0.5 rounded-full shadow-2xs">
                                        <svg class="w-3 h-3 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        {{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if ($myReview->comment)
                            <div class="bg-white p-2.5 rounded-lg border border-slate-200/80 text-xs text-slate-700 italic">
                                "{{ $myReview->comment }}"
                            </div>
                        @endif

                        <div class="text-[10px] text-slate-400">
                            Dikirim pada {{ $myReview->created_at->translatedFormat('d M Y, H:i') }}
                        </div>
                    @elseif ($transaction->canBeReviewedBy($currentUser))
                        <div x-data="{
                            rating: 5,
                            hoveredRating: 0,
                            selectedTags: [],
                            labels: {
                                1: 'Sangat Kecewa',
                                2: 'Kurang Puas',
                                3: 'Cukup',
                                4: 'Puas',
                                5: 'Sangat Puas!'
                            },
                            toggleTag(tag) {
                                if (this.selectedTags.includes(tag)) {
                                    this.selectedTags = this.selectedTags.filter(t => t !== tag);
                                } else {
                                    if (this.selectedTags.length < 5) {
                                        this.selectedTags.push(tag);
                                    }
                                }
                            }
                        }">
                            <form action="{{ route('transactions.reviews.store', $transaction) }}" method="POST" class="space-y-3">
                                @csrf

                                <div>
                                    <h4 class="text-xs font-bold text-slate-900">Beri Ulasan untuk {{ $counterpartyRole }}</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Bagikan pengalaman bertransaksi dengan <strong>{{ $counterparty?->name }}</strong></p>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Rating Bintang *</label>
                                    <input type="hidden" name="rating" :value="rating">
                                    <div class="flex items-center gap-1.5">
                                        <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                            <button
                                                type="button"
                                                @click="rating = star"
                                                @mouseenter="hoveredRating = star"
                                                @mouseleave="hoveredRating = 0"
                                                class="p-1 focus:outline-none transition transform hover:scale-110 active:scale-95"
                                            >
                                                <svg
                                                    class="w-7 h-7 cursor-pointer transition-colors"
                                                    :class="(hoveredRating ? star <= hoveredRating : star <= rating) ? 'fill-amber-400 text-amber-400 drop-shadow-xs' : 'fill-slate-200 text-slate-300'"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                                </svg>
                                            </button>
                                        </template>
                                        <span class="text-xs font-bold text-amber-600 ml-2" x-text="labels[hoveredRating || rating]"></span>
                                    </div>
                                </div>

                                @php
                                    $availableTags = $isBuyer ? \App\Models\Review::SELLER_TAGS : \App\Models\Review::BUYER_TAGS;
                                @endphp
                                <div>
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Tag Pengalaman (Opsional)</label>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($availableTags as $tag)
                                            <label
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium cursor-pointer transition border select-none"
                                                :class="selectedTags.includes('{{ $tag }}') ? 'bg-teal-600 text-white border-teal-600 shadow-2xs' : 'bg-white text-slate-700 border-slate-200 hover:border-teal-400'"
                                            >
                                                <input
                                                    type="checkbox"
                                                    name="tags[]"
                                                    value="{{ $tag }}"
                                                    class="sr-only"
                                                    @change="toggleTag('{{ $tag }}')"
                                                >
                                                <span x-text="selectedTags.includes('{{ $tag }}') ? '✓' : '+'" class="text-[10px]"></span>
                                                <span>{{ $tag }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div>
                                    <label for="review-comment" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Ulasan Tertulis (Opsional)</label>
                                    <textarea
                                        id="review-comment"
                                        name="comment"
                                        rows="2"
                                        maxlength="1000"
                                        placeholder="Tulis ulasanmu secara jujur dan membangun untuk membantu mahasiswa lainnya..."
                                        class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition resize-none"
                                    ></textarea>
                                </div>

                                <button
                                    type="submit"
                                    class="w-full py-2.5 bg-teal-600 hover:bg-teal-700 active:bg-teal-800 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-xs"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                    <span>Kirim Ulasan & Rating</span>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="text-xs text-slate-500 italic">
                            Anda tidak memiliki akses untuk memberikan ulasan pada transaksi ini.
                        </div>
                    @endif
                </div>

                {{-- 2. Counterparty's Review Display --}}
                <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-200/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Ulasan Balik dari {{ $counterpartyRole }}</span>
                        @if ($counterpartyReview)
                            <span class="text-[10px] text-slate-400">{{ $counterpartyReview->created_at->diffForHumans() }}</span>
                        @endif
                    </div>

                    @if ($counterpartyReview)
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-0.5">
                                @for ($s = 1; $s <= 5; $s++)
                                    <svg class="w-3.5 h-3.5 {{ $s <= $counterpartyReview->rating ? 'fill-amber-400 text-amber-400' : 'fill-slate-200 text-slate-200' }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                @endfor
                            </div>
                            <span class="text-xs font-bold text-slate-800">{{ $counterpartyReview->rating }}/5</span>
                            <span class="text-[11px] text-slate-500">({{ $counterpartyReview->rating_label }})</span>
                        </div>

                        @if ($counterpartyReview->tags && count($counterpartyReview->tags) > 0)
                            <div class="flex flex-wrap gap-1">
                                @foreach ($counterpartyReview->tags as $tag)
                                    <span class="text-[10px] font-medium bg-white border border-slate-200 text-slate-700 px-2 py-0.5 rounded-full">
                                        {{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if ($counterpartyReview->comment)
                            <p class="text-xs text-slate-600 bg-white p-2.5 rounded-lg border border-slate-200/80 italic">
                                "{{ $counterpartyReview->comment }}"
                            </p>
                        @endif
                    @else
                        <p class="text-xs text-slate-400 italic">
                            {{ $counterparty?->name ?? $counterpartyRole }} belum menuliskan ulasan balik untuk transaksi ini.
                        </p>
                    @endif
                </div>
            </div>
        @endif

        <!-- Counterparties Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">Pihak Transaksi</h3>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <!-- Pembeli -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Pembeli</span>
                    <strong class="text-slate-900 block mt-0.5 truncate">{{ $transaction->buyer->name }}</strong>
                    <span class="text-[10px] text-slate-500 block truncate">{{ $transaction->buyer->university?->name ?? 'Mahasiswa' }}</span>
                </div>

                <!-- Penjual -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Penjual</span>
                    <strong class="text-slate-900 block mt-0.5 truncate">{{ $transaction->seller->name }}</strong>
                    <span class="text-[10px] text-slate-500 block truncate">{{ $transaction->seller->university?->name ?? 'Mahasiswa' }}</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-2 pt-2">
            <!-- 1. Buyer Release Escrow Button -->
            @if ($isBuyer && $escrow && $escrow->status === \App\Models\EscrowPayment::STATUS_HELD)
                <form method="POST" action="{{ route('transactions.release-escrow', $transaction) }}">
                    @csrf
                    <button
                        type="submit"
                        onclick="return confirm('Apakah Anda telah menerima barang dengan baik dan menyetujui pencairan dana rekber ke rekening penjual?')"
                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Konfirmasi Barang Sesuai & Lepas Dana ke Penjual</span>
                    </button>
                </form>
            @endif

            <!-- 2. Seller Confirm Button -->
            @if ($isSeller && $transaction->status !== 'completed' && $transaction->status !== 'cancelled')
                <form method="POST" action="{{ route('transactions.confirm', $transaction) }}">
                    @csrf
                    <button
                        type="submit"
                        onclick="return confirm('Apakah Anda telah menerima pembayaran dan menyerahkan barang kepada pembeli?')"
                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Konfirmasi Selesai & Pembayaran Diterima</span>
                    </button>
                </form>
            @endif

            <!-- 2. Direct Chat with counterparty -->
            <form method="POST" action="{{ route('products.chat', $transaction->product) }}">
                @csrf
                <button
                    type="submit"
                    class="w-full py-2.5 bg-white border border-teal-600 text-teal-700 hover:bg-teal-50 text-xs font-semibold rounded-xl transition flex items-center justify-center gap-1.5 shadow-2xs"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span>Hubungi {{ $isBuyer ? 'Penjual' : 'Pembeli' }} via Chat</span>
                </button>
            </form>

            <!-- 3. Report Dispute Button -->
            <a
                href="{{ route('disputes.create', ['product_id' => $transaction->product_id, 'offer_id' => $transaction->offer_id]) }}"
                class="block w-full py-2.5 text-center bg-white border border-slate-200 text-amber-700 hover:bg-amber-50/50 text-xs font-semibold rounded-xl transition shadow-2xs flex items-center justify-center gap-1.5"
            >
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Pusat Resolusi Kendala (Laporkan Masalah Transaksi)</span>
            </a>

            <!-- 4. Cancel Transaction Button -->
            @if ($transaction->status === 'pending' || ($transaction->status === 'processing' && $transaction->payment_status === 'pending'))
                <form method="POST" action="{{ route('transactions.cancel', $transaction) }}">
                    @csrf
                    <button
                        type="submit"
                        onclick="return confirm('Apakah Anda yakin ingin membatalkan transaksi ini?')"
                        class="w-full py-2 text-center text-slate-400 hover:text-rose-600 text-xs font-semibold underline transition"
                    >
                        Batalkan Transaksi
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
