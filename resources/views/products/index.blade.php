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

    <!-- Category Grid (SRS-009) -->
    @php
        $categoryShortLabels = [
            'book-open' => 'Buku',
            'beaker' => 'Lab',
            'device-phone' => 'Gadget',
            'briefcase' => 'Jasa',
            'pencil' => 'Alat Tulis',
            'shirt' => 'Pakaian',
            'cpu' => 'Elektronik',
            'cake' => 'Makanan',
        ];
    @endphp

    <div class="grid grid-cols-4 gap-x-2 gap-y-4 mb-5">
        @foreach ($categories->take(8) as $category)
            @php($isActiveCategory = ($filters['category'] ?? '') === $category->slug)
            <a href="{{ $isActiveCategory
                    ? route('products.index', array_diff_key($filters, ['category' => 1]))
                    : route('products.index', array_merge($filters, ['category' => $category->slug])) }}"
               class="flex flex-col items-center gap-1.5 min-w-0"
               title="{{ $isActiveCategory ? 'Tampilkan semua kategori' : $category->name }}"
               @if ($isActiveCategory) aria-current="page" @endif>
                <span class="w-14 h-14 rounded-full border flex items-center justify-center transition {{ $isActiveCategory ? 'bg-teal-600 border-teal-600 text-white shadow-md shadow-teal-600/25' : 'bg-white border-slate-200 text-teal-600 hover:border-teal-300 hover:text-teal-700' }}">
                    <x-category-icon :name="$category->icon" class="w-6 h-6" />
                </span>
                <span class="text-[10px] leading-tight text-center truncate w-full {{ $isActiveCategory ? 'font-semibold text-teal-700' : 'font-medium text-slate-500' }}">{{ $categoryShortLabels[$category->icon] ?? $category->name }}</span>
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
        <div
            x-data="{
                nextUrl: @js($products->nextPageUrl()),
                remaining: {{ max($products->total() - ($products->lastItem() ?? 0), 0) }},
                hasNext: @js($products->hasMorePages()),
                startedWithMore: @js($products->hasMorePages()),
                perPage: {{ $products->perPage() }},
                currentPage: {{ $products->currentPage() }},
                initialLoaded: {{ $products->count() }},
                loading: false,
                error: null,
                statusMessage: '',
                async loadMore() {
                    if (this.loading || ! this.hasNext || ! this.nextUrl) {
                        return;
                    }

                    this.loading = true;
                    this.error = null;

                    try {
                        const url = new URL(this.nextUrl, window.location.origin);
                        const extraLoaded = Math.max(this.$refs.grid.children.length - this.initialLoaded, 0);
                        const nextPage = this.currentPage + Math.floor(extraLoaded / this.perPage) + 1;

                        url.searchParams.set('page', String(nextPage));
                        url.searchParams.set('fragment', '1');

                        const response = await fetch(url.toString(), {
                            cache: 'no-store',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (! response.ok) {
                            throw new Error('HTTP ' + response.status);
                        }

                        const data = await response.json();
                        const previousCardCount = this.$refs.grid.children.length;

                        this.$refs.grid.insertAdjacentHTML('beforeend', data.html);

                        if (window.Alpine) {
                            Array.from(this.$refs.grid.children)
                                .slice(previousCardCount)
                                .forEach((card) => window.Alpine.initTree(card));
                        }

                        this.nextUrl = data.next_page_url;
                        this.hasNext = data.has_more;
                        this.remaining = data.remaining;
                        this.statusMessage = data.count + ' produk lagi ditampilkan.';

                        if (! data.has_more) {
                            this.statusMessage += ' Semua produk sudah ditampilkan.';
                        }
                    } catch (error) {
                        this.error = 'Gagal memuat produk. Periksa koneksi Anda dan coba lagi.';
                        this.statusMessage = this.error;
                    } finally {
                        this.loading = false;
                    }
                },
            }"
        >
            <div
                x-ref="grid"
                id="product-grid"
                class="grid grid-cols-2 gap-3"
                :aria-busy="loading ? 'true' : 'false'"
            >
                @include('products._cards', ['products' => $products])
            </div>

            <div class="mt-5">
                <button
                    type="button"
                    x-show="hasNext"
                    x-cloak
                    @click="loadMore()"
                    :disabled="loading"
                    class="w-full inline-flex items-center justify-center gap-2 py-2.5 bg-white border border-slate-200 text-teal-700 text-xs font-semibold rounded-xl hover:border-teal-300 hover:bg-teal-50/50 disabled:opacity-60 disabled:cursor-wait transition"
                >
                    <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <svg x-show="! loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span x-text="loading ? 'Memuat produk...' : 'Muat ' + remaining + ' produk lagi'"></span>
                </button>

                <div x-show="error" x-cloak class="rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-center">
                    <p class="text-[11px] font-medium text-rose-600" x-text="error"></p>
                    <button type="button" @click="loadMore()" class="mt-1.5 text-[11px] font-bold text-rose-700 underline">Coba lagi</button>
                </div>

                <p x-show="startedWithMore && ! hasNext && ! loading" x-cloak class="text-center text-[11px] font-medium text-slate-400">
                    Semua produk sudah ditampilkan.
                </p>
            </div>

            <p class="sr-only" role="status" aria-live="polite" x-text="statusMessage"></p>

            <noscript>
                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            </noscript>
        </div>
    @endif
</div>
@endsection
