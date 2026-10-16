@extends('layouts.app')

@section('content')
<div class="flex-1 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50">
    <div class="max-w-md w-full space-y-8 bg-white p-8 sm:p-10 rounded-3xl shadow-xl border border-slate-100">
        <!-- Logo & Header -->
        <div class="text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                <img src="{{ asset('image/logo spiritmeter.png') }}" alt="Logo SpiritMeter" class="h-10 w-auto object-contain">
                <span class="text-2xl font-black tracking-tight text-slate-900">Spirit<span class="text-[#003B7A]">Meter</span></span>
            </a>
            <h2 class="mt-6 text-2xl font-extrabold text-slate-900">Daftar Akun Baru</h2>
            <p class="mt-2 text-sm text-slate-500">Gratis selamanya, buat polling dalam hitungan menit.</p>
        </div>

        <!-- Register Form -->
        <form class="space-y-4" action="{{ route('register') }}" method="POST">
            @csrf

            @if ($errors->any())
                <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Nama Lengkap</label>
                <input id="name" name="name" type="text" required value="{{ old('name') }}"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Email</label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Password</label>
                <input id="password" name="password" type="password" required minlength="6"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Konfirmasi Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="6"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <button type="submit" class="w-full mt-2 py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-600/25 transition-all">
                Daftar Sekarang
            </button>
        </form>

        <div class="text-center text-xs text-slate-500">
            Sudah memiliki akun? 
            <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Masuk di sini</a>
        </div>
    </div>
</div>
@endsection
