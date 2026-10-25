<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $presentation->title }} - Presenter View</title>
    <link rel="icon" type="image/png" href="{{ asset('image/logo spiritmeter.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        [x-cloak] { display: none !important; }
        .dock-transition { transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full overflow-hidden select-none transition-colors duration-300"
      :class="isLight() ? 'bg-slate-50 text-slate-900' : 'bg-slate-950 text-white'"
      x-data="presenterApp({{ $presentation->id }}, '{{ csrf_token() }}')"
      @keydown.window="handleKeyboard($event)">

    <!-- Screen Background Theme Wrapper -->
    <div class="h-full w-full flex flex-col justify-between p-6 sm:p-10 relative overflow-hidden transition-all duration-500"
         :class="getThemeBackground()"
         :style="getThemeStyle()">

        <!-- Decorative background elements -->
        <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full blur-3xl pointer-events-none transition-all duration-500"
             :class="isLight() ? 'bg-blue-400/20' : 'bg-indigo-500/10'"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full blur-3xl pointer-events-none transition-all duration-500"
             :class="isLight() ? 'bg-sky-400/20' : 'bg-teal-500/10'"></div>

        <!-- Floating Quick Show Header Trigger when hidden -->
        <div x-show="!showJoinBar" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="absolute top-4 left-1/2 -translate-x-1/2 z-30">
            <button @click="showJoinBar = true; _joinBarUserToggled = true" 
                    class="px-4 py-2 rounded-full backdrop-blur-md text-xs font-bold border shadow-xl flex items-center gap-2 transition-all opacity-75 hover:opacity-100 hover:scale-105"
                    :class="isLight() ? 'bg-white/95 border-blue-200 text-blue-700 shadow-blue-500/10' : 'bg-slate-900/90 border-slate-700 text-slate-200 shadow-black/50'">
                <img src="{{ asset('image/logo spiritmeter.png') }}" alt="SpiritMeter" class="w-4 h-4 object-contain">
                <span>Tampilkan PIN & Link Join</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 font-mono border border-slate-700">H</span>
            </button>
        </div>

        <!-- TOP BAR: Join Instructions -->
        <header x-show="showJoinBar"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-4"
                class="relative z-20 flex items-center justify-between pb-4 border-b transition-colors"
                :class="isLight() ? 'border-blue-200/60' : 'border-white/10'">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" 
                   class="w-10 h-10 rounded-2xl p-1.5 flex items-center justify-center transition-all group"
                   :class="isLight() ? 'bg-white hover:bg-slate-50 border border-blue-200 shadow-md shadow-blue-500/10' : 'bg-slate-900/90 hover:bg-slate-800 border border-slate-700/80 shadow-md'" 
                   title="Kembali ke Dashboard">
                    <img src="{{ asset('image/logo spiritmeter.png') }}" alt="Logo SpiritMeter" class="w-full h-full object-contain group-hover:scale-105 transition-transform">
                </a>
                <div>
                    <div class="text-xs sm:text-sm font-semibold transition-colors"
                         :class="isLight() ? 'text-slate-600' : 'text-slate-300'">
                        Bergabung di <span class="font-extrabold underline underline-offset-4"
                                          :class="isLight() ? 'text-blue-600 decoration-blue-500' : 'text-white decoration-indigo-400'"
                                          x-text="joinDomain">{{ request()->getHost() }}/join</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <div class="px-5 py-2 rounded-2xl backdrop-blur-md border flex items-center gap-3 shadow-lg transition-all"
                     :class="isLight() ? 'bg-white/95 border-blue-200/80 shadow-blue-500/5' : 'bg-white/10 border-white/15'">
                    <span class="text-xs font-bold uppercase tracking-widest hidden sm:inline"
                          :class="isLight() ? 'text-slate-500' : 'text-slate-300'">KODE PIN:</span>
                    <span class="text-2xl sm:text-3xl font-black font-mono tracking-wider"
                          :class="isLight() ? 'text-blue-600' : 'text-amber-300'"
                          x-text="state.formatted_code"></span>
                </div>

                <!-- Big QR Code Toggle Button -->
                <button @click="showQrModal = true; renderQrCode()" 
                        class="p-2.5 rounded-2xl border transition-all"
                        :class="isLight() ? 'bg-white hover:bg-slate-50 border-blue-200 text-slate-700 shadow-sm' : 'bg-white/10 hover:bg-white/20 border-white/15 text-white'"
                        title="Tampilkan QR Code Besar">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm13-2h3v3h-3v-3zm-5 5h3v3h-3v-3zm3 0h3v3h-3v-3zm2-2h3v3h-3v-3z"/>
                    </svg>
                </button>

                <!-- Hide Header Button -->
                <button @click="showJoinBar = false; _joinBarUserToggled = true" 
                        class="p-2.5 rounded-2xl border transition-all"
                        :class="isLight() ? 'bg-white hover:bg-slate-50 border-blue-200 text-slate-500 hover:text-slate-800 shadow-sm' : 'bg-white/10 hover:bg-white/20 border-white/15 text-slate-400 hover:text-white'"
                        title="Sembunyikan Header PIN & Join (Tekan H)">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                    </svg>
                </button>
            </div>
        </header>

        <!-- MAIN PRESENTATION CANVAS -->
        <main class="flex-1 flex flex-col items-center justify-center py-6 px-4 text-center relative z-10 w-full max-w-7xl mx-auto overflow-y-auto">
            
            <!-- A. Stacked Layouts (Center, Top Banner, or No Content Image) -->
            <div x-show="!isSideLayout()" class="w-full flex-1 flex flex-col items-center justify-center text-center max-w-5xl mx-auto transition-all">
                <!-- Top Banner (if layout === 'banner' and image exists) -->
                <div x-show="getImageLayout() === 'banner' && currentSlide.settings?.content_image" class="w-full max-h-56 sm:max-h-72 rounded-3xl overflow-hidden mb-6 shadow-2xl border border-white/20 bg-slate-950 flex-shrink-0">
                    <img :src="currentSlide.settings?.content_image" alt="Banner Image" class="w-full h-full object-cover">
                </div>

                <!-- Slide Question Title -->
                <div class="mb-6 max-w-4xl w-full">
                    <h1 class="transition-colors"
                        :class="[isLight() ? 'text-slate-900 drop-shadow-none' : 'text-white drop-shadow-md', getQuestionClasses(false)]"
                        x-text="currentSlide.question || 'SpiritMeter'"></h1>
                    
                    <p class="mt-3 font-medium max-w-3xl mx-auto transition-colors"
                       :class="[isLight() ? 'text-slate-600' : 'text-slate-300', getDescriptionClasses()]"
                       x-show="currentSlide.type !== 'content_paragraph' && currentSlide.description" x-text="currentSlide.description"></p>

                    <!-- Text Slide Content (Materi) Card -->
                    <div x-show="currentSlide.type === 'content_paragraph' && currentSlide.description"
                         class="mt-6 max-w-3xl mx-auto p-6 sm:p-8 rounded-3xl backdrop-blur-md border text-left shadow-2xl transition-all"
                         :class="isLight() ? 'bg-white/95 border-blue-100 shadow-blue-500/5 text-slate-800' : 'bg-slate-900/80 border-white/10 text-slate-100'">
                        <div :class="getContentParagraphClasses()" class="whitespace-pre-line leading-relaxed" x-text="currentSlide.description"></div>
                    </div>

                    <!-- Center Image (if layout !== 'banner' and image exists) -->
                    <div x-show="getImageLayout() !== 'banner' && currentSlide.settings?.content_image" class="mt-5 max-w-md mx-auto">
                        <img :src="currentSlide.settings?.content_image" alt="Content Image" 
                             class="max-h-52 sm:max-h-64 w-auto mx-auto rounded-3xl object-contain shadow-2xl border border-white/20 bg-slate-900/60">
                    </div>
                </div>

                <!-- Live Results Visualizer (Stacked) -->
                @include('presenter.partials.visualizer')
            </div>

            <!-- B. Side Layouts (Left, Right, Split 50:50, Sidebar) -->
            <div x-show="isSideLayout()" 
                 class="w-full flex-1 flex flex-col md:flex-row items-center justify-center gap-8 lg:gap-12 max-w-7xl mx-auto transition-all"
                 :class="isLeftImage() ? 'md:flex-row' : 'md:flex-row-reverse'">
                
                <!-- Image Column -->
                <div :class="{
                        'w-full md:w-1/2': getImageLayout() === 'split_left' || getImageLayout() === 'split_right',
                        'w-full md:w-1/4': getImageLayout() === 'sidebar_left' || getImageLayout() === 'sidebar_right',
                        'w-full md:w-5/12': getImageLayout() === 'left' || getImageLayout() === 'right'
                     }"
                     class="flex items-center justify-center flex-shrink-0">
                    <img :src="currentSlide.settings?.content_image" alt="Content Image"
                         :class="{
                            'w-full h-80 sm:h-96 object-cover rounded-3xl': getImageLayout() === 'split_left' || getImageLayout() === 'split_right' || getImageLayout() === 'sidebar_left' || getImageLayout() === 'sidebar_right',
                            'max-h-80 sm:max-h-96 w-auto object-contain rounded-3xl': getImageLayout() === 'left' || getImageLayout() === 'right'
                         }"
                         class="shadow-2xl border border-white/20 bg-slate-900/60">
                </div>

                <!-- Text & Visualizer Column -->
                <div :class="{
                        'w-full md:w-1/2': getImageLayout() === 'split_left' || getImageLayout() === 'split_right',
                        'w-full md:w-3/4': getImageLayout() === 'sidebar_left' || getImageLayout() === 'sidebar_right',
                        'w-full md:w-7/12': getImageLayout() === 'left' || getImageLayout() === 'right'
                     }"
                     class="flex flex-col text-left">
                    <div class="mb-6 w-full">
                        <h1 class="transition-colors"
                            :class="[isLight() ? 'text-slate-900 drop-shadow-none' : 'text-white drop-shadow-md', getQuestionClasses(true)]"
                            x-text="currentSlide.question || 'SpiritMeter'"></h1>
                        
                        <p class="mt-3 font-medium transition-colors"
                           :class="[isLight() ? 'text-slate-600' : 'text-slate-300', getDescriptionClasses()]"
                           x-show="currentSlide.type !== 'content_paragraph' && currentSlide.description" x-text="currentSlide.description"></p>

                        <!-- Text Slide Content (Materi) Card in Side Layout -->
                        <div x-show="currentSlide.type === 'content_paragraph' && currentSlide.description"
                             class="mt-4 p-5 sm:p-6 rounded-2xl backdrop-blur-md border text-left shadow-xl transition-all"
                             :class="isLight() ? 'bg-white/95 border-blue-100 shadow-blue-500/5 text-slate-800' : 'bg-slate-900/80 border-white/10 text-slate-100'">
                            <div :class="getContentParagraphClasses()" class="whitespace-pre-line leading-relaxed" x-text="currentSlide.description"></div>
                        </div>
                    </div>

                    <!-- Live Results Visualizer (Side) -->
                    @include('presenter.partials.visualizer')
                </div>

            </div>

        </main>

        <!-- FLOATING BOTTOM PRESENTER DOCK -->
        <footer class="relative z-30 flex items-center justify-between pt-4 border-t transition-colors"
                :class="isLight() ? 'border-blue-200/60' : 'border-white/10'">
            <!-- Left: Response count -->
            <div class="flex items-center gap-3">
                <span class="px-4 py-2 rounded-xl border text-xs sm:text-sm font-bold flex items-center gap-2 shadow-sm"
                      :class="isLight() ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-emerald-500/20 border-emerald-500/30 text-emerald-300'">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span x-text="state.slide_responses"></span> Respons Masuk
                </span>

                <span class="px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm"
                      :class="isLight() ? 'bg-white border border-blue-200/80 text-slate-700' : 'bg-white/10 text-slate-300'"
                      x-text="state.voting_status === 'open' ? '🟢 Voting Terbuka' : '🔴 Voting Ditutup'"></span>
            </div>

            <!-- Center: Slide Navigation -->
            <div class="flex items-center gap-2 backdrop-blur-md px-3 py-1.5 rounded-2xl border shadow-xl"
                 :class="isLight() ? 'bg-white/95 border-blue-200/80 text-slate-800' : 'bg-slate-900/90 border-white/15 text-slate-200'">
                <button @click="previousSlide()" 
                        class="p-2 rounded-xl transition-all"
                        :class="isLight() ? 'hover:bg-slate-100 text-slate-700' : 'hover:bg-white/10 text-white'"
                        title="Slide Sebelumnya (&larr;)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </button>

                <span class="px-3 text-xs sm:text-sm font-bold font-mono"
                      :class="isLight() ? 'text-slate-700' : 'text-slate-200'">
                    <span x-text="currentSlideIndex + 1"></span> / <span x-text="allSlides.length"></span>
                </span>

                <button @click="nextSlide()" 
                        class="p-2 rounded-xl transition-all"
                        :class="isLight() ? 'hover:bg-slate-100 text-slate-700' : 'hover:bg-white/10 text-white'"
                        title="Slide Selanjutnya (&rarr;)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

            <!-- Visualization Model Switcher (Bars, Donut, Pie, Dots) -->
            <div x-show="currentSlide.type === 'multiple_choice' || currentSlide.type === 'quiz'" 
                 class="flex items-center gap-1 backdrop-blur-md px-1.5 py-1 rounded-2xl border shadow-xl"
                 :class="isLight() ? 'bg-white/95 border-blue-200/80 text-slate-800' : 'bg-slate-900/90 border-white/15 text-slate-200'">
                <button @click="setVisualizationType('bars')" 
                        :class="getVisualizationType() === 'bars' ? 'bg-indigo-600 text-white shadow-sm' : (isLight() ? 'hover:bg-slate-100 text-slate-600' : 'hover:bg-white/10 text-slate-400')"
                        class="px-2.5 py-1 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
                        title="Tampilan Bars (Batang)">
                    <span>📊</span>
                    <span class="hidden sm:inline">Bars</span>
                </button>
                <button @click="setVisualizationType('donut')" 
                        :class="getVisualizationType() === 'donut' ? 'bg-indigo-600 text-white shadow-sm' : (isLight() ? 'hover:bg-slate-100 text-slate-600' : 'hover:bg-white/10 text-slate-400')"
                        class="px-2.5 py-1 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
                        title="Tampilan Donut (Cincin)">
                    <span>🍩</span>
                    <span class="hidden sm:inline">Donut</span>
                </button>
                <button @click="setVisualizationType('pie')" 
                        :class="getVisualizationType() === 'pie' ? 'bg-indigo-600 text-white shadow-sm' : (isLight() ? 'hover:bg-slate-100 text-slate-600' : 'hover:bg-white/10 text-slate-400')"
                        class="px-2.5 py-1 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
                        title="Tampilan Pie (Lingkaran)">
                    <span>🥧</span>
                    <span class="hidden sm:inline">Pie</span>
                </button>
                <button @click="setVisualizationType('dots')" 
                        :class="getVisualizationType() === 'dots' ? 'bg-indigo-600 text-white shadow-sm' : (isLight() ? 'hover:bg-slate-100 text-slate-600' : 'hover:bg-white/10 text-slate-400')"
                        class="px-2.5 py-1 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5"
                        title="Tampilan Dots (Titik Respon)">
                    <span>🔵</span>
                    <span class="hidden sm:inline">Dots</span>
                </button>
            </div>

            <!-- Right: Interactive Controls -->
            <div class="flex items-center gap-2">
                <!-- Toggle Reveal Correct Answer (for Quiz) -->
                <button x-show="currentSlide.type === 'quiz'" @click="showCorrectAnswer = !showCorrectAnswer; triggerConfetti()"
                        class="px-3 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 text-xs font-black transition-all shadow-sm">
                    🎉 Kunci Jawaban
                </button>

                <!-- Toggle Close/Open Voting -->
                <button @click="toggleVoting()" 
                        class="p-2.5 rounded-xl border transition-all" 
                        :class="isLight() ? 'bg-white hover:bg-slate-50 border-blue-200 text-slate-700 shadow-sm' : 'bg-white/10 hover:bg-white/20 border-transparent text-white'"
                        :title="state.voting_status === 'open' ? 'Kunci Voting (C)' : 'Buka Voting (C)'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </button>

                <!-- Toggle Results -->
                <button @click="toggleResults()" 
                        class="p-2.5 rounded-xl border transition-all" 
                        :class="isLight() ? 'bg-white hover:bg-slate-50 border-blue-200 text-slate-700 shadow-sm' : 'bg-white/10 hover:bg-white/20 border-transparent text-white'"
                        :title="state.show_results ? 'Sembunyikan Hasil (R)' : 'Tampilkan Hasil (R)'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>

                <!-- Toggle Header PIN & Join Instructions -->
                <button @click="showJoinBar = !showJoinBar; _joinBarUserToggled = true" 
                        class="p-2.5 rounded-xl border transition-all" 
                        :class="showJoinBar 
                            ? (isLight() ? 'bg-blue-50 border-blue-300 text-blue-600 shadow-sm' : 'bg-white/10 hover:bg-white/20 border-white/15 text-amber-300') 
                            : (isLight() ? 'bg-slate-200 text-slate-400 border-slate-300 hover:text-slate-600' : 'bg-slate-800 text-slate-500 border-slate-700 hover:text-white')"
                        :title="showJoinBar ? 'Sembunyikan Header PIN (H)' : 'Tampilkan Header PIN (H)'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="showJoinBar">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                    </svg>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!showJoinBar">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>

                <!-- Fullscreen Toggle -->
                <button @click="toggleFullscreen()" 
                        class="p-2.5 rounded-xl border transition-all" 
                        :class="isLight() ? 'bg-white hover:bg-slate-50 border-blue-200 text-slate-700 shadow-sm' : 'bg-white/10 hover:bg-white/20 border-transparent text-white'"
                        title="Layar Penuh (F)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                </button>
            </div>
        </footer>

    </div>

    <!-- BIG AUDIENCE QR CODE MODAL -->
    <div x-show="showQrModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-950/90 backdrop-blur-md">
        <div @click.away="showQrModal = false" class="bg-slate-900 border border-white/20 rounded-3xl p-8 max-w-lg w-full text-center shadow-2xl text-white">
            <h2 class="text-2xl font-black mb-2">Scan untuk Bergabung Langsung!</h2>
            <p class="text-sm text-slate-300 mb-6">Arahkan kamera ponsel Anda ke QR code di bawah ini.</p>

            <div class="bg-white p-6 rounded-3xl inline-block shadow-2xl mb-6">
                <canvas id="presenter-qr-canvas" class="w-64 h-64"></canvas>
            </div>

            <div class="text-xs text-slate-400">
                Atau buka <strong class="text-white" x-text="joinDomain">{{ request()->getHost() }}/join</strong> &bull; Masukkan PIN: <span class="font-mono text-amber-300 font-bold text-base" x-text="state.formatted_code"></span>
            </div>

            <button @click="showQrModal = false" class="mt-6 px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-sm transition-all">
                Tutup Layar QR
            </button>
        </div>
    </div>

    <script>
    function presenterApp(presentationId, csrfToken) {
        return {
            presentationId: presentationId,
            state: {
                formatted_code: '',
                show_results: true,
                voting_status: 'open',
                theme: 'default',
                slide_responses: 0,
                questions: []
            },
            currentSlide: {},
            results: {},
            allSlides: @json($presentation->slides),
            showCorrectAnswer: false,
            showQrModal: false,
            showJoinBar: true,
            _joinBarUserToggled: false,
            pollingInterval: null,

            init() {
                this.fetchLiveState();
                // Polling live state every 1.5 seconds
                this.pollingInterval = setInterval(() => {
                    this.fetchLiveState();
                }, 1500);
            },

            get joinDomain() {
                return window.location.host + '/join';
            },

            get directParticipantUrl() {
                return window.location.origin + '/m/' + (this.state.access_code || '');
            },

            get currentSlideIndex() {
                return this.allSlides.findIndex(s => s.id === this.state.current_slide_id);
            },

            fetchLiveState() {
                fetch('/presentations/' + this.presentationId + '/live-state')
                    .then(r => r.json())
                    .then(data => {
                        this.state = data;
                        this.currentSlide = data.slide || {};
                        this.results = data.results || {};

                        // Check slide show_header setting if not manually toggled by user in this session
                        if (!this._joinBarUserToggled && this.currentSlide.settings && this.currentSlide.settings.show_header !== undefined) {
                            this.showJoinBar = this.currentSlide.settings.show_header;
                        }

                        // Trigger confetti on leaderboard
                        if (this.currentSlide.type === 'leaderboard' && window.confetti) {
                            if (!this._confettiFired) {
                                window.confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
                                this._confettiFired = true;
                            }
                        } else {
                            this._confettiFired = false;
                        }
                    });
            },

            previousSlide() {
                const idx = this.currentSlideIndex;
                if (idx > 0) {
                    this.switchSlide(this.allSlides[idx - 1].id);
                }
            },

            nextSlide() {
                const idx = this.currentSlideIndex;
                if (idx < this.allSlides.length - 1) {
                    this.switchSlide(this.allSlides[idx + 1].id);
                }
            },

            switchSlide(slideId) {
                this.showCorrectAnswer = false;
                fetch('/presentations/' + this.presentationId + '/slide', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ slide_id: slideId })
                }).then(() => {
                    this.fetchLiveState();
                });
            },

            toggleVoting() {
                fetch('/presentations/' + this.presentationId + '/toggle-voting', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                }).then(() => this.fetchLiveState());
            },

            toggleResults() {
                fetch('/presentations/' + this.presentationId + '/toggle-results', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                }).then(() => this.fetchLiveState());
            },

            toggleFullscreen() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(() => {});
                } else {
                    document.exitFullscreen().catch(() => {});
                }
            },

            handleKeyboard(e) {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

                if (e.key === 'ArrowRight' || e.key === ' ') {
                    e.preventDefault();
                    this.nextSlide();
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    this.previousSlide();
                } else if (e.key.toLowerCase() === 'f') {
                    this.toggleFullscreen();
                } else if (e.key.toLowerCase() === 'r') {
                    this.toggleResults();
                } else if (e.key.toLowerCase() === 'c') {
                    this.toggleVoting();
                } else if (e.key.toLowerCase() === 'h') {
                    this.showJoinBar = !this.showJoinBar;
                    this._joinBarUserToggled = true;
                }
            },

            triggerConfetti() {
                if (window.confetti) {
                    window.confetti({ particleCount: 80, spread: 60, origin: { y: 0.7 } });
                }
            },

            renderQrCode() {
                this.$nextTick(() => {
                    const canvas = document.getElementById('presenter-qr-canvas');
                    if (canvas && window.QRCode) {
                        window.QRCode.toCanvas(canvas, this.directParticipantUrl, {
                            width: 256,
                            margin: 1,
                            color: { dark: '#0f172a', light: '#ffffff' }
                        });
                    }
                });
            },

            isLight() {
                return this.state.theme === 'light_tech';
            },

            getVisualizationType() {
                return (this.currentSlide && this.currentSlide.settings && (this.currentSlide.settings.visualization_type || this.currentSlide.settings.chart_type)) || 'bars';
            },

            setVisualizationType(type) {
                if (!this.currentSlide) return;
                this.currentSlide.settings = this.currentSlide.settings || {};
                this.currentSlide.settings.visualization_type = type;
                this.currentSlide.settings.chart_type = type;

                fetch('/slides/' + this.currentSlide.id, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        settings: this.currentSlide.settings
                    })
                });
            },

            getChartSvg(isDonut = false, customOptions = null) {
                const displayOpts = this.getDisplayOptions(customOptions);
                if (!displayOpts || displayOpts.length === 0) return '';

                const total = displayOpts.reduce((sum, o) => sum + (Number(o.count) || 0), 0);
                const isZero = total === 0;
                const isPercent = this.isPercentageMode();
                const cx = 120, cy = 120;
                const rOuter = 100;
                const rInner = isDonut ? 52 : 0;

                let currentAngle = -90;
                let pathsSvg = '';
                let textsSvg = '';

                displayOpts.forEach((opt, idx) => {
                    const val = Number(opt.count) || 0;
                    const percent = isZero ? (1 / displayOpts.length) : (val / total);
                    const angleSize = percent * 360;

                    if (angleSize <= 0) return;

                    const startAngle = currentAngle;
                    const endAngle = currentAngle + angleSize;
                    currentAngle = endAngle;

                    const startRad = (startAngle * Math.PI) / 180;
                    const endRad = (endAngle * Math.PI) / 180;
                    const midRad = ((startAngle + endAngle) / 2 * Math.PI) / 180;

                    const x1Outer = Number((cx + rOuter * Math.cos(startRad)).toFixed(2));
                    const y1Outer = Number((cy + rOuter * Math.sin(startRad)).toFixed(2));
                    const x2Outer = Number((cx + rOuter * Math.cos(endRad)).toFixed(2));
                    const y2Outer = Number((cy + rOuter * Math.sin(endRad)).toFixed(2));

                    const largeArc = angleSize > 180 ? 1 : 0;
                    const color = isZero ? (opt.color || '#6366f1') + '66' : (opt.color || '#6366f1');

                    let d = '';
                    if (isDonut) {
                        const x1Inner = Number((cx + rInner * Math.cos(startRad)).toFixed(2));
                        const y1Inner = Number((cy + rInner * Math.sin(startRad)).toFixed(2));
                        const x2Inner = Number((cx + rInner * Math.cos(endRad)).toFixed(2));
                        const y2Inner = Number((cy + rInner * Math.sin(endRad)).toFixed(2));

                        if (angleSize >= 359.9) {
                            d = 'M ' + cx + ' ' + (cy - rOuter) + 
                                ' A ' + rOuter + ' ' + rOuter + ' 0 1 1 ' + cx + ' ' + (cy + rOuter) + 
                                ' A ' + rOuter + ' ' + rOuter + ' 0 1 1 ' + cx + ' ' + (cy - rOuter) + 
                                ' M ' + cx + ' ' + (cy - rInner) + 
                                ' A ' + rInner + ' ' + rInner + ' 0 1 0 ' + cx + ' ' + (cy + rInner) + 
                                ' A ' + rInner + ' ' + rInner + ' 0 1 0 ' + cx + ' ' + (cy - rInner) + ' Z';
                        } else {
                            d = 'M ' + x1Outer + ' ' + y1Outer + 
                                ' A ' + rOuter + ' ' + rOuter + ' 0 ' + largeArc + ' 1 ' + x2Outer + ' ' + y2Outer + 
                                ' L ' + x2Inner + ' ' + y2Inner + 
                                ' A ' + rInner + ' ' + rInner + ' 0 ' + largeArc + ' 0 ' + x1Inner + ' ' + y1Inner + ' Z';
                        }
                    } else {
                        if (angleSize >= 359.9) {
                            d = 'M ' + cx + ' ' + (cy - rOuter) + 
                                ' A ' + rOuter + ' ' + rOuter + ' 0 1 1 ' + cx + ' ' + (cy + rOuter) + 
                                ' A ' + rOuter + ' ' + rOuter + ' 0 1 1 ' + cx + ' ' + (cy - rOuter) + 
                                ' Z';
                        } else {
                            d = 'M ' + cx + ' ' + cy + 
                                ' L ' + x1Outer + ' ' + y1Outer + 
                                ' A ' + rOuter + ' ' + rOuter + ' 0 ' + largeArc + ' 1 ' + x2Outer + ' ' + y2Outer + ' Z';
                        }
                    }

                    pathsSvg += '<path d="' + d + '" fill="' + color + '" stroke="rgba(15,23,42,0.4)" stroke-width="2" class="transition-all duration-700" />\n';

                    if (!isZero && angleSize > 25 && val > 0) {
                        const textR = isDonut ? (rOuter + rInner) / 2 : rOuter * 0.65;
                        const textX = Number((cx + textR * Math.cos(midRad)).toFixed(2));
                        const textY = Number((cy + textR * Math.sin(midRad)).toFixed(2));
                        const textVal = isPercent ? (Math.round((val / total) * 100) + '%') : val;
                        textsSvg += '<text x="' + textX + '" y="' + textY + '" fill="#ffffff" font-size="13" font-weight="900" font-family="monospace" text-anchor="middle" dominant-baseline="central">' + textVal + '</text>\n';
                    }
                });

                if (isZero) {
                    textsSvg = '<text x="' + cx + '" y="' + cy + '" fill="rgba(255,255,255,0.4)" font-size="12" font-weight="700" text-anchor="middle" dominant-baseline="central">Belum ada suara</text>\n';
                } else if (isDonut) {
                    textsSvg += '<text x="' + cx + '" y="' + (cy - 7) + '" fill="rgba(255,255,255,0.6)" font-size="10" font-weight="700" text-anchor="middle" dominant-baseline="central">TOTAL</text>\n' +
                                '<text x="' + cx + '" y="' + (cy + 10) + '" fill="#ffffff" font-size="16" font-weight="900" font-family="monospace" text-anchor="middle" dominant-baseline="central">' + total + '</text>\n';
                }

                return '<svg viewBox="0 0 240 240" class="w-full h-full drop-shadow-2xl" style="overflow:visible;">\n' + pathsSvg + textsSvg + '</svg>';
            },

            isPercentageMode() {
                return Boolean(this.currentSlide && this.currentSlide.settings && this.currentSlide.settings.show_percentage);
            },

            getDisplayOptions(customOptions = null) {
                const rawOptions = customOptions || (this.results && this.results.options) || (this.currentSlide && this.currentSlide.options) || [];
                if (!rawOptions || rawOptions.length === 0) return [];

                const totalVotes = rawOptions.reduce((sum, o) => sum + (Number(o.count) || 0), 0);
                const maxVal = Math.max(1, ...rawOptions.map(o => Number(o.count) || 0));

                return rawOptions.map((opt, idx) => {
                    const count = Number(opt.count) || 0;
                    const percent = totalVotes > 0 ? Math.round((count / totalVotes) * 100) : 0;
                    const displayValue = this.isPercentageMode() ? (percent + '%') : count;
                    const barHeight = totalVotes > 0 ? Math.max(8, Math.round((count / maxVal) * 92)) : 6;

                    return {
                        id: opt.id || idx,
                        text: opt.text || ('Opsi ' + (idx + 1)),
                        color: opt.color || ['#6366f1', '#ef4444', '#1e293b', '#10b981', '#f59e0b', '#8b5cf6'][idx % 6],
                        is_correct: opt.is_correct,
                        count: count,
                        percentage: percent,
                        displayValue: displayValue,
                        barHeight: barHeight,
                        dotCount: count
                    };
                });
            },

            getPieSlices(isDonut = false, customOptions = null) {
                const displayOpts = this.getDisplayOptions(customOptions);
                if (!displayOpts || displayOpts.length === 0) return [];

                const total = displayOpts.reduce((sum, o) => sum + (Number(o.count) || 0), 0);
                const isZero = total === 0;
                let currentAngle = -90;
                const slices = [];
                const cx = 120, cy = 120;
                const rOuter = 100;
                const rInner = isDonut ? 52 : 0;

                displayOpts.forEach((opt, idx) => {
                    const val = Number(opt.count) || 0;
                    const percent = isZero ? (1 / displayOpts.length) : (val / total);
                    const angleSize = percent * 360;

                    if (angleSize <= 0) return;

                    const startAngle = currentAngle;
                    const endAngle = currentAngle + angleSize;
                    currentAngle = endAngle;

                    const startRad = (startAngle * Math.PI) / 180;
                    const endRad = (endAngle * Math.PI) / 180;
                    const midRad = ((startAngle + endAngle) / 2 * Math.PI) / 180;

                    const x1Outer = Number((cx + rOuter * Math.cos(startRad)).toFixed(2));
                    const y1Outer = Number((cy + rOuter * Math.sin(startRad)).toFixed(2));
                    const x2Outer = Number((cx + rOuter * Math.cos(endRad)).toFixed(2));
                    const y2Outer = Number((cy + rOuter * Math.sin(endRad)).toFixed(2));

                    const largeArc = angleSize > 180 ? 1 : 0;

                    let pathData = '';
                    if (isDonut) {
                        const x1Inner = Number((cx + rInner * Math.cos(startRad)).toFixed(2));
                        const y1Inner = Number((cy + rInner * Math.sin(startRad)).toFixed(2));
                        const x2Inner = Number((cx + rInner * Math.cos(endRad)).toFixed(2));
                        const y2Inner = Number((cy + rInner * Math.sin(endRad)).toFixed(2));

                        if (angleSize >= 359.9) {
                            pathData = `M ${cx} ${cy - rOuter} A ${rOuter} ${rOuter} 0 1 1 ${cx} ${cy + rOuter} A ${rOuter} ${rOuter} 0 1 1 ${cx} ${cy - rOuter} M ${cx} ${cy - rInner} A ${rInner} ${rInner} 0 1 0 ${cx} ${cy + rInner} A ${rInner} ${rInner} 0 1 0 ${cx} ${cy - rInner} Z`;
                        } else {
                            pathData = `M ${x1Outer} ${y1Outer} A ${rOuter} ${rOuter} 0 ${largeArc} 1 ${x2Outer} ${y2Outer} L ${x2Inner} ${y2Inner} A ${rInner} ${rInner} 0 ${largeArc} 0 ${x1Inner} ${y1Inner} Z`;
                        }
                    } else {
                        if (angleSize >= 359.9) {
                            pathData = `M ${cx} ${cy - rOuter} A ${rOuter} ${rOuter} 0 1 1 ${cx} ${cy + rOuter} A ${rOuter} ${rOuter} 0 1 1 ${cx} ${cy - rOuter} Z`;
                        } else {
                            pathData = `M ${cx} ${cy} L ${x1Outer} ${y1Outer} A ${rOuter} ${rOuter} 0 ${largeArc} 1 ${x2Outer} ${y2Outer} Z`;
                        }
                    }

                    const textR = isDonut ? (rOuter + rInner) / 2 : rOuter * 0.65;
                    const textX = Number((cx + textR * Math.cos(midRad)).toFixed(2));
                    const textY = Number((cy + textR * Math.sin(midRad)).toFixed(2));

                    slices.push({
                        path: pathData,
                        color: isZero ? (opt.color + '66') : opt.color,
                        text: opt.displayValue,
                        textX: textX,
                        textY: textY,
                        showText: !isZero && angleSize > 20 && val > 0
                    });
                });

                return slices;
            },

            getImageLayout() {
                return (this.currentSlide && this.currentSlide.settings && this.currentSlide.settings.image_layout) || 'center';
            },

            isSideLayout() {
                if (!this.currentSlide || !this.currentSlide.settings || !this.currentSlide.settings.content_image) return false;
                const layout = this.getImageLayout();
                return ['left', 'right', 'split_left', 'split_right', 'sidebar_left', 'sidebar_right'].includes(layout);
            },

            isLeftImage() {
                const layout = this.getImageLayout();
                return ['left', 'split_left', 'sidebar_left'].includes(layout);
            },

            getQuestionClasses(isSide = false) {
                const len = (this.currentSlide && this.currentSlide.question ? this.currentSlide.question.length : 0);
                if (isSide) {
                    if (len > 100) return 'text-xl sm:text-2xl lg:text-3xl font-black leading-snug break-words';
                    if (len > 60) return 'text-2xl sm:text-3xl lg:text-4xl font-black leading-snug break-words';
                    return 'text-2xl sm:text-4xl font-black leading-tight break-words';
                }
                if (len > 100) return 'text-2xl sm:text-3xl lg:text-4xl font-black leading-snug break-words';
                if (len > 60) return 'text-3xl sm:text-4xl lg:text-5xl font-black leading-snug break-words';
                return 'text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight break-words';
            },

            getDescriptionClasses() {
                const len = (this.currentSlide && this.currentSlide.description ? this.currentSlide.description.length : 0);
                if (len > 100) return 'text-sm sm:text-base break-words';
                return 'text-base sm:text-lg break-words';
            },

            getContentParagraphClasses() {
                const len = (this.currentSlide && this.currentSlide.description ? this.currentSlide.description.length : 0);
                if (len > 500) return 'text-sm sm:text-base break-words';
                if (len > 250) return 'text-base sm:text-lg break-words';
                return 'text-lg sm:text-xl font-medium break-words';
            },

            calculateWordSize(weight) {
                const base = 1.3;
                const extra = Math.min(3.5, (weight || 1) * 0.7);
                return (base + extra).toFixed(2);
            },

            getWordColor(index) {
                if (this.isLight()) {
                    const palette = ['#2563eb', '#0284c7', '#059669', '#ea580c', '#7c3aed', '#db2777', '#d97706'];
                    return palette[index % palette.length];
                }
                const palette = ['#818cf8', '#38bdf8', '#34d399', '#f472b6', '#fbbf24', '#a78bfa', '#fb7185'];
                return palette[index % palette.length];
            },

            getThemeBackground() {
                switch(this.state.theme) {
                    case 'light_tech':
                        return 'bg-gradient-to-br from-[#f0f6ff] via-[#e8f1fc] to-[#f8fbff] text-slate-900';
                    case 'royal_gold':
                        return 'bg-gradient-to-b from-[#0047ba] via-[#002f87] to-[#001744] text-white';
                    case 'azure_tech':
                        return 'bg-gradient-to-br from-[#005be2] via-[#0082e6] to-[#00a8e8] text-white';
                    case 'cobalt_orange':
                        return 'bg-gradient-to-br from-[#1e2cb8] via-[#2435c5] to-[#16208a] text-white';
                    case 'vibrant':
                        return 'bg-gradient-to-tr from-slate-950 via-purple-950 to-indigo-950';
                    case 'emerald':
                        return 'bg-gradient-to-tr from-slate-950 via-teal-950 to-emerald-950';
                    case 'sunset':
                        return 'bg-gradient-to-tr from-slate-950 via-rose-950 to-amber-950';
                    default:
                        return 'bg-gradient-to-tr from-slate-950 via-slate-900 to-indigo-950';
                }
            },

            getThemeStyle() {
                if (this.currentSlide.settings?.background_image) {
                    return 'background-image: linear-gradient(rgba(15,23,42,0.7), rgba(15,23,42,0.85)), url(' + this.currentSlide.settings.background_image + '); background-size: cover; background-position: center;';
                }
                if (this.state.theme === 'royal_gold') {
                    return 'background-image: linear-gradient(to right, rgba(255,255,255,0.06) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.06) 1px, transparent 1px); background-size: 40px 40px;';
                }
                if (this.state.theme === 'cobalt_orange') {
                    return 'background-image: linear-gradient(to right, rgba(255,255,255,0.08) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.08) 1px, transparent 1px); background-size: 32px 32px;';
                }
                return '';
            },

            numberFormat(num) {
                return (num || 0).toLocaleString();
            }
        }
    }
    </script>
</body>
</html>
