@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-10">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('profile') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Profil">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="text-center">
            <div class="text-[10px] font-bold uppercase tracking-wider text-teal-600">Admin Panel (SRS-011)</div>
            <h1 class="text-base font-bold text-slate-900 leading-tight">Kelola Kategori</h1>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="w-9 h-9 -mr-1 rounded-full bg-teal-600 hover:bg-teal-700 text-white flex items-center justify-center transition shadow-xs" title="Tambah Kategori Baru">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
        </a>
    </div>

    <div class="p-5 space-y-4">
        <!-- Flash Alerts -->
        @if (session('status'))
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Quick Summary Bar -->
        <div class="grid grid-cols-2 gap-3">
            <div class="p-3 bg-white border border-slate-200/90 rounded-2xl shadow-xs">
                <div class="text-[10px] font-semibold text-slate-400">Total Kategori</div>
                <div class="text-lg font-bold text-slate-800 mt-0.5">{{ $categories->count() }}</div>
            </div>
            <div class="p-3 bg-white border border-slate-200/90 rounded-2xl shadow-xs">
                <div class="text-[10px] font-semibold text-slate-400">Total Produk Terhubung</div>
                <div class="text-lg font-bold text-teal-700 mt-0.5">{{ $categories->sum('products_count') }}</div>
            </div>
        </div>

        <!-- Add Category Callout -->
        <div class="flex items-center justify-between p-3.5 bg-teal-50/70 border border-teal-100 rounded-2xl">
            <div>
                <h2 class="text-xs font-bold text-teal-900">Tambah Kategori Baru</h2>
                <p class="text-[11px] text-teal-700 mt-0.5">Sesuaikan kategori kebutuhan mahasiswa kampus.</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="px-3.5 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                + Tambah
            </a>
        </div>

        <!-- Category Items List -->
        <div class="space-y-2.5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">Daftar Kategori Produk</h2>

            @forelse ($categories as $category)
                <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 min-w-0">
                            <span class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-700 shrink-0">
                                <x-category-icon :name="$category->icon" class="w-5 h-5" />
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-slate-900 truncate">{{ $category->name }}</h3>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 bg-slate-100 text-slate-600 rounded-md shrink-0">
                                        Urutan: #{{ $category->sort_order }}
                                    </span>
                                </div>
                                <div class="text-[11px] font-mono text-slate-400 mt-0.5 truncate">/{{ $category->slug }}</div>
                                @if ($category->description)
                                    <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $category->description }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-600">
                            <span class="w-1.5 h-1.5 rounded-full {{ $category->products_count > 0 ? 'bg-teal-500' : 'bg-slate-300' }}"></span>
                            {{ $category->products_count }} produk aktif
                        </span>

                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold rounded-lg transition text-xs border border-slate-200">
                                Edit
                            </a>

                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori \'{{ $category->name }}\'?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-lg transition text-xs border border-rose-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                    @if ($category->products_count > 0)
                                        title="Kategori tidak dapat dihapus karena masih memiliki produk terhubung"
                                    @endif
                                >
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center bg-white border border-slate-200/90 rounded-2xl">
                    <p class="text-xs font-semibold text-slate-500">Belum ada kategori produk yang terdaftar.</p>
                    <a href="{{ route('admin.categories.create') }}" class="inline-block mt-3 px-4 py-2 bg-teal-600 text-white rounded-xl text-xs font-semibold">
                        Buat Kategori Pertama
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

