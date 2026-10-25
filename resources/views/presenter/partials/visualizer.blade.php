<!-- Dynamic Presentation Visualizer based on Type (Shared Presenter Partial) -->
<div class="w-full flex-1 flex flex-col items-center justify-center">
    <div class="w-full flex-1 flex items-center justify-center" x-show="state.show_results">
        
        <!-- 1. MULTIPLE CHOICE / QUIZ (Bars, Donut, Pie, Dots) -->
        <template x-if="currentSlide.type === 'multiple_choice' || currentSlide.type === 'quiz'">
            <div class="w-full">

                <!-- MODEL 1: BARS (Vertical Bar Columns - Mentimeter Style) -->
                <div x-show="getVisualizationType() === 'bars'" class="w-full flex flex-col items-center">
                    <!-- 1. Bar Columns Stage (Grounded bottom) -->
                    <div class="w-full max-w-4xl mx-auto flex items-end justify-center gap-4 sm:gap-8 h-44 sm:h-56 px-4">
                        <template x-for="(opt, idx) in getDisplayOptions(results.options)" :key="opt.id || idx">
                            <div class="flex-1 flex flex-col items-center justify-end h-full max-w-[160px] group">
                                <!-- Count or Percentage on top -->
                                <div class="mb-2 font-black font-mono text-lg sm:text-2xl transition-all"
                                     :class="isLight() ? 'text-slate-900 drop-shadow-none' : 'text-white drop-shadow-md'"
                                     x-text="opt.displayValue"></div>

                                <!-- Correct answer indicator for Quiz -->
                                <span x-show="showCorrectAnswer && opt.is_correct" 
                                      class="mb-1.5 px-2.5 py-0.5 rounded-lg bg-emerald-500 text-white text-[11px] font-black uppercase tracking-wider animate-bounce shadow-lg flex-shrink-0">
                                    ✓ Benar!
                                </span>

                                <!-- Vertical Bar Column -->
                                <div class="w-full rounded-t-2xl sm:rounded-t-3xl transition-all duration-700 ease-out shadow-2xl relative overflow-hidden flex-shrink-0"
                                     :style="'background-color: ' + (opt.color || '#3b82f6') + '; height: ' + opt.barHeight + '%;'">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/25 via-transparent to-white/20 pointer-events-none"></div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- 2. Baseline Divider Line (Mentimeter Chart Floor) -->
                    <div class="w-full max-w-4xl mx-auto h-[2px] rounded-full my-3 transition-colors"
                         :class="isLight() ? 'bg-slate-300/80' : 'bg-white/20'"></div>

                    <!-- 3. Labels Row (Completely independent space below baseline) -->
                    <div class="w-full max-w-4xl mx-auto flex items-start justify-center gap-4 sm:gap-8 px-4">
                        <template x-for="(opt, idx) in getDisplayOptions(results.options)" :key="opt.id || idx">
                            <div class="flex-1 max-w-[160px] text-center">
                                <div class="text-xs sm:text-sm font-bold leading-snug break-words transition-colors"
                                     :class="isLight() ? 'text-slate-800' : 'text-slate-100'"
                                     :title="opt.text"
                                     x-text="opt.text"></div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- MODEL 2: DONUT (Mentimeter Donut Ring Chart) -->
                <div x-show="getVisualizationType() === 'donut'" class="w-full">
                    <div class="flex flex-col md:flex-row items-center justify-center gap-8 lg:gap-12 py-4">
                        <!-- SVG Donut rendered via x-html for 100% SVG compliance -->
                        <div class="w-64 h-64 sm:w-80 sm:h-80 flex-shrink-0 flex items-center justify-center"
                             x-html="getChartSvg(true, results.options)">
                        </div>

                        <!-- Legend list on the right -->
                        <div class="space-y-3 max-w-sm w-full text-left">
                            <template x-for="(opt, idx) in getDisplayOptions(results.options)" :key="opt.id || idx">
                                <div class="backdrop-blur-md rounded-2xl p-3.5 border shadow-lg flex items-center justify-between gap-3 transition-all"
                                     :class="isLight() ? 'bg-white/95 border-blue-100' : 'bg-slate-900/80 border-white/10'">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="w-4 h-4 rounded-lg flex-shrink-0 shadow-sm" :style="'background-color: ' + (opt.color || '#3b82f6')"></span>
                                        <span class="text-base font-bold break-words leading-snug"
                                              :class="isLight() ? 'text-slate-900' : 'text-white'"
                                              x-text="opt.text"></span>
                                        <span x-show="showCorrectAnswer && opt.is_correct" 
                                              class="px-2 py-0.5 rounded-md bg-emerald-500 text-white text-[10px] font-black uppercase flex-shrink-0">
                                            ✓ Benar
                                        </span>
                                    </div>
                                    <span class="text-lg font-mono font-black flex-shrink-0"
                                          :class="isLight() ? 'text-blue-600' : 'text-amber-300'"
                                          x-text="opt.displayValue"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- MODEL 3: PIE (Mentimeter Pie Slices Chart) -->
                <div x-show="getVisualizationType() === 'pie'" class="w-full">
                    <div class="flex flex-col md:flex-row items-center justify-center gap-8 lg:gap-12 py-4">
                        <!-- SVG Pie rendered via x-html for 100% SVG compliance -->
                        <div class="w-64 h-64 sm:w-80 sm:h-80 flex-shrink-0 flex items-center justify-center"
                             x-html="getChartSvg(false, results.options)">
                        </div>

                        <!-- Legend list on the right -->
                        <div class="space-y-3 max-w-sm w-full text-left">
                            <template x-for="(opt, idx) in getDisplayOptions(results.options)" :key="opt.id || idx">
                                <div class="backdrop-blur-md rounded-2xl p-3.5 border shadow-lg flex items-center justify-between gap-3 transition-all"
                                     :class="isLight() ? 'bg-white/95 border-blue-100' : 'bg-slate-900/80 border-white/10'">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="w-4 h-4 rounded-lg flex-shrink-0 shadow-sm" :style="'background-color: ' + (opt.color || '#3b82f6')"></span>
                                        <span class="text-base font-bold break-words leading-snug"
                                              :class="isLight() ? 'text-slate-900' : 'text-white'"
                                              x-text="opt.text"></span>
                                        <span x-show="showCorrectAnswer && opt.is_correct" 
                                              class="px-2 py-0.5 rounded-md bg-emerald-500 text-white text-[10px] font-black uppercase flex-shrink-0">
                                            ✓ Benar
                                        </span>
                                    </div>
                                    <span class="text-lg font-mono font-black flex-shrink-0"
                                          :class="isLight() ? 'text-blue-600' : 'text-amber-300'"
                                          x-text="opt.displayValue"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- MODEL 4: DOTS (Mentimeter Response Dots Matrix) -->
                <div x-show="getVisualizationType() === 'dots'" class="w-full">
                    <div class="w-full max-w-4xl mx-auto grid grid-cols-2 sm:grid-cols-3 gap-4 py-4">
                        <template x-for="(opt, idx) in getDisplayOptions(results.options)" :key="opt.id || idx">
                            <div class="backdrop-blur-md p-4 sm:p-5 rounded-3xl border shadow-xl flex flex-col justify-between text-left gap-3"
                                 :class="isLight() ? 'bg-white/95 border-blue-100' : 'bg-slate-900/80 border-white/10'">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-3.5 h-3.5 rounded-full flex-shrink-0" :style="'background-color: ' + (opt.color || '#3b82f6')"></span>
                                        <span class="font-bold text-base break-words leading-snug"
                                              :class="isLight() ? 'text-slate-900' : 'text-white'"
                                              x-text="opt.text"></span>
                                    </div>
                                    <span class="font-mono font-black text-lg"
                                          :class="isLight() ? 'text-blue-600' : 'text-amber-400'"
                                          x-text="opt.displayValue"></span>
                                </div>

                                <!-- Dots Cluster -->
                                <div class="min-h-[70px] flex flex-wrap items-center justify-start gap-2 p-3 rounded-2xl bg-slate-950/20 border border-white/5">
                                    <template x-for="d in Math.max(0, opt.count)" :key="d">
                                        <span class="w-4 h-4 rounded-full shadow-md animate-in zoom-in duration-300"
                                              :style="'background-color: ' + (opt.color || '#3b82f6')"></span>
                                    </template>
                                    <span x-show="!opt.count || opt.count === 0" class="text-xs opacity-40 italic">0 respon</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

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
                     class="text-center py-12 font-semibold"
                     :class="isLight() ? 'text-slate-400' : 'text-slate-400'">
                    Menunggu kata pertama dari audiens...
                </div>
            </div>
        </template>

        <!-- 3. SCALES (Likert Scale Spider/Progress) -->
        <template x-if="currentSlide.type === 'scales'">
            <div class="w-full max-w-3xl space-y-4">
                <template x-for="(stmt, idx) in (results.statements || [])" :key="stmt.id || idx">
                    <div class="backdrop-blur-md rounded-2xl p-5 border shadow-xl flex flex-col gap-3"
                         :class="isLight() ? 'bg-white/95 border-blue-100 shadow-blue-500/5' : 'bg-slate-900/80 border-white/10'">
                        <div class="flex items-center justify-between text-left">
                            <span class="text-base sm:text-lg font-bold break-words leading-snug"
                                  :class="isLight() ? 'text-slate-900' : 'text-white'"
                                  x-text="stmt.text"></span>
                            <span class="text-xl sm:text-2xl font-black font-mono flex-shrink-0"
                                  :class="isLight() ? 'text-blue-600' : 'text-amber-400'"
                                  x-text="(stmt.average || 0).toFixed(1)"></span>
                        </div>

                        <!-- 1 - 5 Bar Scale Indicator -->
                        <div class="w-full bg-slate-800/20 rounded-full h-3 overflow-hidden border border-white/10">
                            <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-full rounded-full transition-all duration-700"
                                 :style="'width: ' + ((stmt.average || 0) / 5 * 100) + '%;'"></div>
                        </div>

                        <div class="flex items-center justify-between text-xs"
                             :class="isLight() ? 'text-slate-500' : 'text-slate-400'">
                            <span x-text="currentSlide.settings?.low_label || 'Sangat Tidak Setuju'"></span>
                            <span x-text="currentSlide.settings?.high_label || 'Sangat Setuju'"></span>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <!-- 4. RANKING -->
        <template x-if="currentSlide.type === 'ranking'">
            <div class="w-full max-w-3xl space-y-3">
                <template x-for="(item, idx) in (results.ranked_items || [])" :key="item.id || idx">
                    <div class="backdrop-blur-md rounded-2xl p-4 sm:p-5 border shadow-xl flex items-center justify-between"
                         :class="isLight() ? 'bg-white/95 border-blue-100 shadow-blue-500/5' : 'bg-slate-900/80 border-white/10'">
                        <div class="flex items-center gap-4 min-w-0">
                            <span class="w-8 h-8 rounded-xl bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 font-black flex items-center justify-center font-mono text-base flex-shrink-0"
                                  x-text="idx + 1"></span>
                            <span class="text-base sm:text-xl font-bold break-words leading-snug"
                                  :class="isLight() ? 'text-slate-900' : 'text-white'"
                                  x-text="item.text"></span>
                        </div>
                        <span class="text-base sm:text-lg font-bold font-mono flex-shrink-0"
                              :class="isLight() ? 'text-blue-600' : 'text-slate-300'"
                              x-text="(item.score || 0) + ' Poin'"></span>
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
                        <p class="text-sm font-medium mb-4 leading-relaxed break-words"
                           :class="isLight() ? 'text-slate-800' : 'text-white'"
                           x-text="entry.text"></p>
                        <div class="flex items-center justify-between text-xs pt-3 border-t"
                             :class="isLight() ? 'border-slate-100 text-slate-500' : 'border-white/10 text-slate-400'">
                            <span class="font-bold truncate"
                                  :class="isLight() ? 'text-blue-600' : 'text-indigo-300'"
                                  x-text="entry.name"></span>
                            <span class="flex-shrink-0" x-text="entry.created_at"></span>
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
                        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                            <span class="text-2xl flex-shrink-0" x-text="idx === 0 ? '🥇' : (idx === 1 ? '🥈' : (idx === 2 ? '🥉' : '#' + (idx + 1)))"></span>
                            <span class="text-2xl flex-shrink-0" x-text="player.avatar || '🎯'"></span>
                            <span class="text-base sm:text-xl font-bold truncate"
                                  :class="isLight() ? 'text-slate-900' : 'text-white'"
                                  x-text="player.nickname"></span>
                        </div>
                        <span class="text-xl sm:text-2xl font-black font-mono flex-shrink-0"
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
                        <div class="min-w-0 flex-1">
                            <p class="text-base font-bold mb-1 break-words leading-snug"
                               :class="isLight() ? 'text-slate-900' : 'text-white'"
                               x-text="q.question_text"></p>
                            <span class="text-xs truncate block"
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
         class="py-16 font-bold text-lg text-center"
         :class="isLight() ? 'text-slate-500' : 'text-slate-400'">
        🔒 Hasil disembunyikan oleh Presenter. Tekan 'R' untuk menampilkan hasil.
    </div>
</div>
