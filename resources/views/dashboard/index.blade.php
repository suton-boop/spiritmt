@extends('layouts.app')

@section('content')
<div class="min-h-full flex flex-col bg-slate-50" x-data="{ folderModal: false, templateModal: false, moveModal: false, moveActionUrl: '', selectedPresentationTitle: '', currentFolderId: '' }">
    <!-- Workspace Top Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between gap-4">
            <div class="flex items-center gap-8">
                <!-- Brand -->
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('image/logo spiritmeter.png') }}" alt="Logo SpiritMeter" class="h-9 w-auto object-contain">
                    <span class="text-xl font-black tracking-tight text-[#1E293B] hidden sm:inline">Spirit<span class="text-[#003B7A]">Meter</span></span>
                </a>

                <!-- Search form -->
                <form action="{{ route('dashboard') }}" method="GET" class="relative max-w-xs sm:w-80">
                    @if(request('folder'))
                        <input type="hidden" name="folder" value="{{ request('folder') }}">
                    @endif
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari presentasi..."
                           class="w-full pl-10 pr-4 py-2 bg-[#F1F5F9] hover:bg-slate-50 focus:bg-white border border-transparent focus:border-[#003B7A] rounded-xl text-xs sm:text-sm font-medium transition-all focus:outline-none focus:ring-2 focus:ring-[#003B7A]/20">
                </form>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-3">
                @if(Auth::user()->isSuperadmin())
                    <a href="{{ route('superadmin.index') }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-black text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl transition-all border border-blue-200 shadow-sm"
                       title="Masuk ke Panel Superadmin">
                        <span>🛡️</span>
                        <span class="hidden md:inline">Superadmin</span>
                    </a>
                @endif

                <a href="{{ route('participant.join') }}" target="_blank" class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 text-xs font-bold text-[#003B7A] bg-[#F1F5F9] hover:bg-[#003B7A]/10 rounded-xl transition-all border border-[#003B7A]/20">
                    <span class="w-2 h-2 rounded-full bg-[#F9A703] animate-pulse"></span>
                    <span>📱 Buka Layar Peserta</span>
                </a>

                <!-- New Presentation Dropdown / Button -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 px-4 py-2.5 bg-[#003B7A] hover:bg-[#002d5e] text-white text-xs sm:text-sm font-bold rounded-xl shadow-md shadow-[#003B7A]/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Presentasi Baru</span>
                        <svg class="w-3.5 h-3.5 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-50">
                        <form action="{{ route('presentations.store') }}" method="POST">
                            @csrf
                            @if($activeFolderId)
                                <input type="hidden" name="folder_id" value="{{ $activeFolderId }}">
                            @endif
                            <button type="submit" class="w-full text-left px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-[#F1F5F9] hover:text-[#003B7A] flex items-center gap-2.5">
                                <span class="text-base">📄</span> Buat Presentasi Kosong
                            </button>
                        </form>
                        <button @click="open = false; templateModal = true" class="w-full text-left px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-[#F1F5F9] hover:text-[#003B7A] flex items-center gap-2.5">
                            <span class="text-base">✨</span> Pilih dari Template
                        </button>
                    </div>
                </div>

                <!-- User profile & Logout -->
                <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" title="Keluar" class="p-2 text-slate-400 hover:text-rose-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Body Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Workspace Sidebar -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Navigation Links -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm space-y-1">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold {{ !request('folder') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }} transition-colors">
                        <span class="flex items-center gap-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            Semua Presentasi
                        </span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ !request('folder') ? 'bg-indigo-200/60 text-indigo-800' : 'bg-slate-100 text-slate-600' }}">{{ $totalPresentations }}</span>
                    </a>

                    <button @click="templateModal = true" class="w-full flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors text-left">
                        <span class="text-base">✨</span> Galeri Template
                    </button>

                    @if(Auth::user()->isSuperadmin())
                        <a href="{{ route('superadmin.index') }}" class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-blue-700 hover:bg-blue-50 transition-colors text-left border border-blue-100 bg-blue-50/50">
                            <span class="flex items-center gap-2">
                                <span>🛡️</span> Panel Superadmin
                            </span>
                            <span class="text-[10px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded font-black">ADMIN</span>
                        </a>
                    @endif
                </div>

                <!-- Folders Section -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Folder Saya</span>
                        <button @click="folderModal = true" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                            + Tambah
                        </button>
                    </div>

                    <div class="space-y-1">
                        @forelse($folders as $folder)
                            <div class="flex items-center justify-between group rounded-xl px-3 py-2 text-xs sm:text-sm font-semibold {{ request('folder') == $folder->id ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }}">
                                <a href="{{ route('dashboard', ['folder' => $folder->id]) }}" class="flex items-center gap-2.5 flex-1 truncate">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $folder->color }}"></span>
                                    <span class="truncate">{{ $folder->name }}</span>
                                </a>
                                <div class="flex items-center gap-1">
                                    <span class="text-xs text-slate-400">{{ $folder->presentations_count }}</span>
                                    <form action="{{ route('folders.destroy', $folder->id) }}" method="POST" onsubmit="return confirm('Hapus folder ini?')" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-slate-400 hover:text-rose-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 py-2 text-center">Belum ada folder</p>
                        @endforelse
                    </div>
                </div>

                <!-- Stats summary card -->
                <div class="bg-gradient-to-br from-indigo-900 to-slate-900 rounded-2xl p-5 text-white shadow-lg">
                    <div class="text-xs font-bold text-indigo-300 uppercase tracking-widest mb-1">Aktivitas Audiens</div>
                    <div class="text-3xl font-black mb-1">{{ number_format($totalResponses) }}</div>
                    <p class="text-xs text-slate-300">Total suara dan respon terkumpul di seluruh presentasi Anda.</p>
                </div>
            </div>

            <!-- Right Main Area: Presentations Grid -->
            <div class="lg:col-span-9 space-y-6">
                
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                            @if($activeFolderId)
                                Folder: {{ $folders->firstWhere('id', $activeFolderId)?->name ?? 'Folder' }}
                            @else
                                Semua Presentasi
                            @endif
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola slide, jalankan mode layar presenter, dan pantau hasil interaksi audiens.</p>
                    </div>

                    @if(request('folder') || request('search'))
                        <a href="{{ route('dashboard') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                            Reset Filter &times;
                        </a>
                    @endif
                </div>

                <!-- Presentations Grid -->
                @if($presentations->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($presentations as $p)
                            <div class="bg-white rounded-3xl border border-slate-200/90 hover:border-indigo-300 hover:shadow-xl transition-all duration-200 flex flex-col overflow-hidden group">
                                
                                <!-- Card Thumbnail Preview -->
                                <a href="{{ route('presentations.edit', $p->id) }}" class="relative h-36 bg-slate-900 p-4 flex flex-col justify-between overflow-hidden">
                                    <!-- Background subtle gradient based on theme -->
                                    <div class="absolute inset-0 bg-gradient-to-tr from-indigo-950 via-slate-900 to-indigo-900 opacity-90"></div>
                                    
                                    <div class="relative z-10 flex items-center justify-between">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-black/40 backdrop-blur-md text-[11px] font-mono font-bold text-amber-300 border border-white/10">
                                            PIN: {{ $p->getFormattedCode() }}
                                        </span>

                                        <span class="px-2 py-0.5 rounded-md bg-white/10 text-[11px] font-bold text-slate-300">
                                            {{ $p->slides_count }} Slide
                                        </span>
                                    </div>

                                    <div class="relative z-10">
                                        <div class="text-xs text-indigo-300 font-bold uppercase tracking-wider mb-0.5">
                                            {{ $p->slides->first()?->type ? str_replace('_', ' ', strtoupper($p->slides->first()->type)) : 'PRESENTASI' }}
                                        </div>
                                        <h3 class="text-sm font-bold text-white truncate drop-shadow-sm">
                                            {{ $p->title }}
                                        </h3>
                                    </div>
                                </a>

                                <!-- Card Body -->
                                <div class="p-5 flex-1 flex flex-col justify-between">
                                    <div class="mb-4">
                                        <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                                            <span class="flex items-center gap-1 font-semibold">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                                {{ $p->responses_count }} respon
                                            </span>
                                            <span>{{ $p->updated_at->diffForHumans() }}</span>
                                        </div>

                                        @if($p->folder)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md">
                                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $p->folder->color }}"></span>
                                                {{ $p->folder->name }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                                        <!-- Present Button -->
                                        <a href="{{ route('presenter.view', $p->id) }}" target="_blank"
                                           class="flex-1 py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl text-center shadow-sm shadow-indigo-600/20 transition-all flex items-center justify-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            <span>Present</span>
                                        </a>

                                        <!-- Edit Studio Button -->
                                        <a href="{{ route('presentations.edit', $p->id) }}"
                                           class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl text-center transition-all">
                                            Edit
                                        </a>

                                        <!-- More Dropdown -->
                                        <div class="relative" x-data="{ menuOpen: false }">
                                            <button @click="menuOpen = !menuOpen" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-all">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                                            </button>

                                            <div x-show="menuOpen" @click.away="menuOpen = false" x-cloak
                                                 class="absolute right-0 bottom-full mb-2 w-48 bg-white rounded-2xl shadow-2xl border border-slate-100 py-1.5 z-40 text-xs font-semibold">
                                                
                                                <form action="{{ route('presentations.duplicate', $p->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="w-full text-left px-3.5 py-2 text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                                                        <span>📋</span> Duplikasi
                                                    </button>
                                                </form>

                                                <button type="button" 
                                                        @click="menuOpen = false; moveActionUrl = '{{ route('presentations.update', $p->id) }}'; selectedPresentationTitle = '{{ addslashes($p->title) }}'; currentFolderId = '{{ $p->folder_id ?? '' }}'; moveModal = true;" 
                                                        class="w-full text-left px-3.5 py-2 text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                                                    <span>📁</span> Pindahkan Folder
                                                </button>

                                                <a href="{{ route('presentations.export', $p->id) }}" class="block px-3.5 py-2 text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                                                    <span>📥</span> Ekspor CSV Hasil
                                                </a>

                                                <form action="{{ route('presentations.reset', $p->id) }}" method="POST" onsubmit="return confirm('Reset semua hasil respon untuk presentasi ini?')">
                                                    @csrf
                                                    <button type="submit" class="w-full text-left px-3.5 py-2 text-amber-600 hover:bg-amber-50 flex items-center gap-2">
                                                        <span>🔄</span> Reset Hasil Respon
                                                    </button>
                                                </form>

                                                <div class="border-t border-slate-100 my-1"></div>

                                                <form action="{{ route('presentations.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus presentasi ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full text-left px-3.5 py-2 text-rose-600 hover:bg-rose-50 flex items-center gap-2">
                                                        <span>🗑️</span> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    <div class="pt-4">
                        {{ $presentations->links() }}
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="bg-white rounded-3xl p-12 text-center border-2 border-dashed border-slate-200">
                        <div class="w-16 h-16 rounded-3xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-3xl mx-auto mb-4">
                            📊
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-1">Belum ada presentasi</h3>
                        <p class="text-sm text-slate-500 max-w-sm mx-auto mb-6">Mulai buat presentasi interaktif pertama Anda atau gunakan template siap pakai.</p>
                        
                        <div class="flex items-center justify-center gap-3">
                            <form action="{{ route('presentations.store') }}" method="POST">
                                @csrf
                                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-md shadow-indigo-600/20">
                                    + Buat Baru Kosong
                                </button>
                            </form>
                            <button @click="templateModal = true" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-sm">
                                ✨ Gunakan Template
                            </button>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div x-show="folderModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm">
        <div @click.away="folderModal = false" class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-100">
            <h3 class="text-lg font-extrabold text-slate-900 mb-1">Tambah Folder Baru</h3>
            <p class="text-xs text-slate-500 mb-5">Atur dan rapikan presentasi Anda dalam folder terpisah.</p>

            <form action="{{ route('folders.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Nama Folder</label>
                    <input type="text" name="name" required placeholder="Contoh: Rapat Mingguan, Pelatihan"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Warna Label</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="color" value="#6366f1" class="w-10 h-10 rounded-xl cursor-pointer border-0 bg-transparent">
                        <span class="text-xs text-slate-500">Pilih warna untuk identifikasi folder</span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="folderModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/20">Simpan Folder</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Template Selector Modal -->
    <div x-show="templateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-sm">
        <div @click.away="templateModal = false" class="bg-white rounded-3xl p-6 sm:p-8 max-w-2xl w-full shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xl font-extrabold text-slate-900">Pilih Template Presentasi</h3>
                <button @click="templateModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mb-6">Mulai dengan template yang sudah dioptimasi untuk interaksi audiens.</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                
                <!-- Icebreaker -->
                <form action="{{ route('presentations.template') }}" method="POST">
                    @csrf
                    <input type="hidden" name="template" value="icebreaker">
                    <button type="submit" class="w-full text-left p-4 rounded-2xl border-2 border-slate-200 hover:border-indigo-600 hover:shadow-lg transition-all group flex flex-col justify-between h-44">
                        <div>
                            <span class="text-2xl mb-2 block">🧊</span>
                            <h4 class="font-bold text-slate-900 text-sm group-hover:text-indigo-600">Team Icebreaker</h4>
                            <p class="text-[11px] text-slate-500 mt-1">Word Cloud emosi & polling kopi mingguan.</p>
                        </div>
                        <span class="text-xs font-bold text-indigo-600">+ Gunakan</span>
                    </button>
                </form>

                <!-- Live Quiz -->
                <form action="{{ route('presentations.template') }}" method="POST">
                    @csrf
                    <input type="hidden" name="template" value="quiz">
                    <button type="submit" class="w-full text-left p-4 rounded-2xl border-2 border-slate-200 hover:border-amber-500 hover:shadow-lg transition-all group flex flex-col justify-between h-44">
                        <div>
                            <span class="text-2xl mb-2 block">🏆</span>
                            <h4 class="font-bold text-slate-900 text-sm group-hover:text-amber-600">Trivia Battle</h4>
                            <p class="text-[11px] text-slate-500 mt-1">Kuis countdown 15 detik + leaderboard skor.</p>
                        </div>
                        <span class="text-xs font-bold text-amber-600">+ Gunakan</span>
                    </button>
                </form>

                <!-- Feedback -->
                <form action="{{ route('presentations.template') }}" method="POST">
                    @csrf
                    <input type="hidden" name="template" value="feedback">
                    <button type="submit" class="w-full text-left p-4 rounded-2xl border-2 border-slate-200 hover:border-teal-500 hover:shadow-lg transition-all group flex flex-col justify-between h-44">
                        <div>
                            <span class="text-2xl mb-2 block">💡</span>
                            <h4 class="font-bold text-slate-900 text-sm group-hover:text-teal-600">Feedback Workshop</h4>
                            <p class="text-[11px] text-slate-500 mt-1">Skala Likert kepuasan & saran open-ended.</p>
                        </div>
                        <span class="text-xs font-bold text-teal-600">+ Gunakan</span>
                    </button>
                </form>

            </div>
        </div>
    </div>

    <!-- Move Presentation to Folder Modal -->
    <div x-show="moveModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm">
        <div @click.away="moveModal = false" class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-extrabold text-slate-900">Pindahkan ke Folder</h3>
                <button @click="moveModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>
            <p class="text-xs text-slate-500 mb-5">
                Pilih folder tujuan untuk presentasi <strong class="text-indigo-600" x-text="selectedPresentationTitle"></strong>:
            </p>

            <form :action="moveActionUrl" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Pilih Folder</label>
                    <select name="folder_id" x-model="currentFolderId" 
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">📂 Tanpa Folder (Di Luar Folder)</option>
                        @foreach($folders as $folder)
                            <option value="{{ $folder->id }}">📁 {{ $folder->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="moveModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-600/20">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
