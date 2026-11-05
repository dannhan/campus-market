@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-14">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('seller.products') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Etalase">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="text-center">
            <h1 class="text-base font-bold text-slate-900 leading-tight">Upload Massal Produk</h1>
            <span class="text-[10px] text-teal-700 font-semibold uppercase tracking-wider">Impor Sekaligus via CSV / Excel</span>
        </div>
        <div class="w-9"></div>
    </div>

    <div class="p-5 space-y-5">
        <!-- Flash Alert -->
        @if (session('status'))
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl flex items-start gap-2 shadow-xs">
                <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="flex-1">
                    <p class="font-bold">Gagal Mengimpor File:</p>
                    <p class="mt-0.5 text-[11px] leading-relaxed">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if (session('bulk_errors') && is_array(session('bulk_errors')))
            <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-900 text-xs rounded-2xl space-y-2 shadow-xs">
                <div class="flex items-center gap-2 font-bold text-rose-800">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Daftar Kesalahan Baris (Total: {{ count(session('bulk_errors')) }} baris):</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-[11px] text-rose-700 max-h-40 overflow-y-auto pl-1">
                    @foreach (session('bulk_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Quick 3-Step Guide Banner -->
        <div class="bg-gradient-to-br from-teal-900 via-teal-800 to-emerald-950 rounded-2xl p-4 text-white shadow-md border border-teal-800/80">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-teal-200 mb-2">
                Panduan Upload Sekaligus:
            </h2>
            <div class="space-y-2 text-xs text-teal-50">
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-teal-400 text-teal-950 flex items-center justify-center font-black text-[11px] shrink-0">1</span>
                    <p class="leading-relaxed">
                        Unduh file <strong>Template CSV</strong> resmi yang telah disesuaikan dengan format sistem.
                    </p>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-teal-400 text-teal-950 flex items-center justify-center font-black text-[11px] shrink-0">2</span>
                    <p class="leading-relaxed">
                        Buka di <strong>Microsoft Excel</strong> atau Google Sheets, isi data produk Anda, lalu simpan sebagai <strong>CSV</strong>.
                    </p>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-teal-400 text-teal-950 flex items-center justify-center font-black text-[11px] shrink-0">3</span>
                    <p class="leading-relaxed">
                        Unggah file CSV di bawah ini. Semua produk akan langsung tayang aktif di etalase Anda.
                    </p>
                </div>
            </div>

            <!-- Download Template Button -->
            <div class="mt-4 pt-3 border-t border-white/15 flex items-center justify-between">
                <span class="text-[11px] text-teal-200">Format standar (.csv)</span>
                <a
                    href="{{ route('seller.products.bulk.template') }}"
                    class="px-3.5 py-1.5 bg-emerald-400 hover:bg-emerald-300 text-teal-950 text-xs font-black rounded-xl transition shadow-xs flex items-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Unduh Template CSV</span>
                </a>
            </div>
        </div>

        <!-- Upload Form Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs space-y-4" x-data="{ filename: '', isDragging: false }">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <span>Unggah File CSV Produk</span>
            </h3>

            <form
                method="POST"
                action="{{ route('seller.products.bulk.store') }}"
                enctype="multipart/form-data"
                class="space-y-4"
            >
                @csrf

                <!-- Dropzone Area -->
                <div
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="isDragging = false; if ($event.dataTransfer.files.length) { $refs.fileInput.files = $event.dataTransfer.files; filename = $event.dataTransfer.files[0].name; }"
                    :class="isDragging ? 'border-teal-500 bg-teal-50/50' : 'border-slate-300 hover:border-teal-400 bg-slate-50/60'"
                    class="border-2 border-dashed rounded-2xl p-6 text-center transition cursor-pointer relative"
                    @click="$refs.fileInput.click()"
                >
                    <input
                        type="file"
                        name="csv_file"
                        x-ref="fileInput"
                        accept=".csv,text/csv,text/plain"
                        class="hidden"
                        @change="filename = $event.target.files[0] ? $event.target.files[0].name : ''"
                        required
                    >

                    <div class="w-12 h-12 mx-auto rounded-full bg-teal-100 text-teal-700 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>

                    <div x-show="!filename">
                        <p class="text-xs font-bold text-slate-800">
                            Pilih file CSV atau tarik ke sini
                        </p>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Mendukung file berekstensi .csv atau .txt (Maksimal 5MB)
                        </p>
                    </div>

                    <div x-show="filename" x-cloak class="space-y-1">
                        <p class="text-xs font-bold text-teal-800 flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="filename"></span>
                        </p>
                        <p class="text-[10px] text-slate-400">Klik untuk mengganti file</p>
                    </div>
                </div>

                @error('csv_file')
                    <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror

                <button
                    type="submit"
                    class="w-full py-3 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Impor & Terbitkan Produk Massal</span>
                </button>
            </form>
        </div>

        <!-- Categories & Values Reference (Cheat-Sheet) -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">
                Referensi Kategori Produk yang Tersedia
            </h3>
            <p class="text-[11px] text-slate-500 leading-relaxed">
                Anda dapat mengisi nama kategori atau slug di kolom <code>category</code> pada template CSV:
            </p>

            <div class="grid grid-cols-2 gap-2 text-xs">
                @foreach ($categories as $cat)
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-800 text-[11px]">{{ $cat->name }}</div>
                            <div class="font-mono text-[10px] text-slate-400 mt-0.5">{{ $cat->slug }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Column Legend -->
            <div class="pt-3 border-t border-slate-100 space-y-2 text-[11px]">
                <h4 class="font-bold text-slate-800">Keterangan Kolom Template:</h4>
                <div class="grid grid-cols-1 gap-1.5 text-slate-600">
                    <div>• <strong>title</strong>: Judul nama produk (wajib).</div>
                    <div>• <strong>category</strong>: Nama kategori atau slug (wajib).</div>
                    <div>• <strong>price</strong>: Harga produk tanpa simbol titik/koma atau angka biasa (minimal Rp 1.000).</div>
                    <div>• <strong>condition</strong>: <code>baru</code>, <code>seperti_baru</code>, atau <code>bekas</code> (default: bekas).</div>
                    <div>• <strong>transaction_type</strong>: <code>jual</code> atau <code>sewa</code> (default: jual).</div>
                    <div>• <strong>bargainable</strong>: <code>ya</code> atau <code>tidak</code> (bisa ditawar/nego).</div>
                    <div>• <strong>delivery_methods</strong>: Kombinasi <code>pickup_point</code>, <code>cod</code>, <code>courier</code> dipisah tanda koma.</div>
                    <div>• <strong>image_url</strong>: Link URL gambar produk (opsional, jika kosong otomatis menggunakan placeholder etalase).</div>
                    <div>• <strong>location</strong> & <strong>description</strong>: Lokasi kampus dan deskripsi rincian barang.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
