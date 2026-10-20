<a href="{{ route('products.show', $product) }}" class="block bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-xs hover:shadow-md hover:border-teal-200 transition">
    <div class="aspect-square bg-teal-50 overflow-hidden">
        <img src="{{ $product->cover_image_url }}" alt="{{ $product->title }}" class="w-full h-full object-cover" loading="lazy">
    </div>
    <div class="p-2.5">
        <p class="text-[11px] font-semibold text-teal-700 uppercase tracking-wide">{{ $product->category->name }}</p>
        <h3 class="text-xs font-semibold text-slate-800 mt-0.5 line-clamp-2 leading-snug">{{ $product->title }}</h3>
        <p class="text-sm font-extrabold text-slate-900 mt-1">{{ $product->formatted_price }}@if ($product->transaction_type === 'rent') <span class="text-[10px] font-semibold text-slate-400">/sewa</span>@endif</p>
        <div class="flex items-center justify-between mt-1.5">
            <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-500 font-medium">{{ $product->condition_label }}</span>
            <span class="text-[10px] text-slate-400 truncate max-w-[80px]">{{ $product->user?->university?->name ?? 'Mahasiswa' }}</span>
        </div>
    </div>
</a>
