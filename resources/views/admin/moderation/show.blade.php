@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-16">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('admin.moderation.index') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Daftar Moderasi">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="text-center">
            <div class="text-[10px] font-bold uppercase tracking-wider text-teal-600">Pemeriksaan Produk</div>
            <h1 class="text-base font-bold text-slate-900 leading-tight">Detail Moderasi</h1>
        </div>
        <div class="w-9"></div>
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

        <!-- Status & Badge Banner -->
        <div class="p-4 rounded-2xl border flex items-center justify-between gap-3 {{ $product->isRejected() ? 'bg-rose-50 border-rose-200' : ($product->is_verified ? 'bg-emerald-50 border-emerald-200' : ($product->isFlagged() ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-200')) }}">
            <div>
                <div class="text-[10px] uppercase font-bold tracking-wider text-slate-500">Status Moderasi Saat Ini</div>
                <div class="text-sm font-extrabold mt-0.5 {{ $product->isRejected() ? 'text-rose-700' : ($product->is_verified ? 'text-emerald-700' : ($product->isFlagged() ? 'text-amber-700' : 'text-slate-800')) }}">
                    {{ $product->moderation_status_label }}
                    @if ($product->is_verified)
                        • ✓ Terverifikasi Resmi Kampus
                    @endif
                </div>
                @if ($product->moderation_notes)
                    <p class="text-xs text-rose-700 mt-1">Alasan: <strong>{{ $product->moderation_notes }}</strong></p>
                @endif
            </div>

            <div class="shrink-0">
                <a href="{{ route('products.show', $product) }}" target="_blank" class="px-3 py-1.5 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    Lihat di Etalase ↗
                </a>
            </div>
        </div>

        <!-- Product Image & Information Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs space-y-4">
            <!-- Images Carousel / Grid -->
            <div>
                <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">Foto Barang Unggahan Penjual</h2>
                <div class="flex items-center gap-3 overflow-x-auto pb-2">
                    @forelse ($product->images as $img)
                        <img src="{{ $img->url }}" alt="{{ $product->title }}" class="w-28 h-28 rounded-xl object-cover shrink-0 border border-slate-200 bg-slate-100">
                    @empty
                        <img src="{{ $product->cover_image_url }}" alt="{{ $product->title }}" class="w-28 h-28 rounded-xl object-cover shrink-0 border border-slate-200 bg-slate-100">
                    @endforelse
                </div>
            </div>

            <!-- Details -->
            <div class="pt-3 border-t border-slate-100 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-100">
                        {{ $product->category?->name }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                        Kondisi: {{ $product->condition_label }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                        Tipe: {{ $product->transaction_type_label }}
                    </span>
                </div>

                <h1 class="text-base font-bold text-slate-900 leading-snug">{{ $product->title }}</h1>
                <div class="text-xl font-black font-mono text-teal-800">{{ $product->formatted_price }}</div>

                @if ($product->description)
                    <div class="pt-2">
                        <span class="text-[11px] font-bold text-slate-400 block uppercase">Deskripsi Produk</span>
                        <p class="text-xs text-slate-700 mt-1 leading-relaxed whitespace-pre-line">{{ $product->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Seller Credibility Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Informasi Kredibilitas Penjual</h2>

            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-teal-100 text-teal-800 flex items-center justify-center font-bold text-sm shrink-0">
                    {{ substr($product->user?->name ?? 'M', 0, 2) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm text-slate-900">{{ $product->user?->name }}</span>
                        @if ($product->user?->isVerifiedStudent())
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-teal-100 text-teal-800">Mahasiswa Terverifikasi</span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-500 mt-0.5">
                        {{ $product->user?->email }} • Status: <strong class="text-slate-700">{{ ucfirst($product->user?->student_status ?? 'Aktif') }}</strong>
                    </div>
                    @if ($product->user?->student?->university)
                        <div class="text-xs text-slate-500">
                            {{ $product->user->student->university->name }}
                            @if ($product->user->student->major)
                                • Jurusan {{ $product->user->student->major }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-3 gap-2 text-center text-xs">
                <div class="p-2 rounded-xl bg-slate-50">
                    <span class="text-[10px] text-slate-400 block">Rating Penjual</span>
                    <span class="font-bold text-slate-800 font-mono mt-0.5 block">★ {{ $product->user?->seller_rating ?? '5.0' }}</span>
                </div>
                <div class="p-2 rounded-xl bg-slate-50">
                    <span class="text-[10px] text-slate-400 block">Total Ulasan</span>
                    <span class="font-bold text-slate-800 font-mono mt-0.5 block">{{ $product->user?->seller_review_count ?? 0 }}</span>
                </div>
                <div class="p-2 rounded-xl bg-slate-50">
                    <span class="text-[10px] text-slate-400 block">Produk Terdaftar</span>
                    <span class="font-bold text-slate-800 font-mono mt-0.5 block">{{ $product->user?->products()->count() ?? 1 }}</span>
                </div>
            </div>
        </div>

        <!-- Student Violation Reports Section -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Laporan Pengaduan Mahasiswa</h2>
                    <p class="text-[11px] text-slate-400">Pengaduan yang diajukan oleh pengguna terkait produk ini</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $product->reports->isEmpty() ? 'bg-slate-100 text-slate-500' : 'bg-rose-100 text-rose-800' }}">
                    {{ $product->reports->count() }} Laporan
                </span>
            </div>

            @if ($product->reports->isEmpty())
                <div class="p-4 text-center text-xs text-slate-500 bg-slate-50 rounded-xl">
                    Belum ada pengaduan atau indikasi penipuan yang dilaporkan untuk produk ini.
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($product->reports as $report)
                        <div class="p-3.5 rounded-xl border border-rose-100 bg-rose-50/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex-1 min-w-0 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-rose-900">{{ $report->reason_label }}</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full {{ $report->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $report->status_label }}
                                    </span>
                                </div>

                                @if ($report->details)
                                    <p class="text-slate-700 mt-1 italic">"{{ $report->details }}"</p>
                                @endif

                                <div class="mt-1 text-[10px] text-slate-400">
                                    Dilaporkan oleh: <strong>{{ $report->reporter?->name }}</strong> • {{ $report->created_at->translatedFormat('d M Y, H:i') }}
                                </div>
                            </div>

                            @if ($report->status === 'pending')
                                <form action="{{ route('admin.moderation.reports.dismiss', [$product, $report]) }}" method="POST" class="shrink-0">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-600 font-semibold text-xs rounded-lg transition shadow-2xs">
                                        Abaikan Laporan
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Moderation Actions Execution Panel -->
        <div class="bg-gradient-to-br from-slate-900 to-teal-950 text-white rounded-2xl p-5 shadow-md space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-teal-200">Panel Tindakan Moderasi</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Verify / Unverify -->
                @if (! $product->is_verified)
                    <form action="{{ route('admin.moderation.verify', $product) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-1.5">
                            <span>✓</span>
                            <span>Sematkan Badge Terverifikasi</span>
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.moderation.unverify', $product) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 font-bold text-xs rounded-xl transition">
                            Cabut Badge Verifikasi
                        </button>
                    </form>
                @endif

                <!-- Approve -->
                @if ($product->isRejected() || $product->isFlagged() || $product->isPendingModeration())
                    <form action="{{ route('admin.moderation.approve', $product) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2.5 px-4 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                            Setujui &amp; Tayangkan Produk
                        </button>
                    </form>
                @endif
            </div>

            <!-- Reject Form -->
            <div class="pt-3 border-t border-white/10">
                <form action="{{ route('admin.moderation.reject', $product) }}" method="POST" class="space-y-2">
                    @csrf
                    <label for="reject_reason" class="text-xs font-bold text-rose-300 block">Tolak / Tangguhkan Produk (Cabut dari Etalase)</label>
                    <div class="flex gap-2">
                        <input type="text" id="reject_reason" name="reason" value="{{ old('reason', $product->moderation_notes) }}" placeholder="Alasan penolakan / indikasi penipuan..." required class="flex-1 px-3 py-2 text-xs rounded-xl text-slate-900 bg-white border border-rose-300 focus:ring-1 focus:ring-rose-500">
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl transition shadow-xs shrink-0">
                            Tolak Produk
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danger Zone: Delete Permanently -->
            <div class="pt-3 border-t border-white/10 flex items-center justify-between">
                <span class="text-xs text-slate-400">Hapus permanen produk berbahaya atau melanggar fatal:</span>
                <form action="{{ route('admin.moderation.destroy', $product) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini secara permanen dari basis data?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 bg-rose-950/60 hover:bg-rose-900 text-rose-200 border border-rose-800 text-xs font-bold rounded-xl transition">
                        Hapus Permanen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
