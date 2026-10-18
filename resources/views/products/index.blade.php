@extends('layouts.app')

@section('title', 'Etalase Produk — CampusMarket')

@section('content')
<div class="p-4 pb-6 flex-1">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-extrabold tracking-tight text-slate-900">Etalase <span class="text-teal-600">Produk</span></h1>
            <p class="text-xs text-slate-500 mt-0.5">Temukan kebutuhan kampus dari sesama mahasiswa</p>
        </div>
        @auth
            <a href="{{ route('products.create') }}" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-teal-600/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Jual
            </a>
        @endauth
    </div>

    <!-- Search & Filter Form (SRS-005, SRS-006) -->
    <form method="GET" action="{{ route('products.index') }}" class="space-y-2.5 mb-5">
        <div class="relative">
            <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari produk, buku, gadget..."
                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition">
        </div>

        <div class="grid grid-cols-2 gap-2">
            <select name="university" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500">
                <option value="">Semua Kampus</option>
                @foreach ($universities as $university)
                    <option value="{{ $university->slug }}" @selected(($filters['university'] ?? '') === $university->slug)>{{ $university->name }}</option>
                @endforeach
            </select>
            <select name="condition" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500">
                <option value="">Semua Kondisi</option>
                <option value="new" @selected(($filters['condition'] ?? '') === 'new')>Baru</option>
                <option value="like_new" @selected(($filters['condition'] ?? '') === 'like_new')>Seperti Baru</option>
                <option value="used" @selected(($filters['condition'] ?? '') === 'used')>Bekas</option>
            </select>
            <select name="type" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500">
                <option value="">Jual &amp; Sewa</option>
                <option value="sale" @selected(($filters['type'] ?? '') === 'sale')>Dijual</option>
                <option value="rent" @selected(($filters['type'] ?? '') === 'rent')>Disewakan</option>
            </select>
            <select name="sort" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500">
                <option value="">Terbaru</option>
                <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Harga Terendah</option>
                <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Harga Tertinggi</option>
            </select>
            <input type="number" name="min_price" min="0" value="{{ $filters['min_price'] ?? '' }}" placeholder="Harga min"
                   class="col-span-1 px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500">
            <input type="number" name="max_price" min="0" value="{{ $filters['max_price'] ?? '' }}" placeholder="Harga maks"
                   class="col-span-1 px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500">
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="flex-1 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl transition">Terapkan Filter</button>
            <a href="{{ route('products.index') }}" class="py-2.5 px-4 bg-white border border-slate-200 text-slate-600 text-xs font-semibold rounded-xl hover:bg-slate-50 transition">Reset</a>
        </div>
    </form>

    <!-- Category Chips (SRS-009) -->
    <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-2 -mx-4 px-4 mb-4">
        <a href="{{ route('products.index', array_diff_key($filters, ['category' => 1])) }}"
           class="shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold border transition {{ blank($filters['category'] ?? null) ? 'bg-teal-600 border-teal-600 text-white' : 'bg-white border-slate-200 text-slate-600 hover:border-teal-400' }}">
            Semua
        </a>
        @foreach ($categories as $category)
            <a href="{{ route('products.index', array_merge($filters, ['category' => $category->slug])) }}"
               class="shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold border transition {{ ($filters['category'] ?? '') === $category->slug ? 'bg-teal-600 border-teal-600 text-white' : 'bg-white border-slate-200 text-slate-600 hover:border-teal-400' }}">
                {{ $category->name }}
            </a>
        @endforeach
    </div>

    <!-- Product Grid -->
    @if ($products->isEmpty())
        <div class="py-14 text-center">
            <svg class="w-12 h-12 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
            <p class="text-sm text-slate-500 mt-3 font-medium">Tidak ada produk yang cocok.</p>
            <p class="text-xs text-slate-400 mt-1">Coba ubah kata kunci atau filter pencarian.</p>
        </div>
    @else
        <div class="grid grid-cols-2 gap-3">
            @foreach ($products as $product)
                <a href="{{ route('products.show', $product) }}" class="block bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-xs hover:shadow-md hover:border-teal-200 transition">
                    <div class="aspect-square bg-teal-50 overflow-hidden">
                        <img src="{{ $product->cover_image_url }}" alt="{{ $product->title }}" class="w-full h-full object-cover" loading="lazy">
                    </div>
                    <div class="p-2.5">
                        <p class="text-[11px] font-semibold text-teal-700 uppercase tracking-wide">{{ $product->category->name }}</p>
                        <h3 class="text-xs font-semibold text-slate-800 mt-0.5 line-clamp-2 leading-snug">{{ $product->title }}</h3>
                        <p class="text-sm font-extrabold text-slate-900 mt-1">{{ $product->formatted_price }}{{ $product->transaction_type === 'rent' ? ' <span class="text-[10px] font-semibold text-slate-400">/sewa</span>' : '' }}</p>
                        <div class="flex items-center justify-between mt-1.5">
                            <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-500 font-medium">{{ $product->condition_label }}</span>
                            <span class="text-[10px] text-slate-400 truncate max-w-[80px]">{{ $product->user?->university?->name ?? 'Mahasiswa' }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
