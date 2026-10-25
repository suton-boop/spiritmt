<!-- Type Specific Preview Widget (Shared Partial) -->
<div>
    <!-- Multiple Choice & Quiz Preview (Bars, Donut, Pie, Dots - Mentimeter Style) -->
    <template x-if="currentSlide.type === 'multiple_choice' || currentSlide.type === 'quiz'">
        <div class="w-full">
            
            <!-- MODEL 1: BARS (Vertical Bar Columns - Mentimeter Style) -->
            <div x-show="getVisualizationType() === 'bars'" class="w-full flex flex-col items-center">
                <!-- 1. Bar Columns Stage -->
                <div class="w-full flex items-end justify-center gap-3 sm:gap-6 h-36 sm:h-44 px-2">
                    <template x-for="(opt, idx) in getDisplayOptions()" :key="opt.id || idx">
                        <div class="flex-1 flex flex-col items-center justify-end h-full max-w-[120px]">
                            <!-- Count or Percentage on top -->
                            <div class="mb-1.5 font-black font-mono text-xs sm:text-sm transition-colors"
                                 :class="presentation.theme === 'light_tech' ? 'text-slate-900' : 'text-white'"
                                 x-text="opt.displayValue"></div>

                            <!-- Vertical Bar -->
                            <div class="w-full rounded-t-xl sm:rounded-t-2xl transition-all duration-700 ease-out shadow-md relative overflow-hidden flex-shrink-0"
                                 :style="'background-color: ' + (opt.color || '#3b82f6') + '; height: ' + opt.barHeight + '%;'">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-white/10 pointer-events-none"></div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- 2. Baseline Divider Line -->
                <div class="w-full h-[1.5px] rounded-full my-2 transition-colors"
                     :class="presentation.theme === 'light_tech' ? 'bg-slate-300' : 'bg-white/20'"></div>

                <!-- 3. Labels Row below baseline -->
                <div class="w-full flex items-start justify-center gap-3 sm:gap-6 px-2">
                    <template x-for="(opt, idx) in getDisplayOptions()" :key="opt.id || idx">
                        <div class="flex-1 max-w-[120px] text-center">
                            <div class="text-[11px] sm:text-xs font-bold leading-tight break-words transition-colors"
                                 :class="presentation.theme === 'light_tech' ? 'text-slate-800' : 'text-slate-200'"
                                 :title="opt.text"
                                 x-text="opt.text"></div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- MODEL 2: DONUT (Mentimeter Donut Ring Chart) -->
            <div x-show="getVisualizationType() === 'donut'" class="w-full">
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-8 py-2">
                    <!-- SVG Donut rendered via x-html for 100% SVG compliance -->
                    <div class="w-48 h-48 sm:w-56 sm:h-56 flex-shrink-0 flex items-center justify-center"
                         x-html="getChartSvg(true)">
                    </div>

                    <!-- Legend list on the right -->
                    <div class="space-y-2 max-w-xs w-full text-left">
                        <template x-for="(opt, idx) in getDisplayOptions()" :key="opt.id || idx">
                            <div class="flex items-center justify-between gap-3 text-xs p-1.5 rounded-xl border border-white/5 bg-white/5"
                                 :class="presentation.theme === 'light_tech' ? 'bg-white/80 border-slate-200 text-slate-800' : 'text-slate-200'">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-3 h-3 rounded-md flex-shrink-0 shadow-sm" :style="'background-color: ' + (opt.color || '#3b82f6')"></span>
                                    <span class="font-bold break-words leading-tight" x-text="opt.text"></span>
                                </div>
                                <span class="font-mono font-black flex-shrink-0"
                                      :class="presentation.theme === 'light_tech' ? 'text-blue-600' : 'text-amber-300'"
                                      x-text="opt.displayValue"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- MODEL 3: PIE (Mentimeter Pie Slices Chart) -->
            <div x-show="getVisualizationType() === 'pie'" class="w-full">
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-8 py-2">
                    <!-- SVG Pie rendered via x-html for 100% SVG compliance -->
                    <div class="w-48 h-48 sm:w-56 sm:h-56 flex-shrink-0 flex items-center justify-center"
                         x-html="getChartSvg(false)">
                    </div>

                    <!-- Legend list on the right -->
                    <div class="space-y-2 max-w-xs w-full text-left">
                        <template x-for="(opt, idx) in getDisplayOptions()" :key="opt.id || idx">
                            <div class="flex items-center justify-between gap-3 text-xs p-1.5 rounded-xl border border-white/5 bg-white/5"
                                 :class="presentation.theme === 'light_tech' ? 'bg-white/80 border-slate-200 text-slate-800' : 'text-slate-200'">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-3 h-3 rounded-md flex-shrink-0 shadow-sm" :style="'background-color: ' + (opt.color || '#3b82f6')"></span>
                                    <span class="font-bold break-words leading-tight" x-text="opt.text"></span>
                                </div>
                                <span class="font-mono font-black flex-shrink-0"
                                      :class="presentation.theme === 'light_tech' ? 'text-blue-600' : 'text-amber-300'"
                                      x-text="opt.displayValue"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- MODEL 4: DOTS (Mentimeter Response Dots Matrix) -->
            <div x-show="getVisualizationType() === 'dots'" class="w-full">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 py-2">
                    <template x-for="(opt, idx) in getDisplayOptions()" :key="opt.id || idx">
                        <div class="p-3 rounded-2xl border backdrop-blur-sm flex flex-col justify-between text-left gap-2"
                             :class="presentation.theme === 'light_tech' ? 'bg-white/95 border-blue-100 shadow-sm' : 'bg-slate-900/60 border-white/10'">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold break-words leading-tight"
                                      :class="presentation.theme === 'light_tech' ? 'text-slate-800' : 'text-white'"
                                      x-text="opt.text"></span>
                                <span class="font-mono font-black"
                                      :class="presentation.theme === 'light_tech' ? 'text-blue-600' : 'text-amber-400'"
                                      x-text="opt.displayValue"></span>
                            </div>

                            <!-- Dots Cluster -->
                            <div class="min-h-[50px] flex flex-wrap items-center justify-start gap-1.5 p-2 rounded-xl bg-slate-950/20 border border-white/5">
                                <template x-for="d in Math.max(1, opt.dotCount)" :key="d">
                                    <span class="w-3 h-3 rounded-full shadow-sm animate-in zoom-in duration-300"
                                          :style="'background-color: ' + (opt.color || '#3b82f6')"></span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Discreet example badge if no real votes yet -->
            <div x-show="!hasRealVotes()" class="text-[10px] text-center opacity-60 font-medium italic mt-2">
                Showing preview examples
            </div>

        </div>
    </template>

    <!-- Word Cloud Preview -->
    <template x-if="currentSlide.type === 'word_cloud'">
        <div class="h-40 sm:h-44 flex flex-wrap items-center justify-center gap-4 p-4 rounded-2xl bg-white/5 border border-white/10">
            <span class="text-2xl font-black text-indigo-400">Inspiratif</span>
            <span class="text-xl font-bold text-teal-300">Semangat</span>
            <span class="text-3xl font-extrabold text-amber-300">Kolaborasi</span>
            <span class="text-lg font-semibold text-rose-300">Inovasi</span>
            <span class="text-base text-purple-300">Kreatif</span>
        </div>
    </template>

    <!-- Scales Preview -->
    <template x-if="currentSlide.type === 'scales'">
        <div class="space-y-3 w-full">
            <template x-for="(stmt, stmtIdx) in (currentSlide.options || [])" :key="stmt.id || stmtIdx">
                <div class="bg-white/10 border border-white/15 rounded-2xl p-3 flex flex-col gap-2">
                    <div class="text-xs sm:text-sm font-bold text-left text-white break-words" x-text="stmt.text"></div>
                    <div class="flex items-center justify-between text-[11px] opacity-70">
                        <span x-text="currentSlide.settings?.low_label || 'Sangat Tidak Setuju'"></span>
                        <div class="flex gap-2">
                            <span class="w-4 h-4 rounded-full bg-white/20"></span>
                            <span class="w-4 h-4 rounded-full bg-white/20"></span>
                            <span class="w-4 h-4 rounded-full bg-white/20"></span>
                            <span class="w-4 h-4 rounded-full bg-white/20"></span>
                            <span class="w-4 h-4 rounded-full bg-white/20"></span>
                        </div>
                        <span x-text="currentSlide.settings?.high_label || 'Sangat Setuju'"></span>
                    </div>
                </div>
            </template>
        </div>
    </template>

    <!-- Open Ended Preview -->
    <template x-if="currentSlide.type === 'open_ended'">
        <div class="grid grid-cols-2 gap-3 text-left">
            <div class="bg-white/10 p-3 rounded-2xl border border-white/15 text-xs text-white">
                "Pendapat peserta akan muncul dalam kartu bubble seperti ini secara langsung!"
            </div>
            <div class="bg-white/10 p-3 rounded-2xl border border-white/15 text-xs text-white">
                "Setiap ide baru yang dikirim audiens langsung tampil tanpa refresh."
            </div>
        </div>
    </template>

    <!-- Leaderboard Preview -->
    <template x-if="currentSlide.type === 'leaderboard'">
        <div class="space-y-2 w-full max-w-md mx-auto text-left">
            <div class="bg-amber-500/20 border border-amber-500/30 p-2.5 rounded-xl flex items-center justify-between text-amber-200 text-xs font-bold">
                <span>🥇 1. Peserta Tercepat</span>
                <span>1,980 Poin</span>
            </div>
            <div class="bg-slate-700/50 p-2.5 rounded-xl flex items-center justify-between text-slate-200 text-xs font-bold">
                <span>🥈 2. Juara Kedua</span>
                <span>1,850 Poin</span>
            </div>
        </div>
    </template>

    <!-- QA Preview -->
    <template x-if="currentSlide.type === 'qa'">
        <div class="bg-white/10 p-4 rounded-2xl border border-white/15 text-xs text-slate-200 text-left">
            🙋 Audiens dapat mengajukan pertanyaan dari ponsel mereka dan melakukan upvote secara bersama-sama.
        </div>
    </template>
</div>
