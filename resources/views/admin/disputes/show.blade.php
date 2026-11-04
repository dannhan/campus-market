@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-12">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('admin.disputes.index') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Daftar Sengketa">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="text-center">
            <div class="text-[10px] font-mono text-slate-400">#DSP-{{ str_pad($dispute->id, 4, '0', STR_PAD_LEFT) }}</div>
            <h1 class="text-base font-bold text-slate-900 leading-tight">Mediasi Sengketa</h1>
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

        @php
            $badgeStyle = match($dispute->status) {
                'open' => 'bg-amber-100 text-amber-800 border-amber-200',
                'under_review' => 'bg-blue-100 text-blue-800 border-blue-200',
                'resolved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'closed' => 'bg-slate-100 text-slate-700 border-slate-200',
                default => 'bg-slate-100 text-slate-700 border-slate-200',
            };
        @endphp

        <!-- Dispute Case Overview -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status Mediasi Saat Ini</span>
                <span class="text-xs font-bold px-3 py-1 rounded-full border {{ $badgeStyle }}">
                    {{ $dispute->status_label }}
                </span>
            </div>

            <h2 class="text-sm font-bold text-slate-900">{{ $dispute->title }}</h2>
            <div class="text-[10px] text-slate-400 font-mono">
                Diajukan pada {{ $dispute->created_at->translatedFormat('d F Y, H:i') }} WIB
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">Informasi Transaksi</h3>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-2 text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 block">Produk:</span>
                    <strong class="text-slate-900 block truncate">{{ $dispute->product?->title ?? '-' }}</strong>
                </div>
                <div class="flex justify-between text-[11px] pt-1 border-t border-slate-200/60">
                    <span class="text-slate-500">Harga Produk:</span>
                    <strong class="font-mono text-slate-800">Rp {{ number_format($dispute->product?->price ?? 0, 0, ',', '.') }}</strong>
                </div>
                @if ($dispute->offer)
                    <div class="flex justify-between text-[11px] text-teal-800 pt-1 border-t border-slate-200/60">
                        <span>Tawaran Disepakati:</span>
                        <strong class="font-mono">{{ $dispute->offer->formatted_price }}</strong>
                    </div>
                @endif
            </div>
        </div>

        <!-- Parties Involved -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">Pihak Bersengketa</h3>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="p-3 bg-rose-50/50 rounded-xl border border-rose-100">
                    <span class="text-[10px] font-bold text-rose-700 uppercase block">Pelapor (Complainant)</span>
                    <strong class="text-slate-900 block mt-1 truncate">{{ $dispute->reporter?->name ?? 'User' }}</strong>
                    <span class="text-[10px] text-slate-500 block truncate">{{ $dispute->reporter?->university?->name ?? '-' }}</span>
                    <span class="text-[10px] font-mono text-slate-400 block truncate mt-1">{{ $dispute->reporter?->email }}</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-500 uppercase block">Pihak Terlapor</span>
                    <strong class="text-slate-900 block mt-1 truncate">{{ $dispute->respondent?->name ?? 'User' }}</strong>
                    <span class="text-[10px] text-slate-500 block truncate">{{ $dispute->respondent?->university?->name ?? '-' }}</span>
                    <span class="text-[10px] font-mono text-slate-400 block truncate mt-1">{{ $dispute->respondent?->email }}</span>
                </div>
            </div>
        </div>

        <!-- Issue Description -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">Keluhan Pelapor</h3>

            <div class="text-xs space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-500">Kategori Masalah:</span>
                    <strong class="text-slate-800">{{ $dispute->reason_label }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Tuntutan Solusi:</span>
                    <strong class="text-rose-600">{{ $dispute->solution_label }}</strong>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-[10px] text-slate-400 font-semibold block mb-1">Kronologi dari Pelapor:</span>
                    <p class="text-slate-700 leading-relaxed whitespace-pre-line bg-slate-50 p-3 rounded-xl border border-slate-100">
                        {{ $dispute->description }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Admin Mediation Decision Form -->
        <form
            method="POST"
            action="{{ route('admin.disputes.update', $dispute) }}"
            class="bg-white border-2 border-teal-500/30 rounded-2xl p-4 shadow-sm space-y-4"
        >
            @csrf
            @method('PUT')

            <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-teal-600 text-white flex items-center justify-center text-xs font-bold">
                    ⚖
                </span>
                <h3 class="text-xs font-bold uppercase tracking-wider text-teal-900">
                    Formulir Keputusan Mediasi Administrator
                </h3>
            </div>

            <!-- Update Status -->
            <div>
                <label for="status" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Ubah Status Mediasi <span class="text-rose-500">*</span>
                </label>
                <select
                    id="status"
                    name="status"
                    required
                    class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border @error('status') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                >
                    @foreach ($statuses as $val => $label)
                        <option value="{{ $val }}" @selected(old('status', $dispute->status) === $val)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Official Decision Verdict -->
            <div>
                <label for="admin_decision" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Arah Putusan Mediasi
                </label>
                <select
                    id="admin_decision"
                    name="admin_decision"
                    class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border @error('admin_decision') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                >
                    <option value="">-- Pilih Arah Keputusan (Opsional jika masih ditinjau) --</option>
                    @foreach ($decisions as $decKey => $decLabel)
                        <option value="{{ $decKey }}" @selected(old('admin_decision', $dispute->admin_decision) === $decKey)>
                            {{ $decLabel }}
                        </option>
                    @endforeach
                </select>
                @error('admin_decision')
                    <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Resolution Notes & Instructions -->
            <div>
                <label for="admin_notes" class="block text-xs font-semibold text-slate-700 mb-1.5">
                    Catatan Resmi Mediasi & Instruksi Penyelesaian <span class="text-rose-500">*</span>
                </label>
                <textarea
                    id="admin_notes"
                    name="admin_notes"
                    rows="4"
                    required
                    placeholder="Tuliskan hasil evaluasi admin, solusi yang ditetapkan, batas waktu pengembalian dana/barang, atau instruksi lain untuk kedua pihak..."
                    class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border @error('admin_notes') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                >{{ old('admin_notes', $dispute->admin_notes) }}</textarea>
                @error('admin_notes')
                    <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
                <p class="text-[10px] text-slate-400 mt-1">Catatan ini akan dapat dibaca langsung oleh pelapor maupun terlapor di halaman berkas mereka.</p>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full py-3 px-4 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-xs shadow-md shadow-teal-600/20 transition"
            >
                Simpan & Rilis Keputusan Mediasi
            </button>
        </form>
    </div>
</div>
@endsection

