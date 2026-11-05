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
            <h1 class="text-base font-bold text-slate-900 leading-tight">Klaim Penukaran Reward</h1>
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
        <a href="{{ route('admin.rewards.redemptions') }}" class="px-3 py-1.5 rounded-lg font-bold bg-teal-600 text-white shrink-0 shadow-2xs">
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

        <!-- Filter Status Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto text-xs">
            <a href="{{ route('admin.rewards.redemptions') }}" class="px-3 py-1.5 rounded-full font-bold {{ empty($statusFilter) ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua Status
            </a>
            <a href="{{ route('admin.rewards.redemptions', ['status' => 'active']) }}" class="px-3 py-1.5 rounded-full font-bold {{ $statusFilter === 'active' ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Aktif
            </a>
            <a href="{{ route('admin.rewards.redemptions', ['status' => 'fulfilled']) }}" class="px-3 py-1.5 rounded-full font-bold {{ $statusFilter === 'fulfilled' ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Terpenuhi
            </a>
            <a href="{{ route('admin.rewards.redemptions', ['status' => 'used']) }}" class="px-3 py-1.5 rounded-full font-bold {{ $statusFilter === 'used' ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Digunakan
            </a>
        </div>

        <!-- Redemptions List Table -->
        <div class="bg-white border border-slate-200/90 rounded-2xl shadow-xs overflow-hidden">
            <div class="px-4 py-3 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Daftar Klaim & Penukaran Reward</span>
                <span class="text-[11px] text-slate-400 font-medium">{{ $redemptions->total() }} klaim</span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($redemptions as $r)
                    <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/70 transition">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-slate-900">{{ $r->rewardItem->title }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ $r->rewardItem->type_label }}
                                </span>
                            </div>

                            <div class="mt-1 flex items-center gap-2 flex-wrap text-xs text-slate-500">
                                <span class="font-bold text-slate-700">Mahasiswa: {{ $r->user->name }}</span>
                                <span>•</span>
                                <span>Kode: <strong class="font-mono text-teal-800 bg-teal-50 px-1.5 py-0.5 rounded border border-teal-200">{{ $r->voucher_code }}</strong></span>
                                <span>•</span>
                                <span>Biaya: <strong class="font-mono text-slate-700">{{ number_format($r->points_spent) }} Poin</strong></span>
                                <span>•</span>
                                <span>Tanggal: {{ $r->created_at->translatedFormat('d M Y, H:i') }}</span>
                            </div>

                            @if ($r->admin_notes)
                                <div class="mt-1 text-[11px] text-slate-500 italic">
                                    Catatan Admin: {{ $r->admin_notes }}
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $r->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($r->status === 'fulfilled' ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-600') }}">
                                {{ $r->status_label }}
                            </span>

                            @if ($r->status === 'active')
                                <form action="{{ route('admin.rewards.redemptions.fulfill', $r) }}" method="POST" onsubmit="return confirm('Tandai klaim reward ini sebagai terpenuhi?');">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-lg transition shadow-2xs">
                                        Tandai Terpenuhi
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-500">
                        Belum ada klaim reward yang sesuai.
                    </div>
                @endforelse
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $redemptions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
