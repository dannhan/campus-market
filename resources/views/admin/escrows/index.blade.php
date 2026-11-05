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
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">Panel Administrator</span>
            <h1 class="text-base font-bold text-slate-900 leading-tight mt-0.5">Rekening Bersama (Escrow)</h1>
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
        @if (session('error'))
            <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Summary Metric Cards -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-2xl p-4 shadow-xs">
                <span class="text-[10px] uppercase font-bold tracking-wider text-blue-100 block">Dana Diamankan (Holding)</span>
                <div class="text-lg font-extrabold font-mono mt-1">Rp {{ number_format($totalHeld, 0, ',', '.') }}</div>
                <span class="text-[10px] text-blue-200 mt-0.5 block">{{ $heldCount }} transaksi aktif tertahan</span>
            </div>

            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white rounded-2xl p-4 shadow-xs">
                <span class="text-[10px] uppercase font-bold tracking-wider text-emerald-100 block">Total Dana Dicairkan</span>
                <div class="text-lg font-extrabold font-mono mt-1">Rp {{ number_format($totalReleased, 0, ',', '.') }}</div>
                <span class="text-[10px] text-emerald-200 mt-0.5 block">Sukses diteruskan ke penjual</span>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-2 text-center text-xs">
            <div class="bg-white border border-slate-200 p-2.5 rounded-xl shadow-2xs">
                <span class="text-[10px] text-slate-400 block font-semibold">Menunggu Setor</span>
                <strong class="text-amber-600 font-mono text-sm block mt-0.5">{{ $pendingCount }}</strong>
            </div>
            <div class="bg-white border border-slate-200 p-2.5 rounded-xl shadow-2xs">
                <span class="text-[10px] text-slate-400 block font-semibold">Dana Refund</span>
                <strong class="text-purple-600 font-mono text-sm block mt-0.5">Rp {{ number_format($totalRefunded, 0, ',', '.') }}</strong>
            </div>
            <div class="bg-white border border-slate-200 p-2.5 rounded-xl shadow-2xs">
                <span class="text-[10px] text-slate-400 block font-semibold">Kasus Dibekukan</span>
                <strong class="text-rose-600 font-mono text-sm block mt-0.5">{{ $disputedCount }}</strong>
            </div>
        </div>

        <!-- Filter Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
            <a
                href="{{ route('admin.escrows.index') }}"
                class="px-3 py-1.5 rounded-full border transition shrink-0 {{ empty($statusFilter) ? 'bg-slate-900 text-white border-slate-900 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300' }}"
            >
                Semua
            </a>
            @foreach ($statuses as $sKey => $sLabel)
                <a
                    href="{{ route('admin.escrows.index', ['status' => $sKey]) }}"
                    class="px-3 py-1.5 rounded-full border transition shrink-0 {{ $statusFilter === $sKey ? 'bg-slate-900 text-white border-slate-900 font-bold' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300' }}"
                >
                    {{ $sLabel }}
                </a>
            @endforeach
        </div>

        <!-- Escrow List -->
        <div class="space-y-3">
            @forelse ($escrows as $escrow)
                <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 text-xs">
                        <div>
                            <span class="font-mono font-bold text-slate-800 text-[11px] block">{{ $escrow->escrow_code }}</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $escrow->transaction->transaction_code }}</span>
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full border {{ $escrow->status_badge_class }}">
                            {{ $escrow->status_label }}
                        </span>
                    </div>

                    <!-- Product & Parties -->
                    <div class="flex items-start gap-3">
                        <img src="{{ $escrow->transaction->product->cover_image_url }}" alt="{{ $escrow->transaction->product->title }}" class="w-12 h-12 rounded-xl object-cover border border-slate-100 shrink-0">
                        <div class="min-w-0 flex-1 text-xs">
                            <h2 class="font-bold text-slate-900 truncate">{{ $escrow->transaction->product->title }}</h2>
                            <div class="text-[11px] text-slate-500 mt-0.5 space-y-0.5">
                                <div>Pembeli: <strong class="text-slate-800">{{ $escrow->transaction->buyer->name }}</strong> ({{ $escrow->transaction->buyer->student?->university?->name ?? 'Kampus' }})</div>
                                <div>Penjual: <strong class="text-slate-800">{{ $escrow->transaction->seller->name }}</strong></div>
                            </div>
                        </div>
                    </div>

                    <!-- Holding Account & Amount Details -->
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 text-xs space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Kanal Rekber:</span>
                            <span class="font-semibold text-slate-800 uppercase">{{ $escrow->holding_channel }} ({{ $escrow->holding_account_number }})</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Nominal Barang:</span>
                            <span class="font-mono">{{ $escrow->formatted_amount }}</span>
                        </div>
                        @if ($escrow->shipping_cost > 0)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Ongkir Kurir:</span>
                                <span class="font-mono">Rp {{ number_format((float) $escrow->shipping_cost, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between pt-1 border-t border-slate-200/60 font-bold text-slate-900">
                            <span>Total Dana Rekber:</span>
                            <span class="font-mono text-teal-700 font-extrabold text-sm">{{ $escrow->formatted_total_amount }}</span>
                        </div>
                    </div>

                    <!-- Deposit Proof -->
                    @if ($escrow->deposit_proof)
                        <div class="p-2.5 bg-blue-50/70 border border-blue-100 rounded-xl flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="text-base">🧾</span>
                                <div>
                                    <span class="text-[10px] font-bold text-blue-900 block">Bukti Setoran Pembeli</span>
                                    <span class="text-[10px] text-blue-700 font-mono">{{ $escrow->deposited_at?->translatedFormat('d M, H:i') }} WIB</span>
                                </div>
                            </div>
                            <a href="{{ $escrow->deposit_proof_url }}" target="_blank" class="px-2.5 py-1 bg-white border border-blue-200 text-blue-800 font-semibold text-[11px] rounded-lg shadow-2xs hover:bg-blue-50 transition">
                                Cek Foto Struk
                            </a>
                        </div>
                    @endif

                    <!-- Admin Action Buttons -->
                    <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-2 text-xs">
                        <a href="{{ route('transactions.show', $escrow->transaction) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition">
                            Lihat Transaksi
                        </a>

                        @if ($escrow->status === \App\Models\EscrowPayment::STATUS_PENDING_DEPOSIT && $escrow->deposit_proof)
                            <!-- Verify Deposit Button -->
                            <form method="POST" action="{{ route('admin.escrows.verify', $escrow) }}" class="contents">
                                @csrf
                                <button
                                    type="submit"
                                    onclick="return confirm('Verifikasi bahwa mutasi dana pembeli telah masuk ke rekening rekber resmi?')"
                                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-2xs transition"
                                >
                                    Verifikasi Dana Masuk (Hold)
                                </button>
                            </form>
                        @endif

                        @if ($escrow->status === \App\Models\EscrowPayment::STATUS_HELD)
                            <!-- Release Funds Button -->
                            <form method="POST" action="{{ route('admin.escrows.release', $escrow) }}" class="contents">
                                @csrf
                                <button
                                    type="submit"
                                    onclick="return confirm('Cairkan dana rekber ini dan teruskan ke rekening penjual?')"
                                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-2xs transition"
                                >
                                    Cairkan ke Penjual
                                </button>
                            </form>

                            <!-- Refund Funds Button -->
                            <form method="POST" action="{{ route('admin.escrows.refund', $escrow) }}" class="contents">
                                @csrf
                                <button
                                    type="submit"
                                    onclick="return confirm('Kembalikan dana rekber ini kepada pembeli?')"
                                    class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg shadow-2xs transition"
                                >
                                    Refund ke Pembeli
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-12 text-center bg-white border border-slate-200/90 rounded-2xl p-6">
                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-xl mb-3">
                        🛡️
                    </div>
                    <p class="text-xs font-bold text-slate-700">Tidak ada data rekening bersama.</p>
                    <p class="text-[11px] text-slate-400 mt-1">Pembayaran melalui rekening bersama akan tercatat di sini secara otomatis.</p>
                </div>
            @endforelse

            {{ $escrows->links() }}
        </div>
    </div>
</div>
@endsection
