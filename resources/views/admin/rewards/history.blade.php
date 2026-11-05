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
            <h1 class="text-base font-bold text-slate-900 leading-tight">Audit Mutasi Poin Mahasiswa</h1>
        </div>
        <div class="w-9"></div>
    </div>

    <!-- Sub-navigation for Rewards Section -->
    <div class="px-5 py-2.5 bg-slate-50 border-b border-slate-200/80 flex items-center gap-2 overflow-x-auto text-xs">
        <a href="{{ route('admin.rewards.index') }}" class="px-3 py-1.5 rounded-lg font-medium text-slate-600 hover:bg-slate-200/70 transition shrink-0">
            Katalog Reward
        </a>
        <a href="{{ route('admin.rewards.users') }}" class="px-3 py-1.5 rounded-lg font-medium text-slate-600 hover:bg-slate-200/70 transition shrink-0">
            Kelola Poin Mahasiswa
        </a>
        <a href="{{ route('admin.rewards.redemptions') }}" class="px-3 py-1.5 rounded-lg font-medium text-slate-600 hover:bg-slate-200/70 transition shrink-0">
            Klaim Penukaran
        </a>
        <a href="{{ route('admin.rewards.history') }}" class="px-3 py-1.5 rounded-lg font-bold bg-teal-600 text-white shrink-0 shadow-2xs">
            Audit Mutasi Poin
        </a>
    </div>

    <div class="p-5 space-y-4">
        <!-- History Ledger Table -->
        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
            <div class="px-4 py-3 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Buku Kas & Log Mutasi Poin</span>
                <span class="text-[11px] text-slate-400 font-medium">{{ $transactions->total() }} entri log</span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($transactions as $t)
                    <div class="p-4 flex items-center justify-between gap-3 hover:bg-slate-50/70 transition">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-slate-900">{{ $t->description }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                    {{ $t->type_label }}
                                </span>
                            </div>

                            <div class="mt-1 flex items-center gap-2 text-xs text-slate-500 flex-wrap">
                                <span class="font-semibold text-slate-700">Mahasiswa: {{ $t->user->name }} ({{ $t->user->email }})</span>
                                <span>•</span>
                                <span>Waktu: {{ $t->created_at->translatedFormat('d M Y, H:i:s') }}</span>
                                @if ($t->admin)
                                    <span>•</span>
                                    <span class="text-amber-800 font-medium">Oleh Admin: {{ $t->admin->name }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="font-mono text-base font-extrabold shrink-0 {{ $t->isCredit() ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $t->amount > 0 ? '+' : '' }}{{ number_format($t->amount) }}
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-500">
                        Belum ada riwayat mutasi poin tercatat.
                    </div>
                @endforelse
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
