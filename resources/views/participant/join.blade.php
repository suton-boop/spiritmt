@extends('layouts.app')

@section('content')
<div class="flex-1 min-h-screen flex flex-col justify-between py-12 px-4 sm:px-6 lg:px-8 bg-[#1E293B] text-white">
    
    <!-- Header -->
    <div class="text-center">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5">
            <img src="{{ asset('image/logo spiritmeter.png') }}" alt="Logo SpiritMeter" class="h-9 w-auto object-contain">
            <span class="text-xl font-black tracking-tight text-white">Spirit<span class="text-[#F9A703]">Meter</span></span>
        </a>
    </div>

    <!-- Center Card -->
    <div class="max-w-md w-full mx-auto bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-2xl text-slate-800">
        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-[#003B7A]/10 text-[#003B7A] flex items-center justify-center text-2xl mx-auto mb-3">
                📱
            </div>
            <h1 class="text-2xl font-black text-[#1E293B] tracking-tight">Gabung ke Presentasi</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Masukkan 8-digit kode PIN yang tampil di layar presenter.</p>
        </div>

        @if($errors->any())
            <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-2xl text-center font-bold">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('participant.join.submit') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <input type="text" name="code" placeholder="1234 5678" autofocus required
                       value="{{ old('code') }}"
                       class="w-full py-4 px-4 bg-[#F1F5F9] border-2 border-slate-200 rounded-2xl text-2xl font-black font-mono tracking-widest text-center text-[#003B7A] placeholder:text-slate-400 focus:bg-white focus:outline-none focus:border-[#003B7A] transition-all">
            </div>

            <button type="submit" class="w-full py-4 px-4 bg-[#003B7A] hover:bg-[#002d5e] text-white font-extrabold text-base rounded-2xl shadow-xl shadow-[#003B7A]/30 transition-all">
                Gabung Sekarang &rarr;
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 text-center">
            <p class="text-[11px] text-slate-400">
                Tidak ada instalasi aplikasi yang dibutuhkan. Berpartisipasi langsung dari browser apa pun.
            </p>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} SpiritMeter. Interaksi audiens real-time.
    </div>

</div>
@endsection
