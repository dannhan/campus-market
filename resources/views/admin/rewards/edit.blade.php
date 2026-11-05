@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-10">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('admin.rewards.index') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="text-center">
            <div class="text-[10px] font-bold uppercase tracking-wider text-teal-600">Admin Panel</div>
            <h1 class="text-base font-bold text-slate-900 leading-tight">Edit Item Reward</h1>
        </div>
        <div class="w-9"></div>
    </div>

    <div class="p-5 max-w-xl mx-auto w-full">
        <form action="{{ route('admin.rewards.update', $rewardItem) }}" method="POST" class="space-y-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-xs font-bold text-slate-700 mb-1">Nama Reward <span class="text-rose-500">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title', $rewardItem->title) }}" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-teal-500 focus:border-teal-500">
                @error('title')
                    <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-bold text-slate-700 mb-1">Deskripsi & Syarat Ketentuan</label>
                <textarea name="description" id="description" rows="3" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-teal-500 focus:border-teal-500">{{ old('description', $rewardItem->description) }}</textarea>
                @error('description')
                    <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Reward Type & Points Cost -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="reward_type" class="block text-xs font-bold text-slate-700 mb-1">Tipe Reward <span class="text-rose-500">*</span></label>
                    <select name="reward_type" id="reward_type" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-teal-500 focus:border-teal-500">
                        <option value="discount_fixed" {{ old('reward_type', $rewardItem->reward_type) === 'discount_fixed' ? 'selected' : '' }}>Diskon Belanja (Nominal)</option>
                        <option value="free_shipping" {{ old('reward_type', $rewardItem->reward_type) === 'free_shipping' ? 'selected' : '' }}>Gratis Ongkos Kirim</option>
                        <option value="merchandise" {{ old('reward_type', $rewardItem->reward_type) === 'merchandise' ? 'selected' : '' }}>Merchandise Kampus</option>
                        <option value="badge" {{ old('reward_type', $rewardItem->reward_type) === 'badge' ? 'selected' : '' }}>Badge Profil Khusus</option>
                        <option value="other" {{ old('reward_type', $rewardItem->reward_type) === 'other' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                <div>
                    <label for="points_cost" class="block text-xs font-bold text-slate-700 mb-1">Harga Poin <span class="text-rose-500">*</span></label>
                    <input type="number" name="points_cost" id="points_cost" value="{{ old('points_cost', $rewardItem->points_cost) }}" min="1" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-teal-500 focus:border-teal-500">
                    @error('points_cost')
                        <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Discount Amount & Stock -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="discount_amount" class="block text-xs font-bold text-slate-700 mb-1">Nominal Diskon (Rp)</label>
                    <input type="number" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', $rewardItem->discount_amount) }}" min="0" step="1000" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-teal-500 focus:border-teal-500">
                </div>

                <div>
                    <label for="stock" class="block text-xs font-bold text-slate-700 mb-1">Kuota Stok (-1 = Unlimited) <span class="text-rose-500">*</span></label>
                    <input type="number" name="stock" id="stock" value="{{ old('stock', $rewardItem->stock) }}" min="-1" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-teal-500 focus:border-teal-500">
                    @error('stock')
                        <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Validity Days -->
            <div>
                <label for="validity_days" class="block text-xs font-bold text-slate-700 mb-1">Masa Berlaku (Hari setelah klaim) <span class="text-rose-500">*</span></label>
                <input type="number" name="validity_days" id="validity_days" value="{{ old('validity_days', $rewardItem->validity_days) }}" min="0" max="365" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-1 focus:ring-teal-500 focus:border-teal-500">
            </div>

            <!-- Active Checkbox -->
            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $rewardItem->is_active) ? 'checked' : '' }} class="w-4 h-4 text-teal-600 rounded border-slate-300 focus:ring-teal-500">
                <label for="is_active" class="text-xs font-bold text-slate-700">Reward ini aktif dan dapat ditukar oleh mahasiswa</label>
            </div>

            <!-- Buttons -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <a href="{{ route('admin.rewards.index') }}" class="px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold shadow-xs transition">
                    Perbarui Reward
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
