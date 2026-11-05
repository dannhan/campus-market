@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-12" x-data="{ currentTab: '{{ $tab === 'buyer' ? 'buyer' : 'seller' }}' }">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ url()->previous() != url()->current() ? url()->previous() : route('profile.show') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-base font-bold text-slate-900">Reputasi & Ulasan</h1>
        <div class="w-9"></div>
    </div>

    <div class="p-5 space-y-5">
        <!-- Student Identity & Rating Summary Card -->
        <div class="bg-gradient-to-b from-teal-50/70 via-white to-white border border-teal-100/90 rounded-2xl p-5 text-center shadow-xs">
            <div class="relative w-20 h-20 mx-auto mb-3">
                <img
                    src="{{ $user->avatar_url }}"
                    alt="{{ $user->name }}"
                    class="w-20 h-20 rounded-full object-cover border-2 border-teal-600 shadow-sm"
                >
                @if ($user->is_student_verified)
                    <div class="absolute bottom-0 right-0 w-6 h-6 bg-teal-600 border-2 border-white rounded-full flex items-center justify-center text-white" title="Mahasiswa Terverifikasi">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                @endif
            </div>

            <h2 class="text-base font-bold text-slate-900">{{ $user->name }}</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ $user->student?->university?->name ?? 'Mahasiswa' }}
                @if ($user->student?->major)
                    &bull; {{ $user->student->major }}
                @endif
            </p>

            @if ($user->student)
                <div class="mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold {{ $user->isAlumni() ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                        {{ $user->student_status_label }}
                    </span>
                </div>
            @endif

            <!-- Overall Reputation Metrics -->
            <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-2 gap-3 text-center">
                <div class="p-3 bg-white rounded-xl border border-slate-200/80 shadow-2xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Sebagai Penjual</span>
                    <div class="flex items-center justify-center gap-1.5 mt-1">
                        <svg class="w-4 h-4 fill-amber-400 text-amber-400 shrink-0" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        <span class="text-sm font-extrabold text-slate-900">{{ $user->seller_rating !== null ? number_format((float) $user->seller_rating, 1) . ' / 5.0' : 'Belum Ada' }}</span>
                    </div>
                    <span class="text-[10px] text-slate-500 block mt-0.5">{{ $user->seller_review_count }} ulasan tuntas</span>
                </div>

                <div class="p-3 bg-white rounded-xl border border-slate-200/80 shadow-2xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Sebagai Pembeli</span>
                    <div class="flex items-center justify-center gap-1.5 mt-1">
                        <svg class="w-4 h-4 fill-amber-400 text-amber-400 shrink-0" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        <span class="text-sm font-extrabold text-slate-900">{{ $user->buyer_rating !== null ? number_format((float) $user->buyer_rating, 1) . ' / 5.0' : 'Belum Ada' }}</span>
                    </div>
                    <span class="text-[10px] text-slate-500 block mt-0.5">{{ $user->buyer_review_count }} ulasan tuntas</span>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex border-b border-slate-200 bg-white rounded-xl p-1 shadow-2xs">
            <button
                type="button"
                @click="currentTab = 'seller'"
                class="flex-1 py-2 text-center text-xs font-bold rounded-lg transition"
                :class="currentTab === 'seller' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
            >
                Ulasan Penjual ({{ $user->seller_review_count }})
            </button>
            <button
                type="button"
                @click="currentTab = 'buyer'"
                class="flex-1 py-2 text-center text-xs font-bold rounded-lg transition"
                :class="currentTab === 'buyer' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
            >
                Ulasan Pembeli ({{ $user->buyer_review_count }})
            </button>
        </div>

        <!-- TAB 1: Seller Reviews -->
        <div x-show="currentTab === 'seller'" class="space-y-4">
            @if ($user->seller_review_count > 0)
                <!-- Star Rating Distribution Bar -->
                <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-2">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2">Distribusi Penilaian</h3>
                    @for ($star = 5; $star >= 1; $star--)
                        @php
                            $count = $sellerRatingsDistribution[$star] ?? 0;
                            $percent = $user->seller_review_count > 0 ? round(($count / $user->seller_review_count) * 100) : 0;
                        @endphp
                        <div class="flex items-center gap-2 text-xs">
                            <span class="w-6 text-right font-medium text-slate-600 flex items-center justify-end gap-0.5">
                                {{ $star }} <svg class="w-3 h-3 fill-amber-400 text-amber-400 inline" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            </span>
                            <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-400 rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                            <span class="w-8 text-right text-[11px] text-slate-400">{{ $count }}</span>
                        </div>
                    @endfor
                </div>
            @endif

            <!-- List of Seller Reviews -->
            @if ($sellerReviews->count() > 0)
                <div class="space-y-3">
                    @foreach ($sellerReviews as $review)
                        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-2.5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <img
                                        src="{{ $review->reviewer->avatar_url }}"
                                        alt="{{ $review->reviewer->name }}"
                                        class="w-8 h-8 rounded-full object-cover border border-slate-200"
                                    >
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-900">{{ $review->reviewer->name }}</h4>
                                        <span class="text-[10px] text-slate-400 block">{{ $review->reviewer->student?->university?->name ?? 'Mahasiswa' }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="flex items-center gap-0.5 justify-end">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <svg class="w-3.5 h-3.5 {{ $s <= $review->rating ? 'fill-amber-400 text-amber-400' : 'fill-slate-200 text-slate-200' }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                        @endfor
                                    </div>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            @if ($review->product)
                                <div class="text-[11px] text-slate-500 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100 flex items-center justify-between">
                                    <span class="truncate">Produk: <strong>{{ $review->product->title }}</strong></span>
                                </div>
                            @endif

                            @if ($review->tags && count($review->tags) > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($review->tags as $tag)
                                        <span class="text-[10px] font-medium bg-teal-50 border border-teal-200 text-teal-800 px-2 py-0.5 rounded-full">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            @if ($review->comment)
                                <p class="text-xs text-slate-700 leading-relaxed bg-slate-50/60 p-2.5 rounded-xl border border-slate-100">
                                    "{{ $review->comment }}"
                                </p>
                            @endif
                        </div>
                    @endforeach

                    <!-- Pagination -->
                    <div class="pt-2">
                        {{ $sellerReviews->links() }}
                    </div>
                </div>
            @else
                <div class="bg-white border border-slate-200/90 rounded-2xl p-8 text-center shadow-xs">
                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </div>
                    <h4 class="text-xs font-bold text-slate-700">Belum Ada Ulasan Penjual</h4>
                    <p class="text-[11px] text-slate-400 mt-1">Pengguna ini belum menerima ulasan dari transaksi penjualan.</p>
                </div>
            @endif
        </div>

        <!-- TAB 2: Buyer Reviews -->
        <div x-show="currentTab === 'buyer'" class="space-y-4" style="display: none;">
            @if ($buyerReviews->count() > 0)
                <div class="space-y-3">
                    @foreach ($buyerReviews as $review)
                        <div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-2.5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <img
                                        src="{{ $review->reviewer->avatar_url }}"
                                        alt="{{ $review->reviewer->name }}"
                                        class="w-8 h-8 rounded-full object-cover border border-slate-200"
                                    >
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-900">{{ $review->reviewer->name }}</h4>
                                        <span class="text-[10px] text-slate-400 block">{{ $review->reviewer->student?->university?->name ?? 'Mahasiswa' }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="flex items-center gap-0.5 justify-end">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <svg class="w-3.5 h-3.5 {{ $s <= $review->rating ? 'fill-amber-400 text-amber-400' : 'fill-slate-200 text-slate-200' }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                        @endfor
                                    </div>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            @if ($review->product)
                                <div class="text-[11px] text-slate-500 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100 flex items-center justify-between">
                                    <span class="truncate">Produk: <strong>{{ $review->product->title }}</strong></span>
                                </div>
                            @endif

                            @if ($review->tags && count($review->tags) > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($review->tags as $tag)
                                        <span class="text-[10px] font-medium bg-teal-50 border border-teal-200 text-teal-800 px-2 py-0.5 rounded-full">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            @if ($review->comment)
                                <p class="text-xs text-slate-700 leading-relaxed bg-slate-50/60 p-2.5 rounded-xl border border-slate-100">
                                    "{{ $review->comment }}"
                                </p>
                            @endif
                        </div>
                    @endforeach

                    <!-- Pagination -->
                    <div class="pt-2">
                        {{ $buyerReviews->links() }}
                    </div>
                </div>
            @else
                <div class="bg-white border border-slate-200/90 rounded-2xl p-8 text-center shadow-xs">
                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </div>
                    <h4 class="text-xs font-bold text-slate-700">Belum Ada Ulasan Pembeli</h4>
                    <p class="text-[11px] text-slate-400 mt-1">Pengguna ini belum menerima ulasan dari transaksi pembelian.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
