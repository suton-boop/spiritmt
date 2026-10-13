<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $presentation->title }} - Presenter View</title>

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
         :class="getThemeBackground()">

        <!-- Decorative background elements -->
        <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full blur-3xl pointer-events-none transition-all duration-500"
             :class="isLight() ? 'bg-blue-400/20' : 'bg-indigo-500/10'"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full blur-3xl pointer-events-none transition-all duration-500"
             :class="isLight() ? 'bg-sky-400/20' : 'bg-teal-500/10'"></div>

        <!-- TOP BAR: Join Instructions -->
        <header class="relative z-20 flex items-center justify-between pb-4 border-b transition-colors"
                :class="isLight() ? 'border-blue-200/60' : 'border-white/10'">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" 
                   class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-lg transition-all"
                   :class="isLight() ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-500/20' : 'bg-white/10 hover:bg-white/20 text-white'" 
                   title="Kembali">
                    S
                </a>
                <div>
                    <div class="text-xs sm:text-sm font-semibold transition-colors"
                         :class="isLight() ? 'text-slate-600' : 'text-slate-300'">
                        Bergabung di <span class="font-extrabold underline underline-offset-4"
                                          :class="isLight() ? 'text-blue-600 decoration-blue-500' : 'text-white decoration-indigo-400'"
                                          x-text="joinDomain">spiritmeter.test/join</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
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
            </div>
        </header>

        <!-- MAIN PRESENTATION CANVAS -->
        <main class="flex-1 flex flex-col items-center justify-center py-6 px-4 text-center relative z-10 w-full max-w-6xl mx-auto overflow-y-auto">
            
            <!-- Slide Question Title -->
            <div class="mb-6 max-w-4xl">
                <h1 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight transition-colors"
                    :class="isLight() ? 'text-slate-900 drop-shadow-none' : 'text-white drop-shadow-md'"
                    x-text="currentSlide.question || 'SpiritMeter'"></h1>
                
                <p class="mt-3 text-base sm:text-lg font-medium max-w-2xl mx-auto transition-colors"
                   :class="isLight() ? 'text-slate-600' : 'text-slate-300'"
                   x-show="currentSlide.description" x-text="currentSlide.description"></p>
            </div>

            <!-- Dynamic Presentation Visualizer based on Type -->
            <div class="w-full flex-1 flex items-center justify-center" x-show="state.show_results">
                
                <!-- 1. MULTIPLE CHOICE / QUIZ (Bar & Donut Charts) -->
                <template x-if="currentSlide.type === 'multiple_choice' || currentSlide.type === 'quiz'">
                    <div class="w-full max-w-3xl space-y-4">
                        <template x-for="(opt, idx) in (results.options || [])" :key="opt.id || idx">
                            <div class="backdrop-blur-md rounded-2xl p-4 sm:p-5 border shadow-xl transition-all relative overflow-hidden flex flex-col gap-2"
                                 :class="isLight() ? 'bg-white/95 border-blue-100 shadow-blue-500/5 hover:border-blue-300' : 'bg-slate-900/80 border-white/10'">
                                <!-- Progress fill bar in background -->
                                <div class="absolute inset-y-0 left-0 rounded-2xl transition-all duration-700 ease-out"
                                     :class="isLight() ? 'opacity-20' : 'opacity-25'"
                                     :style="'background-color: ' + (opt.color || '#2563eb') + '; width: ' + (opt.percentage || 0) + '%;'"></div>

                                <div class="relative z-10 flex items-center justify-between text-left">
                                    <div class="flex items-center gap-3">
                                        <span class="w-4 h-4 rounded-full flex-shrink-0 shadow-sm" :style="'background-color: ' + (opt.color || '#2563eb')"></span>
                                        <span class="text-base sm:text-xl font-bold"
                                              :class="isLight() ? 'text-slate-900' : 'text-white'"
                                              x-text="opt.text"></span>
                                        
                                        <span x-show="showCorrectAnswer && opt.is_correct" 
                                              class="px-2.5 py-1 rounded-lg bg-emerald-500 text-white text-xs font-black uppercase tracking-wider animate-bounce shadow-sm">
                                            ✓ Benar!
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xl sm:text-2xl font-black font-mono"
                                              :class="isLight() ? 'text-blue-600' : 'text-white'"
                                              x-text="(opt.percentage || 0) + '%'"></span>
                                        <span class="text-xs block font-mono"
                                              :class="isLight() ? 'text-slate-500' : 'text-slate-400'"
                                              x-text="'(' + (opt.count || 0) + ' suara)'"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- 2. WORD CLOUD (Dynamic Sized Keywords) -->
                <template x-if="currentSlide.type === 'word_cloud'">
                    <div class="w-full max-w-4xl p-8 rounded-3xl backdrop-blur-md border flex flex-wrap items-center justify-center gap-6 sm:gap-8 min-h-[300px]"
                         :class="isLight() ? 'bg-white/90 border-blue-100 shadow-xl shadow-blue-500/5' : 'bg-slate-900/60 border-white/10'">
                        <template x-for="(w, idx) in (results.words || [])" :key="idx">
                            <span :style="'font-size: ' + calculateWordSize(w.weight) + 'rem; color: ' + getWordColor(idx) + ';'"
                                  class="font-black tracking-tight transition-all duration-500 hover:scale-110 cursor-pointer drop-shadow-sm capitalize"
                                  x-text="w.text"></span>
                        </template>

                        <div x-show="!results.words || results.words.length === 0" 
                             class="text-base font-semibold"
                             :class="isLight() ? 'text-slate-400' : 'text-slate-400'">
                            Menunggu kata-kata pertama dari audiens...
                        </div>
                    </div>
                </template>

                <!-- 3. SCALES (Likert rating averages) -->
                <template x-if="currentSlide.type === 'scales'">
                    <div class="w-full max-w-3xl space-y-5">
                        <template x-for="(stmt, idx) in (results.statements || [])" :key="idx">
                            <div class="backdrop-blur-md rounded-2xl p-5 border shadow-xl text-left"
                                 :class="isLight() ? 'bg-white/95 border-blue-100 shadow-blue-500/5 text-slate-800' : 'bg-slate-900/80 border-white/10 text-white'">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-base sm:text-lg font-bold"
                                        :class="isLight() ? 'text-slate-900' : 'text-white'"
                                        x-text="stmt.text"></h3>
                                    <span class="text-2xl font-black font-mono"
                                          :class="isLight() ? 'text-blue-600' : 'text-amber-300'"
                                          x-text="(stmt.average || 0) + ' / 5.0'"></span>
                                </div>

                                <div class="w-full h-4 rounded-full overflow-hidden p-0.5"
                                     :class="isLight() ? 'bg-blue-100' : 'bg-slate-800'">
                                    <div class="bg-gradient-to-r from-blue-600 to-cyan-500 h-full rounded-full transition-all duration-700"
                                         :style="'width: ' + ((stmt.average / 5) * 100) + '%;'"></div>
                                </div>

                                <div class="flex justify-between text-xs mt-2"
                                     :class="isLight() ? 'text-slate-500' : 'text-slate-400'">
                                    <span x-text="currentSlide.settings?.low_label || 'Sangat Rendah'"></span>
                                    <span x-text="(stmt.respondents || 0) + ' responden'"></span>
                                    <span x-text="currentSlide.settings?.high_label || 'Sangat Tinggi'"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- 4. RANKING -->
                <template x-if="currentSlide.type === 'ranking'">
                    <div class="w-full max-w-2xl space-y-3">
                        <template x-for="(item, idx) in (results.items || [])" :key="idx">
                            <div class="backdrop-blur-md rounded-2xl p-4 border shadow-xl flex items-center justify-between transition-all duration-500"
                                 :class="isLight() ? 'bg-white/95 border-blue-100 shadow-blue-500/5' : 'bg-slate-900/80 border-white/10'">
                                <div class="flex items-center gap-4">
                                    <span class="w-8 h-8 rounded-xl bg-blue-600 font-mono font-black text-white flex items-center justify-center text-sm shadow-sm"
                                          x-text="'#' + (idx + 1)"></span>
                                    <span class="text-base sm:text-lg font-bold"
                                          :class="isLight() ? 'text-slate-900' : 'text-white'"
                                          x-text="item.text"></span>
                                </div>
                                <span class="text-sm font-mono font-bold"
                                      :class="isLight() ? 'text-slate-600' : 'text-slate-300'"
                                      x-text="item.score + ' Poin'"></span>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- 5. OPEN ENDED (Ideas & Thoughts Grid) -->
                <template x-if="currentSlide.type === 'open_ended'">
                    <div class="w-full max-w-4xl grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-left">
                        <template x-for="(entry, idx) in (results.entries || [])" :key="idx">
                            <div class="backdrop-blur-md border p-5 rounded-3xl shadow-xl flex flex-col justify-between"
                                 :class="isLight() ? 'bg-white/95 border-blue-100 shadow-blue-500/5' : 'bg-white/10 border-white/15'">
                                <p class="text-sm font-medium mb-4 leading-relaxed"
                                   :class="isLight() ? 'text-slate-800' : 'text-white'"
                                   x-text="entry.text"></p>
                                <div class="flex items-center justify-between text-xs pt-3 border-t"
                                     :class="isLight() ? 'border-slate-100 text-slate-500' : 'border-white/10 text-slate-400'">
                                    <span class="font-bold"
                                          :class="isLight() ? 'text-blue-600' : 'text-indigo-300'"
                                          x-text="entry.name"></span>
                                    <span x-text="entry.created_at"></span>
                                </div>
                            </div>
                        </template>

                        <div x-show="!results.entries || results.entries.length === 0" 
                             class="col-span-full py-12 text-center font-semibold"
                             :class="isLight() ? 'text-slate-400' : 'text-slate-400'">
                            Belum ada jawaban yang dikirim audiens...
                        </div>
                    </div>
                </template>

                <!-- 6. LEADERBOARD (Top Quiz Winners with Medals & Confetti) -->
                <template x-if="currentSlide.type === 'leaderboard'">
                    <div class="w-full max-w-2xl space-y-3">
                        <template x-for="(player, idx) in (results.leaderboard || [])" :key="idx">
                            <div :class="idx === 0 
                                    ? (isLight() ? 'bg-gradient-to-r from-amber-50 to-amber-100/60 border-amber-300 shadow-xl scale-105' : 'bg-gradient-to-r from-amber-500/30 to-amber-600/20 border-amber-400/50 scale-105') 
                                    : (isLight() ? 'bg-white/95 border-blue-100 shadow-md' : 'bg-slate-900/80 border-white/10')"
                                 class="backdrop-blur-md rounded-2xl p-4 border flex items-center justify-between transition-all duration-500">
                                <div class="flex items-center gap-3 sm:gap-4">
                                    <span class="text-2xl" x-text="idx === 0 ? '🥇' : (idx === 1 ? '🥈' : (idx === 2 ? '🥉' : '#' + (idx + 1)))"></span>
                                    <span class="text-2xl" x-text="player.avatar || '🎯'"></span>
                                    <span class="text-base sm:text-xl font-bold"
                                          :class="isLight() ? 'text-slate-900' : 'text-white'"
                                          x-text="player.nickname"></span>
                                </div>
                                <span class="text-xl sm:text-2xl font-black font-mono"
                                      :class="isLight() ? 'text-blue-600' : 'text-amber-300'"
                                      x-text="numberFormat(player.score) + ' Poin'"></span>
                            </div>
                        </template>

                        <div x-show="!results.leaderboard || results.leaderboard.length === 0" 
                             class="py-12 font-semibold"
                             :class="isLight() ? 'text-slate-400' : 'text-slate-400'">
                            Belum ada skor kuis tercatat.
                        </div>
                    </div>
                </template>

                <!-- 7. AUDIENCE Q&A -->
                <template x-if="currentSlide.type === 'qa'">
                    <div class="w-full max-w-3xl space-y-3 text-left">
                        <template x-for="(q, idx) in (state.questions || [])" :key="q.id">
                            <div class="backdrop-blur-md rounded-2xl p-4 border shadow-xl flex items-center justify-between gap-4"
                                 :class="isLight() ? 'bg-white/95 border-blue-100 shadow-blue-500/5' : 'bg-slate-900/80 border-white/10'">
                                <div>
                                    <p class="text-base font-bold mb-1"
                                       :class="isLight() ? 'text-slate-900' : 'text-white'"
                                       x-text="q.question_text"></p>
                                    <span class="text-xs"
                                          :class="isLight() ? 'text-slate-500' : 'text-slate-400'"
                                          x-text="'Oleh: ' + (q.participant_name || 'Anonim')"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1.5 rounded-xl text-sm font-black font-mono"
                                          :class="isLight() ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'bg-indigo-600/40 text-indigo-200'">
                                        ▲ <span x-text="q.upvotes"></span>
                                    </span>
                                </div>
                            </div>
                        </template>

                        <div x-show="!state.questions || state.questions.length === 0" 
                             class="text-center py-12 font-semibold"
                             :class="isLight() ? 'text-slate-400' : 'text-slate-400'">
                            Belum ada pertanyaan dari audiens.
                        </div>
                    </div>
                </template>

            </div>

            <!-- Hide Results Placeholder -->
            <div x-show="!state.show_results" 
                 class="py-16 font-bold text-lg"
                 :class="isLight() ? 'text-slate-500' : 'text-slate-400'">
                🔒 Hasil disembunyikan oleh Presenter. Tekan 'R' untuk menampilkan hasil.
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
                Atau buka <strong class="text-white">spiritmeter.test/join</strong> &bull; Masukkan PIN: <span class="font-mono text-amber-300 font-bold text-base" x-text="state.formatted_code"></span>
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

            numberFormat(num) {
                return (num || 0).toLocaleString();
            }
        }
    }
    </script>
</body>
</html>
