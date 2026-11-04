@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-10">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('admin.categories.index') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-base font-bold text-slate-900">Tambah Kategori</h1>
        <div class="w-9"></div>
    </div>

    <div class="p-5">
        <form
            method="POST"
            action="{{ route('admin.categories.store') }}"
            class="space-y-4"
            x-data="{
                name: @js(old('name', '')),
                slug: @js(old('slug', '')),
                selectedIcon: @js(old('icon', 'book-open')),
                generateSlug() {
                    if (! this.slug || this.slug.trim() === '') {
                        this.slug = this.name.toLowerCase()
                            .replace(/[^a-z0-9]+/g, '-')
                            .replace(/^-+|-+$/g, '');
                    }
                }
            }"
        >
            @csrf

            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">
                    Informasi Kategori
                </h3>

                <!-- Nama Kategori -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nama Kategori <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        x-model="name"
                        @blur="generateSlug()"
                        value="{{ old('name') }}"
                        required
                        placeholder="contoh: Perlengkapan Arsitektur"
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border @error('name') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                    >
                    @error('name')
                        <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug Kategori -->
                <div>
                    <label for="slug" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Slug URL <span class="text-slate-400 font-normal">(opsional, dibuat otomatis jika kosong)</span>
                    </label>
                    <input
                        type="text"
                        id="slug"
                        name="slug"
                        x-model="slug"
                        value="{{ old('slug') }}"
                        placeholder="contoh: perlengkapan-arsitektur"
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border @error('slug') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition font-mono"
                    >
                    @error('slug')
                        <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ikon Kategori -->
                <div>
                    <label for="icon" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Ikon Kategori
                    </label>
                    <div class="flex items-center gap-3">
                        <span class="w-11 h-11 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-700 shrink-0">
                            <x-category-icon x-bind:name="selectedIcon" class="w-6 h-6" />
                        </span>
                        <select
                            id="icon"
                            name="icon"
                            x-model="selectedIcon"
                            class="flex-1 px-3.5 py-2.5 text-xs bg-slate-50 border @error('icon') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                        >
                            @foreach ($icons as $key => $label)
                                <option value="{{ $key }}" @selected(old('icon', 'book-open') === $key)>
                                    {{ $label }} ({{ $key }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('icon')
                        <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deskripsi Kategori -->
                <div>
                    <label for="description" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Deskripsi Kategori <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        placeholder="Penjelasan singkat barang apa saja yang masuk ke kategori ini..."
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border @error('description') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Urutan Tampilan -->
                <div>
                    <label for="sort_order" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Urutan Tampilan (Sort Order)
                    </label>
                    <input
                        type="number"
                        id="sort_order"
                        name="sort_order"
                        min="0"
                        value="{{ old('sort_order', $nextSortOrder) }}"
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border @error('sort_order') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                    >
                    @error('sort_order')
                        <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                    <p class="text-[10px] text-slate-400 mt-1">Angka lebih kecil akan ditampilkan lebih awal pada grid kategori marketplace.</p>
                </div>
            </div>

            <!-- Submit Button & Cancel -->
            <div class="space-y-2 pt-2">
                <button
                    type="submit"
                    class="w-full py-3.5 px-4 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-md shadow-teal-600/20 transition duration-150 text-xs"
                >
                    Simpan Kategori Baru
                </button>
                <a
                    href="{{ route('admin.categories.index') }}"
                    class="w-full block text-center py-3 px-4 bg-white border border-slate-200 text-slate-600 font-semibold rounded-xl hover:bg-slate-50 transition duration-150 text-xs"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

