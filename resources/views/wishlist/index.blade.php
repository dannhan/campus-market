@extends('layouts.app')

@section('title', 'Wishlist Saya — CampusMarket')

@section('content')
<div class="flex-1 flex flex-col bg-slate-50 min-h-full">
    <!-- Header -->
    <header class="bg-white border-b border-slate-200 px-4 py-3.5 sticky top-0 z-10 flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('profile') }}" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-base font-bold text-slate-800 leading-tight">Wishlist Saya</h1>
                <p class="text-[11px] text-slate-400">Produk incaran yang kamu simpan</p>
            </div>
        </div>

        @if ($wishlists->total() > 0)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200/60 shadow-xs">
                {{ $wishlists->total() }} barang
            </span>
        @endif
    </header>

    <!-- Wishlist Product Grid -->
    <div class="flex-1 p-3.5">
        @if ($wishlists->isEmpty())
            <div class="py-16 px-4 text-center">
                <div class="w-16 h-16 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-xs">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <h2 class="text-sm font-bold text-slate-800">Wishlist kamu masih kosong</h2>
                <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto leading-relaxed">
                    Tandai produk yang kamu sukai dengan menekan ikon hati agar kamu dapat menemukannya lagi dengan mudah.
                </p>
                <a href="{{ route('products.index') }}" class="mt-4 inline-block px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-teal-600/20 transition">
                    Jelajahi Etalase Produk
                </a>
            </div>
        @else
            <div class="grid grid-cols-2 gap-3">
                @foreach ($wishlists as $item)
                    @php($product = $item->product)
                    @if ($product)
                        <div class="relative bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs hover:shadow-md hover:border-teal-200 transition group flex flex-col justify-between">
                            <div>
                                <!-- Image & Delete Button -->
                                <div class="relative aspect-square bg-teal-50 overflow-hidden">
                                    <a href="{{ route('products.show', $product) }}" class="block w-full h-full">
                                        <img src="{{ $product->cover_image_url }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
                                    </a>

                                    <!-- Remove Button -->
                                    <form method="POST" action="{{ route('wishlist.destroy', $product) }}" class="absolute top-2 right-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="Hapus dari Wishlist"
                                                class="w-7 h-7 rounded-full bg-white/90 backdrop-blur text-rose-500 hover:bg-white hover:text-rose-600 shadow-sm flex items-center justify-center transition active:scale-90">
                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                            </svg>
                                        </button>
                                    </form>

                                    @if ($product->status !== 'active')
                                        <span class="absolute bottom-2 left-2 px-2 py-0.5 rounded-md text-[9px] font-bold {{ $product->status === 'sold' ? 'bg-slate-800/90 text-white' : 'bg-amber-500/90 text-white' }} backdrop-blur">
                                            {{ $product->status === 'sold' ? 'TERJUAL' : 'DITARIK' }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Product Info -->
                                <div class="p-2.5">
                                    <p class="text-[10px] font-semibold text-teal-700 uppercase tracking-wide truncate">{{ $product->category->name }}</p>
                                    <a href="{{ route('products.show', $product) }}" class="block">
                                        <h3 class="text-xs font-semibold text-slate-800 mt-0.5 line-clamp-2 leading-snug hover:text-teal-700 transition">{{ $product->title }}</h3>
                                    </a>
                                    <p class="text-sm font-extrabold text-slate-900 mt-1">
                                        {{ $product->formatted_price }}
                                        @if ($product->transaction_type === 'rent')
                                            <span class="text-[10px] font-semibold text-slate-400">/sewa</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="px-2.5 pb-2.5 pt-0 flex items-center justify-between text-[10px] text-slate-400 border-t border-slate-50 mt-1">
                                <span class="truncate max-w-[80px]">{{ $product->user?->university?->name ?? 'Mahasiswa' }}</span>
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-medium">{{ $product->condition_label }}</span>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            @if ($wishlists->hasPages())
                <div class="mt-4">
                    {{ $wishlists->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
