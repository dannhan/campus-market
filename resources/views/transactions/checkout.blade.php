@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-12" x-data="{
    method: '{{ old('payment_method', 'cod') }}',
    channel: '{{ old('payment_channel', 'cod') }}',
    setMethod(m, c) {
        this.method = m;
        this.channel = c;
    }
}">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('products.show', $product) }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Detail Produk">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-base font-bold text-slate-900 leading-tight">Checkout Pembayaran</h1>
        <div class="w-9"></div>
    </div>

    <form method="POST" action="{{ route('transactions.store') }}" class="p-5 space-y-4">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        @if ($offer)
            <input type="hidden" name="offer_id" value="{{ $offer->id }}">
        @endif
        <input type="hidden" name="payment_method" :value="method">
        <input type="hidden" name="payment_channel" :value="channel">

        <!-- Product Summary Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Barang yang Dibeli</div>
            <div class="flex items-center gap-3">
                <img src="{{ $product->cover_image_url }}" alt="{{ $product->title }}" class="w-14 h-14 rounded-xl object-cover border border-slate-100 shrink-0">
                <div class="min-w-0 flex-1">
                    <h2 class="text-xs font-bold text-slate-900 truncate">{{ $product->title }}</h2>
                    <p class="text-[11px] text-slate-500 truncate">Penjual: {{ $seller->name }}</p>
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="text-xs font-extrabold text-teal-700">Rp {{ number_format($price, 0, ',', '.') }}</span>
                        @if ($offer)
                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-teal-50 text-teal-700 border border-teal-100">
                                Harga Nego
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Student-Friendly Payment Methods Header -->
        <div class="bg-gradient-to-br from-teal-700 to-teal-900 text-white rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center text-xs font-bold">💳</span>
                <h3 class="text-xs font-bold uppercase tracking-wider">Metode Pembayaran Mahasiswa</h3>
            </div>
            <p class="text-[11px] text-teal-100 leading-relaxed">
                Pilih opsi pembayaran yang paling fleksibel & hemat untuk kantong mahasiswa: bayar tunai COD di kampus, transfer bank, atau e-wallet instan.
            </p>
        </div>

        <!-- Payment Method Selection Cards -->
        <div class="space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">Pilih Metode Pembayaran</h3>

            <!-- 1. COD (Bayar Tunai di Kampus) -->
            <div
                @click="setMethod('cod', 'cod')"
                :class="method === 'cod' ? 'border-teal-600 bg-teal-50/40 ring-2 ring-teal-600/20' : 'border-slate-200 bg-white hover:border-slate-300'"
                class="border rounded-2xl p-4 cursor-pointer transition shadow-xs"
            >
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 text-base font-bold">
                            💵
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900">Bayar Tunai di Kampus (COD)</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                                    Gratis Biaya Admin
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                Bertemu langsung di area kampus (Perpustakaan, Kantin, Gedung Kuliah). Cek kondisi barang sebelum menyerahkan uang tunai.
                            </p>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5"
                         :class="method === 'cod' ? 'border-teal-600 bg-teal-600' : 'border-slate-300'">
                        <div class="w-2 h-2 rounded-full bg-white" x-show="method === 'cod'"></div>
                    </div>
                </div>

                <!-- COD Meeting Spot Input -->
                <div x-show="method === 'cod'" x-cloak class="mt-3 pt-3 border-t border-teal-200/60 space-y-2">
                    <label for="meeting_location" class="block text-[11px] font-semibold text-slate-700">
                        Usulan Lokasi Janji Temu di Kampus <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="meeting_location"
                        id="meeting_location"
                        placeholder="Contoh: Depan Perpustakaan Pusat Undip / Kantin FT"
                        value="{{ old('meeting_location', $seller->preferred_meeting_location) }}"
                        class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition"
                    >
                    @if ($seller->preferred_meeting_location)
                        <p class="text-[10px] text-slate-400">
                            Lokasi rekomendasi penjual: <strong>{{ $seller->preferred_meeting_location }}</strong>
                        </p>
                    @endif
                    @error('meeting_location')
                        <p class="text-[11px] text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 2. Transfer Bank -->
            <div
                @click="setMethod('bank_transfer', channel.startsWith('b') || channel === 'mandiri' ? channel : 'bca')"
                :class="method === 'bank_transfer' ? 'border-teal-600 bg-teal-50/40 ring-2 ring-teal-600/20' : 'border-slate-200 bg-white hover:border-slate-300'"
                class="border rounded-2xl p-4 cursor-pointer transition shadow-xs"
            >
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center shrink-0 text-base font-bold">
                            🏦
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900">Transfer Bank Mahasiswa</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">
                                    ATM & Mobile Banking
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                Transfer mudah via m-Banking atau ATM ke rekening bank penjual (BCA, Mandiri, BNI, BRI, BSI).
                            </p>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5"
                         :class="method === 'bank_transfer' ? 'border-teal-600 bg-teal-600' : 'border-slate-300'">
                        <div class="w-2 h-2 rounded-full bg-white" x-show="method === 'bank_transfer'"></div>
                    </div>
                </div>

                <!-- Bank Channel Selection -->
                <div x-show="method === 'bank_transfer'" x-cloak class="mt-3 pt-3 border-t border-teal-200/60 space-y-2">
                    <label class="block text-[11px] font-semibold text-slate-700">
                        Pilih Bank Tujuan:
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($bankChannels as $key => $bankName)
                            <button
                                type="button"
                                @click.stop="channel = '{{ $key }}'"
                                :class="channel === '{{ $key }}' ? 'border-teal-600 bg-white text-teal-800 font-bold shadow-xs' : 'border-slate-200 bg-white/70 text-slate-600 hover:bg-white'"
                                class="p-2 border rounded-xl text-left text-xs transition flex items-center justify-between"
                            >
                                <span>{{ $bankName }}</span>
                                <span x-show="channel === '{{ $key }}'" class="text-teal-600 text-xs">✓</span>
                            </button>
                        @endforeach
                    </div>

                    @if ($seller->bank_account_number)
                        <div class="p-2.5 bg-blue-50/70 rounded-xl border border-blue-100 text-[11px] text-blue-900 space-y-0.5 mt-2">
                            <span class="text-[10px] font-semibold text-blue-700 uppercase block">Rekening Penjual Terdata:</span>
                            <div class="font-mono font-bold">{{ $seller->bank_name_label ?? 'Bank' }} — {{ $seller->bank_account_number }}</div>
                            <div class="text-[10px] text-blue-800">a.n. {{ $seller->bank_account_name ?? $seller->name }}</div>
                        </div>
                    @else
                        <p class="text-[10px] text-slate-400 mt-1">
                            Nomor rekening lengkap akan ditampilkan pada berkas transaksi setelah pesanan dibuat.
                        </p>
                    @endif
                </div>
            </div>

            <!-- 3. Dompet Digital (E-Wallet) -->
            <div
                @click="setMethod('e_wallet', ['gopay', 'ovo', 'dana', 'shopeepay'].includes(channel) ? channel : 'gopay')"
                :class="method === 'e_wallet' ? 'border-teal-600 bg-teal-50/40 ring-2 ring-teal-600/20' : 'border-slate-200 bg-white hover:border-slate-300'"
                class="border rounded-2xl p-4 cursor-pointer transition shadow-xs"
            >
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center shrink-0 text-base font-bold">
                            📱
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900">Dompet Digital (E-Wallet)</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-100 text-purple-800">
                                    Instan & Praktis
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                Transfer saldo langsung menggunakan aplikasi GoPay, OVO, DANA, atau ShopeePay tanpa biaya antar akun.
                            </p>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5"
                         :class="method === 'e_wallet' ? 'border-teal-600 bg-teal-600' : 'border-slate-300'">
                        <div class="w-2 h-2 rounded-full bg-white" x-show="method === 'e_wallet'"></div>
                    </div>
                </div>

                <!-- E-Wallet Channel Selection -->
                <div x-show="method === 'e_wallet'" x-cloak class="mt-3 pt-3 border-t border-teal-200/60 space-y-2">
                    <label class="block text-[11px] font-semibold text-slate-700">
                        Pilih E-Wallet:
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($ewalletChannels as $key => $walletName)
                            <button
                                type="button"
                                @click.stop="channel = '{{ $key }}'"
                                :class="channel === '{{ $key }}' ? 'border-teal-600 bg-white text-teal-800 font-bold shadow-xs' : 'border-slate-200 bg-white/70 text-slate-600 hover:bg-white'"
                                class="p-2 border rounded-xl text-left text-xs transition flex items-center justify-between"
                            >
                                <span>{{ $walletName }}</span>
                                <span x-show="channel === '{{ $key }}'" class="text-teal-600 text-xs">✓</span>
                            </button>
                        @endforeach
                    </div>

                    @if ($seller->ewallet_account_number)
                        <div class="p-2.5 bg-purple-50/70 rounded-xl border border-purple-100 text-[11px] text-purple-900 space-y-0.5 mt-2">
                            <span class="text-[10px] font-semibold text-purple-700 uppercase block">Akun E-Wallet Penjual:</span>
                            <div class="font-mono font-bold">{{ $seller->ewallet_provider_label ?? 'E-Wallet' }} — {{ $seller->ewallet_account_number }}</div>
                            <div class="text-[10px] text-purple-800">a.n. {{ $seller->ewallet_account_name ?? $seller->name }}</div>
                        </div>
                    @else
                        <p class="text-[10px] text-slate-400 mt-1">
                            Nomor akun e-wallet lengkap akan ditampilkan pada berkas transaksi setelah pesanan dibuat.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        @error('payment_method')
            <p class="text-xs text-rose-500">{{ $message }}</p>
        @enderror
        @error('payment_channel')
            <p class="text-xs text-rose-500">{{ $message }}</p>
        @enderror

        <!-- Buyer Notes -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-2">
            <label for="buyer_notes" class="block text-xs font-semibold text-slate-700">
                Catatan untuk Penjual (Opsional)
            </label>
            <textarea
                name="buyer_notes"
                id="buyer_notes"
                rows="2"
                placeholder="Contoh: Bisa COD hari Kamis jam istirahat kuliah ya..."
                class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition"
            >{{ old('buyer_notes') }}</textarea>
            @error('buyer_notes')
                <p class="text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Payment Breakdown Summary -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">Ringkasan Pembayaran</h3>

            <div class="text-xs space-y-1.5 pt-1">
                <div class="flex justify-between text-slate-600">
                    <span>Harga Barang</span>
                    <span class="font-mono font-semibold">Rp {{ number_format($price, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Layanan Kampus</span>
                    <span class="font-semibold text-emerald-600">Rp 0 (Gratis)</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Penanganan</span>
                    <span class="font-semibold text-emerald-600">Rp 0 (Gratis)</span>
                </div>
                <div class="flex justify-between text-sm font-bold text-slate-900 pt-2 border-t border-slate-100">
                    <span>Total Tagihan</span>
                    <span class="text-teal-700 font-extrabold font-mono text-base">
                        Rp {{ number_format($price, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <button
            type="submit"
            class="w-full py-3.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-md shadow-teal-600/20 transition flex items-center justify-center gap-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>Konfirmasi & Buat Pesanan</span>
        </button>
    </form>
</div>
@endsection

