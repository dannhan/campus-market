@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-12">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('disputes.index') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-base font-bold text-slate-900">Ajukan Mediasi Kendala</h1>
        <div class="w-9"></div>
    </div>

    <div class="p-5">
        <form
            method="POST"
            action="{{ route('disputes.store') }}"
            class="space-y-4"
        >
            @csrf

            @if ($product)
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <!-- Product Preview Card -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5 flex items-start gap-3">
                    <div class="w-12 h-12 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-700 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="text-[10px] uppercase font-bold text-teal-700">Produk Terkait</span>
                        <h2 class="text-xs font-bold text-slate-900 truncate">{{ $product->title }}</h2>
                        <div class="text-[11px] font-mono text-slate-600 mt-0.5">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">Penjual: {{ $product->user?->name }} ({{ $product->user?->university?->name ?? 'Mahasiswa' }})</div>
                    </div>
                </div>
            @else
                <div>
                    <label for="product_id" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        ID Produk Transaksi <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="number"
                        id="product_id"
                        name="product_id"
                        value="{{ old('product_id') }}"
                        required
                        placeholder="Masukkan ID produk terkait"
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border @error('product_id') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                    >
                    @error('product_id')
                        <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            @if ($offer)
                <input type="hidden" name="offer_id" value="{{ $offer->id }}">
                <div class="p-3 bg-teal-50/70 border border-teal-100 rounded-xl text-[11px] text-teal-900 flex items-center justify-between">
                    <div>
                        <span class="block text-[10px] text-teal-700 font-bold uppercase">Tawaran Transaksi Terkait:</span>
                        <span class="font-mono font-bold">{{ $offer->formatted_price }}</span>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-teal-100 text-teal-800">
                        {{ ucfirst($offer->status) }}
                    </span>
                </div>
            @endif

            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">
                    Detail Laporan Kendala
                </h3>

                <!-- Judul Kendala -->
                <div>
                    <label for="title" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Judul Masalah <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title') }}"
                        required
                        placeholder="contoh: Penjual tidak hadir saat janji temu COD di perpus"
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border @error('title') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                    >
                    @error('title')
                        <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Penyebab Masalah -->
                <div>
                    <label for="reason" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Kategori Masalah <span class="text-rose-500">*</span>
                    </label>
                    <select
                        id="reason"
                        name="reason"
                        required
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border @error('reason') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                    >
                        <option value="">-- Pilih Kategori Masalah --</option>
                        @foreach ($reasons as $key => $label)
                            <option value="{{ $key }}" @selected(old('reason') === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('reason')
                        <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Solusi yang diharapkan -->
                <div>
                    <label for="solution_requested" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Tuntutan Solusi yang Diharapkan <span class="text-rose-500">*</span>
                    </label>
                    <select
                        id="solution_requested"
                        name="solution_requested"
                        required
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border @error('solution_requested') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                    >
                        @foreach ($solutions as $key => $label)
                            <option value="{{ $key }}" @selected(old('solution_requested', 'refund') === $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('solution_requested')
                        <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kronologi / Deskripsi Lengkap -->
                <div>
                    <label for="description" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Penjelasan Kronologi Masalah <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        required
                        placeholder="Ceritakan dengan jelas urutan kejadian, lokasi janji temu, atau bagian barang yang tidak sesuai..."
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border @error('description') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="space-y-2 pt-2">
                <button
                    type="submit"
                    class="w-full py-3.5 px-4 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl shadow-md shadow-rose-600/20 transition duration-150 text-xs"
                >
                    Kirim Laporan Mediasi
                </button>
                <a
                    href="{{ route('disputes.index') }}"
                    class="w-full block text-center py-3 px-4 bg-white border border-slate-200 text-slate-600 font-semibold rounded-xl hover:bg-slate-50 transition duration-150 text-xs"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

