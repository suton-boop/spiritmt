@extends('layouts.app')

@section('content')
<div class="flex-1 min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-950 relative overflow-hidden">
    
    <!-- Ambient glowing backgrounds -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center space-y-4">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-3.5 group">
            <div class="p-2.5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl shadow-blue-500/5 group-hover:border-slate-700 transition-all flex items-center justify-center">
                <img src="{{ asset('image/logo spiritmeter.png') }}" alt="Logo SpiritMeter" class="h-12 w-auto object-contain group-hover:scale-105 transition-all">
            </div>
            <div class="text-left">
                <span class="text-2xl font-black tracking-tight text-white block">Spirit<span class="text-[#F9A703]">Meter</span></span>
                <span class="text-[11px] font-extrabold text-blue-400 uppercase tracking-widest block -mt-1">Superadmin Portal</span>
            </div>
        </a>

        <div>
            <h2 class="text-2xl font-black text-white tracking-tight">
                Autentikasi Superadmin
            </h2>
            <p class="text-sm text-slate-400 max-w-sm mx-auto mt-1.5">
                Area terbatas. Masukkan kredensial akun administrator untuk membuka panel kontrol.
            </p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10 px-4 sm:px-0">
        <div class="bg-slate-900/90 backdrop-blur-xl py-8 px-6 sm:px-10 shadow-2xl rounded-3xl border border-slate-800 space-y-6">
            
            <form action="{{ route('superadmin.login.submit') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                        Email Superadmin
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                        </div>
                        <input id="email" name="email" type="email" required autocomplete="email" autofocus
                               value="{{ old('email') }}"
                               placeholder="nama@email.com"
                               class="w-full pl-10 pr-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-sm font-medium text-white placeholder-slate-500 focus:bg-slate-950 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                               placeholder="••••••••"
                               class="w-full pl-10 pr-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-sm font-medium text-white placeholder-slate-500 focus:bg-slate-950 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                </div>

                <button type="submit" 
                        class="w-full py-3.5 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Masuk ke Panel Superadmin</span>
                </button>
            </form>

            <div class="pt-2 text-center">
                <a href="{{ route('dashboard') }}" class="text-xs font-bold text-slate-400 hover:text-white transition-colors inline-flex items-center gap-1.5">
                    <span>&larr; Kembali ke Dashboard Presenter</span>
                </a>
            </div>

        </div>
    </div>

</div>
@endsection
