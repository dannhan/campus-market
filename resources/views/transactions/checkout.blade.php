@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-12" x-data="{
    method: '{{ old('payment_method', 'cod') }}',
    channel: '{{ old('payment_channel', 'cod') }}',
    deliveryMethod: '{{ old('delivery_method', 'cod') }}',
    courierName: '{{ old('courier_name', 'jnt') }}',
    itemPrice: {{ (float) $price }},
    courierFee: {{ (float) $courierFee }},
    setMethod(m, c) {
        this.method = m;
        this.channel = c;
    },
    setDelivery(d) {
        this.deliveryMethod = d;
    },
    get shippingCost() {
        return this.deliveryMethod === 'courier' ? this.courierFee : 0;
    },
    get totalBill() {
        return this.itemPrice + this.shippingCost;
    },
    formatRupiah(amount) {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }
}">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('products.show', $product) }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Detail Produk">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-base font-bold text-slate-900 leading-tight">Checkout Pembayaran & Pengiriman</h1>
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
        <input type="hidden" name="delivery_method" :value="deliveryMethod">

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

        <!-- Delivery Options Header -->
        <div class="bg-gradient-to-br from-indigo-700 to-indigo-900 text-white rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center text-xs font-bold">📦</span>
                <h3 class="text-xs font-bold uppercase tracking-wider">Opsi Pengiriman & Penyerahan Barang</h3>
            </div>
            <p class="text-[11px] text-indigo-100 leading-relaxed">
                Pilih cara serah terima barang yang paling nyaman: COD langsung di area kampus, ambil sendiri (pickup) di titik kampus, atau kirim via kurir ke kos/alamat Anda.
            </p>
        </div>

        <!-- Delivery Option Cards -->
        <div class="space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">Pilih Metode Pengiriman</h3>

            <!-- 1. COD (Ketemu Langsung di Kampus) -->
            <div
                @click="setDelivery('cod')"
                :class="deliveryMethod === 'cod' ? 'border-teal-600 bg-teal-50/40 ring-2 ring-teal-600/20' : 'border-slate-200 bg-white hover:border-slate-300'"
                class="border rounded-2xl p-4 cursor-pointer transition shadow-xs"
            >
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0 text-base font-bold">
                            🤝
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900">COD / Ketemu Langsung di Kampus</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                                    Gratis Ongkir
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                Bertemu tatap muka di area kampus (Perpustakaan, Kantin, Gedung Kuliah). Fleksibel untuk memeriksa barang secara langsung.
                            </p>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5"
                         :class="deliveryMethod === 'cod' ? 'border-teal-600 bg-teal-600' : 'border-slate-300'">
                        <div class="w-2 h-2 rounded-full bg-white" x-show="deliveryMethod === 'cod'"></div>
                    </div>
                </div>

                <!-- COD Meeting Spot Input -->
                <div x-show="deliveryMethod === 'cod'" x-cloak class="mt-3 pt-3 border-t border-teal-200/60 space-y-2">
                    <label for="meeting_location" class="block text-[11px] font-semibold text-slate-700">
                        Usulan Titik Janji Temu di Kampus <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="meeting_location"
                        id="meeting_location"
                        placeholder="Contoh: Depan Perpustakaan Pusat / Kantin FT"
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

            <!-- 2. Pickup di Kampus (Ambil Sendiri) -->
            <div
                @click="setDelivery('pickup')"
                :class="deliveryMethod === 'pickup' ? 'border-teal-600 bg-teal-50/40 ring-2 ring-teal-600/20' : 'border-slate-200 bg-white hover:border-slate-300'"
                class="border rounded-2xl p-4 cursor-pointer transition shadow-xs"
            >
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0 text-base font-bold">
                            🏫
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900">Pickup di Kampus (Ambil Sendiri)</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">
                                    Gratis Ongkir
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                Ambil pesanan mandiri di titik kumpul kampus yang ditentukan penjual (misal di loker gedung kuliah, pos satpam, atau sekretariat himpunan).
                            </p>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5"
                         :class="deliveryMethod === 'pickup' ? 'border-teal-600 bg-teal-600' : 'border-slate-300'">
                        <div class="w-2 h-2 rounded-full bg-white" x-show="deliveryMethod === 'pickup'"></div>
                    </div>
                </div>

                <!-- Pickup Location Info & Custom Input -->
                <div x-show="deliveryMethod === 'pickup'" x-cloak class="mt-3 pt-3 border-t border-teal-200/60 space-y-2">
                    <label for="pickup_location" class="block text-[11px] font-semibold text-slate-700">
                        Titik Pengambilan Barang di Kampus:
                    </label>
                    <input
                        type="text"
                        name="pickup_location"
                        id="pickup_location"
                        placeholder="Contoh: Loker Bersama Gedung A / Sekretariat Himpunan"
                        value="{{ old('pickup_location', $product->pickup_location ?: ($seller->preferred_pickup_location ?: 'Titik kumpul kampus penjual')) }}"
                        class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition"
                    >
                    @if ($product->pickup_location || $seller->preferred_pickup_location)
                        <p class="text-[10px] text-slate-400">
                            Titik pickup bawaan penjual: <strong>{{ $product->pickup_location ?: $seller->preferred_pickup_location }}</strong>
                        </p>
                    @endif
                    @error('pickup_location')
                        <p class="text-[11px] text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- 3. Kurir Ekspedisi (Kirim ke Kos / Rumah) -->
            <div
                @click="setDelivery('courier')"
                :class="deliveryMethod === 'courier' ? 'border-teal-600 bg-teal-50/40 ring-2 ring-teal-600/20' : 'border-slate-200 bg-white hover:border-slate-300'"
                class="border rounded-2xl p-4 cursor-pointer transition shadow-xs"
            >
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center shrink-0 text-base font-bold">
                            🚚
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900">Pengiriman Kurir Ekspedisi</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">
                                    + Rp {{ number_format($courierFee, 0, ',', '.') }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                                Pesanan dikirim langsung ke kos, asrama, atau alamat tempat tinggal Anda melalui layanan ekspedisi atau kurir mahasiswa.
                            </p>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5"
                         :class="deliveryMethod === 'courier' ? 'border-teal-600 bg-teal-600' : 'border-slate-300'">
                        <div class="w-2 h-2 rounded-full bg-white" x-show="deliveryMethod === 'courier'"></div>
                    </div>
                </div>

                <!-- Courier Address & Service Inputs -->
                <div x-show="deliveryMethod === 'courier'" x-cloak class="mt-3 pt-3 border-t border-teal-200/60 space-y-3">
                    <!-- Courier Selection -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                            Pilih Kurir Ekspedisi <span class="text-rose-500">*</span>
                        </label>
                        <input type="hidden" name="courier_name" :value="courierName">
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($couriers as $ckey => $cname)
                                <button
                                    type="button"
                                    @click.stop="courierName = '{{ $ckey }}'"
                                    :class="courierName === '{{ $ckey }}' ? 'border-teal-600 bg-white text-teal-800 font-bold shadow-xs' : 'border-slate-200 bg-white/70 text-slate-600 hover:bg-white'"
                                    class="p-2 border rounded-xl text-left text-xs transition flex items-center justify-between"
                                >
                                    <span>{{ $cname }}</span>
                                    <span x-show="courierName === '{{ $ckey }}'" class="text-teal-600 text-xs font-bold">✓</span>
                                </button>
                            @endforeach
                        </div>
                        @error('courier_name')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Recipient Name -->
                    <div>
                        <label for="shipping_recipient_name" class="block text-[11px] font-semibold text-slate-700 mb-1">
                            Nama Penerima <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="shipping_recipient_name"
                            id="shipping_recipient_name"
                            placeholder="Nama lengkap penerima"
                            value="{{ old('shipping_recipient_name', auth()->user()->name) }}"
                            class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition"
                        >
                        @error('shipping_recipient_name')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Recipient Phone -->
                    <div>
                        <label for="shipping_phone" class="block text-[11px] font-semibold text-slate-700 mb-1">
                            Nomor Telepon / WhatsApp <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="shipping_phone"
                            id="shipping_phone"
                            placeholder="Contoh: 081234567890"
                            value="{{ old('shipping_phone', auth()->user()->student?->phone) }}"
                            class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl font-mono focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition"
                        >
                        @error('shipping_phone')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Shipping Address -->
                    <div>
                        <label for="shipping_address" class="block text-[11px] font-semibold text-slate-700 mb-1">
                            Alamat Lengkap Pengiriman (Kos/Rumah) <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            name="shipping_address"
                            id="shipping_address"
                            rows="2"
                            placeholder="Contoh: Kos Melati No. 12, Jl. Prof. Soedarto, Tembalang, Kota Semarang"
                            class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition"
                        >{{ old('shipping_address') }}</textarea>
                        @error('shipping_address')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        @error('delivery_method')
            <p class="text-xs text-rose-500">{{ $message }}</p>
        @enderror

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
                                Bayar tunai saat bertemu di area kampus. Cek kondisi barang sebelum menyerahkan uang tunai.
                            </p>
                        </div>
                    </div>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5"
                         :class="method === 'cod' ? 'border-teal-600 bg-teal-600' : 'border-slate-300'">
                        <div class="w-2 h-2 rounded-full bg-white" x-show="method === 'cod'"></div>
                    </div>
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
                placeholder="Contoh: Tolong bungkus yang rapi ya, atau bisa ketemu pas jam makan siang..."
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
                    <span>Ongkos Kirim (<span x-text="deliveryMethod === 'cod' ? 'COD Kampus' : (deliveryMethod === 'pickup' ? 'Pickup Kampus' : 'Kurir Ekspedisi')"></span>)</span>
                    <span class="font-mono font-semibold" :class="deliveryMethod === 'courier' ? 'text-slate-900' : 'text-emerald-600'" x-text="deliveryMethod === 'courier' ? formatRupiah(courierFee) : 'Rp 0 (Gratis)'"></span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Layanan Kampus</span>
                    <span class="font-semibold text-emerald-600">Rp 0 (Gratis)</span>
                </div>
                <div class="flex justify-between text-sm font-bold text-slate-900 pt-2 border-t border-slate-100">
                    <span>Total Tagihan</span>
                    <span class="text-teal-700 font-extrabold font-mono text-base" x-text="formatRupiah(totalBill)">
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
