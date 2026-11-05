@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-10">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('profile') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Profil">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="text-center">
            <div class="text-[10px] font-bold uppercase tracking-wider text-teal-600">Admin Panel</div>
            <h1 class="text-base font-bold text-slate-900 leading-tight">Kelola Reward &amp; Poin</h1>
        </div>
        <a href="{{ route('admin.rewards.create') }}" class="w-9 h-9 -mr-1 rounded-full bg-teal-600 hover:bg-teal-700 text-white flex items-center justify-center transition shadow-xs" title="Tambah Item Reward">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
        </a>
    </div>

    <!-- Navigation Tabs -->
    <div class="px-5 pt-3 bg-white border-b border-slate-100 flex items-center gap-2 overflow-x-auto">
        <a href="{{ route('admin.categories.index') }}" class="pb-2.5 text-xs font-medium text-slate-400 hover:text-slate-600 transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span>Kategori</span>
        </a>
        <a href="{{ route('admin.reports.index') }}" class="pb-2.5 text-xs font-medium text-slate-400 hover:text-slate-600 transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            <span>Laporan</span>
        </a>
        <a href="{{ route('admin.disputes.index') }}" class="pb-2.5 text-xs font-medium text-slate-400 hover:text-slate-600 transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Mediasi</span>
        </a>
        <a href="{{ route('admin.escrows.index') }}" class="pb-2.5 text-xs font-medium text-slate-400 hover:text-slate-600 transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            <span>Rekber</span>
        </a>
        <a href="{{ route('admin.rewards.index') }}" class="pb-2.5 text-xs font-bold text-teal-700 border-b-2 border-teal-600 flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Reward & Poin</span>
        </a>
    </div>

    <!-- Sub-navigation for Rewards Section -->
    <div class="px-5 py-2.5 bg-slate-50 border-b border-slate-200/80 flex items-center gap-2 overflow-x-auto text-xs">
        <a href="{{ route('admin.rewards.index') }}" class="px-3 py-1.5 rounded-lg font-bold bg-teal-600 text-white shrink-0 shadow-2xs">
            Katalog Reward
        </a>
        <a href="{{ route('admin.rewards.users') }}" class="px-3 py-1.5 rounded-lg font-medium text-slate-600 hover:bg-slate-200/70 transition shrink-0">
            Kelola Poin Mahasiswa
        </a>
        <a href="{{ route('admin.rewards.redemptions') }}" class="px-3 py-1.5 rounded-lg font-medium text-slate-600 hover:bg-slate-200/70 transition shrink-0">
            Klaim Penukaran
        </a>
        <a href="{{ route('admin.rewards.history') }}" class="px-3 py-1.5 rounded-lg font-medium text-slate-600 hover:bg-slate-200/70 transition shrink-0">
            Audit Mutasi Poin
        </a>
    </div>

    <div class="p-5 space-y-5">
        <!-- Flash Alerts -->
        @if (session('status'))
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Metric Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-gradient-to-br from-teal-700 to-teal-900 text-white rounded-2xl p-4 shadow-xs">
                <span class="text-[10px] uppercase font-bold tracking-wider text-teal-200 block">Poin Beredar</span>
                <div class="text-xl font-extrabold font-mono mt-1">{{ number_format($totalCirculating) }}</div>
                <span class="text-[10px] text-teal-200 mt-0.5 block">{{ $usersWithPointsCount }} mahasiswa berpoin</span>
            </div>

            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs">
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Total Diberikan</span>
                <div class="text-xl font-extrabold font-mono text-emerald-600 mt-1">+{{ number_format($totalEarned) }}</div>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Dari aktivitas kampus</span>
            </div>

            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs">
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Total Ditukarkan</span>
                <div class="text-xl font-extrabold font-mono text-rose-600 mt-1">-{{ number_format($totalSpent) }}</div>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Melalui klaim reward</span>
            </div>

            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs">
                <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block">Total Klaim</span>
                <div class="text-xl font-extrabold font-mono text-slate-800 mt-1">{{ number_format($totalRedemptionsCount) }}</div>
                <span class="text-[10px] text-slate-400 mt-0.5 block">Voucher & hadiah</span>
            </div>
        </div>

        <!-- Catalog Management Table -->
        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Katalog Item Reward Mahasiswa</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Daftar reward yang dapat ditukarkan mahasiswa dengan poin aktif</p>
                </div>
                <a href="{{ route('admin.rewards.create') }}" class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Reward</span>
                </a>
            </div>

            @if ($rewardItems->isEmpty())
                <div class="p-8 text-center">
                    <span class="text-3xl">🎁</span>
                    <p class="text-xs font-semibold text-slate-600 mt-2">Katalog reward masih kosong.</p>
                    <a href="{{ route('admin.rewards.create') }}" class="inline-block mt-3 px-4 py-2 bg-teal-600 text-white text-xs font-bold rounded-xl">
                        + Tambah Reward Pertama
                    </a>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($rewardItems as $item)
                        <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/70 transition">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ $item->type_label }}
                                    </span>
                                    <span class="font-bold text-sm text-slate-900">{{ $item->title }}</span>
                                    @if ($item->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">Nonaktif</span>
                                    @endif
                                </div>

                                <div class="mt-1 flex items-center gap-3 text-xs text-slate-500 flex-wrap">
                                    <span class="font-mono font-bold text-teal-700">{{ number_format($item->points_cost) }} Poin</span>
                                    <span>•</span>
                                    <span>Potongan: <strong>{{ $item->formatted_discount }}</strong></span>
                                    <span>•</span>
                                    <span>Stok: <strong>{{ $item->isUnlimitedStock() ? 'Unlimited' : $item->stock . ' kuota' }}</strong></span>
                                    <span>•</span>
                                    <span>Ditukar: <strong>{{ $item->redemptions_count }}x</strong></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <form action="{{ route('admin.rewards.toggle', $item) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 transition" title="Ubah status">
                                        {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>

                                <a href="{{ route('admin.rewards.edit', $item) }}" class="px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-teal-50 text-teal-800 border border-teal-200 hover:bg-teal-100 transition">
                                    Edit
                                </a>

                                <form action="{{ route('admin.rewards.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus item reward ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $rewardItems->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
