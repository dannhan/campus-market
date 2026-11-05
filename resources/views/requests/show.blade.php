@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-14">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('requests.index') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Papan Permintaan">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="text-center">
            <h1 class="text-base font-bold text-slate-900 leading-tight">Detail Permintaan Barang</h1>
            <span class="text-[10px] text-teal-700 font-semibold uppercase tracking-wider">Wanted Board</span>
        </div>
        <div class="w-9"></div>
    </div>

    <div class="p-5 space-y-4">
        <!-- Flash Alerts -->
        @if (session('status'))
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @php
            $urgencyBadge = match($itemRequest->urgency) {
                'urgent' => 'bg-rose-50 text-rose-700 border-rose-200',
                'low' => 'bg-slate-100 text-slate-600 border-slate-200',
                default => 'bg-amber-50 text-amber-700 border-amber-200',
            };
            $statusBadge = match($itemRequest->status) {
                'fulfilled' => 'bg-blue-50 text-blue-700 border-blue-200',
                'closed' => 'bg-slate-100 text-slate-600 border-slate-200',
                default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            };
        @endphp

        <!-- Main Request Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs space-y-4">
            <!-- Badges & Stats -->
            <div class="flex items-center justify-between gap-2 pb-3 border-b border-slate-100 text-[11px]">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="font-bold px-2.5 py-0.5 rounded-full border {{ $statusBadge }}">
                        {{ $itemRequest->status_label }}
                    </span>
                    @if ($itemRequest->urgency === 'urgent')
                        <span class="font-extrabold px-2.5 py-0.5 rounded-full border {{ $urgencyBadge }} flex items-center gap-1">
                            <span>⚡</span>
                            <span>Mendesak</span>
                        </span>
                    @endif
                    <span class="text-slate-300">&bull;</span>
                    <span class="text-slate-600 font-medium">{{ $itemRequest->category?->name ?? 'Kategori' }}</span>
                </div>
                <span class="text-slate-400 shrink-0">{{ $itemRequest->created_at->diffForHumans() }}</span>
            </div>

            <!-- Title & Budget -->
            <div>
                <h2 class="text-base font-extrabold text-slate-900 leading-snug">
                    {{ $itemRequest->title }}
                </h2>
                <div class="mt-2 p-3 bg-teal-50/70 border border-teal-100 rounded-xl flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-teal-800 uppercase block tracking-wider">Budget Pembeli</span>
                        <span class="text-sm font-black text-teal-900 font-mono mt-0.5 block">{{ $itemRequest->formatted_budget }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block tracking-wider">Kondisi yang Dicari</span>
                        <span class="text-xs font-bold text-slate-700 mt-0.5 block">{{ $itemRequest->condition_label }}</span>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div>
                <h3 class="text-xs font-bold text-slate-800 mb-1">Rincian Kebutuhan:</h3>
                <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line bg-slate-50/80 p-3 rounded-xl border border-slate-100">
                    {{ $itemRequest->description }}
                </p>
            </div>

            <!-- Requester Identity Card -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <img
                        src="{{ $itemRequest->user->avatar_url }}"
                        alt="{{ $itemRequest->user->name }}"
                        class="w-9 h-9 rounded-full object-cover border border-slate-200 shrink-0"
                    >
                    <div class="min-w-0">
                        <div class="flex items-center gap-1">
                            <span class="font-bold text-slate-900 text-xs truncate">{{ $itemRequest->user->name }}</span>
                            @if ($itemRequest->user->is_student_verified)
                                <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 text-white inline-flex items-center justify-center text-[9px] font-black" title="Terverifikasi">✓</span>
                            @endif
                        </div>
                        <span class="text-[11px] text-slate-400 block truncate">
                            {{ $itemRequest->university?->name ?? 'Mahasiswa' }}
                            @if ($itemRequest->user->student?->major)
                                &bull; {{ $itemRequest->user->student->major }}
                            @endif
                        </span>
                    </div>
                </div>

                @if (! $isOwner && auth()->check())
                    <a href="{{ route('chats.index') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition flex items-center gap-1 shrink-0" title="Hubungi Pembeli">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Chat</span>
                    </a>
                @endif
            </div>

            <!-- Owner Action Buttons -->
            @if ($isOwner)
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <span class="text-[11px] font-bold text-slate-500 block uppercase tracking-wider">Aksi Pembuat Permintaan:</span>

                    <div class="grid grid-cols-2 gap-2">
                        @if ($itemRequest->isOpen())
                            <form method="POST" action="{{ route('requests.close', $itemRequest) }}">
                                @csrf
                                <input type="hidden" name="status" value="fulfilled">
                                <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center justify-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Sudah Ditemukan</span>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('requests.close', $itemRequest) }}">
                                @csrf
                                <input type="hidden" name="status" value="closed">
                                <button type="submit" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                                    Tutup Permintaan
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('requests.reopen', $itemRequest) }}">
                                @csrf
                                <button type="submit" class="w-full py-2 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center justify-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Buka Kembali Permintaan</span>
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('requests.edit', $itemRequest) }}" class="text-xs font-semibold text-slate-600 hover:text-teal-700 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Edit Permintaan</span>
                        </a>

                        <form method="POST" action="{{ route('requests.destroy', $itemRequest) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus postingan permintaan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                                Hapus Permintaan
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <!-- Offer Form: For Potential Sellers (Non-Owners) when Request is Open -->
        @if (! $isOwner && $itemRequest->isOpen())
            <div class="bg-white border border-teal-200 rounded-2xl p-5 shadow-xs space-y-4">
                <div class="flex items-center gap-2 text-teal-800">
                    <span class="w-7 h-7 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-sm">🤝</span>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">Punya Barang Ini? Tawarkan Sekarang!</h3>
                        <p class="text-[11px] text-slate-500">Kirim penawaran harga dan kondisi barang Anda ke pembeli</p>
                    </div>
                </div>

                @auth
                    <form method="POST" action="{{ route('requests.offers.store', $itemRequest) }}" class="space-y-3.5">
                        @csrf

                        @if ($userActiveProducts->count() > 0)
                            <div>
                                <label for="product_id" class="block text-xs font-bold text-slate-700 mb-1">
                                    Tautkan Produk dari Etalase Anda (Opsional):
                                </label>
                                <select
                                    name="product_id"
                                    id="product_id"
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                                >
                                    <option value="">-- Tanpa Tautan Produk (Penawaran Langsung) --</option>
                                    @foreach ($userActiveProducts as $prod)
                                        <option value="{{ $prod->id }}">
                                            {{ $prod->title }} ({{ $prod->formatted_price }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label for="price" class="block text-xs font-bold text-slate-700 mb-1">
                                Harga Penawaran Anda (Rupiah) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-slate-400 font-bold">Rp</span>
                                <input
                                    type="number"
                                    name="price"
                                    id="price"
                                    placeholder="Contoh: 60000"
                                    min="1000"
                                    step="1000"
                                    class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                                    required
                                >
                            </div>
                        </div>

                        <div>
                            <label for="message" class="block text-xs font-bold text-slate-700 mb-1">
                                Pesan & Penjelasan Kondisi Barang <span class="text-rose-500">*</span>
                            </label>
                            <textarea
                                name="message"
                                id="message"
                                rows="3"
                                placeholder="Contoh: Halo! Saya punya bukunya edisi 9 asli, kondisi 90% bersih dan cover rapi. Siap ketemuan COD di kampus besok siang."
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                                required
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            class="w-full py-2.5 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center justify-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <span>Kirim Penawaran ke Pembeli</span>
                        </button>
                    </form>
                @else
                    <div class="p-3 bg-slate-50 rounded-xl text-center">
                        <p class="text-xs text-slate-600">Masuk untuk menawarkan barang Anda kepada mahasiswa ini.</p>
                        <a href="{{ route('login') }}" class="inline-block mt-2 px-4 py-1.5 bg-teal-700 text-white text-xs font-bold rounded-lg shadow-xs">
                            Masuk Sekarang
                        </a>
                    </div>
                @endauth
            </div>
        @endif

        <!-- Offers Received List -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900">
                    Daftar Penawaran Masuk ({{ $itemRequest->offers->count() }})
                </h3>
                @if ($itemRequest->acceptedOffer)
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Penawaran Diterima
                    </span>
                @endif
            </div>

            @forelse ($itemRequest->offers as $offer)
                @php
                    $isAccepted = $offer->status === \App\Models\ItemRequestOffer::STATUS_ACCEPTED;
                @endphp

                <div class="p-3.5 rounded-xl border {{ $isAccepted ? 'bg-emerald-50/60 border-emerald-300 ring-2 ring-emerald-100' : 'bg-slate-50 border-slate-200/80' }} space-y-2.5 text-xs transition">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <img
                                src="{{ $offer->user->avatar_url }}"
                                alt="{{ $offer->user->name }}"
                                class="w-8 h-8 rounded-full object-cover shrink-0"
                            >
                            <div>
                                <div class="flex items-center gap-1">
                                    <span class="font-bold text-slate-900 text-xs">{{ $offer->user->name }}</span>
                                    @if ($offer->user->is_student_verified)
                                        <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 text-white inline-flex items-center justify-center text-[9px] font-black">✓</span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-slate-400 block">{{ $offer->user->student?->university?->name ?? 'Mahasiswa' }} &bull; {{ $offer->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-xs font-black text-teal-800 font-mono block">{{ $offer->formatted_price }}</span>
                            @if ($isAccepted)
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.2 rounded block mt-0.5">Diterima</span>
                            @endif
                        </div>
                    </div>

                    <!-- Linked Product Snippet -->
                    @if ($offer->product)
                        <div class="p-2 bg-white rounded-lg border border-slate-200 flex items-center gap-2.5">
                            <img
                                src="{{ $offer->product->cover_image_url }}"
                                alt="{{ $offer->product->title }}"
                                class="w-9 h-9 rounded-md object-cover shrink-0"
                            >
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] text-slate-400 block">Produk Terkait di Etalase:</span>
                                <a href="{{ route('products.show', $offer->product) }}" class="text-xs font-bold text-slate-800 hover:text-teal-700 truncate block">
                                    {{ $offer->product->title }}
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Message Body -->
                    <p class="text-slate-700 text-xs leading-relaxed bg-white/70 p-2.5 rounded-lg border border-slate-100">
                        {{ $offer->message }}
                    </p>

                    <!-- Owner Action on Offer -->
                    @if ($isOwner && ! $itemRequest->isFulfilled())
                        <div class="pt-1 flex items-center justify-end gap-2">
                            <form method="POST" action="{{ route('requests.offers.accept', [$itemRequest, $offer]) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition shadow-xs flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>Terima Penawaran</span>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-6 text-center text-slate-400 text-xs">
                    <p>Belum ada penawaran barang yang masuk.</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Mahasiswa lain yang memiliki barang ini akan mengirimkan tawaran di sini.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
