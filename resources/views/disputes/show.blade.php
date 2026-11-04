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
        <div class="text-center">
            <div class="text-[10px] font-mono text-slate-400">#DSP-{{ str_pad($dispute->id, 4, '0', STR_PAD_LEFT) }}</div>
            <h1 class="text-base font-bold text-slate-900 leading-tight">Berkas Mediasi</h1>
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

        <!-- Status Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status Mediasi</span>
                <span class="text-xs font-bold px-3 py-1 rounded-full border {{ $badgeStyle }}">
                    {{ $dispute->status_label }}
                </span>
            </div>

            <h2 class="text-sm font-bold text-slate-900 mt-3">{{ $dispute->title }}</h2>
            <div class="text-[11px] text-slate-400 mt-1">
                Diajukan pada {{ $dispute->created_at->translatedFormat('d F Y, H:i') }} WIB
            </div>
        </div>

        <!-- Official Admin Decision Banner (if available) -->
        @if ($dispute->admin_notes)
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 shadow-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Keputusan Resmi Mediasi Administrator</span>
                    </span>
                    @if ($dispute->resolved_at)
                        <span class="text-[10px] text-emerald-600 font-mono">{{ $dispute->resolved_at->translatedFormat('d M Y') }}</span>
                    @endif
                </div>

                @if ($dispute->admin_decision)
                    <div class="text-xs font-bold text-emerald-900 bg-emerald-100/70 px-3 py-1.5 rounded-lg">
                        {{ $dispute->decision_label }}
                    </div>
                @endif

                <div class="text-xs text-emerald-950 leading-relaxed whitespace-pre-line pt-1">
                    {{ $dispute->admin_notes }}
                </div>

                @if ($dispute->admin)
                    <div class="text-[10px] text-emerald-700 pt-1 border-t border-emerald-200/60">
                        Dimediasi oleh: <strong>{{ $dispute->admin->name }}</strong>
                    </div>
                @endif
            </div>
        @else
            <div class="p-3.5 bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl text-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Laporan ini telah masuk ke antrean administrator CampusMarket untuk ditinjau dan dimediasi.</span>
            </div>
        @endif

        <!-- Product & Transaction Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">Transaksi Terkait</h3>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-2">
                <div class="text-xs font-bold text-slate-900">{{ $dispute->product?->title ?? '-' }}</div>
                <div class="text-[11px] text-slate-500 flex justify-between">
                    <span>Harga Etalase:</span>
                    <strong class="font-mono text-slate-800">Rp {{ number_format($dispute->product?->price ?? 0, 0, ',', '.') }}</strong>
                </div>
                @if ($dispute->offer)
                    <div class="text-[11px] text-teal-700 flex justify-between pt-1 border-t border-slate-200/60">
                        <span>Tawaran Transaksi:</span>
                        <strong class="font-mono">{{ $dispute->offer->formatted_price }}</strong>
                    </div>
                @endif
            </div>
        </div>

        <!-- Parties Involved -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">Pihak yang Terlibat</h3>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-[10px] font-bold text-rose-600 uppercase block">Pelapor (Complainant)</span>
                    <strong class="text-slate-900 block mt-1 truncate">{{ $dispute->reporter?->name ?? 'User' }}</strong>
                    <span class="text-[10px] text-slate-500 block truncate">{{ $dispute->reporter?->university?->name ?? '-' }}</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-[10px] font-bold text-slate-500 uppercase block">Pihak Terlapor</span>
                    <strong class="text-slate-900 block mt-1 truncate">{{ $dispute->respondent?->name ?? 'User' }}</strong>
                    <span class="text-[10px] text-slate-500 block truncate">{{ $dispute->respondent?->university?->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Issue Details & Chronology -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">Uraian Masalah & Tuntutan</h3>

            <div class="text-xs space-y-2.5">
                <div>
                    <span class="text-[10px] text-slate-400 font-semibold block">Kategori Masalah:</span>
                    <strong class="text-slate-800">{{ $dispute->reason_label }}</strong>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-semibold block">Tuntutan Solusi yang Diajukan:</span>
                    <strong class="text-teal-700">{{ $dispute->solution_label }}</strong>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-[10px] text-slate-400 font-semibold block mb-1">Kronologi Kejadian:</span>
                    <p class="text-slate-700 leading-relaxed whitespace-pre-line bg-slate-50 p-3 rounded-xl border border-slate-100">
                        {{ $dispute->description }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

