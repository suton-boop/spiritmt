@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col bg-white">
    <!-- Navbar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center gap-10">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('image/logo spiritmeter.png') }}" alt="Logo SpiritMeter" class="h-10 w-auto object-contain">
                    <span class="text-2xl font-black tracking-tight text-[#1E293B]">Spirit<span class="text-[#003B7A]">Meter</span></span>
                </a>

                <!-- Nav links -->
                <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                    <a href="#fitur" class="hover:text-[#003B7A] transition-colors">Fitur Utama</a>
                    <a href="#template" class="hover:text-[#003B7A] transition-colors">Template</a>
                    <a href="#solusi" class="hover:text-[#003B7A] transition-colors">Solusi</a>
                    <a href="#harga" class="hover:text-[#003B7A] transition-colors">Harga</a>
                </nav>
            </div>

            <div class="flex items-center gap-4">
                <!-- Join Quick Pill -->
                <a href="{{ route('participant.join') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-[#003B7A] bg-[#F1F5F9] hover:bg-[#003B7A]/10 rounded-full border border-[#003B7A]/20 transition-all">
                    <span class="w-2 h-2 rounded-full bg-[#F9A703] animate-ping"></span>
                    Gabung Polling
                </a>

                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 text-sm font-bold text-white bg-[#003B7A] hover:bg-[#002d5e] rounded-xl shadow-md shadow-[#003B7A]/20 transition-all">
                        Dashboard Saya
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-bold text-[#1E293B] hover:text-[#003B7A] px-3 py-2">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="px-5 py-2.5 text-sm font-bold text-white bg-[#003B7A] hover:bg-[#002d5e] rounded-xl shadow-md shadow-[#003B7A]/25 transition-all">
                        Daftar Gratis
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-12 pb-24 overflow-hidden bg-gradient-to-b from-indigo-50/40 via-white to-white shrink-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Column -->
                <div class="lg:col-span-7 space-y-8 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#003B7A]/10 border border-[#003B7A]/20 text-[#003B7A] text-xs font-bold uppercase tracking-wider">
                        ✨ Alternatif Mentimeter Terbaik & Real-time
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-[#1E293B] tracking-tight leading-[1.15]">
                        Presentasi interaktif yang membuat <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#003B7A] via-[#004b99] to-[#F9A703]">audiens Anda terlibat aktif.</span>
                    </h1>

                    <p class="text-lg sm:text-xl text-slate-600 leading-relaxed max-w-2xl">
                        Ubah ceramah yang membosankan menjadi percakapan dua arah yang dinamis. Kumpulkan polling langsung, buat word cloud yang memukau, kuis seru, dan Q&A terbuka tanpa instal aplikasi apapun.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start">
                        <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 bg-[#003B7A] hover:bg-[#002d5e] text-white font-bold rounded-2xl shadow-xl shadow-[#003B7A]/30 hover:scale-[1.02] transition-all text-center">
                            Mulai Buat Presentasi — Gratis
                        </a>

                        <form action="{{ route('demo.login') }}" method="POST" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="w-full sm:w-auto px-6 py-4 bg-white hover:bg-[#F1F5F9] text-[#1E293B] border-2 border-slate-200 font-bold rounded-2xl shadow-sm hover:border-[#F9A703] transition-all text-center flex items-center justify-center gap-2">
                                <span>⚡ Coba Akun Demo</span>
                            </button>
                        </form>
                    </div>

                    <!-- Direct Pin Join Card inside Hero -->
                    <div class="pt-4 max-w-md mx-auto lg:mx-0">
                        <div class="bg-white p-5 rounded-2xl shadow-xl border border-slate-100 ring-1 ring-slate-900/5">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Punya kode presentasi? Masuk langsung di sini:</p>
                            <form action="{{ route('participant.join.submit') }}" method="POST" class="flex gap-2">
                                @csrf
                                <input type="text" name="code" placeholder="Contoh: 1234 5678" required
                                       class="flex-1 px-4 py-3 bg-[#F1F5F9] border border-slate-200 rounded-xl font-mono font-bold text-center tracking-widest text-[#1E293B] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#003B7A]">
                                <button type="submit" class="px-5 py-3 bg-[#F9A703] hover:bg-[#d98f02] text-slate-950 font-black rounded-xl shadow-md shadow-[#F9A703]/25 transition-all">
                                    Gabung
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Interactive Presentation Canvas Mockup -->
                <div class="lg:col-span-5">
                    <div class="relative mx-auto max-w-lg lg:max-w-none">
                        <!-- Decorative glow -->
                        <div class="absolute -top-10 -right-10 w-72 h-72 bg-indigo-400/20 rounded-full blur-3xl pointer-events-none"></div>
                        <div class="absolute -bottom-10 -left-10 w-72 h-72 bg-teal-400/20 rounded-full blur-3xl pointer-events-none"></div>

                        <!-- 16:9 Presenter screen card -->
                        <div class="relative bg-slate-900 text-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-800">
                            <!-- Top presentation instruction bar -->
                            <div class="flex items-center justify-between pb-6 border-b border-slate-800">
                                <div>
                                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest">Bergabung di {{ request()->getHost() }}/join</div>
                                    <div class="text-xl sm:text-2xl font-black text-amber-400 tracking-wider font-mono">1234 5678</div>
                                </div>
                                <div class="w-12 h-12 bg-white rounded-xl p-1 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-slate-900" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm13-2h3v3h-3v-3zm-5 5h3v3h-3v-3zm3 0h3v3h-3v-3zm2-2h3v3h-3v-3z"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Question Title -->
                            <div class="py-6 text-center">
                                <h3 class="text-lg sm:text-xl font-bold text-slate-100">Apa prioritas utama Anda untuk kesuksesan tim?</h3>
                            </div>

                            <!-- Animated Bars Mockup -->
                            <div class="space-y-3.5">
                                <div>
                                    <div class="flex justify-between text-xs font-bold text-slate-300 mb-1">
                                        <span>Komunikasi Terbuka</span>
                                        <span class="text-indigo-400 font-mono">54% (27 suara)</span>
                                    </div>
                                    <div class="w-full bg-slate-800 h-7 rounded-xl overflow-hidden p-1 flex items-center">
                                        <div class="bg-gradient-to-r from-indigo-500 to-indigo-400 h-full rounded-lg transition-all" style="width: 54%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between text-xs font-bold text-slate-300 mb-1">
                                        <span>Kejelasan Target & Visi</span>
                                        <span class="text-teal-400 font-mono">32% (16 suara)</span>
                                    </div>
                                    <div class="w-full bg-slate-800 h-7 rounded-xl overflow-hidden p-1 flex items-center">
                                        <div class="bg-gradient-to-r from-teal-500 to-teal-400 h-full rounded-lg transition-all" style="width: 32%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between text-xs font-bold text-slate-300 mb-1">
                                        <span>Budaya Apresiasi</span>
                                        <span class="text-amber-400 font-mono">14% (7 suara)</span>
                                    </div>
                                    <div class="w-full bg-slate-800 h-7 rounded-xl overflow-hidden p-1 flex items-center">
                                        <div class="bg-gradient-to-r from-amber-500 to-amber-400 h-full rounded-lg transition-all" style="width: 14%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Presenter Bottom Dock -->
                            <div class="mt-8 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                                <span class="flex items-center gap-1.5 font-bold text-emerald-400">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    50 Peserta Memilih
                                </span>
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 bg-slate-800 rounded-lg font-bold">Slide 2 / 5</span>
                                    <span class="px-2.5 py-1 bg-indigo-600/30 text-indigo-300 rounded-lg font-bold">Voting Aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Slide Types Showcase Section -->
    <section id="fitur" class="py-20 bg-slate-50 border-y border-slate-200 shrink-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs font-extrabold tracking-widest text-indigo-600 uppercase mb-2">Tipe Slide Lengkap Mentimeter</h2>
                <h3 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Semua format interaksi yang Anda butuhkan</h3>
                <p class="mt-4 text-base text-slate-600">Dari voting cepat hingga kompetisi kuis berhadiah, bangun presentasi yang diingat audiens.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Word Cloud -->
                <div class="bg-white p-7 rounded-3xl shadow-sm border border-slate-200 hover:shadow-xl hover:border-indigo-200 transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-2xl font-bold mb-5">
                        ☁️
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-2">Word Cloud Interaktif</h4>
                    <p class="text-sm text-slate-600 leading-relaxed mb-4">
                        Visualisasikan kata kunci terbanyak dari audiens secara real-time. Semakin banyak kata yang sama dikirim, semakin besar ukurannya.
                    </p>
                    <div class="flex flex-wrap gap-2 text-xs font-bold">
                        <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg">Brainstorming</span>
                        <span class="px-2.5 py-1 bg-teal-50 text-teal-700 rounded-lg">Icebreaker</span>
                    </div>
                </div>

                <!-- Multiple Choice -->
                <div class="bg-white p-7 rounded-3xl shadow-sm border border-slate-200 hover:shadow-xl hover:border-indigo-200 transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-teal-100 text-teal-600 flex items-center justify-center text-2xl font-bold mb-5">
                        📊
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-2">Pilihan Ganda (Bar & Donut)</h4>
                    <p class="text-sm text-slate-600 leading-relaxed mb-4">
                        Polling instan dengan diagram batang atau donat animasi. Tampilkan persentase atau jumlah riil, lengkap dengan opsi kunci jawaban benar.
                    </p>
                    <div class="flex flex-wrap gap-2 text-xs font-bold">
                        <span class="px-2.5 py-1 bg-teal-50 text-teal-700 rounded-lg">Polling Cepat</span>
                        <span class="px-2.5 py-1 bg-purple-50 text-purple-700 rounded-lg">Statistik</span>
                    </div>
                </div>

                <!-- Live Quiz & Leaderboard -->
                <div class="bg-white p-7 rounded-3xl shadow-sm border border-slate-200 hover:shadow-xl hover:border-indigo-200 transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-2xl font-bold mb-5">
                        🏆
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-2">Kuis & Leaderboard</h4>
                    <p class="text-sm text-slate-600 leading-relaxed mb-4">
                        Kompetisi adu cepat menjawab dengan countdown timer. Poin dihitung otomatis berdasarkan kecepatan dan ketepatan, diakhiri papan juara!
                    </p>
                    <div class="flex flex-wrap gap-2 text-xs font-bold">
                        <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg">Gamifikasi</span>
                        <span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg">Trivia Battle</span>
                    </div>
                </div>

                <!-- Scales & Rating -->
                <div class="bg-white p-7 rounded-3xl shadow-sm border border-slate-200 hover:shadow-xl hover:border-indigo-200 transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-2xl font-bold mb-5">
                        ⭐
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-2">Scales & Rating Likert</h4>
                    <p class="text-sm text-slate-600 leading-relaxed mb-4">
                        Ukur persetujuan audiens terhadap beberapa pernyataan sekaligus dengan skala 1-5. Hitung skor rata-rata secara langsung.
                    </p>
                    <div class="flex flex-wrap gap-2 text-xs font-bold">
                        <span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg">Evaluasi</span>
                        <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg">Survei Sikap</span>
                    </div>
                </div>

                <!-- Open Ended -->
                <div class="bg-white p-7 rounded-3xl shadow-sm border border-slate-200 hover:shadow-xl hover:border-indigo-200 transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-2xl font-bold mb-5">
                        💬
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-2">Open Ended (Teks Bebas)</h4>
                    <p class="text-sm text-slate-600 leading-relaxed mb-4">
                        Beri ruang audiens mencurahkan ide, cerita, atau umpan balik tertulis. Ditampilkan dalam bentuk kartu bubble interaktif.
                    </p>
                    <div class="flex flex-wrap gap-2 text-xs font-bold">
                        <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg">Kritik & Saran</span>
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg">Retrospektif</span>
                    </div>
                </div>

                <!-- Audience Q&A -->
                <div class="bg-white p-7 rounded-3xl shadow-sm border border-slate-200 hover:shadow-xl hover:border-indigo-200 transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center text-2xl font-bold mb-5">
                        🙋
                    </div>
                    <h4 class="text-xl font-bold text-slate-900 mb-2">Audience Q&A & Upvoting</h4>
                    <p class="text-sm text-slate-600 leading-relaxed mb-4">
                        Audiens mengajukan pertanyaan dan melakukan upvoting untuk topik yang paling diminati. Presenter dapat menandai pertanyaan yang sudah dijawab.
                    </p>
                    <div class="flex flex-wrap gap-2 text-xs font-bold">
                        <span class="px-2.5 py-1 bg-purple-50 text-purple-700 rounded-lg">Townhall</span>
                        <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg">Kuliah Umum</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Templates Preview Section -->
    <section id="template" class="py-20 bg-white shrink-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
                <div>
                    <h2 class="text-xs font-extrabold tracking-widest text-indigo-600 uppercase mb-2">Siap Pakai 1-Klik</h2>
                    <h3 class="text-3xl font-extrabold text-slate-900 tracking-tight">Koleksi Template Populer</h3>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="{{ route('register') }}" class="text-indigo-600 font-bold hover:text-indigo-700 inline-flex items-center gap-1">
                        Lihat Semua Template &rarr;
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Template 1 -->
                <div class="border border-slate-200 rounded-3xl p-6 hover:shadow-lg transition-all group">
                    <div class="h-40 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 p-5 flex flex-col justify-between text-white mb-5">
                        <span class="text-2xl">🧊</span>
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider bg-white/20 px-2.5 py-1 rounded-full">3 Slide Interaktif</span>
                            <h4 class="text-lg font-black mt-2">Team Icebreaker</h4>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600 mb-4">Sangat cocok untuk membuka rapat mingguan, melatih tim baru, atau memecah kebekuan acara.</p>
                    <a href="{{ route('register') }}" class="inline-flex items-center text-sm font-bold text-indigo-600 group-hover:text-indigo-700">
                        Gunakan Template Ini &rarr;
                    </a>
                </div>

                <!-- Template 2 -->
                <div class="border border-slate-200 rounded-3xl p-6 hover:shadow-lg transition-all group">
                    <div class="h-40 rounded-2xl bg-gradient-to-tr from-amber-500 to-rose-600 p-5 flex flex-col justify-between text-white mb-5">
                        <span class="text-2xl">🏆</span>
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider bg-white/20 px-2.5 py-1 rounded-full">Kuis Kompetisi</span>
                            <h4 class="text-lg font-black mt-2">Live Trivia Battle</h4>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600 mb-4">Buat suasana heboh dengan format kuis cepat dan papan leaderboard juara otomatis.</p>
                    <a href="{{ route('register') }}" class="inline-flex items-center text-sm font-bold text-indigo-600 group-hover:text-indigo-700">
                        Gunakan Template Ini &rarr;
                    </a>
                </div>

                <!-- Template 3 -->
                <div class="border border-slate-200 rounded-3xl p-6 hover:shadow-lg transition-all group">
                    <div class="h-40 rounded-2xl bg-gradient-to-tr from-teal-500 to-emerald-600 p-5 flex flex-col justify-between text-white mb-5">
                        <span class="text-2xl">💡</span>
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider bg-white/20 px-2.5 py-1 rounded-full">Evaluasi Sesi</span>
                            <h4 class="text-lg font-black mt-2">Workshop Feedback</h4>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600 mb-4">Kumpulkan kepuasan peserta, skala pemahaman materi, dan kritik konstruktif pasca acara.</p>
                    <a href="{{ route('register') }}" class="inline-flex items-center text-sm font-bold text-indigo-600 group-hover:text-indigo-700">
                        Gunakan Template Ini &rarr;
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="mt-auto bg-slate-900 text-slate-400 py-16 border-t border-slate-800 shrink-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8 mb-12">
                <div class="col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('image/logo spiritmeter.png') }}" alt="SpiritMeter Logo" class="h-8 w-auto object-contain">
                        <span class="text-xl font-black text-white">Spirit<span class="text-[#F9A703]">Meter</span></span>
                    </div>
                    <p class="text-sm text-slate-400 max-w-sm mb-4">
                        Platform interaksi presentasi modern, mudah digunakan, dan real-time. Terinspirasi dari pengalaman terbaik Mentimeter.
                    </p>
                    <div class="text-xs text-slate-500">
                        &copy; {{ date('Y') }} SpiritMeter. Dirancang untuk engagement audiens maksimal.
                    </div>
                </div>

                <div>
                    <h5 class="text-white text-xs font-bold uppercase tracking-wider mb-4">Fitur</h5>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="#fitur" class="hover:text-white transition-colors">Word Cloud</a></li>
                        <li><a href="#fitur" class="hover:text-white transition-colors">Pilihan Ganda</a></li>
                        <li><a href="#fitur" class="hover:text-white transition-colors">Kuis & Leaderboard</a></li>
                        <li><a href="#fitur" class="hover:text-white transition-colors">Skala Likert</a></li>
                        <li><a href="#fitur" class="hover:text-white transition-colors">Audience Q&A</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="text-white text-xs font-bold uppercase tracking-wider mb-4">Solusi</h5>
                    <ul class="space-y-2.5 text-sm">
                        <li><span class="text-slate-300">Pendidikan & Kampus</span></li>
                        <li><span class="text-slate-300">Rapat Perusahaan</span></li>
                        <li><span class="text-slate-300">Webinar & Event</span></li>
                        <li><span class="text-slate-300">Pelatihan & Workshop</span></li>
                    </ul>
                </div>

                <div>
                    <h5 class="text-white text-xs font-bold uppercase tracking-wider mb-4">Akses Cepat</h5>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('participant.join') }}" class="text-indigo-400 font-bold hover:text-indigo-300">Gabung via PIN</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white">Masuk Akun</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white">Daftar Akun Baru</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
</div>
@endsection
