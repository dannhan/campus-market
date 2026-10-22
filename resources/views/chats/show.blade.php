@extends('layouts.app')

@section('title', 'Chat dengan ' . $otherParticipant->name . ' — CampusMarket')

@section('content')
<div x-data="{
        init() {
            this.scrollToBottom();
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.messagesThread;
                if (el) {
                    el.scrollTop = el.scrollHeight;
                }
            });
        }
     }"
     class="flex-1 flex flex-col bg-slate-50 h-[calc(100vh-4rem)]">

    <!-- Top Chat Bar -->
    <header class="bg-white border-b border-slate-200 px-3 py-2.5 flex items-center justify-between sticky top-0 z-20 shadow-xs">
        <div class="flex items-center gap-2.5 min-w-0">
            <a href="{{ route('chats.index') }}" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-600 hover:bg-slate-100 transition shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <img src="{{ $otherParticipant->avatar_url }}" alt="{{ $otherParticipant->name }}" class="w-9 h-9 rounded-xl object-cover border border-slate-200 shrink-0">
            <div class="min-w-0">
                <h1 class="text-xs font-bold text-slate-800 truncate leading-tight">{{ $otherParticipant->name }}</h1>
                <p class="text-[10px] text-slate-400 truncate leading-tight">
                    {{ $otherParticipant->university?->name ?? 'Mahasiswa' }} @if($otherParticipant->student?->major)· {{ $otherParticipant->student->major }}@endif
                </p>
            </div>
        </div>

        @if ($conversation->product)
            <a href="{{ route('products.show', $conversation->product) }}" class="text-[11px] font-semibold text-teal-700 hover:text-teal-800 bg-teal-50 px-2.5 py-1 rounded-lg shrink-0 transition">
                Lihat Produk
            </a>
        @endif
    </header>

    <!-- Product Context Banner -->
    @if ($conversation->product)
        @php($product = $conversation->product)
        <div class="bg-white border-b border-slate-200 p-2.5 px-3 flex items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-2.5 min-w-0">
                <img src="{{ $product->cover_image_url }}" alt="{{ $product->title }}" class="w-10 h-10 rounded-lg object-cover border border-slate-100 shrink-0">
                <div class="min-w-0">
                    <p class="text-[11px] font-bold text-slate-800 truncate">{{ $product->title }}</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="text-xs font-extrabold text-teal-700">{{ $product->formatted_price }}</span>
                        @if ($product->bargainable)
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-teal-50 text-teal-700">Nego</span>
                        @endif
                    </div>
                </div>
            </div>

            @if ($product->user_id !== $currentUser->id && $product->status === 'active' && $product->bargainable)
                <a href="{{ route('products.show', $product) }}"
                   class="text-[10px] font-bold px-2.5 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg shadow-xs shrink-0 transition">
                    Tawar Harga
                </a>
            @endif
        </div>
    @endif

    <!-- Messages Thread -->
    <div x-ref="messagesThread" class="flex-1 overflow-y-auto p-3 space-y-3">
        @forelse ($messages as $message)
            @php($isMe = $message->isSender($currentUser))
            <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                <div class="max-w-[78%] px-3.5 py-2 rounded-2xl text-xs leading-relaxed shadow-2xs
                    {{ $isMe ? 'bg-teal-600 text-white rounded-tr-xs' : 'bg-white text-slate-800 border border-slate-200 rounded-tl-xs' }}">
                    <p class="whitespace-pre-line break-words">{{ $message->body }}</p>
                    <div class="mt-1 flex items-center justify-end gap-1 text-[9px] {{ $isMe ? 'text-teal-100' : 'text-slate-400' }}">
                        <span>{{ $message->created_at->format('H:i') }}</span>
                        @if ($isMe)
                            @if ($message->isRead())
                                <span title="Dibaca">✓✓</span>
                            @else
                                <span title="Terkirim">✓</span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="h-full flex flex-col items-center justify-center text-center p-6 text-slate-400">
                <div class="w-12 h-12 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-slate-700">Mulai Percakapan</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Tanyakan ketersediaan barang, kondisi, atau lokasi COD.</p>
            </div>
        @endforelse
    </div>

    <!-- Message Input Bar -->
    <div class="bg-white border-t border-slate-200 p-2.5 pb-3">
        <form method="POST" action="{{ route('chats.messages.store', $conversation) }}" class="flex items-center gap-2">
            @csrf
            <div class="flex-1 relative">
                <input type="text"
                       name="body"
                       required
                       maxlength="2000"
                       autocomplete="off"
                       placeholder="Tulis pesan..."
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 transition">
            </div>
            <button type="submit"
                    class="w-9 h-9 bg-teal-600 hover:bg-teal-700 text-white rounded-xl flex items-center justify-center shadow-md shadow-teal-600/20 transition shrink-0 active:scale-95">
                <svg class="w-4 h-4 rotate-90" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                </svg>
            </button>
        </form>
        @error('body')
            <p class="text-[10px] text-rose-600 mt-1 px-1">{{ $message }}</p>
        @enderror
    </div>
</div>
@endsection
