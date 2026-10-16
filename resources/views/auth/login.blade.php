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
            <h2 class="mt-6 text-2xl font-extrabold text-slate-900">Masuk ke Akun Anda</h2>
            <p class="mt-2 text-sm text-slate-500">Mulai buat dan kendalikan presentasi interaktif Anda.</p>
        </div>

        <!-- 1-Click Demo Login Banner -->
        <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4 text-center">
            <p class="text-xs font-semibold text-indigo-900 mb-2">Ingin langsung mencoba tanpa mengisi form?</p>
            <form action="{{ route('demo.login') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md shadow-indigo-600/20 transition-all flex items-center justify-center gap-2">
                    <span>⚡ 1-Klik Masuk Presenter Demo</span>
                </button>
            </form>
        </div>

        <div class="relative flex items-center justify-center">
            <div class="border-t border-slate-200 w-full"></div>
            <span class="bg-white px-3 text-xs uppercase font-bold text-slate-400 tracking-wider absolute">atau email</span>
        </div>

        <!-- Login Form -->
        <form class="space-y-5" action="{{ route('login') }}" method="POST">
            @csrf

            @if ($errors->any())
                <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Email</label>
                <input id="email" name="email" type="email" required value="{{ old('email') }}"
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Password</label>
                <input id="password" name="password" type="password" required
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold rounded-xl shadow-lg transition-all">
                Masuk
            </button>
        </form>

        <div class="text-center text-xs text-slate-500">
            Belum punya akun? 
            <a href="{{ route('register') }}" class="font-bold text-indigo-600 hover:text-indigo-700">Daftar sekarang</a>
        </div>
    </div>
</div>
@endsection
