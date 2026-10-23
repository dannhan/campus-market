@props([
    'type' => 'success',
    'message' => '',
    'duration' => null,
])

@php
    $isError = $type === 'error';
    $duration = $duration ?? ($isError ? 6000 : 3500);
@endphp

<div x-data="{ show: true }"
     x-init="setTimeout(() => show = false, {{ (int) $duration }})"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 -translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 -translate-y-2"
     role="{{ $isError ? 'alert' : 'status' }}"
     aria-live="{{ $isError ? 'assertive' : 'polite' }}"
     class="pointer-events-none relative w-full max-w-sm overflow-hidden rounded-xl border border-slate-200 bg-white/95 shadow-lg backdrop-blur">
    <div class="flex items-start gap-2.5 px-3 py-2.5">
        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $isError ? 'bg-rose-50 text-rose-600' : 'bg-teal-50 text-teal-600' }}">
            @if ($isError)
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @else
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @endif
        </span>

        <p class="flex-1 text-xs leading-relaxed font-medium {{ $isError ? 'text-rose-800' : 'text-teal-800' }}">
            {{ $message }}
        </p>

        <button type="button"
                @click="show = false"
                class="pointer-events-auto -mr-1 -mt-1 shrink-0 rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                aria-label="Tutup notifikasi">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <span class="toast-progress absolute inset-x-0 bottom-0 h-0.5 {{ $isError ? 'bg-rose-500' : 'bg-teal-500' }}"
          style="animation-duration: {{ (int) $duration }}ms"
          aria-hidden="true"></span>
</div>
