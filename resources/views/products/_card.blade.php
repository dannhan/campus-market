<div class="relative group">
    <a href="{{ route('products.show', $product) }}" class="block bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-xs hover:shadow-md hover:border-teal-200 transition">
        <div class="aspect-square bg-teal-50 overflow-hidden">
            <img src="{{ $product->cover_image_url }}" alt="{{ $product->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
        </div>
        <div class="p-2.5">
            <p class="text-[10px] font-medium text-teal-700/80 uppercase tracking-wide truncate">{{ $product->category->name }}</p>
            <h3 class="text-xs font-semibold text-slate-800 mt-0.5 line-clamp-2 leading-snug">{{ $product->title }}</h3>
            <p class="text-sm font-extrabold text-slate-900 mt-1">{{ $product->formatted_price }}@if ($product->transaction_type === 'rent') <span class="text-[10px] font-semibold text-slate-400">/sewa</span>@endif</p>
            @php
                $cardLocation = filled($product->location)
                    ? $product->location
                    : ($product->user?->university?->name ?: 'Mahasiswa');
            @endphp
            <div class="flex items-center gap-1.5 mt-1.5 min-w-0">
                <span class="shrink-0 text-[10px] px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-500 font-medium">{{ $product->condition_label }}</span>
                <span class="flex items-center gap-0.5 min-w-0 text-[10px] text-slate-400">
                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.99 1.99 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="truncate">{{ $cardLocation }}</span>
                </span>
            </div>
        </div>
    </a>

    @auth
        @if ($product->user_id !== auth()->id())
            @php($isCardWishlisted = $product->isWishlistedBy(auth()->user()))
            <form method="POST" action="{{ route('products.wishlist.toggle', $product) }}" class="absolute top-2 right-2 z-10">
                @csrf
                <button type="submit"
                        title="{{ $isCardWishlisted ? 'Hapus dari Wishlist' : 'Simpan ke Wishlist' }}"
                        class="w-7 h-7 rounded-full bg-white/90 backdrop-blur {{ $isCardWishlisted ? 'text-rose-500' : 'text-slate-400 hover:text-rose-500' }} shadow-sm flex items-center justify-center transition active:scale-90">
                    <svg class="w-4 h-4 {{ $isCardWishlisted ? 'fill-current' : 'fill-none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </button>
            </form>
        @endif
    @endauth
</div>
