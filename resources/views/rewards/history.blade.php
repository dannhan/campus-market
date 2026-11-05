@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-16">
    <!-- Top Header Bar -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('rewards.index') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="text-center">
            <div class="text-[10px] font-bold uppercase tracking-wider text-teal-600">Buku Kas Poin</div>
            <h1 class="text-base font-bold text-slate-900 leading-tight">Riwayat Poin Mahasiswa</h1>
        </div>
        <div class="w-9"></div>
    </div>

    <div class="p-5 space-y-4">
        <!-- Balance Summary Header -->
        <div class="bg-gradient-to-r from-teal-700 to-teal-800 text-white rounded-2xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] text-teal-100 block">Saldo Poin Aktif Saat Ini</span>
                <span class="text-2xl font-black font-mono mt-0.5 block">{{ number_format($user->points_balance) }} Poin</span>
            </div>
            <div class="text-right">
                <span class="text-[11px] text-teal-100 block">Akumulasi Lifetime</span>
                <span class="text-sm font-bold font-mono text-amber-300 block">{{ number_format($user->points_lifetime) }} Poin</span>
            </div>
        </div>

        <!-- Ledger Entries -->
        @if ($transactions->isEmpty())
            <div class="p-8 text-center bg-white border border-slate-200 rounded-2xl">
                <span class="text-3xl">📜</span>
                <p class="text-xs font-semibold text-slate-600 mt-2">Belum ada riwayat transaksi poin.</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Lakukan daily check-in atau selesaikan transaksi untuk mulai mengumpulkan poin.</p>
            </div>
        @else
            <div class="bg-white border border-slate-200/90 rounded-2xl divide-y divide-slate-100 shadow-xs overflow-hidden">
                @foreach ($transactions as $tx)
                    <div class="p-3.5 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-800 leading-snug">{{ $tx->description }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-1.5 flex-wrap">
                                <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-medium">{{ $tx->type_label }}</span>
                                <span>•</span>
                                <span>{{ $tx->created_at->translatedFormat('d M Y, H:i') }}</span>
                            </div>
                        </div>

                        <div class="font-mono text-sm font-extrabold shrink-0 {{ $tx->isCredit() ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $tx->amount > 0 ? '+' : '' }}{{ number_format($tx->amount) }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
