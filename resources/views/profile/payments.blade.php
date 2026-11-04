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
        <h1 class="text-base font-bold text-slate-900 leading-tight">Pengaturan Pembayaran</h1>
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

        <!-- Banner Info -->
        <div class="bg-gradient-to-br from-teal-700 to-teal-900 text-white rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center text-xs font-bold">💳</span>
                <h2 class="text-xs font-bold uppercase tracking-wider">Rekening & E-Wallet Penerimaan</h2>
            </div>
            <p class="text-[11px] text-teal-100 leading-relaxed">
                Kelola informasi rekening bank dan dompet digital Anda agar pembeli dapat langsung mentransfer dana saat melakukan transaksi di CampusMarket.
            </p>
        </div>

        <form method="POST" action="{{ route('profile.payments.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Section 1: Transfer Bank -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
                <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-800 flex items-center justify-center text-xs font-bold">🏦</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Rekening Transfer Bank</h3>
                </div>

                <!-- Bank Name -->
                <div>
                    <label for="bank_name" class="block text-xs font-semibold text-slate-700 mb-1">
                        Pilihan Bank
                    </label>
                    <select
                        name="bank_name"
                        id="bank_name"
                        class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition"
                    >
                        <option value="">-- Pilih Bank --</option>
                        @foreach ($bankChannels as $key => $name)
                            <option value="{{ $key }}" @selected(old('bank_name', $user->bank_name) === $key)>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @error('bank_name')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Account Number -->
                <div>
                    <label for="bank_account_number" class="block text-xs font-semibold text-slate-700 mb-1">
                        Nomor Rekening
                    </label>
                    <input
                        type="text"
                        name="bank_account_number"
                        id="bank_account_number"
                        placeholder="Contoh: 1234567890"
                        value="{{ old('bank_account_number', $user->bank_account_number) }}"
                        class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl font-mono focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition"
                    >
                    @error('bank_account_number')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Account Holder Name -->
                <div>
                    <label for="bank_account_name" class="block text-xs font-semibold text-slate-700 mb-1">
                        Nama Pemilik Rekening (Atas Nama)
                    </label>
                    <input
                        type="text"
                        name="bank_account_name"
                        id="bank_account_name"
                        placeholder="Sesuai buku tabungan / m-banking"
                        value="{{ old('bank_account_name', $user->bank_account_name ?? $user->name) }}"
                        class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition"
                    >
                    @error('bank_account_name')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Section 2: E-Wallet -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
                <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                    <span class="w-6 h-6 rounded-lg bg-purple-100 text-purple-800 flex items-center justify-center text-xs font-bold">📱</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Dompet Digital (E-Wallet)</h3>
                </div>

                <!-- E-Wallet Provider -->
                <div>
                    <label for="ewallet_provider" class="block text-xs font-semibold text-slate-700 mb-1">
                        Penyedia E-Wallet
                    </label>
                    <select
                        name="ewallet_provider"
                        id="ewallet_provider"
                        class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition"
                    >
                        <option value="">-- Pilih E-Wallet --</option>
                        @foreach ($ewalletChannels as $key => $name)
                            <option value="{{ $key }}" @selected(old('ewallet_provider', $user->ewallet_provider) === $key)>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @error('ewallet_provider')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- E-Wallet Phone Number -->
                <div>
                    <label for="ewallet_account_number" class="block text-xs font-semibold text-slate-700 mb-1">
                        Nomor HP / Akun E-Wallet
                    </label>
                    <input
                        type="text"
                        name="ewallet_account_number"
                        id="ewallet_account_number"
                        placeholder="Contoh: 081234567890"
                        value="{{ old('ewallet_account_number', $user->ewallet_account_number) }}"
                        class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl font-mono focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition"
                    >
                    @error('ewallet_account_number')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- E-Wallet Name -->
                <div>
                    <label for="ewallet_account_name" class="block text-xs font-semibold text-slate-700 mb-1">
                        Nama Akun E-Wallet (Atas Nama)
                    </label>
                    <input
                        type="text"
                        name="ewallet_account_name"
                        id="ewallet_account_name"
                        placeholder="Sesuai nama profil e-wallet"
                        value="{{ old('ewallet_account_name', $user->ewallet_account_name ?? $user->name) }}"
                        class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition"
                    >
                    @error('ewallet_account_name')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Section 3: COD & Campus Pickup Preference -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
                <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                    <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center text-xs font-bold">🏫</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Preferensi COD & Titik Pickup Kampus</h3>
                </div>

                <div>
                    <label for="preferred_meeting_location" class="block text-xs font-semibold text-slate-700 mb-1">
                        Lokasi COD Favorit di Lingkungan Kampus
                    </label>
                    <input
                        type="text"
                        name="preferred_meeting_location"
                        id="preferred_meeting_location"
                        placeholder="Contoh: Perpustakaan Pusat, Kantin Fakultas, Gazebo Taman Kampus"
                        value="{{ old('preferred_meeting_location', $user->preferred_meeting_location) }}"
                        class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition"
                    >
                    <p class="text-[10px] text-slate-400 mt-1">
                        Lokasi pertemuan yang disarankan saat pembeli memilih COD / Ketemu Langsung.
                    </p>
                    @error('preferred_meeting_location')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="preferred_pickup_location" class="block text-xs font-semibold text-slate-700 mb-1">
                        Titik Pickup / Pengambilan Barang di Kampus
                    </label>
                    <input
                        type="text"
                        name="preferred_pickup_location"
                        id="preferred_pickup_location"
                        placeholder="Contoh: Loker Bersama Gedung A, Ruang Himpunan Mahasiswa, Pos Satpam Barat"
                        value="{{ old('preferred_pickup_location', $user->preferred_pickup_location) }}"
                        class="w-full px-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition"
                    >
                    <p class="text-[10px] text-slate-400 mt-1">
                        Lokasi titik ambil mandiri jika pembeli memilih opsi Pickup di Kampus.
                    </p>
                    @error('preferred_pickup_location')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-md shadow-teal-600/20 transition"
            >
                Simpan Informasi Pembayaran
            </button>
        </form>
    </div>
</div>
@endsection

