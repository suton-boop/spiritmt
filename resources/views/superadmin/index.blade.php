@extends('layouts.app')

@section('content')
<div class="min-h-full flex flex-col bg-slate-50" 
     x-data="superadminApp({{ json_encode($stats) }}, '{{ csrf_token() }}')">
    
    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between gap-4">
            
            <!-- Left: Title & Badge -->
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-sky-500 flex items-center justify-center text-white font-black text-xl shadow-md shadow-blue-500/20 group-hover:scale-105 transition-all">
                        🛡️
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-black tracking-tight text-slate-900">Control Center</span>
                            <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-700 text-[10px] font-extrabold uppercase tracking-wider border border-blue-200">Superadmin</span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium hidden sm:block">Kelola Akun, Reset Akses & Kontrol Fitur SpiritMeter</p>
                    </div>
                </a>
            </div>

            <!-- Right: Action & Back to Dashboard -->
            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold rounded-xl transition-all border border-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span class="hidden sm:inline">Dashboard</span>
                </a>

                <!-- Superadmin Logout Button -->
                <form action="{{ route('superadmin.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs sm:text-sm font-bold rounded-xl border border-rose-200 transition-all shadow-sm"
                            title="Kunci sesi Superadmin dan keluar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Keluar Superadmin</span>
                    </button>
                </form>

                <div class="flex items-center gap-2 pl-2 sm:pl-3 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-black flex items-center justify-center text-xs shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <span class="text-xs font-bold text-slate-800 hidden md:inline">{{ Auth::user()->name }}</span>
                </div>
            </div>

        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">

        <!-- Top Metric Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            
            <!-- 1. Total Akun Pengguna -->
            <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/90 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Pengguna</span>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1 font-mono" x-text="stats.total_users"></h3>
                    <span class="text-xs text-blue-600 font-bold mt-1 inline-block" x-text="stats.total_superadmins + ' Superadmin'"></span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                    👥
                </div>
            </div>

            <!-- 2. Total Presentasi -->
            <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/90 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Presentasi</span>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1 font-mono" x-text="stats.total_presentations"></h3>
                    <span class="text-xs text-slate-500 font-medium mt-1 inline-block">Sesi interaktif</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                    📊
                </div>
            </div>

            <!-- 3. Total Partisipasi / Respons -->
            <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/90 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Respons Masuk</span>
                    <h3 class="text-2xl sm:text-3xl font-black text-emerald-600 mt-1 font-mono" x-text="stats.total_responses"></h3>
                    <span class="text-xs text-emerald-600 font-medium mt-1 inline-block">Voting & interaksi</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                    🗳️
                </div>
            </div>

            <!-- 4. Fitur Aktif -->
            <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/90 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Fitur Aktif</span>
                    <h3 class="text-2xl sm:text-3xl font-black text-sky-600 mt-1 font-mono">
                        <span x-text="stats.active_features"></span><span class="text-slate-400 text-lg">/</span><span class="text-slate-500 text-lg" x-text="stats.total_features"></span>
                    </h3>
                    <span class="text-xs text-sky-600 font-medium mt-1 inline-block">Modul sistem</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl font-bold">
                    ⚙️
                </div>
            </div>

        </div>

        <!-- Tab Controls & Quick Actions -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
            
            <div class="p-4 sm:p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                
                <!-- Nav Tabs -->
                <div class="flex items-center gap-2 p-1.5 bg-slate-100 rounded-2xl w-fit">
                    <button @click="activeTab = 'users'"
                            :class="activeTab === 'users' ? 'bg-white text-slate-900 shadow-sm font-black' : 'text-slate-600 font-bold hover:text-slate-900'"
                            class="px-5 py-2.5 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-2">
                        <span>👥 Kelola Akun Pengguna</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-mono"
                              :class="activeTab === 'users' ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-600'">
                            {{ $users->total() }}
                        </span>
                    </button>

                    <button @click="activeTab = 'features'"
                            :class="activeTab === 'features' ? 'bg-white text-slate-900 shadow-sm font-black' : 'text-slate-600 font-bold hover:text-slate-900'"
                            class="px-5 py-2.5 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-2">
                        <span>⚡ Kelola Fitur Sistem</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-mono"
                              :class="activeTab === 'features' ? 'bg-sky-100 text-sky-700' : 'bg-slate-200 text-slate-600'">
                            {{ $features->count() }}
                        </span>
                    </button>
                </div>

                <!-- Tab Specific Toolbar Action -->
                <div x-show="activeTab === 'users'">
                    <button @click="openCreateUserModal = true"
                            class="w-full sm:w-auto px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Akun Baru</span>
                    </button>
                </div>

            </div>

            <!-- TAB 1: KELOLA AKUN PENGGUNA -->
            <div x-show="activeTab === 'users'" class="p-4 sm:p-6 space-y-6">
                
                <!-- Search & Filter Bar -->
                <form action="{{ route('superadmin.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative flex-1 w-full">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari berdasarkan nama atau email pengguna..."
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-blue-500 rounded-xl text-xs sm:text-sm font-medium transition-all focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <select name="role" onchange="this.form.submit()" 
                                class="px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                            <option value="all" {{ $roleFilter === 'all' ? 'selected' : '' }}>Semua Peran</option>
                            <option value="superadmin" {{ $roleFilter === 'superadmin' ? 'selected' : '' }}>Hanya Superadmin</option>
                            <option value="presenter" {{ $roleFilter === 'presenter' ? 'selected' : '' }}>Hanya Presenter</option>
                        </select>

                        <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-xs sm:text-sm font-bold rounded-xl transition-all">
                            Cari
                        </button>

                        @if($search || $roleFilter !== 'all')
                            <a href="{{ route('superadmin.index') }}" class="px-3 py-2.5 text-xs text-rose-600 hover:text-rose-700 font-bold">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>

                <!-- Users Table -->
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-extrabold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="py-3.5 px-4 sm:px-6">Pengguna</th>
                                <th class="py-3.5 px-4">Peran</th>
                                <th class="py-3.5 px-4 text-center">Presentasi</th>
                                <th class="py-3.5 px-4 text-center">Folder</th>
                                <th class="py-3.5 px-4">Terdaftar</th>
                                <th class="py-3.5 px-4 sm:px-6 text-right">Aksi Manajemen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($users as $user)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    
                                    <!-- User Avatar & Info -->
                                    <td class="py-4 px-4 sm:px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-sm font-black shadow-sm flex-shrink-0 {{ $user->is_superadmin ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div class="truncate max-w-[200px] sm:max-w-none">
                                                <div class="font-extrabold text-slate-900 flex items-center gap-1.5">
                                                    <span>{{ $user->name }}</span>
                                                    @if($user->id === Auth::id())
                                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">Anda</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-slate-500 font-medium">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Role Badge -->
                                    <td class="py-4 px-4">
                                        @if($user->is_superadmin)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-black bg-blue-50 text-blue-700 border border-blue-200">
                                                <span>🛡️</span> Superadmin
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                <span>🎤</span> Presenter
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Presentations Count -->
                                    <td class="py-4 px-4 text-center font-mono font-bold text-slate-700">
                                        {{ $user->presentations_count }}
                                    </td>

                                    <!-- Folders Count -->
                                    <td class="py-4 px-4 text-center font-mono font-bold text-slate-500">
                                        {{ $user->folders_count }}
                                    </td>

                                    <!-- Created Date -->
                                    <td class="py-4 px-4 text-slate-500 font-medium text-xs">
                                        {{ $user->created_at->format('d M Y') }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-4 px-4 sm:px-6 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            
                                            <!-- Reset Password Button -->
                                            <button @click="openResetPasswordModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->email }}')"
                                                    class="p-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-bold transition-all flex items-center gap-1"
                                                    title="Reset Password Akun">
                                                <span>🔑</span>
                                                <span class="hidden md:inline">Reset Pass</span>
                                            </button>

                                            <!-- Toggle Role Button -->
                                            @if($user->id !== Auth::id())
                                                <form action="{{ route('superadmin.users.toggle-role', $user->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            onclick="return confirm('Ubah peran akun {{ $user->name }} menjadi {{ $user->is_superadmin ? 'Presenter' : 'Superadmin' }}?')"
                                                            class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 text-xs font-bold transition-all"
                                                            title="{{ $user->is_superadmin ? 'Turunkan ke Presenter' : 'Jadikan Superadmin' }}">
                                                        {{ $user->is_superadmin ? 'Demote' : 'Promote' }}
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Delete User Button -->
                                            @if($user->id !== Auth::id())
                                                <button @click="openDeleteUserModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->email }}', {{ $user->presentations_count }})"
                                                        class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition-all"
                                                        title="Hapus Akun Pengguna">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            @endif

                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400 font-semibold">
                                        Tidak ada akun pengguna yang cocok dengan pencarian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($users->hasPages())
                    <div class="pt-4">
                        {{ $users->links() }}
                    </div>
                @endif

            </div>

            <!-- TAB 2: KELOLA FITUR SISTEM -->
            <div x-show="activeTab === 'features'" class="p-4 sm:p-6 space-y-6">
                
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-start gap-3">
                    <span class="text-xl">💡</span>
                    <div>
                        <h4 class="text-sm font-extrabold text-blue-900">Kontrol Fitur Realtime (Feature Flags)</h4>
                        <p class="text-xs text-blue-700 mt-0.5">
                            Superadmin dapat mengaktifkan atau menonaktifkan fitur sistem secara langsung tanpa perlu mengubah konfigurasi server atau restart kode.
                        </p>
                    </div>
                </div>

                <!-- Grid of Features -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    @foreach($features as $feature)
                        <div class="bg-white rounded-2xl p-5 border border-slate-200 hover:border-blue-300 transition-all shadow-sm flex flex-col justify-between gap-4"
                             x-data="{ enabled: {{ $feature->is_enabled ? 'true' : 'false' }}, loading: false }">
                            
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">
                                            @switch($feature->key)
                                                @case('feature_registration') 📝 @break
                                                @case('feature_quiz') ⚡ @break
                                                @case('feature_word_cloud') ☁️ @break
                                                @case('feature_qa') 🙋 @break
                                                @case('feature_leaderboard') 🏆 @break
                                                @case('feature_quick_demo') 🚀 @break
                                                @default ⚙️
                                            @endswitch
                                        </span>
                                        <h4 class="text-sm sm:text-base font-extrabold text-slate-900">{{ $feature->name }}</h4>
                                    </div>

                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider"
                                          :class="enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                          x-text="enabled ? 'AKTIF' : 'NONAKTIF'"></span>
                                </div>

                                <p class="text-xs text-slate-500 font-medium leading-relaxed">
                                    {{ $feature->description }}
                                </p>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[11px] font-mono font-bold text-slate-400">
                                    Key: <code class="text-slate-600 bg-slate-100 px-1 py-0.5 rounded">{{ $feature->key }}</code>
                                </span>

                                <!-- Toggle Switch -->
                                <button type="button" 
                                        @click="loading = true; toggleFeature({{ $feature->id }}, (res) => { enabled = res.is_enabled; loading = false; })"
                                        :disabled="loading"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                        :class="enabled ? 'bg-blue-600' : 'bg-slate-300'">
                                    <span class="sr-only">Toggle fitur</span>
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                          :class="enabled ? 'translate-x-5' : 'translate-x-0'"></span>
                                </button>
                            </div>

                        </div>
                    @endforeach
                </div>

            </div>

        </div>

    </main>

    <!-- ==================== MODALS ==================== -->

    <!-- 1. MODAL TAMBAH PENGGUNA BARU -->
    <div x-show="openCreateUserModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openCreateUserModal = false" 
             class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-slate-100 space-y-6">
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="text-2xl">👤</span>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Buat Akun Pengguna Baru</h3>
                        <p class="text-xs text-slate-500">Superadmin mendaftarkan akun secara langsung.</p>
                    </div>
                </div>
                <button @click="openCreateUserModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('superadmin.users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Contoh: Budi Santoso"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" required placeholder="budi@example.com"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Password</label>
                    <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_superadmin" value="1" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                        <span class="text-xs font-bold text-slate-700">Jadikan Akun Superadmin (Akses Penuh)</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="openCreateUserModal = false" 
                            class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-all">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-500/20 transition-all">
                        Simpan Akun
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- 2. MODAL RESET PASSWORD -->
    <div x-show="openResetModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openResetModal = false" 
             class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-slate-100 space-y-6">
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="text-2xl">🔑</span>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Reset Password Pengguna</h3>
                        <p class="text-xs text-slate-500 truncate max-w-[260px]" x-text="resetUser.name + ' (' + resetUser.email + ')'"></p>
                    </div>
                </div>
                <button @click="openResetModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="space-y-4">
                <!-- Choice: Auto generate vs Manual -->
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" x-model="resetMode" value="auto" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-xs font-bold text-slate-800">Generate Password Aman Otomatis (Rekomendasi)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" x-model="resetMode" value="manual" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-xs font-bold text-slate-800">Tentukan Password Baru Manual</span>
                    </label>
                </div>

                <div x-show="resetMode === 'manual'">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Password Baru</label>
                    <input type="text" x-model="customPassword" placeholder="Masukkan password baru..."
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Success Box if reset via AJAX -->
                <div x-show="generatedPasswordResult" class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-center space-y-2">
                    <span class="text-xs font-bold text-emerald-800 block">Password Berhasil Direset! Berikan kepada pengguna:</span>
                    <div class="text-base font-black font-mono text-emerald-900 select-all bg-white py-2 px-3 rounded-xl border border-emerald-300" 
                         x-text="generatedPasswordResult"></div>
                    <span class="text-[11px] text-emerald-700">Klik teks untuk menyalin password di atas.</span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" @click="openResetModal = false" 
                        class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-all">
                    Tutup
                </button>
                <button type="button" @click="submitResetPassword()"
                        :disabled="resetLoading"
                        class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-black rounded-xl shadow-md transition-all">
                    <span x-show="!resetLoading">Eksekusi Reset</span>
                    <span x-show="resetLoading">Memproses...</span>
                </button>
            </div>

        </div>
    </div>

    <!-- 3. MODAL KONFIRMASI HAPUS PENGGUNA -->
    <div x-show="openDeleteModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openDeleteModal = false" 
             class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-slate-100 space-y-6">
            
            <div class="text-center space-y-2">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-2xl mx-auto">
                    ⚠️
                </div>
                <h3 class="text-lg font-black text-slate-900">Hapus Akun Pengguna?</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Anda akan menghapus akun <strong class="text-slate-800" x-text="deleteUser.name"></strong> (<span x-text="deleteUser.email"></span>).
                </p>
            </div>

            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-xs text-rose-800 space-y-1">
                <p class="font-bold">Perhatian Penting:</p>
                <p>Tindakan ini permanen. Semua data presentasi (<span x-text="deleteUser.presentations_count"></span> presentasi) dan respons polling terkait akan terhapus secara permanen.</p>
            </div>

            <form :action="'/superadmin/users/' + deleteUser.id" method="POST" class="flex items-center justify-end gap-2 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" @click="openDeleteModal = false" 
                        class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-all">
                    Batal
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-md shadow-rose-600/20 transition-all">
                    Ya, Hapus Akun Ini
                </button>
            </form>

        </div>
    </div>

</div>

<script>
function superadminApp(stats, csrfToken) {
    return {
        activeTab: 'users',
        stats: stats,
        csrfToken: csrfToken,

        // Modals state
        openCreateUserModal: false,
        openResetModal: false,
        openDeleteModal: false,

        // Reset password state
        resetUser: { id: null, name: '', email: '' },
        resetMode: 'auto',
        customPassword: '',
        generatedPasswordResult: '',
        resetLoading: false,

        // Delete user state
        deleteUser: { id: null, name: '', email: '', presentations_count: 0 },

        openResetPasswordModal(id, name, email) {
            this.resetUser = { id, name, email };
            this.resetMode = 'auto';
            this.customPassword = '';
            this.generatedPasswordResult = '';
            this.openResetModal = true;
        },

        openDeleteUserModal(id, name, email, presentations_count) {
            this.deleteUser = { id, name, email, presentations_count };
            this.openDeleteModal = true;
        },

        submitResetPassword() {
            if (this.resetMode === 'manual' && (!this.customPassword || this.customPassword.length < 6)) {
                alert('Password manual minimal harus 6 karakter.');
                return;
            }

            this.resetLoading = true;
            fetch('/superadmin/users/' + this.resetUser.id + '/reset-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    auto_generate: this.resetMode === 'auto',
                    password: this.customPassword
                })
            })
            .then(r => r.json())
            .then(data => {
                this.resetLoading = false;
                if (data.success) {
                    if (data.new_password) {
                        this.generatedPasswordResult = data.new_password;
                    } else {
                        alert(data.message);
                        this.openResetModal = false;
                    }
                } else {
                    alert(data.error || 'Gagal mereset password.');
                }
            })
            .catch(() => {
                this.resetLoading = false;
                alert('Terjadi kesalahan jaringan.');
            });
        },

        toggleFeature(settingId, callback) {
            fetch('/superadmin/features/' + settingId + '/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (data.is_enabled) {
                        this.stats.active_features++;
                    } else {
                        this.stats.active_features--;
                    }
                    if (callback) callback(data);
                }
            })
            .catch(() => {
                alert('Gagal mengubah status fitur.');
            });
        }
    };
}
</script>
@endsection
