@extends('layouts.app')

@section('content')
<div class="p-6 flex flex-col justify-between flex-1">
    @auth
        <div class="flex justify-end -mt-2 -mr-2">
            <a href="{{ route('notifications.index') }}" class="relative w-9 h-9 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Notifikasi">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if (auth()->user()?->unreadNotificationsCount() > 0)
                    <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-rose-500 rounded-full ring-2 ring-white"></span>
                @endif
            </a>
        </div>
    @endauth

    <!-- Top Status / Branding -->
    <div class="pt-4 flex flex-col items-center text-center">
        <!-- Logo Icon -->
        <div class="w-16 h-16 bg-teal-50 border border-teal-100 rounded-2xl flex items-center justify-center mb-3 shadow-xs">
            <svg class="w-10 h-10 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                <path d="M6 12v5c3 3 9 3 12 0v-5"/>
            </svg>
        </div>

        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">
            Campus<span class="text-teal-600">Market</span>
        </h1>
        <p class="text-xs font-semibold uppercase tracking-wider text-teal-700 mt-1">
            Dari Mahasiswa, Untuk Mahasiswa
        </p>
    </div>

    <!-- Hero Card & Value Proposition -->
    <div class="my-6 bg-slate-50 border border-slate-100 rounded-2xl p-6 text-center shadow-xs">
        <div class="inline-flex p-3 bg-white rounded-xl shadow-xs border border-slate-100 mb-3">
            <svg class="w-10 h-10 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
        </div>
        <h2 class="text-lg font-bold text-slate-800 leading-snug">
            Jual, Beli, dan Temukan Semua Kebutuhan Kampus dalam Satu Tempat
        </h2>
        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
            Marketplace eksklusif komunitas mahasiswa dengan verifikasi data akademik dan transaksi aman.
        </p>

        <!-- Feature List -->
        <div class="mt-5 space-y-2 text-left">
            <div class="flex items-center gap-2.5 text-xs text-slate-700 bg-white p-2.5 rounded-xl border border-slate-100">
                <div class="w-5 h-5 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center shrink-0 font-bold">✓</div>
                <span>Verifikasi identitas mahasiswa aktif</span>
            </div>
            <div class="flex items-center gap-2.5 text-xs text-slate-700 bg-white p-2.5 rounded-xl border border-slate-100">
                <div class="w-5 h-5 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center shrink-0 font-bold">✓</div>
                <span>Domain email universitas terpercaya</span>
            </div>
            <div class="flex items-center gap-2.5 text-xs text-slate-700 bg-white p-2.5 rounded-xl border border-slate-100">
                <div class="w-5 h-5 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center shrink-0 font-bold">✓</div>
                <span>Komunitas jual-beli antar mahasiswa</span>
            </div>
        </div>

        <!-- Carousel Indicators -->
        <div class="flex justify-center items-center gap-1.5 mt-5">
            <span class="w-6 h-1.5 bg-teal-600 rounded-full"></span>
            <span class="w-2 h-1.5 bg-slate-300 rounded-full"></span>
            <span class="w-2 h-1.5 bg-slate-300 rounded-full"></span>
            <span class="w-2 h-1.5 bg-slate-300 rounded-full"></span>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="space-y-3 pb-4">
        @auth
            <a href="{{ route('products.index') }}" class="w-full block text-center py-3.5 px-4 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-md shadow-teal-600/20 transition duration-150">
                Lihat Etalase Produk
            </a>
            <a href="{{ route('profile') }}" class="w-full block text-center py-3.5 px-4 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition duration-150">
                Buka Profil Saya
            </a>
            <form method="POST" action="{{ route('logout') }}" data-turbo="false">
                @csrf
                <button type="submit" class="w-full text-center py-3.5 px-4 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition duration-150">
                    Keluar
                </button>
            </form>
        @else
            <a href="{{ route('register') }}" class="w-full block text-center py-3.5 px-4 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl shadow-md shadow-teal-600/20 transition duration-150">
                Mulai Sekarang
            </a>
            <a href="{{ route('login') }}" class="w-full block text-center py-3.5 px-4 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition duration-150">
                Sudah punya akun? Masuk
            </a>
        @endauth
    </div>
</div>
@endsection
