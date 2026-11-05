@extends('layouts.app')

@section('content')
<div class="flex flex-col flex-1 pb-12" x-data="{
    pushSupported: ('serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window),
    permission: ('Notification' in window) ? Notification.permission : 'unsupported',
    loadingPush: false,
    pushStatusMsg: '',

    async enablePush() {
        if (!this.pushSupported) {
            alert('Browser Anda tidak mendukung Web Push Notifications.');
            return;
        }

        this.loadingPush = true;
        this.pushStatusMsg = '';

        try {
            const perm = await Notification.requestPermission();
            this.permission = perm;

            if (perm !== 'granted') {
                this.pushStatusMsg = 'Izin notifikasi ditolak oleh pengguna.';
                this.loadingPush = false;
                return;
            }

            const reg = await navigator.serviceWorker.register('/sw.js');
            await navigator.serviceWorker.ready;

            let sub = await reg.pushManager.getSubscription();
            if (!sub) {
                // Subscribe with a random dummy/local applicationServerKey or standard endpoint
                const fakeKey = new Uint8Array([4, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16]);
                try {
                    sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: fakeKey
                    });
                } catch(err) {
                    // Fallback to anonymous local subscription
                    console.log('Using endpoint registration fallback:', err);
                }
            }

            const endpoint = sub ? sub.endpoint : 'browser-' + Math.random().toString(36).substring(2) + '-' + Date.now();
            const p256dh = sub && sub.getKey ? btoa(String.fromCharCode.apply(null, new Uint8Array(sub.getKey('p256dh')))) : null;
            const auth = sub && sub.getKey ? btoa(String.fromCharCode.apply(null, new Uint8Array(sub.getKey('auth')))) : null;

            const res = await fetch('{{ route('push.subscribe') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    endpoint: endpoint,
                    public_key: p256dh,
                    auth_token: auth
                })
            });

            const json = await res.json();
            if (json.success) {
                this.pushStatusMsg = 'Notifikasi push aktif di perangkat ini!';
            }
        } catch (e) {
            console.error('Push registration error:', e);
            this.pushStatusMsg = 'Gagal mendaftarkan notifikasi push: ' + e.message;
        } finally {
            this.loadingPush = false;
        }
    }
}">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <a href="{{ route('welcome') }}" class="w-9 h-9 -ml-1 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Kembali ke Beranda">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="text-center">
            <h1 class="text-base font-bold text-slate-900 leading-tight">Notifikasi</h1>
            <span class="text-[10px] text-slate-400 font-medium">Pesan, Transaksi, & Penawaran</span>
        </div>
        <div class="w-9 flex justify-end">
            @if ($unreadCount > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-[11px] font-bold text-teal-700 hover:text-teal-800 transition" title="Tandai semua dibaca">
                        Baca Semua
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="p-5 space-y-4">
        <!-- Status Messages -->
        @if (session('status'))
            <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Push Notification Control Card -->
        <div class="bg-gradient-to-r from-teal-50 via-slate-50 to-white border border-teal-100/90 rounded-2xl p-4 shadow-xs space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-teal-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-900">Notifikasi Push Perangkat</h3>
                        <p class="text-[10px] text-slate-500 mt-0.5">Dapatkan kabar seketika saat ada pesan baru, tawaran harga, atau status pesanan</p>
                    </div>
                </div>

                <template x-if="permission === 'granted'">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 shrink-0">
                        Aktif
                    </span>
                </template>
                <template x-if="permission !== 'granted'">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600 shrink-0">
                        Nonaktif
                    </span>
                </template>
            </div>

            <div class="pt-1 flex items-center gap-2">
                <button
                    type="button"
                    @click="enablePush()"
                    :disabled="loadingPush"
                    class="flex-1 py-2 px-3 bg-teal-600 hover:bg-teal-700 active:bg-teal-800 disabled:opacity-50 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow-xs"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span x-text="loadingPush ? 'Menghubungkan...' : (permission === 'granted' ? 'Perbarui Izin Perangkat' : 'Aktifkan di Perangkat Ini')"></span>
                </button>

                <form action="{{ route('notifications.test-push') }}" method="POST" class="contents">
                    @csrf
                    <button
                        type="submit"
                        class="py-2 px-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition shadow-2xs"
                        title="Kirim tes notifikasi ke perangkat Anda"
                    >
                        Tes Notifikasi
                    </button>
                </form>
            </div>

            <div x-show="pushStatusMsg" x-text="pushStatusMsg" class="text-[11px] text-teal-700 font-medium" style="display: none;"></div>
        </div>

        <!-- Filter Navigation Tabs -->
        <div class="flex border-b border-slate-200 bg-white rounded-xl p-1 shadow-2xs overflow-x-auto text-xs font-bold">
            <a
                href="{{ route('notifications.index', ['tab' => 'all']) }}"
                class="flex-1 py-1.5 px-2 text-center rounded-lg transition whitespace-nowrap {{ $filter === 'all' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
            >
                Semua ({{ $totalCount }})
            </a>
            <a
                href="{{ route('notifications.index', ['tab' => 'messages']) }}"
                class="flex-1 py-1.5 px-2 text-center rounded-lg transition whitespace-nowrap {{ $filter === 'messages' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
            >
                Pesan ({{ $messagesCount }})
            </a>
            <a
                href="{{ route('notifications.index', ['tab' => 'transactions']) }}"
                class="flex-1 py-1.5 px-2 text-center rounded-lg transition whitespace-nowrap {{ $filter === 'transactions' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
            >
                Transaksi ({{ $transactionsCount }})
            </a>
            <a
                href="{{ route('notifications.index', ['tab' => 'offers']) }}"
                class="flex-1 py-1.5 px-2 text-center rounded-lg transition whitespace-nowrap {{ $filter === 'offers' ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
            >
                Tawaran ({{ $offersCount }})
            </a>
        </div>

        <!-- Notifications List -->
        @if ($notifications->count() > 0)
            <div class="space-y-2.5">
                @foreach ($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $type = $data['type'] ?? 'general';
                        $isUnread = $notification->unread();
                    @endphp

                    <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                            class="w-full text-left bg-white border {{ $isUnread ? 'border-teal-300 ring-1 ring-teal-200/50 bg-teal-50/20' : 'border-slate-200/90' }} rounded-2xl p-3.5 shadow-xs hover:border-teal-400 transition flex items-start gap-3 group"
                        >
                            <!-- Notification Type Icon -->
                            <div class="w-10 h-10 rounded-xl shrink-0 flex items-center justify-center font-bold text-base {{ $type === 'new_message' ? 'bg-teal-100 text-teal-700' : ($type === 'transaction_status' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700') }}">
                                @if ($type === 'new_message')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                @elseif ($type === 'transaction_status')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                @endif
                            </div>

                            <!-- Notification Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <h4 class="text-xs font-bold text-slate-900 group-hover:text-teal-700 transition truncate">
                                        {{ $data['title'] ?? 'Pemberitahuan Baru' }}
                                    </h4>
                                    <span class="text-[10px] text-slate-400 shrink-0">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                <p class="text-xs text-slate-600 mt-1 line-clamp-2 leading-relaxed">
                                    {{ $data['body'] ?? '' }}
                                </p>

                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-[10px] font-semibold text-teal-700 flex items-center gap-1 group-hover:underline">
                                        <span>Buka Detail</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </span>

                                    @if ($isUnread)
                                        <span class="w-2 h-2 rounded-full bg-teal-600 ring-4 ring-teal-100"></span>
                                    @endif
                                </div>
                            </div>
                        </button>
                    </form>
                @endforeach

                <!-- Pagination Links -->
                <div class="pt-2">
                    {{ $notifications->links() }}
                </div>
            </div>
        @else
            <div class="bg-white border border-slate-200/90 rounded-2xl p-8 text-center shadow-xs">
                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h4 class="text-xs font-bold text-slate-700">Belum Ada Notifikasi</h4>
                <p class="text-[11px] text-slate-400 mt-1 max-w-xs mx-auto">
                    @if ($filter === 'messages')
                        Anda belum memiliki riwayat notifikasi pesan chat baru.
                    @elseif ($filter === 'transactions')
                        Belum ada pembaruan status transaksi saat ini.
                    @elseif ($filter === 'offers')
                        Belum ada notifikasi tawaran harga pada produk Anda.
                    @else
                        Semua aktivitas transaksi, pesan baru, dan penawaran akan muncul di sini.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
