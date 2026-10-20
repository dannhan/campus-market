@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-10">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('profile') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-base font-bold text-slate-900">Edit Profil</h1>
        <div class="w-9"></div>
    </div>

    <div class="p-5">
        <form
            method="POST"
            action="{{ route('profile.update') }}"
            enctype="multipart/form-data"
            class="space-y-5"
            data-turbo="false"
            x-data="{
                avatarPreview: @js($user->avatar_url),
                avatarError: '',
                handleAvatar(event) {
                    const file = event.target.files[0];
                    this.avatarError = '';

                    if (! file) {
                        return;
                    }

                    if (! ['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                        this.avatarError = 'Format avatar yang didukung: JPG, PNG, WEBP.';
                        return;
                    }

                    if (file.size > 2 * 1024 * 1024) {
                        this.avatarError = 'Ukuran avatar maksimal 2MB.';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (loadEvent) => {
                        this.avatarPreview = loadEvent.target.result;
                    };
                    reader.readAsDataURL(file);
                },
            }"
        >
            @csrf
            @method('PATCH')

            <!-- Avatar Upload Card -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-5 text-center shadow-xs">
                <div class="relative w-24 h-24 mx-auto mb-3">
                    <img
                        :src="avatarPreview"
                        alt="{{ $user->name }}"
                        class="w-24 h-24 rounded-full object-cover border-2 border-teal-600 shadow-sm"
                    >
                    <label
                        for="avatar"
                        class="absolute bottom-0 right-0 w-8 h-8 bg-teal-600 hover:bg-teal-700 text-white rounded-full flex items-center justify-center shadow-md cursor-pointer transition"
                        title="Unggah Foto Baru"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </label>
                    <input
                        type="file"
                        id="avatar"
                        name="avatar"
                        accept="image/png, image/jpeg, image/jpg, image/webp"
                        class="hidden"
                        @change="handleAvatar($event)"
                    >
                </div>
                <div class="text-xs font-semibold text-slate-700">Foto Profil</div>
                <div class="text-[10px] text-slate-400 mt-0.5">Format JPG, PNG, WEBP (maksimal 2MB)</div>
                <p x-show="avatarError" x-cloak x-text="avatarError" class="text-[11px] text-rose-600 mt-1 font-medium"></p>
                @error('avatar')
                    <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Editable Fields -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">
                    Informasi Akun
                </h3>

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nama Tampilan <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border @error('name') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                    >
                    @error('name')
                        <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Read-Only Academic Identity Fields -->
            <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>Data Kemahasiswaan Terkunci</span>
                    </h3>
                    <span class="text-[10px] px-2 py-0.5 bg-amber-100 text-amber-800 rounded-md font-semibold">
                        Terverifikasi
                    </span>
                </div>

                <p class="text-[11px] text-slate-500 px-1 leading-relaxed">
                    Data berikut terikat langsung dengan verifikasi pangkalan data kampus dan tidak dapat diubah secara bebas demi keamanan transaksi sesama mahasiswa.
                </p>

                <div class="space-y-2.5 pt-1">
                    <div>
                        <div class="text-[10px] font-semibold text-slate-400">Universitas</div>
                        <input
                            type="text"
                            value="{{ $university?->name ?? 'Universitas' }}"
                            disabled
                            class="w-full px-3 py-2 text-xs bg-white border border-slate-200 text-slate-500 rounded-xl cursor-not-allowed font-medium"
                        >
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <div class="text-[10px] font-semibold text-slate-400">NIM</div>
                            <input
                                type="text"
                                value="{{ $student?->student_number ?? '-' }}"
                                disabled
                                class="w-full px-3 py-2 text-xs bg-white border border-slate-200 text-slate-500 rounded-xl cursor-not-allowed font-mono"
                            >
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold text-slate-400">Program Studi</div>
                            <input
                                type="text"
                                value="{{ $student?->major ?? '-' }}"
                                disabled
                                class="w-full px-3 py-2 text-xs bg-white border border-slate-200 text-slate-500 rounded-xl cursor-not-allowed font-medium"
                            >
                        </div>
                    </div>

                    <div>
                        <div class="text-[10px] font-semibold text-slate-400">Email Universitas</div>
                        <input
                            type="text"
                            value="{{ $user->email }}"
                            disabled
                            class="w-full px-3 py-2 text-xs bg-white border border-slate-200 text-slate-500 rounded-xl cursor-not-allowed font-mono"
                        >
                    </div>
                </div>
            </div>

            <!-- Submit Button & Cancel -->
            <div class="space-y-2 pt-2">
                <button
                    type="submit"
                    :disabled="avatarError !== ''"
                    class="w-full py-3.5 px-4 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-md shadow-teal-600/20 transition duration-150 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    Simpan Perubahan
                </button>
                <a
                    href="{{ route('profile') }}"
                    class="w-full block text-center py-3 px-4 bg-white border border-slate-200 text-slate-600 font-semibold rounded-xl hover:bg-slate-50 transition duration-150 text-xs"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
