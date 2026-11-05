@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-10">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('admin.rewards.index') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Katalog">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="text-center">
            <div class="text-[10px] font-bold uppercase tracking-wider text-teal-600">Admin Panel</div>
            <h1 class="text-base font-bold text-slate-900 leading-tight">Kelola Poin Mahasiswa</h1>
        </div>
        <div class="w-9"></div>
    </div>

    <!-- Sub-navigation for Rewards Section -->
    <div class="px-5 py-2.5 bg-slate-50 border-b border-slate-200/80 flex items-center gap-2 overflow-x-auto text-xs">
        <a href="{{ route('admin.rewards.index') }}" class="px-3 py-1.5 rounded-lg font-medium text-slate-600 hover:bg-slate-200/70 transition shrink-0">
            Katalog Reward
        </a>
        <a href="{{ route('admin.rewards.users') }}" class="px-3 py-1.5 rounded-lg font-bold bg-teal-600 text-white shrink-0 shadow-2xs">
            Kelola Poin Mahasiswa
        </a>
        <a href="{{ route('admin.rewards.redemptions') }}" class="px-3 py-1.5 rounded-lg font-medium text-slate-600 hover:bg-slate-200/70 transition shrink-0">
            Klaim Penukaran
        </a>
        <a href="{{ route('admin.rewards.history') }}" class="px-3 py-1.5 rounded-lg font-medium text-slate-600 hover:bg-slate-200/70 transition shrink-0">
            Audit Mutasi Poin
        </a>
    </div>

    <div class="p-5 space-y-4">
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

        <!-- Search Bar -->
        <form action="{{ route('admin.rewards.users') }}" method="GET" class="flex gap-2">
            <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama atau email mahasiswa..." class="flex-1 px-3.5 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-teal-500 focus:border-teal-500">
            <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl transition">
                Cari
            </button>
            @if ($search)
                <a href="{{ route('admin.rewards.users') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl transition">
                    Reset
                </a>
            @endif
        </form>

        <!-- Users Point Table Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
            <div class="px-4 py-3 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Daftar Mahasiswa & Saldo Poin</span>
                <span class="text-[11px] text-slate-400 font-medium">{{ $users->total() }} akun terdaftar</span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($users as $u)
                    <div class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 hover:bg-slate-50/70 transition">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-slate-900">{{ $u->name }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200">
                                    {{ $u->reward_tier['name'] }}
                                </span>
                                @if ($u->isAdmin())
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-purple-100 text-purple-800">Admin</span>
                                @endif
                            </div>

                            <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2 flex-wrap">
                                <span>{{ $u->email }}</span>
                                @if ($u->student?->university)
                                    <span>•</span>
                                    <span>{{ $u->student->university->name }}</span>
                                @endif
                            </div>

                            <div class="mt-1.5 flex items-center gap-3 text-xs">
                                <span>Saldo: <strong class="font-mono text-teal-700">{{ number_format($u->points_balance) }} Poin</strong></span>
                                <span>•</span>
                                <span>Lifetime: <strong class="font-mono text-slate-700">{{ number_format($u->points_lifetime) }} Poin</strong></span>
                            </div>
                        </div>

                        <!-- Manual Point Adjustment Form -->
                        <div class="pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                            <form action="{{ route('admin.rewards.adjust', $u) }}" method="POST" class="flex items-center gap-1.5 flex-wrap">
                                @csrf
                                <select name="action" class="px-2 py-1.5 text-xs border border-slate-300 rounded-lg bg-white">
                                    <option value="add">+ Tambah Poin</option>
                                    <option value="deduct">- Potong Poin</option>
                                </select>
                                <input type="number" name="points" placeholder="Jumlah" min="1" max="10000" required class="w-20 px-2 py-1.5 text-xs border border-slate-300 rounded-lg">
                                <input type="text" name="reason" placeholder="Alasan (Wajib)" required class="w-36 px-2 py-1.5 text-xs border border-slate-300 rounded-lg">
                                <button type="submit" class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-lg transition shadow-2xs">
                                    Simpan
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-500">
                        Tidak ada mahasiswa yang cocok dengan kriteria pencarian.
                    </div>
                @endforelse
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
