@extends('layouts.app')

@section('title', 'Tawaran Harga — CampusMarket')

@section('content')
<div x-data="{ tab: 'received' }" class="p-4 pb-6 flex-1">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('products.index') }}" class="w-9 h-9 bg-white border border-slate-200 rounded-full flex items-center justify-center shadow-xs hover:bg-slate-50 transition">
            <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-lg font-extrabold tracking-tight text-slate-900">Tawaran <span class="text-teal-600">Harga</span></h1>
            <p class="text-xs text-slate-500">Kotak masuk negosiasi tawar-menawar</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex bg-slate-100 rounded-xl p-1 mb-4">
        <button type="button" @click="tab = 'received'" :class="tab === 'received' ? 'bg-white shadow-sm text-teal-700' : 'text-slate-500'" class="flex-1 py-2 text-xs font-semibold rounded-lg transition">
            Masuk ({{ $receivedOffers->total() }})
        </button>
        <button type="button" @click="tab = 'sent'" :class="tab === 'sent' ? 'bg-white shadow-sm text-teal-700' : 'text-slate-500'" class="flex-1 py-2 text-xs font-semibold rounded-lg transition">
            Terkirim ({{ $sentOffers->total() }})
        </button>
    </div>

    <!-- Received Offers (as seller) -->
    <div x-show="tab === 'received'" class="space-y-3">
        @forelse ($receivedOffers as $offer)
            <div class="bg-white border border-slate-100 rounded-2xl p-3.5 shadow-xs">
                <div class="flex items-center gap-3">
                    <img src="{{ $offer->product->cover_image_url }}" alt="{{ $offer->product->title }}" class="w-12 h-12 rounded-xl object-cover border border-slate-100 shrink-0">
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('products.show', $offer->product) }}" class="text-xs font-bold text-slate-800 truncate block hover:text-teal-700">{{ $offer->product->title }}</a>
                        <p class="text-[11px] text-slate-500 truncate">{{ $offer->user->name }} · {{ $offer->user->university?->name ?? 'Mahasiswa' }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-extrabold text-teal-700">{{ $offer->formatted_price }}</p>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-md {{ $offer->status === 'pending' ? 'bg-amber-100 text-amber-700' : ($offer->status === 'accepted' ? 'bg-teal-100 text-teal-700' : ($offer->status === 'countered' ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-500')) }}">
                            {{ ucfirst($offer->status) }}
                        </span>
                    </div>
                </div>
                @if ($offer->message)
                    <p class="text-[11px] text-slate-600 bg-slate-50 rounded-xl px-3 py-2 mt-2.5 leading-relaxed">"{{ $offer->message }}"</p>
                @endif
                @if ($offer->status === 'pending' && $offer->product->status === 'active')
                    <div class="grid grid-cols-3 gap-2 mt-3">
                        <form method="POST" action="{{ route('offers.respond', $offer) }}">
                            @csrf
                            <input type="hidden" name="action" value="accept">
                            <button type="submit" class="w-full py-2 bg-teal-600 hover:bg-teal-700 text-white text-[11px] font-semibold rounded-xl transition">Terima</button>
                        </form>
                        <form method="POST" action="{{ route('offers.respond', $offer) }}">
                            @csrf
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="w-full py-2 bg-white border border-slate-200 text-slate-600 text-[11px] font-semibold rounded-xl hover:bg-slate-50 transition">Tolak</button>
                        </form>
                        <form method="POST" action="{{ route('offers.respond', $offer) }}" class="contents">
                            @csrf
                            <input type="hidden" name="action" value="counter">
                            <input type="hidden" name="message" value="Tawaran balasan dari penjual">
                            <div class="relative">
                                <input type="number" name="offered_price" min="1000" step="500" required placeholder="Rp"
                                       class="w-full pl-6 pr-2 py-2 bg-white border border-teal-200 rounded-xl text-[11px] placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/40">
                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-semibold">Rp</span>
                            </div>
                            <button type="submit" class="sr-only">Nego balik</button>
                        </form>
                    </div>
                    @error('offered_price')<p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>@enderror
                @endif
            </div>
        @empty
            <div class="py-12 text-center">
                <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                <p class="text-xs text-slate-500 mt-3 font-medium">Belum ada penawaran masuk.</p>
            </div>
        @endforelse
        {{ $receivedOffers->links() }}
    </div>

    <!-- Sent Offers (as buyer/counterparty) -->
    <div x-show="tab === 'sent'" x-cloak class="space-y-3">
        @forelse ($sentOffers as $offer)
            <div class="bg-white border border-slate-100 rounded-2xl p-3.5 shadow-xs">
                <div class="flex items-center gap-3">
                    <img src="{{ $offer->product->cover_image_url }}" alt="{{ $offer->product->title }}" class="w-12 h-12 rounded-xl object-cover border border-slate-100 shrink-0">
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('products.show', $offer->product) }}" class="text-xs font-bold text-slate-800 truncate block hover:text-teal-700">{{ $offer->product->title }}</a>
                        <p class="text-[11px] text-slate-500 truncate">Penjual: {{ $offer->product->user->name }}</p>
                        @if ($offer->parent_offer_id)
                            <p class="text-[10px] text-sky-600 font-semibold mt-0.5">Tawaran balasan</p>
                        @endif
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-sm font-extrabold text-teal-700">{{ $offer->formatted_price }}</p>
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-md {{ $offer->status === 'pending' ? 'bg-amber-100 text-amber-700' : ($offer->status === 'accepted' ? 'bg-teal-100 text-teal-700' : ($offer->status === 'countered' ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-500')) }}">
                            {{ ucfirst($offer->status) }}
                        </span>
                    </div>
                </div>
                @if ($offer->message)
                    <p class="text-[11px] text-slate-600 bg-slate-50 rounded-xl px-3 py-2 mt-2.5 leading-relaxed">"{{ $offer->message }}"</p>
                @endif
                @if ($offer->status === 'pending')
                    <form method="POST" action="{{ route('offers.cancel', $offer) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="text-[11px] font-semibold text-slate-500 hover:text-rose-600 underline">Batalkan penawaran</button>
                    </form>
                @endif
            </div>
        @empty
            <div class="py-12 text-center">
                <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <p class="text-xs text-slate-500 mt-3 font-medium">Anda belum mengajukan penawaran.</p>
                <a href="{{ route('products.index') }}" class="inline-block mt-2 text-[11px] font-semibold text-teal-700 underline">Jelajahi etalase</a>
            </div>
        @endforelse
        {{ $sentOffers->links() }}
    </div>
</div>
@endsection
