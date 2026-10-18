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
        <div class="mb-6">
            <h1 class="text-2xl font-extrabold text-slate-900">Masuk</h1>
            <p class="text-xs text-slate-500 mt-1">Gunakan email resmi universitas kamu</p>
        </div>

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ showPassword: false }">
            @csrf

            <!-- Email Universitas -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">Email Universitas</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        placeholder="contoh: budi.santoso@students.undip.ac.id"
                        class="w-full pl-10 pr-3.5 py-3 text-xs bg-slate-50 border @error('email') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition duration-150"
                    >
                </div>
                @error('email')
                    <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        name="password"
                        required
                        placeholder="Masukkan password kamu"
                        class="w-full pl-10 pr-10 py-3 text-xs bg-slate-50 border @error('password') border-rose-500 @else border-slate-200 @enderror rounded-xl focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition duration-150"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600"
                    >
                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-[11px] text-rose-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me & Forgot Password Row -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        name="remember"
                        id="remember"
                        value="1"
                        {{ old('remember') ? 'checked' : '' }}
                        class="w-4 h-4 rounded text-teal-600 border-slate-300 focus:ring-teal-500"
                    >
                    <span class="text-xs text-slate-600">Ingat saya</span>
                </label>

                <span class="text-xs text-teal-600 hover:text-teal-700 font-semibold cursor-pointer">
                    Lupa password?
                </span>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="w-full py-3.5 px-4 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-md shadow-teal-600/20 transition duration-150 mt-2"
            >
                Masuk
            </button>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200"></div>
            </div>
            <div class="relative flex justify-center text-xs">
                <span class="px-3 bg-white text-slate-400">atau</span>
            </div>
        </div>

        <!-- Academic SSO Info / Development Demo Helper -->
        <div class="bg-teal-50/50 border border-teal-100 rounded-xl p-3 text-[11px] text-slate-600 leading-relaxed">
            <span class="font-semibold text-teal-800">Akun Pengujian Demo:</span>
            <div class="mt-1 font-mono text-[10px] text-slate-700 space-y-0.5">
                <div>• Terverifikasi: <span class="text-teal-700 font-bold">budi.santoso@students.undip.ac.id</span> / <span class="text-slate-800">Password123!</span></div>
                <div>• Belum Verifikasi: <span class="text-teal-700 font-bold">siti.rahmawati@students.undip.ac.id</span> / <span class="text-slate-800">Password123!</span></div>
            </div>
        </div>
    </div>

    <!-- Footer Register Link -->
    <div class="text-center py-4 border-t border-slate-100 text-xs text-slate-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-bold text-teal-600 hover:text-teal-700 ml-1">
            Daftar Sekarang
        </a>
    </div>
</div>
@endsection
