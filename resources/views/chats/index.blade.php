@extends('layouts.app')

@section('title', 'Pesan — CampusMarket')

@section('content')
<div class="flex-1 flex flex-col bg-slate-50 min-h-full">
    <!-- Header -->
    <header class="bg-white border-b border-slate-200 px-4 py-3.5 sticky top-0 z-10 flex items-center justify-between shadow-xs">
        <div>
            <h1 class="text-base font-bold text-slate-800">Chat</h1>
            <p class="text-[11px] text-slate-400">Komunikasi langsung pembeli & penjual</p>
        </div>
        @php($totalUnread = auth()->user()->unreadMessagesCount())
        @if ($totalUnread > 0)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-600 text-white shadow-xs">
                {{ $totalUnread }} pesan baru
            </span>
        @endif
    </header>

    <!-- Conversation List -->
    <div class="flex-1 p-3 space-y-2">
        @forelse ($conversations as $conversation)
            @php($other = $conversation->otherParticipant(auth()->user()))
            @php($product = $conversation->product)
            @php($latest = $conversation->latestMessage)
            @php($unread = $conversation->unreadCountFor(auth()->user()))

            <a href="{{ route('chats.show', $conversation) }}"
               class="block bg-white border {{ $unread > 0 ? 'border-teal-300 ring-1 ring-teal-200 bg-teal-50/20' : 'border-slate-200 hover:border-slate-300' }} rounded-2xl p-3 shadow-xs transition active:scale-[0.99]">
                <div class="flex items-start gap-3">
                    <!-- Avatar -->
                    <div class="relative shrink-0">
                        <img src="{{ $other->avatar_url }}" alt="{{ $other->name }}" class="w-12 h-12 rounded-xl object-cover border border-slate-200">
                        @if ($unread > 0)
                            <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-teal-600 border-2 border-white rounded-full"></span>
                        @endif
                    </div>

                    <!-- Conversation Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1">
                            <h2 class="text-xs font-bold text-slate-800 truncate">{{ $other->name }}</h2>
                            @if ($latest)
                                <span class="text-[10px] text-slate-400 shrink-0 font-medium">
                                    {{ $latest->created_at->diffForHumans(null, true) }}
                                </span>
                            @endif
                        </div>

                        <!-- University badge -->
                        <p class="text-[10px] text-slate-400 truncate">
                            {{ $other->university?->name ?? 'Mahasiswa' }} @if($other->student?->major)· {{ $other->student->major }}@endif
                        </p>

                        <!-- Product snippet if attached -->
                        @if ($product)
                            <div class="mt-1.5 flex items-center gap-2 bg-slate-50 border border-slate-100 rounded-lg p-1.5">
                                <img src="{{ $product->cover_image_url }}" alt="{{ $product->title }}" class="w-7 h-7 rounded object-cover shrink-0">
                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-semibold text-slate-700 truncate leading-tight">{{ $product->title }}</p>
                                    <p class="text-[10px] font-extrabold text-teal-700 leading-tight">{{ $product->formatted_price }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Latest Message -->
                        <div class="mt-1.5 flex items-center justify-between gap-2">
                            <p class="text-[11px] {{ $unread > 0 ? 'font-bold text-slate-900' : 'text-slate-500 font-normal' }} truncate">
                                @if ($latest)
                                    @if ($latest->isSender(auth()->user()))
                                        <span class="text-slate-400 font-normal">Anda: </span>
                                    @endif
                                    {{ $latest->body }}
                                @else
                                    <span class="italic text-slate-400">Belum ada pesan</span>
                                @endif
                            </p>

                            @if ($unread > 0)
                                <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-teal-600 text-white">
                                    {{ $unread }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="py-16 px-4 text-center">
                <div class="w-16 h-16 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-xs">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h2 class="text-sm font-bold text-slate-800">Belum ada obrolan</h2>
                <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto leading-relaxed">
                    Mulai percakapan dengan penjual melalui tombol <span class="font-semibold text-slate-700">"Chat Penjual"</span> di halaman detail produk.
                </p>
                <a href="{{ route('products.index') }}" class="mt-4 inline-block px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl shadow-md shadow-teal-600/20 transition">
                    Jelajahi Etalase Produk
                </a>
            </div>
        @endforelse

        <!-- Pagination -->
        @if ($conversations->hasPages())
            <div class="pt-2">
                {{ $conversations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
