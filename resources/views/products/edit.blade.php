@extends('layouts.app')

@section('title', 'Edit Produk — CampusMarket')

@section('content')
<div class="p-4 pb-6 flex-1">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('products.show', $product) }}" class="w-9 h-9 bg-white border border-slate-200 rounded-full flex items-center justify-center shadow-xs hover:bg-slate-50 transition">
            <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-lg font-extrabold tracking-tight text-slate-900">Edit <span class="text-teal-600">Produk</span></h1>
            <p class="text-xs text-slate-500 truncate max-w-[220px]">{{ $product->title }}</p>
        </div>
    </div>

    @include('products._form')
</div>
@endsection
