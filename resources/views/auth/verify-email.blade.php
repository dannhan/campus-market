@extends('layouts.app')

@section('content')
<div class="p-6 flex flex-col justify-between flex-1 text-center">
    <div>
        <!-- Top Nav / CampusMarket Branding -->
        <div class="flex items-center justify-between mb-4">
            <div class="w-10"></div>
            <div class="text-sm font-bold tracking-tight text-slate-900">
                Campus<span class="text-teal-600">Market</span>
            </div>
            <div class="w-10"></div>
        </div>

        <!-- Verification Illustration -->
        <div class="w-20 h-20 bg-teal-50 border border-teal-100 rounded-3xl mx-auto flex items-center justify-center my-6 shadow-xs">
            <svg class="w-10 h-10 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>

        <h1 class="text-2xl font-extrabold text-slate-900">Verifikasi Email Anda</h1>
        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
            Terima kasih telah mendaftar di CampusMarket! Kami telah mengirimkan tautan verifikasi ke email universitas Anda:
        </p>

        <!-- Target Email Card -->
        <div class="my-4 p-3 bg-slate-50 border border-slate-200 rounded-xl">
            <div class="text-xs font-bold text-teal-700 font-mono">{{ auth()->user()->email }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">
                {{ auth()->user()->student?->university?->name ?? 'Mahasiswa Universitas' }}
            </div>
        </div>

        <!-- Verified Academic Data Badge -->
        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-left flex items-start gap-2.5 mb-6">
            <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-xs mt-0.5">✓</div>
            <div class="text-xs text-emerald-900">
                <span class="font-bold">Status Kemahasiswaan Terverifikasi!</span>
                <p class="text-[11px] text-emerald-700 mt-0.5">
                    NIM: {{ auth()->user()->student?->student_number }} ({{ auth()->user()->student?->major }}). Selesaikan verifikasi email untuk membuka profil dan mulai bertransaksi.
                </p>
            </div>
        </div>

        <!-- Resend Action Form -->
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button
                type="submit"
                class="w-full py-3.5 px-4 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-md shadow-teal-600/20 transition duration-150"
            >
                Kirim Ulang Email Verifikasi
            </button>
        </form>

        <!-- Development Inspection Notice -->
        @if (app()->environment('local', 'testing'))
            <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl text-left text-[11px] text-amber-900">
                <span class="font-bold">Catatan Pengujian Lokal:</span>
                <p class="mt-0.5 text-[10px] text-amber-800">
                    Pada lingkungan development, email verifikasi dicatat pada log sistem (<code class="font-mono text-amber-950">storage/logs/laravel.log</code>). Buka tautan tersebut untuk memverifikasi akun secara instan.
                </p>
            </div>
        @endif
    </div>

    <!-- Logout Action -->
    <div class="pt-6 border-t border-slate-100 mt-6">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-xs text-slate-500 hover:text-slate-800 font-medium">
                Keluar / Gunakan Akun Lain
            </button>
        </form>
    </div>
</div>
@endsection
