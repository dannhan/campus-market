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
        <div class="text-center">
            <div class="text-[10px] font-bold uppercase tracking-wider text-teal-600">Admin Panel</div>
            <h1 class="text-base font-bold text-slate-900 leading-tight">Mediasi Sengketa</h1>
        </div>
        <div class="w-9"></div>
    </div>

    <!-- Navigation Tabs -->
    <div class="px-5 pt-3 bg-white border-b border-slate-100 flex items-center gap-2 overflow-x-auto">
        <a href="{{ route('admin.categories.index') }}" class="pb-2.5 text-xs font-medium text-slate-400 hover:text-slate-600 transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span>Kategori Produk</span>
        </a>
        <a href="{{ route('admin.reports.index') }}" class="pb-2.5 text-xs font-medium text-slate-400 hover:text-slate-600 transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span>Laporan & Statistik</span>
        </a>
        <a href="{{ route('admin.disputes.index') }}" class="pb-2.5 text-xs font-bold text-teal-700 border-b-2 border-teal-600 flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>Mediasi Sengketa</span>
            @if ($openCount > 0)
                <span class="px-1.5 py-0.2 bg-rose-500 text-white rounded-full text-[10px] font-bold">{{ $openCount }}</span>
            @endif
        </a>
    </div>

    <div class="p-5 space-y-4">
        <!-- Quick Summary Bar -->
        <div class="grid grid-cols-3 gap-2">
            <div class="p-3 bg-white border border-slate-200/90 rounded-2xl shadow-xs text-center">
                <div class="text-[10px] font-semibold text-amber-600">Menunggu</div>
                <div class="text-lg font-bold text-slate-800 mt-0.5">{{ $openCount }}</div>
            </div>
            <div class="p-3 bg-white border border-slate-200/90 rounded-2xl shadow-xs text-center">
                <div class="text-[10px] font-semibold text-blue-600">Ditinjau</div>
                <div class="text-lg font-bold text-slate-800 mt-0.5">{{ $underReviewCount }}</div>
            </div>
            <div class="p-3 bg-white border border-slate-200/90 rounded-2xl shadow-xs text-center">
                <div class="text-[10px] font-semibold text-emerald-600">Selesai</div>
                <div class="text-lg font-bold text-slate-800 mt-0.5">{{ $resolvedCount }}</div>
            </div>
        </div>

        <!-- Filter Pills -->
        <div class="flex flex-wrap gap-1.5">
            @php
                $statusTabs = [
                    '' => 'Semua Kasus (' . $disputes->total() . ')',
                    'open' => 'Menunggu Mediasi (' . $openCount . ')',
                    'under_review' => 'Sedang Ditinjau (' . $underReviewCount . ')',
                    'resolved' => 'Selesai (' . $resolvedCount . ')',
                    'closed' => 'Ditutup (' . $closedCount . ')',
                ];
            @endphp
            @foreach ($statusTabs as $val => $tabLabel)
                @php
                    $isTabActive = ($currentStatus ?? '') === $val;
                @endphp
                <a
                    href="{{ route('admin.disputes.index', $val ? ['status' => $val] : []) }}"
                    class="px-2.5 py-1 text-[11px] font-semibold rounded-lg transition {{ $isTabActive ? 'bg-teal-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                >
                    {{ $tabLabel }}
                </a>
            @endforeach
        </div>

        <!-- Disputes List -->
        <div class="space-y-3">
            @forelse ($disputes as $dispute)
                @php
                    $badgeStyle = match($dispute->status) {
                        'open' => 'bg-amber-100 text-amber-800 border-amber-200',
                        'under_review' => 'bg-blue-100 text-blue-800 border-blue-200',
                        'resolved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'closed' => 'bg-slate-100 text-slate-700 border-slate-200',
                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                    };
                @endphp
                <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-[10px] text-slate-400 font-mono">
                                #DSP-{{ str_pad($dispute->id, 4, '0', STR_PAD_LEFT) }} · {{ $dispute->created_at->translatedFormat('d M Y, H:i') }}
                            </span>
                            <h3 class="text-xs font-bold text-slate-900 mt-0.5">{{ $dispute->title }}</h3>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $badgeStyle }} shrink-0">
                            {{ $dispute->status_label }}
                        </span>
                    </div>

                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-[11px] space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Barang:</span>
                            <strong class="text-slate-800 truncate">{{ $dispute->product?->title ?? '-' }}</strong>
                        </div>
                        <div class="flex justify-between text-[10px]">
                            <span>Pelapor: <strong class="text-rose-600">{{ $dispute->reporter?->name ?? 'User' }}</strong> ({{ $dispute->reporter?->university?->name ?? '-' }})</span>
                            <span>Terlapor: <strong class="text-slate-800">{{ $dispute->respondent?->name ?? 'User' }}</strong></span>
                        </div>
                        <div class="flex justify-between text-[10px] pt-0.5 text-slate-500">
                            <span>Masalah: <strong class="text-slate-700">{{ $dispute->reason_label }}</strong></span>
                            <span>Tuntutan: <strong class="text-teal-700">{{ $dispute->solution_label }}</strong></span>
                        </div>
                    </div>

                    @if ($dispute->admin_notes)
                        <div class="p-2 bg-emerald-50 border border-emerald-200 rounded-xl text-[11px] text-emerald-900">
                            <strong class="text-[10px] text-emerald-700 block uppercase">Keputusan Mediasi:</strong>
                            <p class="line-clamp-2 mt-0.5">{{ $dispute->admin_notes }}</p>
                        </div>
                    @endif

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] text-slate-400">
                            {{ $dispute->admin ? 'Dimediasi oleh: ' . $dispute->admin->name : 'Belum ada keputusan admin' }}
                        </span>
                        <a
                            href="{{ route('admin.disputes.show', $dispute) }}"
                            class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-xs transition shadow-2xs"
                        >
                            Buka Mediasi →
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center bg-white border border-slate-200/90 rounded-2xl">
                    <p class="text-xs font-semibold text-slate-500">Tidak ada kasus sengketa transaksi pada filter ini.</p>
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

