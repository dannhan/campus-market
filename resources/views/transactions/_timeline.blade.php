@php
    $timeline = $transaction->timeline;
    $steps = $timeline['steps'];
    $events = $timeline['events'];
    $isCancelled = $timeline['is_cancelled'];
    $isCompleted = $timeline['is_completed'];
@endphp

<!-- Visual Transaction Status & Timeline Card -->
<div class="bg-white border border-slate-200/90 rounded-2xl p-4 shadow-xs space-y-4">
    <!-- Status Header & Progress Indicator -->
    <div class="space-y-2">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0 font-bold {{ $isCancelled ? 'bg-rose-100 text-rose-700' : ($isCompleted ? 'bg-emerald-100 text-emerald-700' : 'bg-teal-100 text-teal-800') }}">
                    @if ($isCancelled)
                        ✕
                    @elseif ($isCompleted)
                        🎉
                    @elseif ($transaction->delivery_method === 'cod')
                        🤝
                    @elseif ($transaction->delivery_method === 'pickup')
                        🏫
                    @else
                        🚚
                    @endif
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Status Pelacakan Transaksi</span>
                    <h2 class="text-sm font-extrabold text-slate-900 leading-snug">{{ $timeline['status_title'] }}</h2>
                </div>
            </div>
            <span class="text-xs font-bold px-2.5 py-1 rounded-full border shrink-0 {{ $timeline['status_badge'] }}">
                {{ $transaction->status_label }}
            </span>
        </div>

        <p class="text-xs text-slate-600 leading-relaxed pt-0.5">
            {{ $timeline['status_description'] }}
        </p>

        <!-- Progress Percentage Bar -->
        <div class="pt-1">
            <div class="flex items-center justify-between text-[11px] font-semibold mb-1 text-slate-500">
                <span>Progres Transaksi</span>
                <span class="font-mono {{ $isCancelled ? 'text-rose-600' : ($isCompleted ? 'text-emerald-600' : 'text-teal-700') }}">
                    {{ $timeline['progress_percentage'] }}%
                </span>
            </div>
            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                <div
                    class="h-full rounded-full transition-all duration-500 {{ $isCancelled ? 'bg-rose-500' : ($isCompleted ? 'bg-emerald-500' : 'bg-gradient-to-r from-teal-500 to-indigo-600') }}"
                    style="width: {{ $timeline['progress_percentage'] }}%"
                ></div>
            </div>
        </div>
    </div>

    <!-- Stepper Track (Horizontal Visual Timeline) -->
    <div class="pt-3 border-t border-slate-100">
        <div class="relative">
            <!-- Connecting Line Background -->
            <div class="absolute top-4 left-6 right-6 h-0.5 bg-slate-200 -z-0"></div>

            <ol class="relative z-10 grid {{ count($steps) === 4 ? 'grid-cols-4' : 'grid-cols-5' }} gap-1 text-center">
                @foreach ($steps as $st)
                    @php
                        $isPast = $st['state'] === 'completed';
                        $isCurrent = $st['state'] === 'current';
                        $isUpcoming = $st['state'] === 'upcoming';
                        $isStepCancelled = $st['state'] === 'cancelled';
                    @endphp
                    <li class="flex flex-col items-center group">
                        <!-- Node Circle -->
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all shadow-xs {{
                            $isPast ? 'bg-emerald-600 text-white' :
                            ($isCurrent ? 'bg-teal-600 text-white ring-4 ring-teal-600/20 animate-pulse' :
                            ($isStepCancelled ? 'bg-rose-600 text-white' : 'bg-white border-2 border-slate-300 text-slate-400'))
                        }}">
                            @if ($isPast)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @elseif ($isStepCancelled)
                                <span>✕</span>
                            @elseif ($isCurrent)
                                <span>{{ $st['index'] }}</span>
                            @else
                                <span>{{ $st['index'] }}</span>
                            @endif
                        </div>

                        <!-- Step Title & State -->
                        <span class="text-[10px] font-bold mt-1.5 leading-tight {{
                            $isPast ? 'text-emerald-800' :
                            ($isCurrent ? 'text-teal-800 font-extrabold' :
                            ($isStepCancelled ? 'text-rose-700' : 'text-slate-400'))
                        }}">
                            {{ $st['title'] }}
                        </span>

                        @if ($st['formatted_time'] && $isPast)
                            <span class="text-[8px] text-slate-400 font-mono mt-0.5 leading-none hidden sm:block">
                                {{ $st['timestamp']?->format('H:i') }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </div>

    <!-- Collapsible Detailed Timeline Audit History -->
    <div x-data="{ expandedTimeline: true }" class="pt-2 border-t border-slate-100">
        <button
            type="button"
            @click="expandedTimeline = !expandedTimeline"
            class="w-full flex items-center justify-between text-xs font-bold text-slate-700 hover:text-teal-700 py-1 transition"
        >
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Riwayat Jejak Waktu Aktivitas Transaksi</span>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                    {{ count($events) }} Peristiwa
                </span>
            </div>
            <svg
                class="w-4 h-4 text-slate-400 transition-transform duration-200"
                :class="expandedTimeline ? 'rotate-180' : ''"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <!-- Vertical Timeline List -->
        <div x-show="expandedTimeline" x-collapse class="mt-3 space-y-3 pt-1">
            <div class="relative pl-5 border-l-2 border-teal-200 space-y-4 ml-2">
                @forelse ($events as $event)
                    @php
                        $dotColor = match($event['type']) {
                            'danger' => 'bg-rose-500 border-rose-200 ring-rose-100',
                            'info' => 'bg-blue-500 border-blue-200 ring-blue-100',
                            'primary' => 'bg-indigo-600 border-indigo-200 ring-indigo-100',
                            default => 'bg-emerald-600 border-emerald-200 ring-emerald-100',
                        };
                    @endphp
                    <div class="relative group">
                        <!-- Connecting Dot -->
                        <div class="absolute -left-[27px] top-1 w-3.5 h-3.5 rounded-full {{ $dotColor }} border-2 ring-4"></div>

                        <div class="bg-slate-50/80 hover:bg-slate-50 border border-slate-200/80 rounded-xl p-3 transition space-y-1">
                            <div class="flex items-center justify-between flex-wrap gap-1">
                                <h4 class="text-xs font-bold text-slate-900">{{ $event['title'] }}</h4>
                                <div class="text-[10px] text-slate-400 font-mono flex items-center gap-1">
                                    <span>{{ $event['formatted_time'] }}</span>
                                    <span>({{ $event['relative_time'] }})</span>
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">{{ $event['description'] }}</p>
                            <div class="text-[10px] font-semibold text-slate-400 pt-0.5 flex items-center gap-1">
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Oleh: <strong class="text-slate-700">{{ $event['actor'] }}</strong></span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic">Belum ada peristiwa aktivitas yang tercatat.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
