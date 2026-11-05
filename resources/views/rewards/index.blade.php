@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-16">
    <!-- Top Header Bar -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('profile') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Profil">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="text-center">
            <div class="text-[10px] font-bold uppercase tracking-wider text-teal-600">Gamifikasi Mahasiswa</div>
            <h1 class="text-base font-bold text-slate-900 leading-tight">Poin &amp; Reward Mahasiswa</h1>
        </div>
        <a href="{{ route('rewards.history') }}" class="w-9 h-9 -mr-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Riwayat Poin">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </a>
    </div>

    <div class="p-5 space-y-5">
        <!-- Flash Alerts -->
        @if (session('status'))
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-2xl flex items-center gap-2.5 shadow-2xs">
                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs shrink-0 font-bold">✓</span>
                <span class="font-medium leading-relaxed">{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-2xl flex items-center gap-2.5 shadow-2xs">
                <span class="w-6 h-6 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center text-xs shrink-0 font-bold">!</span>
                <span class="font-medium leading-relaxed">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Hero Points & Tier Card -->
        <div class="bg-gradient-to-br from-teal-800 via-teal-700 to-slate-900 text-white rounded-3xl p-5 shadow-lg relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-emerald-400/20 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -left-6 -top-6 w-32 h-32 bg-teal-400/20 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-1">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-semibold text-teal-200 uppercase tracking-wider flex items-center gap-1.5">
                            <span>⭐ Saldo Poin Anda</span>
                        </div>
                        <div class="text-3xl font-black font-mono tracking-tight text-white mt-1">
                            {{ number_format($user->points_balance, 0, ',', '.') }}
                            <span class="text-xs font-sans font-bold text-teal-200 tracking-normal ml-1">POIN</span>
                        </div>
                    </div>

                    <!-- Tier Badge -->
                    <div class="text-right">
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-extrabold bg-white/15 backdrop-blur-md text-amber-200 border border-white/20 shadow-xs">
                            <span>🏅</span>
                            <span>{{ $tier['name'] }}</span>
                        </span>
                        <div class="text-[10px] text-teal-200/90 mt-1">Tier: {{ $tier['badge'] }}</div>
                    </div>
                </div>

                <!-- Progress to Next Tier -->
                <div class="mt-4 pt-4 border-t border-white/15">
                    @if ($tier['next_threshold'])
                        <div class="flex items-center justify-between text-[11px] text-teal-100 mb-1.5 font-medium">
                            <span>Progress ke Level Berikutnya</span>
                            <span class="font-bold font-mono">{{ $tier['points_needed'] }} poin lagi</span>
                        </div>
                        <div class="w-full bg-black/25 rounded-full h-2.5 overflow-hidden p-0.5 border border-white/10">
                            <div class="bg-gradient-to-r from-amber-300 to-emerald-300 h-1.5 rounded-full transition-all duration-500" style="width: {{ $tier['progress_percent'] }}%"></div>
                        </div>
                    @else
                        <div class="flex items-center gap-1.5 text-[11px] text-emerald-200 font-semibold">
                            <span>👑</span>
                            <span>Selamat! Anda telah mencapai level tertinggi Mahasiswa Kampus!</span>
                        </div>
                    @endif
                </div>

                <!-- Daily Check-in Action -->
                <div class="mt-4 pt-3 border-t border-white/15 flex items-center justify-between gap-3">
                    <div class="text-[11px] text-teal-100">
                        <span class="font-bold">Daily Check-in:</span> Dapatkan +10 poin setiap hari saat aktif!
                    </div>
                    @if ($user->canCheckInToday())
                        <form action="{{ route('rewards.checkin') }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3.5 py-1.5 bg-amber-400 hover:bg-amber-300 text-amber-950 font-extrabold text-xs rounded-xl shadow-md transition flex items-center gap-1.5 shrink-0">
                                <span>🎁</span>
                                <span>Klaim +10 Poin</span>
                            </button>
                        </form>
                    @else
                        <span class="px-3 py-1.5 bg-white/20 text-teal-100 font-semibold text-[11px] rounded-xl flex items-center gap-1.5 shrink-0">
                            <span>✓</span>
                            <span>Sudah Klaim Hari Ini</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cara Mendapatkan Poin Info -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs">
            <h2 class="text-xs font-bold text-slate-800 mb-2.5 flex items-center gap-1.5">
                <span class="text-teal-600">💡</span>
                <span>Cara Mengumpulkan Poin Mahasiswa Aktif</span>
            </h2>
            <div class="grid grid-cols-2 gap-2 text-[11px]">
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="font-bold text-slate-700 flex items-center gap-1">
                        <span class="text-teal-600">+40 Poin</span>
                    </div>
                    <div class="text-slate-500 text-[10px] mt-0.5">Selesaikan transaksi jual-beli dengan sukses</div>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="font-bold text-slate-700 flex items-center gap-1">
                        <span class="text-teal-600">+15 Poin</span>
                    </div>
                    <div class="text-slate-500 text-[10px] mt-0.5">Beri ulasan & rating jujur untuk transaksi</div>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="font-bold text-slate-700 flex items-center gap-1">
                        <span class="text-teal-600">+10 Poin</span>
                    </div>
                    <div class="text-slate-500 text-[10px] mt-0.5">Daily Check-in rutin setiap hari</div>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="font-bold text-slate-700 flex items-center gap-1">
                        <span class="text-teal-600">+25 Poin</span>
                    </div>
                    <div class="text-slate-500 text-[10px] mt-0.5">Bantu teman lewat tawaran di Papan Cari Barang</div>
                </div>
            </div>
        </div>

        <!-- Voucher / Reward Aktif Saya -->
        @if ($activeRedemptions->isNotEmpty())
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Reward & Voucher Saya</h2>
                    <span class="text-[11px] font-semibold text-teal-600">{{ $activeRedemptions->count() }} voucher</span>
                </div>

                <div class="space-y-2.5">
                    @foreach ($activeRedemptions as $redemption)
                        <div class="p-3.5 bg-gradient-to-r from-teal-50/70 to-emerald-50/70 border border-teal-200/90 rounded-2xl flex items-center justify-between gap-3 shadow-2xs">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-md bg-teal-600 text-white font-bold text-[10px] uppercase">
                                        {{ $redemption->rewardItem->type_label }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-800 truncate">{{ $redemption->rewardItem->title }}</span>
                                </div>
                                <div class="mt-1.5 flex items-center gap-2">
                                    <span class="font-mono text-xs font-black bg-white px-2 py-0.5 rounded-lg border border-teal-200 text-teal-800 tracking-wider">
                                        {{ $redemption->voucher_code }}
                                    </span>
                                    @if ($redemption->expires_at)
                                        <span class="text-[10px] text-slate-400">
                                            Berlaku s.d {{ $redemption->expires_at->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <span class="inline-block text-[10px] font-bold px-2 py-1 rounded-full {{ $redemption->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $redemption->status_label }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Katalog Penukaran Reward Mahasiswa -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Katalog Penukaran Reward</h2>
                    <div class="text-[11px] text-slate-400">Tukarkan poin aktif Anda dengan voucher dan hadiah eksklusif</div>
                </div>
            </div>

            @if ($rewardItems->isEmpty())
                <div class="p-8 text-center bg-white border border-slate-200 rounded-2xl">
                    <span class="text-3xl">🎁</span>
                    <p class="text-xs font-semibold text-slate-600 mt-2">Belum ada item reward yang tersedia saat ini.</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Administrator sedang menyiapkan penawaran reward menarik untuk mahasiswa.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($rewardItems as $item)
                        @php
                            $canAfford = $user->points_balance >= $item->points_cost;
                        @endphp
                        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs flex flex-col justify-between hover:border-teal-300 transition">
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                                        {{ $item->type_label }}
                                    </span>
                                    <span class="text-xs font-black font-mono text-teal-700 bg-teal-50 px-2.5 py-0.5 rounded-full border border-teal-100">
                                        {{ number_format($item->points_cost) }} Poin
                                    </span>
                                </div>

                                <h3 class="text-sm font-bold text-slate-900 mt-2 leading-snug">{{ $item->title }}</h3>

                                @if ($item->description)
                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $item->description }}</p>
                                @endif

                                <div class="mt-2.5 flex items-center gap-3 text-[11px] text-slate-400">
                                    @if ($item->discount_amount > 0)
                                        <span class="font-semibold text-slate-600">Hemat: {{ $item->formatted_discount }}</span>
                                    @endif
                                    @if (! $item->isUnlimitedStock())
                                        <span>Sisa: <strong class="text-slate-600 font-mono">{{ $item->stock }}</strong> kuota</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-100">
                                @if ($canAfford)
                                    <form action="{{ route('rewards.redeem', $item) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menukarkan {{ number_format($item->points_cost) }} poin untuk reward ini?');">
                                        @csrf
                                        <button type="submit" class="w-full py-2 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-1.5">
                                            <span>Tukar Sekarang</span>
                                            <span>→</span>
                                        </button>
                                    </form>
                                @else
                                    <div class="text-center py-1.5 bg-slate-50 rounded-xl border border-slate-200/80 text-[11px] text-slate-400 font-medium">
                                        Kurang {{ number_format($item->points_cost - $user->points_balance) }} poin lagi
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Riwayat Poin Ringkas -->
        @if ($recentTransactions->isNotEmpty())
            <div class="space-y-2.5 pt-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Mutasi Poin Terbaru</h2>
                    <a href="{{ route('rewards.history') }}" class="text-[11px] font-bold text-teal-600 hover:underline">Lihat Semua →</a>
                </div>

                <div class="bg-white border border-slate-200/90 rounded-2xl divide-y divide-slate-100 shadow-xs overflow-hidden">
                    @foreach ($recentTransactions as $tx)
                        <div class="p-3.5 flex items-center justify-between gap-3">
                            <div>
                                <div class="text-xs font-bold text-slate-800">{{ $tx->description }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $tx->type_label }} • {{ $tx->created_at->translatedFormat('d M Y, H:i') }}
                                </div>
                            </div>
                            <div class="font-mono text-xs font-extrabold {{ $tx->isCredit() ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $tx->amount > 0 ? '+' : '' }}{{ number_format($tx->amount) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
