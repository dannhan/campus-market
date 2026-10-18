@php($isEdit = isset($product) && $product->exists)

<form method="POST" action="{{ $isEdit ? route('products.update', $product) : route('products.store') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <!-- Photos -->
    <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Foto Produk <span class="text-slate-400 font-normal">(maks. 5 foto, JPG/PNG/WEBP, 5MB per foto)</span></label>
        <label class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-white border-2 border-dashed border-slate-200 rounded-xl text-xs text-slate-500 cursor-pointer hover:border-teal-400 hover:text-teal-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Pilih foto dari perangkat
            <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden">
        </label>
        @if ($isEdit && $product->images->isNotEmpty())
            <p class="text-[11px] text-slate-400 mt-1">Foto lama akan diganti bila Anda mengunggah foto baru.</p>
        @endif
        @error('images')<p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>@enderror
        @error('images.0')<p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>@enderror
    </div>

    <!-- Title -->
    <div>
        <label for="title" class="block text-xs font-semibold text-slate-700 mb-1.5">Judul Produk</label>
        <input type="text" id="title" name="title" value="{{ old('title', $product->title ?? '') }}" required maxlength="255"
               placeholder="cth: Buku Struktur Data Edisi Revisi"
               class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition">
        @error('title')<p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>@enderror
    </div>

    <!-- Category -->
    <div>
        <label for="category_id" class="block text-xs font-semibold text-slate-700 mb-1.5">Kategori</label>
        <select id="category_id" name="category_id" required
                class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition">
            <option value="">Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>@enderror
    </div>

    <!-- Price & Type -->
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label for="price" class="block text-xs font-semibold text-slate-700 mb-1.5">Harga (Rp)</label>
            <input type="number" id="price" name="price" min="1000" step="500" value="{{ old('price', $product->price ?? '') }}" required
                   placeholder="150000"
                   class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition">
            @error('price')<p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="transaction_type" class="block text-xs font-semibold text-slate-700 mb-1.5">Jenis Transaksi</label>
            <select id="transaction_type" name="transaction_type" required
                    class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition">
                <option value="sale" @selected(old('transaction_type', $product->transaction_type ?? 'sale') === 'sale')>Dijual</option>
                <option value="rent" @selected(old('transaction_type', $product->transaction_type ?? '') === 'rent')>Disewakan</option>
            </select>
            @error('transaction_type')<p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>@enderror
            @error('transaction_type')<p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <!-- Condition & Location -->
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label for="condition" class="block text-xs font-semibold text-slate-700 mb-1.5">Kondisi Barang</label>
            <select id="condition" name="condition" required
                    class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition">
                <option value="new" @selected(old('condition', $product->condition ?? '') === 'new')>Baru</option>
                <option value="like_new" @selected(old('condition', $product->condition ?? '') === 'like_new')>Seperti Baru</option>
                <option value="used" @selected(old('condition', $product->condition ?? 'used') === 'used')>Bekas</option>
            </select>
            @error('condition')<p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>@enderror
            @error('condition')<p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="location" class="block text-xs font-semibold text-slate-700 mb-1.5">Lokasi <span class="text-slate-400 font-normal">(opsional)</span></label>
            <input type="text" id="location" name="location" value="{{ old('location', $product->location ?? '') }}" maxlength="255"
                   placeholder="cth: Kampus Tembalang"
                   class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition">
        </div>
    </div>

    <!-- Description -->
    <div>
        <label for="description" class="block text-xs font-semibold text-slate-700 mb-1.5">Deskripsi</label>
        <textarea id="description" name="description" rows="4" required maxlength="5000"
                  placeholder="Jelaskan kondisi barang, kelengkapan, dan alasan menjual..."
                  class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40 focus:border-teal-500 transition">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description')<p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>@enderror
    </div>

    <!-- Bargaining -->
    <label class="flex items-center gap-3 bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 cursor-pointer">
        <input type="hidden" name="bargainable" value="0">
        <input type="checkbox" name="bargainable" value="1" @checked(old('bargainable', $product->bargainable ?? true))
               class="w-4 h-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500/40">
        <span class="text-xs text-slate-700 font-medium">Izinkan pembeli menawar harga (tawar-menawar)</span>
    </label>

    <button type="submit" class="w-full py-3.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-teal-600/20 transition">
        {{ $isEdit ? 'Simpan Perubahan' : 'Publikasikan Produk' }}
    </button>
</form>
