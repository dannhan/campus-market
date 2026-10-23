@extends('layouts.app')

@section('content')
<div class="p-6 flex flex-col justify-between flex-1">
    <div>
        <!-- Top Nav / Back Button -->
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('welcome') }}" class="w-10 h-10 -ml-2 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="text-sm font-bold tracking-tight text-slate-900">
                Campus<span class="text-teal-600">Market</span>
            </div>
            <div class="w-10"></div>
        </div>

        <!-- Title & Subtitle -->
        <div class="mb-4">
            <h1 class="text-2xl font-extrabold text-slate-900">Daftar Akun Mahasiswa</h1>
            <p class="text-xs text-slate-500 mt-1">Registrasi diverifikasi langsung dengan data pangkalan kampus</p>
        </div>

        <!-- Visual Verification Stepper (Wireframe Step Tracker) -->
        <nav aria-label="Tahap registrasi" class="bg-teal-50/70 border border-teal-100 rounded-xl p-3 mb-5">
            <div class="relative">
                <span class="absolute left-[16.6667%] right-[16.6667%] top-3 h-px bg-slate-200" aria-hidden="true"></span>
                <ol class="relative grid grid-cols-3 gap-2 text-[11px] font-semibold text-slate-700">
                    <li class="flex min-w-0 flex-col items-center gap-1 text-center text-teal-800" aria-current="step">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-teal-600 text-[10px] font-bold text-white">1</span>
                        <span class="leading-tight">Data Diri</span>
                    </li>
                    <li class="flex min-w-0 flex-col items-center gap-1 text-center text-slate-400">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-[10px] font-bold text-slate-600">2</span>
                        <span class="leading-tight">Validasi Pangkalan</span>
                    </li>
                    <li class="flex min-w-0 flex-col items-center gap-1 text-center text-slate-400">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-[10px] font-bold text-slate-600">3</span>
                        <span class="leading-tight">Aktivasi Email</span>
                    </li>
                </ol>
            </div>
        </nav>

        <!-- Registration Form -->
        <form method="POST" action="{{ route('register') }}" data-turbo="false" class="space-y-3.5" x-data="{ showPass: false, showConfirm: false }">
            @csrf

            <!-- Nama Lengkap -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        placeholder="Nama lengkap sesuai data akademik / KTM"
                        class="w-full pl-9 pr-3 py-2.5 text-xs bg-slate-50 border @error('name') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                    >
                </div>
                @error('name')
                    <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Universitas Selection -->
            <div>
                <label for="university_id" class="block text-xs font-semibold text-slate-700 mb-1">
                    Universitas <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <select
                        id="university_id"
                        name="university_id"
                        required
                        class="w-full pl-9 pr-8 py-2.5 text-xs bg-slate-50 border @error('university_id') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition appearance-none"
                    >
                        <option value="">-- Pilih Universitas Asal --</option>
                        @foreach ($universities as $univ)
                            <option value="{{ $univ->id }}" {{ old('university_id') == $univ->id ? 'selected' : '' }}>
                                {{ $univ->name }} (@{{ $univ->email_domain }})
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                @error('university_id')
                    <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Grid: NIM and Jurusan -->
            <div class="grid grid-cols-2 gap-3">
                <!-- NIM -->
                <div>
                    <label for="student_number" class="block text-xs font-semibold text-slate-700 mb-1">
                        NIM <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            id="student_number"
                            name="student_number"
                            value="{{ old('student_number') }}"
                            required
                            placeholder="Nomor Mahasiswa"
                            class="w-full pl-9 pr-3 py-2.5 text-xs bg-slate-50 border @error('student_number') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                        >
                    </div>
                    @error('student_number')
                        <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Program Studi -->
                <div>
                    <label for="major" class="block text-xs font-semibold text-slate-700 mb-1">
                        Program Studi
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            id="major"
                            name="major"
                            value="{{ old('major') }}"
                            placeholder="cth: Informatika"
                            class="w-full pl-9 pr-3 py-2.5 text-xs bg-slate-50 border @error('major') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                        >
                    </div>
                    @error('major')
                        <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Email Universitas -->
            <div>
                <label for="university_email" class="block text-xs font-semibold text-slate-700 mb-1">
                    Email Universitas <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input
                        type="email"
                        id="university_email"
                        name="university_email"
                        value="{{ old('university_email') }}"
                        required
                        placeholder="contoh: nama@students.undip.ac.id"
                        class="w-full pl-9 pr-3 py-2.5 text-xs bg-slate-50 border @error('university_email') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                    >
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Harus sesuai dengan domain email resmi universitas yang dipilih.</p>
                @error('university_email')
                    <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">
                    Password <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input
                        :type="showPass ? 'text' : 'password'"
                        id="password"
                        name="password"
                        required
                        placeholder="Minimal 8 karakter"
                        class="w-full pl-9 pr-10 py-2.5 text-xs bg-slate-50 border @error('password') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                    >
                    <button
                        type="button"
                        @click="showPass = !showPass"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Konfirmasi Password -->
            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">
                    Konfirmasi Password <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input
                        :type="showConfirm ? 'text' : 'password'"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        placeholder="Ulangi password kamu"
                        class="w-full pl-9 pr-10 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 transition"
                    >
                    <button
                        type="button"
                        @click="showConfirm = !showConfirm"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Syarat dan Ketentuan Agreement -->
            <div class="pt-1">
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input
                        type="checkbox"
                        name="terms"
                        id="terms"
                        value="1"
                        {{ old('terms') ? 'checked' : '' }}
                        required
                        class="w-4 h-4 mt-0.5 rounded text-teal-600 border-slate-300 focus:ring-teal-500"
                    >
                    <span class="text-[11px] text-slate-600 leading-snug">
                        Saya menyatakan data kemahasiswaan yang saya masukkan adalah valid dan menyetujui Ketentuan Layanan CampusMarket.
                    </span>
                </label>
                @error('terms')
                    <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full py-3.5 px-4 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-md shadow-teal-600/20 transition duration-150 mt-4"
            >
                Daftar & Verifikasi Status
            </button>
        </form>

        <!-- Development Hint -->
        <div class="mt-4 bg-slate-50 border border-slate-200 rounded-xl p-3 text-[11px] text-slate-600">
            <span class="font-semibold text-slate-800">Uji Coba Pendaftaran (Data Dummy):</span>
            <p class="text-[10px] text-slate-500 mt-0.5">
                Coba gunakan data UNDIP yang belum terdaftar: NIM <code class="text-teal-700 font-bold">24060122130003</code>, Nama <code class="text-slate-800">Ahmad Pratama</code>, Email <code class="text-teal-700">ahmad.pratama@students.undip.ac.id</code>.
            </p>
        </div>
    </div>

    <!-- Footer Login Link -->
    <div class="text-center py-4 border-t border-slate-100 text-xs text-slate-500 mt-4">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-bold text-teal-600 hover:text-teal-700 ml-1">
            Masuk
        </a>
    </div>
</div>
@endsection
