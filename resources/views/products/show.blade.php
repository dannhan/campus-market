@extends('layouts.app')

@section('title', $product->title . ' — CampusMarket')

@section('content')
<div x-data="{ activeImage: 0 }" class="flex-1 flex flex-col">
    <!-- Image Gallery (SRS-007) -->
    <div class="relative bg-teal-50 aspect-square">
        @if ($product->images->isNotEmpty())
            <template x-for="(image, index) in {{ $product->images->map(fn ($i) => $i->url)->toJson() }}" :key="index">
                <img x-show="activeImage === index" :src="image" :alt="'Foto ' + (index + 1) + ' ' + @js($product->title)" class="absolute inset-0 w-full h-full object-cover">
            </template>
            @if ($product->images->count() > 1)
                <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-1.5">
                    <template x-for="(image, index) in {{ $product->images->map(fn ($i) => $i->url)->toJson() }}" :key="'dot-' + index">
                        <button type="button" @click="activeImage = index" :class="activeImage === index ? 'w-5 bg-teal-600' : 'w-1.5 bg-white/80'" class="h-1.5 rounded-full transition-all"></button>
                    </template>
                </div>
            @endif
        @else
            <img src="{{ $product->cover_image_url }}" alt="{{ $product->title }}" class="absolute inset-0 w-full h-full object-cover">
        @endif

        <a href="{{ route('products.index') }}" class="absolute top-4 left-4 w-9 h-9 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow-md hover:bg-white transition">
            <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>

        @if ($product->status !== 'active')
            <span class="absolute top-4 right-4 px-3 py-1.5 rounded-full text-[11px] font-bold {{ $product->status === 'sold' ? 'bg-slate-800 text-white' : 'bg-amber-500 text-white' }}">
                {{ $product->status === 'sold' ? 'TERJUAL' : 'DITARIK' }}
            </span>
        @endif
    </div>

    <div class="p-4 pb-6 flex-1">
        <!-- Title & Price -->
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[11px] font-semibold text-teal-700 uppercase tracking-wide">{{ $product->category->name }}</p>
                <h1 class="text-lg font-extrabold text-slate-900 leading-snug mt-0.5">{{ $product->title }}</h1>
            </div>
            <div class="text-right shrink-0">
                <p class="text-lg font-extrabold text-teal-700">{{ $product->formatted_price }}</p>
                <p class="text-[10px] text-slate-400 font-medium">{{ $product->transaction_type_label }} · {{ $product->condition_label }}</p>
            </div>
        </div>

        <!-- Meta badges -->
        <div class="flex flex-wrap gap-1.5 mt-3">
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-100 text-[10px] font-medium text-slate-600">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.99 1.99 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ $product->location ?? 'Lokasi fleksibel' }}
            </span>
            @if ($product->bargainable)
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-teal-50 text-[10px] font-medium text-teal-700">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Bisa nego
                </span>
            @endif
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-100 text-[10px] font-medium text-slate-600">{{ $product->views_count }} dilihat</span>
        </div>

        <!-- Description -->
        <div class="mt-5">
            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Deskripsi</h2>
            <p class="text-sm text-slate-600 leading-relaxed mt-2 whitespace-pre-line">{{ $product->description }}</p>
        </div>

        <!-- Seller Card -->
        <div class="mt-5 bg-slate-50 border border-slate-100 rounded-2xl p-3.5 flex items-center gap-3">
            <img src="{{ $seller->avatar_url }}" alt="{{ $seller->name }}" class="w-11 h-11 rounded-xl object-cover border border-slate-200">
            <div class="min-w-0">
                <p class="text-sm font-bold text-slate-800 truncate">{{ $seller->name }}</p>
                <p class="text-[11px] text-slate-500 truncate">{{ $seller->university?->name ?? 'Mahasiswa' }} @if($seller->university)· {{ $seller->student->major ?? '' }}@endif</p>
                <p class="text-[11px] font-medium text-amber-600 mt-0.5">★ {{ $seller->seller_rating_display }}</p>
            </div>
        </div>

        @auth
            @if ($isOwner)
                <!-- Owner Actions -->
                <div class="mt-5 grid grid-cols-2 gap-2">
                    <a href="{{ route('products.edit', $product) }}" class="text-center py-3 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition">Edit Produk</a>
                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini dari etalase?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-3 bg-rose-50 border border-rose-100 text-rose-600 text-xs font-semibold rounded-xl hover:bg-rose-100 transition">Hapus Produk</button>
                    </form>
                </div>
                @if ($pendingOffersCount > 0)
                    <a href="{{ route('offers.index') }}" class="mt-3 block text-center py-3 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-teal-600/20 transition">
                        Lihat {{ $pendingOffersCount }} penawaran masuk
                    </a>
                @endif
            @elseif ($product->status === 'active')
                <!-- Direct Chat Action (SRS-016) -->
                <form method="POST" action="{{ route('products.chat', $product) }}" class="mt-5">
                    @csrf
                    <button type="submit" class="w-full py-3 bg-white border border-teal-600 text-teal-700 hover:bg-teal-50 text-xs font-bold rounded-xl flex items-center justify-center gap-2 shadow-xs transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        Chat Penjual
                    </button>
                </form>

                <!-- Bargaining (SRS-010) -->
                @if ($myPendingOffer)
                    <div class="mt-5 bg-teal-50 border border-teal-100 rounded-2xl p-4 text-center">
                        <p class="text-xs font-semibold text-teal-800">Penawaran Anda sedang menunggu respons penjual</p>
                        <p class="text-lg font-extrabold text-teal-700 mt-1">{{ $myPendingOffer->formatted_price }}</p>
                        <form method="POST" action="{{ route('offers.cancel', $myPendingOffer) }}" class="mt-3">
                            @csrf
                            <button type="submit" class="text-[11px] font-semibold text-slate-500 hover:text-rose-600 underline">Batalkan penawaran</button>
                        </form>
                    </div>
                @elseif (! $product->bargainable)
                    <div class="mt-5 bg-slate-50 border border-slate-100 rounded-2xl p-4 text-center">
                        <p class="text-xs text-slate-500 font-medium">Penjual tidak menerima tawaran harga untuk produk ini.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('offers.store', $product) }}" class="mt-5 bg-white border border-slate-200 rounded-2xl p-4 space-y-3">
                        @csrf
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wide">Ajukan Tawaran Harga</h2>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="offered_price" min="1000" step="500" required placeholder="Harga tawaran"
                                   class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition">
                            <input type="text" name="message" maxlength="500" placeholder="Pesan (opsional)"
                                   class="px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition">
                        </div>
                        <button type="submit" class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-teal-600/20 transition">Kirim Tawaran</button>
                    </form>
                @endif
            @endif
        @else
            <a href="{{ route('login') }}" class="mt-5 block text-center py-3.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-teal-600/20 transition">
                Masuk untuk menawar produk ini
            </a>
        @endauth
    </div>
</div>
@endsection
