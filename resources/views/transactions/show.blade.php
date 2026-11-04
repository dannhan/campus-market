@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-12" x-data="{
    copied: false,
    copyText(text) {
        navigator.clipboard.writeText(text);
        this.copied = true;
        setTimeout(() => this.copied = false, 2500);
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

        @php
            $statusBadgeClass = match($transaction->status) {
                'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
                default => 'bg-slate-100 text-slate-700 border-slate-200',
            };
        @endphp

        <!-- Status Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status Transaksi</span>
                <span class="text-xs font-bold px-3 py-1 rounded-full border {{ $statusBadgeClass }}">
                    {{ $transaction->status_label }}
                </span>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                <span class="text-slate-500">Metode Pembayaran:</span>
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
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500">Waktu Pemesanan:</span>
                <span class="text-slate-700 font-mono text-[11px]">{{ $transaction->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
            </div>
        </div>

        <!-- 1. COD Details (if method is COD) -->
        @if ($transaction->payment_method === 'cod')
            <div class="bg-emerald-50/70 border border-emerald-200 rounded-2xl p-4 shadow-xs space-y-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">💵</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-900">Petunjuk Pembayaran Tunai (COD Kampus)</h3>
                </div>

                <div class="text-xs text-emerald-950 space-y-2 pt-1 leading-relaxed">
                    <div class="p-2.5 bg-white/80 rounded-xl border border-emerald-200">
                        <span class="text-[10px] font-bold text-emerald-700 uppercase block">Lokasi Janji Temu di Kampus:</span>
                        <div class="text-xs font-bold text-slate-900 mt-0.5">{{ $transaction->meeting_location ?? 'Area kampus yang disepakati bersama' }}</div>
                    </div>

                    <div class="p-2.5 bg-white/80 rounded-xl border border-emerald-200">
                        <span class="text-[10px] font-bold text-emerald-700 uppercase block">Nominal Pembayaran Tunai Pas:</span>
                        <div class="text-base font-extrabold text-teal-700 font-mono">{{ $transaction->formatted_amount }}</div>
                    </div>

                    <div class="text-[11px] text-emerald-800 flex items-start gap-1.5 pt-1">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Tips Aman Mahasiswa: Periksa kondisi fisik barang secara teliti saat bertemu sebelum menyerahkan uang tunai kepada penjual.</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- 2. Bank Transfer / E-Wallet Destination Account -->
        @if (in_array($transaction->payment_method, ['bank_transfer', 'e_wallet'], true))
            @php
                $details = $transaction->payment_details ?? [];
                $accNumber = $transaction->payment_method === 'bank_transfer'
                    ? ($details['bank_account_number'] ?? $transaction->seller->bank_account_number ?? '-')
                    : ($details['ewallet_account_number'] ?? $transaction->seller->ewallet_account_number ?? '-');
                $accName = $transaction->payment_method === 'bank_transfer'
                    ? ($details['bank_account_name'] ?? $transaction->seller->bank_account_name ?? $transaction->seller->name)
                    : ($details['ewallet_account_name'] ?? $transaction->seller->ewallet_account_name ?? $transaction->seller->name);
                $channelName = $transaction->payment_channel_label;
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

                <!-- Account Box -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-semibold text-slate-400 block uppercase">
                                {{ $transaction->payment_method === 'bank_transfer' ? 'Nomor Rekening' : 'Nomor Akun / HP E-Wallet' }}
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
                        <span class="text-slate-500">Jumlah Transfer:</span>
                        <strong class="text-teal-700 font-mono text-sm">{{ $transaction->formatted_amount }}</strong>
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

        <!-- Product Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">Barang Transaksi</h3>

            <div class="flex items-center gap-3">
                <img src="{{ $transaction->product->cover_image_url }}" alt="{{ $transaction->product->title }}" class="w-14 h-14 rounded-xl object-cover border border-slate-100 shrink-0">
                <div class="min-w-0 flex-1">
                    <a href="{{ route('products.show', $transaction->product) }}" class="text-xs font-bold text-slate-900 hover:text-teal-700 block truncate">
                        {{ $transaction->product->title }}
                    </a>
                    <span class="text-[10px] text-slate-500 block truncate">{{ $transaction->product->category->name ?? 'Kategori' }}</span>
                    <span class="text-xs font-extrabold text-teal-700 mt-1 block font-mono">
                        {{ $transaction->formatted_amount }}
                    </span>
                </div>
            </div>

            @if ($transaction->buyer_notes)
                <div class="p-2.5 bg-slate-50 rounded-xl text-xs text-slate-600 leading-relaxed border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Catatan Pembeli:</span>
                    "{{ $transaction->buyer_notes }}"
                </div>
            @endif
        </div>

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
            <!-- 1. Seller Confirm Button -->
            @if ($isSeller && $transaction->status !== 'completed' && $transaction->status !== 'cancelled')
                <form method="POST" action="{{ route('transactions.confirm', $transaction) }}">
                    @csrf
                    <button
                        type="submit"
                        onclick="return confirm('Apakah Anda telah menerima pembayaran dan menyerahkan barang kepada pembeli?')"
                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Konfirmasi Pembayaran Diterima (Selesaikan Transaksi)</span>
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

