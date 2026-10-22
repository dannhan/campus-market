@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('welcome') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-base font-bold text-slate-900">Profil Saya</h1>
        <a href="{{ route('profile.edit') }}" class="w-9 h-9 -mr-1 rounded-full flex items-center justify-center text-slate-600 hover:bg-slate-100 transition" title="Pengaturan & Edit">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </a>
    </div>

    <div class="p-5 space-y-5">
        <!-- Profile Identity Card -->
        <div class="bg-gradient-to-b from-teal-50/60 to-white border border-teal-100/80 rounded-2xl p-5 text-center shadow-xs">
            <!-- Avatar -->
            <div class="relative w-20 h-20 mx-auto mb-3">
                <img
                    src="{{ $user->avatar_url }}"
                    alt="{{ $user->name }}"
                    class="w-20 h-20 rounded-full object-cover border-2 border-teal-600 shadow-sm"
                >
                <div class="absolute bottom-0 right-0 w-6 h-6 bg-teal-600 border-2 border-white rounded-full flex items-center justify-center text-white" title="Mahasiswa Terverifikasi">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>

            <!-- Name & Badge -->
            <h2 class="text-lg font-bold text-slate-900 leading-tight">{{ $user->name }}</h2>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-teal-100/80 text-teal-800 rounded-full text-[11px] font-semibold mt-2">
                <svg class="w-3.5 h-3.5 text-teal-700" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>Mahasiswa Terverifikasi</span>
            </div>

            <!-- Action: Edit Profile -->
            <div class="mt-4 pt-4 border-t border-teal-100 flex items-center justify-center gap-3">
                <a
                    href="{{ route('profile.edit') }}"
                    class="px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl shadow-xs transition"
                >
                    Edit Profil
                </a>
            </div>
        </div>

        <!-- Academic Information Card -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 px-1">
                Data Kemahasiswaan
            </h3>

            <div class="space-y-3.5">
                <!-- Universitas -->
                <div class="flex items-start gap-3 p-2 rounded-xl hover:bg-slate-50 transition">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="text-[11px] text-slate-400 font-medium">Universitas</div>
                        <div class="text-xs font-bold text-slate-800">{{ $university?->name ?? 'Belum terdata' }}</div>
                    </div>
                </div>

                <!-- Jurusan / Program Studi -->
                <div class="flex items-start gap-3 p-2 rounded-xl hover:bg-slate-50 transition">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="text-[11px] text-slate-400 font-medium">Program Studi / Jurusan</div>
                        <div class="text-xs font-bold text-slate-800">{{ $student?->major ?? 'Informatika' }}</div>
                    </div>
                </div>

                <!-- NIM -->
                <div class="flex items-start gap-3 p-2 rounded-xl hover:bg-slate-50 transition">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="text-[11px] text-slate-400 font-medium">Nomor Induk Mahasiswa (NIM)</div>
                        <div class="text-xs font-bold text-slate-800 font-mono">{{ $student?->student_number ?? '-' }}</div>
                    </div>
                </div>

                <!-- Email Universitas -->
                <div class="flex items-start gap-3 p-2 rounded-xl hover:bg-slate-50 transition">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="text-[11px] text-slate-400 font-medium">Email Universitas</div>
                        <div class="text-xs font-bold text-slate-800 font-mono">{{ $user->email }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Marketplace Activity (Sprint 2) -->
        <a href="{{ route('offers.index') }}" class="block bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs hover:border-teal-300 transition">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="text-xs font-bold text-slate-800">Tawaran Saya</div>
                    <div class="text-[11px] text-slate-400 font-medium">Kotak masuk negosiasi tawar-menawar</div>
                </div>
                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>

        <!-- Wishlist / Bookmark (SRS-017) -->
        <a href="{{ route('wishlist.index') }}" class="block bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs hover:border-teal-300 transition">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800">Wishlist Saya</span>
                        @php($wishlistCount = $user->wishlists()->count())
                        @if ($wishlistCount > 0)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 border border-rose-100">
                                {{ $wishlistCount }} barang
                            </span>
                        @endif
                    </div>
                    <div class="text-[11px] text-slate-400 font-medium">Daftar produk incaran yang kamu simpan</div>
                </div>
                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>

        <!-- Rating Card (Prepared for future sprints without fabricated data) -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 px-1">
                Reputasi & Rating
            </h3>

            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="text-[11px] text-slate-500 font-medium">Sebagai Penjual</div>
                    <div class="text-xs font-bold text-slate-800 mt-1">
                        {{ $user->seller_rating_display }}
                    </div>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="text-[11px] text-slate-500 font-medium">Sebagai Pembeli</div>
                    <div class="text-xs font-bold text-slate-800 mt-1">
                        {{ $user->buyer_rating_display }}
                    </div>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 mt-2 px-1">
                Rating dihitung otomatis dari transaksi tuntas pada sprint mendatang.
            </p>
        </div>

        <!-- Account Meta & Logout -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-3">
            <div class="flex items-center justify-between text-xs px-1 text-slate-600">
                <span>Bergabung sejak:</span>
                <span class="font-bold text-slate-800">{{ $user->created_at->translatedFormat('F Y') }}</span>
            </div>

            <form method="POST" action="{{ route('logout') }}" data-turbo="false" class="pt-2 border-t border-slate-100">
                @csrf
                <button
                    type="submit"
                    class="w-full py-2.5 px-4 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold rounded-xl transition duration-150 flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Keluar dari Akun</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Bottom navigation is rendered by the shared layout (layouts/nav.blade.php). -->
</div>
@endsection
