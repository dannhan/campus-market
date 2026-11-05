@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-14">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('requests.show', $itemRequest) }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Detail">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="text-center">
            <h1 class="text-base font-bold text-slate-900 leading-tight">Edit Permintaan</h1>
            <span class="text-[10px] text-teal-700 font-semibold uppercase tracking-wider">Perbarui Informasi Barang</span>
        </div>
        <div class="w-9"></div>
    </div>

    <div class="p-5">
        <form method="POST" action="{{ route('requests.update', $itemRequest) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Form Card -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs space-y-4">
                <!-- Title -->
                <div>
                    <label for="title" class="block text-xs font-bold text-slate-800 mb-1">
                        Nama / Judul Barang yang Dicari <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="title"
                        id="title"
                        value="{{ old('title', $itemRequest->title) }}"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                        required
                    >
                    @error('title')
                        <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-xs font-bold text-slate-800 mb-1">
                        Kategori Barang <span class="text-rose-500">*</span>
                    </label>
                    <select
                        name="category_id"
                        id="category_id"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                        required
                    >
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $itemRequest->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Budget Range -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">
                        Budget yang Anda Siapkan (Rupiah)
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-[10px] text-slate-400 block mb-0.5 font-medium">Budget Minimal:</span>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-slate-400 font-bold">Rp</span>
                                <input
                                    type="number"
                                    name="budget_min"
                                    value="{{ old('budget_min', (int) $itemRequest->budget_min) }}"
                                    min="0"
                                    step="1000"
                                    class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                                >
                            </div>
                        </div>

                        <div>
                            <span class="text-[10px] text-slate-400 block mb-0.5 font-medium">Budget Maksimal:</span>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-slate-400 font-bold">Rp</span>
                                <input
                                    type="number"
                                    name="budget_max"
                                    value="{{ old('budget_max', (int) $itemRequest->budget_max) }}"
                                    min="0"
                                    step="1000"
                                    class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Condition Preference -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1.5">
                        Preferensi Kondisi Barang <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <label class="p-2.5 border rounded-xl flex items-center gap-2 cursor-pointer hover:bg-slate-50 transition {{ old('condition_preference', $itemRequest->condition_preference) === 'any' ? 'border-teal-600 bg-teal-50/40 text-teal-900 font-bold' : 'border-slate-200 text-slate-700' }}">
                            <input type="radio" name="condition_preference" value="any" {{ old('condition_preference', $itemRequest->condition_preference) === 'any' ? 'checked' : '' }} class="text-teal-600 focus:ring-teal-500">
                            <span>Bebas (Apapun)</span>
                        </label>
                        <label class="p-2.5 border rounded-xl flex items-center gap-2 cursor-pointer hover:bg-slate-50 transition {{ old('condition_preference', $itemRequest->condition_preference) === 'used' ? 'border-teal-600 bg-teal-50/40 text-teal-900 font-bold' : 'border-slate-200 text-slate-700' }}">
                            <input type="radio" name="condition_preference" value="used" {{ old('condition_preference', $itemRequest->condition_preference) === 'used' ? 'checked' : '' }} class="text-teal-600 focus:ring-teal-500">
                            <span>Bekas / Layak</span>
                        </label>
                        <label class="p-2.5 border rounded-xl flex items-center gap-2 cursor-pointer hover:bg-slate-50 transition {{ old('condition_preference', $itemRequest->condition_preference) === 'like_new' ? 'border-teal-600 bg-teal-50/40 text-teal-900 font-bold' : 'border-slate-200 text-slate-700' }}">
                            <input type="radio" name="condition_preference" value="like_new" {{ old('condition_preference', $itemRequest->condition_preference) === 'like_new' ? 'checked' : '' }} class="text-teal-600 focus:ring-teal-500">
                            <span>Seperti Baru (Mulus)</span>
                        </label>
                        <label class="p-2.5 border rounded-xl flex items-center gap-2 cursor-pointer hover:bg-slate-50 transition {{ old('condition_preference', $itemRequest->condition_preference) === 'new' ? 'border-teal-600 bg-teal-50/40 text-teal-900 font-bold' : 'border-slate-200 text-slate-700' }}">
                            <input type="radio" name="condition_preference" value="new" {{ old('condition_preference', $itemRequest->condition_preference) === 'new' ? 'checked' : '' }} class="text-teal-600 focus:ring-teal-500">
                            <span>Baru (Segel)</span>
                        </label>
                    </div>
                </div>

                <!-- Urgency -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1.5">
                        Tingkat Urgensi Kebutuhan <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <label class="p-2.5 border rounded-xl text-center cursor-pointer hover:bg-slate-50 transition {{ old('urgency', $itemRequest->urgency) === 'urgent' ? 'border-rose-500 bg-rose-50 text-rose-900 font-bold' : 'border-slate-200 text-slate-700' }}">
                            <input type="radio" name="urgency" value="urgent" {{ old('urgency', $itemRequest->urgency) === 'urgent' ? 'checked' : '' }} class="hidden">
                            <span class="block text-sm">⚡</span>
                            <span class="text-[11px] font-bold block mt-0.5">Mendesak</span>
                        </label>
                        <label class="p-2.5 border rounded-xl text-center cursor-pointer hover:bg-slate-50 transition {{ old('urgency', $itemRequest->urgency) === 'normal' ? 'border-teal-600 bg-teal-50 text-teal-900 font-bold' : 'border-slate-200 text-slate-700' }}">
                            <input type="radio" name="urgency" value="normal" {{ old('urgency', $itemRequest->urgency) === 'normal' ? 'checked' : '' }} class="hidden">
                            <span class="block text-sm">⏱️</span>
                            <span class="text-[11px] font-bold block mt-0.5">Normal</span>
                        </label>
                        <label class="p-2.5 border rounded-xl text-center cursor-pointer hover:bg-slate-50 transition {{ old('urgency', $itemRequest->urgency) === 'low' ? 'border-indigo-500 bg-indigo-50 text-indigo-900 font-bold' : 'border-slate-200 text-slate-700' }}">
                            <input type="radio" name="urgency" value="low" {{ old('urgency', $itemRequest->urgency) === 'low' ? 'checked' : '' }} class="hidden">
                            <span class="block text-sm">☕</span>
                            <span class="text-[11px] font-bold block mt-0.5">Santai</span>
                        </label>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-xs font-bold text-slate-800 mb-1">
                        Deskripsi & Kebutuhan Spesifik <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        name="description"
                        id="description"
                        rows="4"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                        required
                    >{{ old('description', $itemRequest->description) }}</textarea>
                    @error('description')
                        <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button
                type="submit"
                class="w-full py-3 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center justify-center gap-2"
            >
                <span>Simpan Perubahan</span>
            </button>
        </form>
    </div>
</div>
@endsection
