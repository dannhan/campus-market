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
        <h1 class="text-base font-bold text-slate-900 leading-tight">Pusat Resolusi Kendala</h1>
        <a href="{{ route('disputes.create') }}" class="w-9 h-9 -mr-1 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 flex items-center justify-center transition" title="Laporkan Kendala Baru">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
        </a>
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

        <!-- Info Box -->
        <div class="p-4 bg-gradient-to-r from-rose-50 to-orange-50 border border-rose-100 rounded-2xl flex items-start gap-3">
            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h2 class="text-xs font-bold text-slate-900">Mediasi Transaksi Kampus</h2>
                <p class="text-[11px] text-slate-600 leading-relaxed mt-0.5">
                    Mengalami barang tidak sesuai, kendala COD, atau selisih pembayaran? Tim administrator CampusMarket siap memediasi transaksi Anda secara netral.
                </p>
                <a href="{{ route('disputes.create') }}" class="inline-block mt-2.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold shadow-2xs transition">
                    + Ajukan Laporan Kendala
                </a>
            </div>
        </div>

        <!-- Disputes List -->
        <div class="space-y-3 pt-1">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">
                Berkas Kendala Transaksi Anda
            </h2>

            @forelse ($disputes as $dispute)
                @php
                    $isReporter = $dispute->reporter_id === auth()->id();
                    $badgeStyle = match($dispute->status) {
                        'open' => 'bg-amber-100 text-amber-800 border-amber-200',
                        'under_review' => 'bg-blue-100 text-blue-800 border-blue-200',
                        'resolved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'closed' => 'bg-slate-100 text-slate-700 border-slate-200',
                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                    };
                @endphp
                <a href="{{ route('disputes.show', $dispute) }}" class="block bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs hover:border-teal-300 transition space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <span class="text-[10px] text-slate-400 font-mono">
                                #DSP-{{ str_pad($dispute->id, 4, '0', STR_PAD_LEFT) }} · {{ $dispute->created_at->translatedFormat('d M Y') }}
                            </span>
                            <h3 class="text-xs font-bold text-slate-900 truncate mt-0.5">
                                {{ $dispute->title }}
                            </h3>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $badgeStyle }} shrink-0">
                            {{ $dispute->status_label }}
                        </span>
                    </div>

                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-[11px] space-y-1">
                        <div class="text-slate-500 truncate">
                            <span class="text-slate-400">Produk:</span> <strong class="text-slate-800">{{ $dispute->product?->title ?? '-' }}</strong>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-500 pt-0.5">
                            <span>Peran: <strong class="{{ $isReporter ? 'text-rose-600' : 'text-slate-700' }}">{{ $isReporter ? 'Pelapor' : 'Terlapor' }}</strong></span>
                            <span>Solusi: <strong class="text-slate-800">{{ $dispute->solution_label }}</strong></span>
                        </div>
                    </div>

                    @if ($dispute->admin_notes)
                        <div class="p-2.5 bg-emerald-50/70 border border-emerald-200/80 rounded-xl text-[11px] text-emerald-900">
                            <span class="font-bold block text-[10px] text-emerald-700 uppercase">Keputusan Mediasi Admin:</span>
                            <p class="mt-0.5 line-clamp-2">{{ $dispute->admin_notes }}</p>
                        </div>
                    @endif

                    <div class="flex items-center justify-end text-xs font-bold text-teal-700 pt-1">
                        Lihat Berkas Mediasi →
                    </div>
                </a>
            @empty
                <div class="p-8 text-center bg-white border border-slate-200/90 rounded-2xl">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-slate-700">Tidak ada kendala transaksi</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Semua transaksi dan tawar-menawar Anda berjalan lancar.</p>
                </div>
            @endforelse

            @if ($disputes->hasPages())
                <div class="pt-2">
                    {{ $disputes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

