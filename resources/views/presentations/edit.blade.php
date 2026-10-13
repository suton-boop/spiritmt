@extends('layouts.app')

@section('content')
<div class="h-screen flex flex-col bg-slate-900 overflow-hidden" 
     x-data="presentationStudio({{ $presentation->toJson() }}, {{ $presentation->slides->toJson() }}, '{{ csrf_token() }}')">
    
    <!-- Top Header Bar -->
    <header class="h-16 bg-slate-950 border-b border-slate-800 px-4 sm:px-6 flex items-center justify-between text-white flex-shrink-0 z-30">
        <!-- Left: Back & Title -->
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-all" title="Kembali ke Dashboard">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>

            <div class="flex items-center gap-3">
                <input type="text" x-model="presentation.title" @change="savePresentationMeta()"
                       class="bg-transparent hover:bg-slate-800/80 focus:bg-slate-800 text-white font-bold text-base sm:text-lg px-2.5 py-1 rounded-lg border border-transparent focus:border-indigo-500 focus:outline-none transition-all w-48 sm:w-80 truncate">
                <span class="text-xs text-slate-400 font-mono hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-800/70 border border-slate-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    PIN: <span x-text="formattedCode"></span>
                </span>
            </div>
        </div>

        <!-- Right: Actions (Theme, Share, Present) -->
        <div class="flex items-center gap-2 sm:gap-3">
            <span class="text-xs text-emerald-400 font-medium hidden md:inline flex items-center gap-1" x-show="autoSaved">
                ✓ Tersimpan
            </span>

            <!-- Share Modal Button -->
            <button @click="shareModal = true; generateQrCode()" 
                    class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs sm:text-sm font-bold rounded-xl border border-slate-700 flex items-center gap-2 transition-all">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                <span>Bagikan</span>
            </button>

            <!-- Live Present Button -->
            <a :href="'/presentations/' + presentation.id + '/present'" target="_blank"
               class="px-4 sm:px-5 py-2 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white text-xs sm:text-sm font-bold rounded-xl shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                <span>Present</span>
            </a>
        </div>
    </header>

    <!-- Studio Main Workspace -->
    <div class="flex-1 flex overflow-hidden">
        
        <!-- Left Panel: Slide Navigator / Filmstrip -->
        <div class="w-56 sm:w-64 bg-slate-950 border-r border-slate-800 flex flex-col flex-shrink-0">
            <div class="p-3 border-b border-slate-800">
                <button @click="newSlideModal = true" 
                        class="w-full py-2.5 px-3 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Slide</span>
                </button>
            </div>

            <!-- Slides List -->
            <div class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar" id="slides-sortable">
                <template x-for="(slide, index) in slides" :key="slide.id">
                    <div @click="selectSlide(slide.id)"
                         :class="{ 'ring-2 ring-indigo-500 bg-slate-800/90': currentSlideId === slide.id, 'bg-slate-900 hover:bg-slate-800/50': currentSlideId !== slide.id }"
                         class="group relative rounded-2xl p-2.5 border border-slate-800 cursor-pointer transition-all flex flex-col gap-2">
                        
                        <!-- Slide Top Meta -->
                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span class="font-bold flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded bg-slate-800 flex items-center justify-center text-[10px]" x-text="index + 1"></span>
                                <span x-text="getTypeBadge(slide.type)"></span>
                            </span>

                            <!-- Slide Dropdown -->
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click.stop="duplicateSlide(slide.id)" title="Duplikasi Slide" class="p-1 hover:text-white">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                                <button @click.stop="deleteSlide(slide.id)" title="Hapus Slide" class="p-1 hover:text-rose-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Slide Mini Preview Canvas -->
                        <div class="h-20 bg-slate-950 rounded-xl p-2 border border-slate-800/80 flex flex-col justify-between overflow-hidden">
                            <div class="text-[11px] font-bold text-slate-200 line-clamp-2 leading-tight" x-text="slide.question || 'Slide Tanpa Judul'"></div>
                            
                            <div class="flex items-center gap-1 text-[9px] text-slate-500">
                                <span x-text="slide.type"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Center: Interactive 16:9 Presentation Canvas Preview -->
        <div class="flex-1 bg-slate-900 p-4 sm:p-8 flex flex-col items-center justify-center overflow-auto relative">
            
            <!-- Canvas Frame (16:9) -->
            <div class="w-full max-w-4xl aspect-video rounded-3xl p-6 sm:p-10 flex flex-col justify-between shadow-2xl border relative overflow-hidden transition-all duration-300"
                 :class="getThemeClasses(presentation.theme)">
                
                <!-- Slide Header (Join Instruction) -->
                <div class="flex items-center justify-between pb-4 border-b"
                     :class="presentation.theme === 'light_tech' ? 'border-blue-200/80' : 'border-white/10'">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white font-black text-xs shadow-sm"
                             :class="presentation.theme === 'light_tech' ? 'bg-blue-600 shadow-blue-500/20' : 'bg-indigo-500'">
                            S
                        </div>
                        <div class="text-xs sm:text-sm font-medium tracking-wide"
                             :class="presentation.theme === 'light_tech' ? 'text-slate-600' : 'text-slate-200 opacity-80'">
                            Masuk ke <strong class="font-bold underline" :class="presentation.theme === 'light_tech' ? 'text-blue-600 decoration-blue-500' : ''">spiritmeter.test/join</strong>
                        </div>
                    </div>
                    <div class="px-3 py-1 rounded-xl backdrop-blur-md text-xs sm:text-sm font-mono font-black border"
                         :class="presentation.theme === 'light_tech' ? 'bg-white/95 border-blue-200 text-blue-600 shadow-sm' : 'bg-white/10 border-white/15 text-amber-300'">
                        KODE: <span x-text="formattedCode"></span>
                    </div>
                </div>

                <!-- Slide Content Area (Dynamic based on current slide type) -->
                <div class="flex-1 flex flex-col items-center justify-center py-6 text-center w-full">
                    
                    <!-- Question / Title -->
                    <h2 class="text-xl sm:text-3xl font-extrabold tracking-tight mb-3 max-w-2xl"
                        :class="presentation.theme === 'light_tech' ? 'text-slate-900' : 'text-white'"
                        x-text="currentSlide.question || 'Tulis pertanyaan Anda di panel samping'"></h2>

                    <p class="text-xs sm:text-sm mb-6 max-w-xl"
                       :class="presentation.theme === 'light_tech' ? 'text-slate-600' : 'text-slate-200 opacity-70'"
                       x-show="currentSlide.description" x-text="currentSlide.description"></p>

                    <!-- Type Specific Preview Widget -->
                    <div class="w-full max-w-xl mx-auto">
                        
                        <!-- Multiple Choice Preview -->
                        <template x-if="currentSlide.type === 'multiple_choice' || currentSlide.type === 'quiz'">
                            <div class="space-y-3 w-full">
                                <template x-for="(opt, optIdx) in (currentSlide.options || [])" :key="opt.id || optIdx">
                                    <div class="rounded-2xl p-3 sm:p-3.5 flex items-center justify-between backdrop-blur-sm text-left border"
                                         :class="presentation.theme === 'light_tech' ? 'bg-white/95 border-blue-100 shadow-sm text-slate-900' : 'bg-white/10 border-white/15 text-white'">
                                        <div class="flex items-center gap-3">
                                            <span class="w-3.5 h-3.5 rounded-full shadow-sm" :style="'background-color:' + (opt.color || '#2563eb')"></span>
                                            <span class="text-xs sm:text-sm font-bold"
                                                  :class="presentation.theme === 'light_tech' ? 'text-slate-900' : 'text-white'"
                                                  x-text="opt.text || 'Opsi ' + (optIdx + 1)"></span>
                                            <span x-show="opt.is_correct" class="px-2 py-0.5 rounded-md bg-emerald-500/30 text-emerald-600 text-[10px] font-bold">✓ Benar</span>
                                        </div>
                                        <span class="text-xs font-mono"
                                              :class="presentation.theme === 'light_tech' ? 'text-blue-600 font-bold' : 'opacity-60'">0%</span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <!-- Word Cloud Preview -->
                        <template x-if="currentSlide.type === 'word_cloud'">
                            <div class="h-44 flex flex-wrap items-center justify-center gap-4 p-4 rounded-2xl bg-white/5 border border-white/10">
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
                                        <div class="text-xs sm:text-sm font-bold text-left text-white" x-text="stmt.text"></div>
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
                </div>

                <!-- Slide Footer (Status) -->
                <div class="flex items-center justify-between pt-3 border-t border-white/10 text-xs opacity-70">
                    <span class="flex items-center gap-1.5 font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Menunggu Respon Peserta
                    </span>
                    <span class="font-bold">SpiritMeter</span>
                </div>
            </div>

            <!-- Canvas Bottom Controls -->
            <div class="mt-4 flex items-center gap-3">
                <button @click="previousSlide()" :disabled="currentSlideIndex <= 0"
                        class="p-2 bg-slate-800 hover:bg-slate-700 disabled:opacity-30 text-white rounded-xl transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <span class="text-xs font-bold text-slate-400">
                    Slide <span x-text="currentSlideIndex + 1"></span> dari <span x-text="slides.length"></span>
                </span>
                <button @click="nextSlide()" :disabled="currentSlideIndex >= slides.length - 1"
                        class="p-2 bg-slate-800 hover:bg-slate-700 disabled:opacity-30 text-white rounded-xl transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- Right Panel: Slide Configuration (Tabs: Type, Content, Design) -->
        <div class="w-80 sm:w-96 bg-slate-950 border-l border-slate-800 flex flex-col flex-shrink-0">
            <!-- Tabs Header -->
            <div class="flex border-b border-slate-800 text-xs font-bold">
                <button @click="activeTab = 'content'" 
                        :class="{ 'text-indigo-400 border-b-2 border-indigo-500 bg-slate-900': activeTab === 'content', 'text-slate-400 hover:text-white': activeTab !== 'content' }"
                        class="flex-1 py-3 text-center transition-all">
                    Konten
                </button>
                <button @click="activeTab = 'type'" 
                        :class="{ 'text-indigo-400 border-b-2 border-indigo-500 bg-slate-900': activeTab === 'type', 'text-slate-400 hover:text-white': activeTab !== 'type' }"
                        class="flex-1 py-3 text-center transition-all">
                    Tipe Slide
                </button>
                <button @click="activeTab = 'design'" 
                        :class="{ 'text-indigo-400 border-b-2 border-indigo-500 bg-slate-900': activeTab === 'design', 'text-slate-400 hover:text-white': activeTab !== 'design' }"
                        class="flex-1 py-3 text-center transition-all">
                    Desain & Tema
                </button>
            </div>

            <!-- Tab Content -->
            <div class="flex-1 overflow-y-auto p-5 space-y-6 text-slate-200 custom-scrollbar">
                
                <!-- TAB 1: KONTEN -->
                <div x-show="activeTab === 'content'" class="space-y-5">
                    
                    <!-- Question Input -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Pertanyaan Slide</label>
                        <textarea x-model="currentSlide.question" @input="debounceSaveSlide()" rows="2"
                                  placeholder="Ketik pertanyaan atau judul slide di sini..."
                                  class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-sm font-semibold text-white focus:outline-none focus:border-indigo-500"></textarea>
                    </div>

                    <!-- Description Input -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Deskripsi / Petunjuk (Opsional)</label>
                        <input type="text" x-model="currentSlide.description" @input="debounceSaveSlide()"
                               placeholder="Petunjuk singkat untuk peserta..."
                               class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>

                    <!-- Multiple Choice / Quiz Options Section -->
                    <template x-if="currentSlide.type === 'multiple_choice' || currentSlide.type === 'quiz'">
                        <div class="space-y-3 pt-2 border-t border-slate-800">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pilihan Jawaban</span>
                                <button @click="addOption()" class="text-xs font-bold text-indigo-400 hover:text-indigo-300">
                                    + Tambah Opsi
                                </button>
                            </div>

                            <div class="space-y-2.5">
                                <template x-for="(opt, optIdx) in (currentSlide.options || [])" :key="opt.id || optIdx">
                                    <div class="flex items-center gap-2 bg-slate-900 p-2 rounded-xl border border-slate-800">
                                        <input type="color" x-model="opt.color" @change="saveSlide()" 
                                               class="w-6 h-6 rounded border-0 bg-transparent cursor-pointer">
                                        <input type="text" x-model="opt.text" @input="debounceSaveSlide()"
                                               placeholder="Teks opsi..."
                                               class="flex-1 bg-transparent text-xs font-medium text-white focus:outline-none">
                                        
                                        <!-- Correct toggle for Quiz -->
                                        <button type="button" @click="opt.is_correct = !opt.is_correct; saveSlide()"
                                                :class="opt.is_correct ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/50' : 'text-slate-500 border-slate-700'"
                                                class="px-2 py-1 rounded text-[10px] font-bold border transition-all" title="Tandai Kunci Jawaban Benar">
                                            ✓ Benar
                                        </button>

                                        <button @click="removeOption(optIdx)" class="text-slate-500 hover:text-rose-400 p-1">
                                            &times;
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Scales Statements Section -->
                    <template x-if="currentSlide.type === 'scales'">
                        <div class="space-y-3 pt-2 border-t border-slate-800">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pernyataan Skala</span>
                                <button @click="addScaleStatement()" class="text-xs font-bold text-indigo-400 hover:text-indigo-300">
                                    + Tambah
                                </button>
                            </div>

                            <div class="space-y-2">
                                <template x-for="(stmt, stmtIdx) in (currentSlide.options || [])" :key="stmt.id || stmtIdx">
                                    <div class="flex items-center gap-2 bg-slate-900 p-2 rounded-xl border border-slate-800">
                                        <input type="text" x-model="stmt.text" @input="debounceSaveSlide()"
                                               class="flex-1 bg-transparent text-xs font-medium text-white focus:outline-none">
                                        <button @click="removeOption(stmtIdx)" class="text-slate-500 hover:text-rose-400 p-1">
                                            &times;
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Ranking Items Section -->
                    <template x-if="currentSlide.type === 'ranking'">
                        <div class="space-y-3 pt-2 border-t border-slate-800">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Item Prioritas</span>
                                <button @click="addOption()" class="text-xs font-bold text-indigo-400 hover:text-indigo-300">
                                    + Tambah
                                </button>
                            </div>

                            <div class="space-y-2">
                                <template x-for="(item, itemIdx) in (currentSlide.options || [])" :key="item.id || itemIdx">
                                    <div class="flex items-center gap-2 bg-slate-900 p-2 rounded-xl border border-slate-800">
                                        <span class="text-xs text-slate-500 font-mono" x-text="itemIdx + 1"></span>
                                        <input type="text" x-model="item.text" @input="debounceSaveSlide()"
                                               class="flex-1 bg-transparent text-xs font-medium text-white focus:outline-none">
                                        <button @click="removeOption(itemIdx)" class="text-slate-500 hover:text-rose-400 p-1">
                                            &times;
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Notes Input -->
                    <div class="pt-2 border-t border-slate-800">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Catatan Presenter (Speaker Notes)</label>
                        <textarea x-model="currentSlide.notes" @input="debounceSaveSlide()" rows="2"
                                  placeholder="Catatan rahasia yang hanya dilihat oleh presenter..."
                                  class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-slate-300 focus:outline-none focus:border-indigo-500"></textarea>
                    </div>

                </div>

                <!-- TAB 2: TIPE SLIDE -->
                <div x-show="activeTab === 'type'" class="space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-2">Pilih Format Slide</span>
                    
                    <div class="grid grid-cols-2 gap-2.5">
                        <button @click="changeSlideType('multiple_choice')" 
                                :class="currentSlide.type === 'multiple_choice' ? 'border-indigo-500 bg-indigo-950/40' : 'border-slate-800 bg-slate-900'"
                                class="p-3 rounded-2xl border text-left hover:border-indigo-500 transition-all flex flex-col gap-1">
                            <span class="text-xl">📊</span>
                            <span class="text-xs font-bold text-white">Pilihan Ganda</span>
                        </button>

                        <button @click="changeSlideType('word_cloud')" 
                                :class="currentSlide.type === 'word_cloud' ? 'border-indigo-500 bg-indigo-950/40' : 'border-slate-800 bg-slate-900'"
                                class="p-3 rounded-2xl border text-left hover:border-indigo-500 transition-all flex flex-col gap-1">
                            <span class="text-xl">☁️</span>
                            <span class="text-xs font-bold text-white">Word Cloud</span>
                        </button>

                        <button @click="changeSlideType('quiz')" 
                                :class="currentSlide.type === 'quiz' ? 'border-indigo-500 bg-indigo-950/40' : 'border-slate-800 bg-slate-900'"
                                class="p-3 rounded-2xl border text-left hover:border-indigo-500 transition-all flex flex-col gap-1">
                            <span class="text-xl">⏱️</span>
                            <span class="text-xs font-bold text-white">Kuis Cepat</span>
                        </button>

                        <button @click="changeSlideType('leaderboard')" 
                                :class="currentSlide.type === 'leaderboard' ? 'border-indigo-500 bg-indigo-950/40' : 'border-slate-800 bg-slate-900'"
                                class="p-3 rounded-2xl border text-left hover:border-indigo-500 transition-all flex flex-col gap-1">
                            <span class="text-xl">🏆</span>
                            <span class="text-xs font-bold text-white">Leaderboard</span>
                        </button>

                        <button @click="changeSlideType('scales')" 
                                :class="currentSlide.type === 'scales' ? 'border-indigo-500 bg-indigo-950/40' : 'border-slate-800 bg-slate-900'"
                                class="p-3 rounded-2xl border text-left hover:border-indigo-500 transition-all flex flex-col gap-1">
                            <span class="text-xl">⭐</span>
                            <span class="text-xs font-bold text-white">Scales Rating</span>
                        </button>

                        <button @click="changeSlideType('ranking')" 
                                :class="currentSlide.type === 'ranking' ? 'border-indigo-500 bg-indigo-950/40' : 'border-slate-800 bg-slate-900'"
                                class="p-3 rounded-2xl border text-left hover:border-indigo-500 transition-all flex flex-col gap-1">
                            <span class="text-xl">🔢</span>
                            <span class="text-xs font-bold text-white">Urutan Ranking</span>
                        </button>

                        <button @click="changeSlideType('open_ended')" 
                                :class="currentSlide.type === 'open_ended' ? 'border-indigo-500 bg-indigo-950/40' : 'border-slate-800 bg-slate-900'"
                                class="p-3 rounded-2xl border text-left hover:border-indigo-500 transition-all flex flex-col gap-1">
                            <span class="text-xl">💬</span>
                            <span class="text-xs font-bold text-white">Open Ended</span>
                        </button>

                        <button @click="changeSlideType('qa')" 
                                :class="currentSlide.type === 'qa' ? 'border-indigo-500 bg-indigo-950/40' : 'border-slate-800 bg-slate-900'"
                                class="p-3 rounded-2xl border text-left hover:border-indigo-500 transition-all flex flex-col gap-1">
                            <span class="text-xl">🙋</span>
                            <span class="text-xs font-bold text-white">Audience Q&A</span>
                        </button>

                        <button @click="changeSlideType('content_heading')" 
                                :class="currentSlide.type === 'content_heading' ? 'border-indigo-500 bg-indigo-950/40' : 'border-slate-800 bg-slate-900'"
                                class="p-3 rounded-2xl border text-left hover:border-indigo-500 transition-all flex flex-col gap-1">
                            <span class="text-xl">📢</span>
                            <span class="text-xs font-bold text-white">Judul Pengantar</span>
                        </button>

                        <button @click="changeSlideType('content_paragraph')" 
                                :class="currentSlide.type === 'content_paragraph' ? 'border-indigo-500 bg-indigo-950/40' : 'border-slate-800 bg-slate-900'"
                                class="p-3 rounded-2xl border text-left hover:border-indigo-500 transition-all flex flex-col gap-1">
                            <span class="text-xl">📄</span>
                            <span class="text-xs font-bold text-white">Poin Materi</span>
                        </button>
                    </div>
                </div>

                <!-- TAB 3: DESAIN & TEMA -->
                <div x-show="activeTab === 'design'" class="space-y-5">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-2">Tema Visual Presentasi</span>
                        <div class="grid grid-cols-2 gap-3">
                            <button @click="presentation.theme = 'light_tech'; savePresentationMeta()"
                                    :class="presentation.theme === 'light_tech' ? 'ring-2 ring-blue-500' : ''"
                                    class="p-3 rounded-2xl bg-slate-900 border border-slate-700 text-left flex flex-col gap-1 col-span-2">
                                <div class="h-6 rounded-lg bg-gradient-to-r from-blue-100 via-sky-200 to-indigo-100 border border-blue-200 flex items-center justify-between px-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                        <span class="w-2.5 h-2.5 rounded-full bg-sky-400"></span>
                                    </div>
                                    <span class="text-[10px] font-bold text-blue-900 uppercase tracking-wider">Cerah / Light</span>
                                </div>
                                <span class="text-xs font-bold text-white flex items-center justify-between">
                                    <span>Tech Blue (Terang)</span>
                                    <span class="text-[10px] text-sky-400">Palet Ilustrasi</span>
                                </span>
                            </button>

                            <button @click="presentation.theme = 'default'; savePresentationMeta()"
                                    :class="presentation.theme === 'default' ? 'ring-2 ring-indigo-500' : ''"
                                    class="p-3 rounded-2xl bg-slate-900 border border-slate-700 text-left flex flex-col gap-1">
                                <div class="h-6 rounded-lg bg-gradient-to-r from-slate-900 to-indigo-950 border border-slate-800"></div>
                                <span class="text-xs font-bold text-white">Classic Dark</span>
                            </button>

                            <button @click="presentation.theme = 'vibrant'; savePresentationMeta()"
                                    :class="presentation.theme === 'vibrant' ? 'ring-2 ring-indigo-500' : ''"
                                    class="p-3 rounded-2xl bg-slate-900 border border-slate-700 text-left flex flex-col gap-1">
                                <div class="h-6 rounded-lg bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500"></div>
                                <span class="text-xs font-bold text-white">Vibrant Neon</span>
                            </button>

                            <button @click="presentation.theme = 'emerald'; savePresentationMeta()"
                                    :class="presentation.theme === 'emerald' ? 'ring-2 ring-indigo-500' : ''"
                                    class="p-3 rounded-2xl bg-slate-900 border border-slate-700 text-left flex flex-col gap-1">
                                <div class="h-6 rounded-lg bg-gradient-to-r from-teal-900 to-emerald-800"></div>
                                <span class="text-xs font-bold text-white">Emerald Forest</span>
                            </button>

                            <button @click="presentation.theme = 'sunset'; savePresentationMeta()"
                                    :class="presentation.theme === 'sunset' ? 'ring-2 ring-indigo-500' : ''"
                                    class="p-3 rounded-2xl bg-slate-900 border border-slate-700 text-left flex flex-col gap-1">
                                <div class="h-6 rounded-lg bg-gradient-to-r from-amber-600 to-rose-700"></div>
                                <span class="text-xs font-bold text-white">Sunset Glow</span>
                            </button>
                        </div>
                    </div>

                    <!-- Options & Controls -->
                    <div class="pt-4 border-t border-slate-800 space-y-4">
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-xs font-medium text-slate-300">Status Voting Slide</span>
                            <select x-model="presentation.voting_status" @change="savePresentationMeta()"
                                    class="px-2.5 py-1 bg-slate-900 border border-slate-800 rounded-lg text-xs font-bold text-white focus:outline-none">
                                <option value="open">Buka (Terima Suara)</option>
                                <option value="closed">Tutup (Kunci Voting)</option>
                            </select>
                        </label>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Share Presentation Modal (PIN, Direct Link & QR Code) -->
    <div x-show="shareModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="shareModal = false" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl text-white">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-black text-white">Bagikan ke Audiens</h3>
                <button @click="shareModal = false" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
            </div>

            <!-- PIN Box -->
            <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 text-center mb-5">
                <p class="text-xs text-slate-400 uppercase tracking-widest font-bold mb-1">KODE PIN PRESENTASI</p>
                <div class="text-3xl font-black text-amber-400 tracking-widest font-mono" x-text="formattedCode"></div>
                <p class="text-[11px] text-slate-500 mt-1">Audiens dapat membuka <strong class="text-slate-300">spiritmeter.test/join</strong> dan memasukkan kode ini.</p>
            </div>

            <!-- QR Code Canvas -->
            <div class="bg-white p-4 rounded-2xl flex flex-col items-center justify-center mb-5">
                <canvas id="share-qr-canvas" class="w-48 h-48"></canvas>
                <p class="text-[11px] font-bold text-slate-700 mt-2">Scan dengan kamera HP untuk masuk langsung</p>
            </div>

            <!-- Direct URL Copy -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Tautan Langsung Peserta</label>
                <div class="flex items-center gap-2">
                    <input type="text" :value="directParticipantUrl" readonly
                           class="flex-1 px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs font-mono text-slate-300 focus:outline-none select-all">
                    <button @click="copyParticipantLink()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl transition-all">
                        Salin
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- New Slide Type Picker Modal -->
    <div x-show="newSlideModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="newSlideModal = false" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl text-white">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xl font-black text-white">Tambah Slide Baru</h3>
                <button @click="newSlideModal = false" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
            </div>
            <p class="text-xs text-slate-400 mb-6">Pilih jenis interaksi yang ingin Anda tanyakan kepada audiens.</p>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <button @click="createNewSlide('multiple_choice')" class="p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-indigo-500 transition-all text-left flex flex-col gap-1.5 group">
                    <span class="text-2xl">📊</span>
                    <span class="text-xs font-bold text-white group-hover:text-indigo-400">Pilihan Ganda</span>
                    <span class="text-[10px] text-slate-500">Diagram batang / donat</span>
                </button>

                <button @click="createNewSlide('word_cloud')" class="p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-indigo-500 transition-all text-left flex flex-col gap-1.5 group">
                    <span class="text-2xl">☁️</span>
                    <span class="text-xs font-bold text-white group-hover:text-indigo-400">Word Cloud</span>
                    <span class="text-[10px] text-slate-500">Kumpulan kata kunci</span>
                </button>

                <button @click="createNewSlide('quiz')" class="p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-indigo-500 transition-all text-left flex flex-col gap-1.5 group">
                    <span class="text-2xl">⏱️</span>
                    <span class="text-xs font-bold text-white group-hover:text-indigo-400">Kuis Cepat</span>
                    <span class="text-[10px] text-slate-500">Timer & poin kecepatan</span>
                </button>

                <button @click="createNewSlide('leaderboard')" class="p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-indigo-500 transition-all text-left flex flex-col gap-1.5 group">
                    <span class="text-2xl">🏆</span>
                    <span class="text-xs font-bold text-white group-hover:text-indigo-400">Leaderboard</span>
                    <span class="text-[10px] text-slate-500">Papan peringkat skor</span>
                </button>

                <button @click="createNewSlide('scales')" class="p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-indigo-500 transition-all text-left flex flex-col gap-1.5 group">
                    <span class="text-2xl">⭐</span>
                    <span class="text-xs font-bold text-white group-hover:text-indigo-400">Scales Rating</span>
                    <span class="text-[10px] text-slate-500">Skala Likert 1-5</span>
                </button>

                <button @click="createNewSlide('ranking')" class="p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-indigo-500 transition-all text-left flex flex-col gap-1.5 group">
                    <span class="text-2xl">🔢</span>
                    <span class="text-xs font-bold text-white group-hover:text-indigo-400">Ranking</span>
                    <span class="text-[10px] text-slate-500">Urutan prioritas</span>
                </button>

                <button @click="createNewSlide('open_ended')" class="p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-indigo-500 transition-all text-left flex flex-col gap-1.5 group">
                    <span class="text-2xl">💬</span>
                    <span class="text-xs font-bold text-white group-hover:text-indigo-400">Open Ended</span>
                    <span class="text-[10px] text-slate-500">Teks bebas audiens</span>
                </button>

                <button @click="createNewSlide('qa')" class="p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-indigo-500 transition-all text-left flex flex-col gap-1.5 group">
                    <span class="text-2xl">🙋</span>
                    <span class="text-xs font-bold text-white group-hover:text-indigo-400">Audience Q&A</span>
                    <span class="text-[10px] text-slate-500">Tanya jawab & upvote</span>
                </button>

                <button @click="createNewSlide('content_heading')" class="p-4 rounded-2xl bg-slate-950 border border-slate-800 hover:border-indigo-500 transition-all text-left flex flex-col gap-1.5 group">
                    <span class="text-2xl">📢</span>
                    <span class="text-xs font-bold text-white group-hover:text-indigo-400">Judul Informasi</span>
                    <span class="text-[10px] text-slate-500">Slide pembuka/pemisah</span>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function presentationStudio(presentationData, slidesData, csrfToken) {
    return {
        presentation: presentationData,
        slides: slidesData,
        currentSlideId: slidesData.length > 0 ? slidesData[0].id : null,
        activeTab: 'content',
        autoSaved: true,
        saveTimeout: null,
        shareModal: false,
        newSlideModal: false,

        init() {
            // Sortable slide thumbnails on the left
            const el = document.getElementById('slides-sortable');
            if (el && window.Sortable) {
                new window.Sortable(el, {
                    animation: 150,
                    onEnd: (evt) => {
                        const movedItem = this.slides.splice(evt.oldIndex, 1)[0];
                        this.slides.splice(evt.newIndex, 0, movedItem);
                        this.reorderSlides();
                    }
                });
            }
        },

        get currentSlide() {
            return this.slides.find(s => s.id === this.currentSlideId) || (this.slides[0] || {});
        },

        get currentSlideIndex() {
            return this.slides.findIndex(s => s.id === this.currentSlideId);
        },

        get formattedCode() {
            const c = (this.presentation.access_code || '').replace(/\s+/g, '');
            if (c.length === 8) {
                return c.substring(0, 4) + ' ' + c.substring(4, 8);
            }
            return c;
        },

        get directParticipantUrl() {
            return window.location.origin + '/m/' + (this.presentation.access_code || '').replace(/\s+/g, '');
        },

        selectSlide(id) {
            this.currentSlideId = id;
        },

        previousSlide() {
            const idx = this.currentSlideIndex;
            if (idx > 0) {
                this.currentSlideId = this.slides[idx - 1].id;
            }
        },

        nextSlide() {
            const idx = this.currentSlideIndex;
            if (idx < this.slides.length - 1) {
                this.currentSlideId = this.slides[idx + 1].id;
            }
        },

        getTypeBadge(type) {
            const map = {
                'multiple_choice': 'Pilihan Ganda',
                'word_cloud': 'Word Cloud',
                'quiz': 'Kuis Cepat',
                'leaderboard': 'Leaderboard',
                'scales': 'Scales',
                'ranking': 'Ranking',
                'open_ended': 'Open Ended',
                'qa': 'Q&A',
                'content_heading': 'Judul',
                'content_paragraph': 'Materi'
            };
            return map[type] || type;
        },

        getThemeClasses(theme) {
            switch(theme) {
                case 'light_tech':
                    return 'bg-gradient-to-br from-[#f0f6ff] via-[#e8f1fc] to-[#f8fbff] border-blue-200 text-slate-900';
                case 'vibrant':
                    return 'bg-gradient-to-br from-indigo-950 via-purple-950 to-slate-950 border-purple-800 text-white';
                case 'emerald':
                    return 'bg-gradient-to-br from-teal-950 via-emerald-950 to-slate-950 border-emerald-800 text-white';
                case 'sunset':
                    return 'bg-gradient-to-br from-amber-950 via-rose-950 to-slate-950 border-rose-800 text-white';
                default:
                    return 'bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 border-slate-800 text-white';
            }
        },

        addOption() {
            if (!this.currentSlide.options) this.currentSlide.options = [];
            const colors = ['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#3b82f6', '#8b5cf6'];
            const nextIdx = this.currentSlide.options.length + 1;
            this.currentSlide.options.push({
                id: 'opt_' + Date.now(),
                text: 'Opsi ' + nextIdx,
                color: colors[(nextIdx - 1) % colors.length],
                is_correct: false
            });
            this.saveSlide();
        },

        addScaleStatement() {
            if (!this.currentSlide.options) this.currentSlide.options = [];
            const nextIdx = this.currentSlide.options.length + 1;
            this.currentSlide.options.push({
                id: 's_' + Date.now(),
                text: 'Pernyataan ' + nextIdx
            });
            this.saveSlide();
        },

        removeOption(index) {
            this.currentSlide.options.splice(index, 1);
            this.saveSlide();
        },

        changeSlideType(newType) {
            this.currentSlide.type = newType;
            this.saveSlide();
        },

        debounceSaveSlide() {
            this.autoSaved = false;
            clearTimeout(this.saveTimeout);
            this.saveTimeout = setTimeout(() => {
                this.saveSlide();
            }, 600);
        },

        saveSlide() {
            fetch('/slides/' + this.currentSlide.id, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    type: this.currentSlide.type,
                    question: this.currentSlide.question,
                    description: this.currentSlide.description,
                    options: this.currentSlide.options,
                    settings: this.currentSlide.settings,
                    notes: this.currentSlide.notes
                })
            }).then(r => r.json()).then(data => {
                this.autoSaved = true;
            });
        },

        savePresentationMeta() {
            fetch('/presentations/' + this.presentation.id, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    title: this.presentation.title,
                    theme: this.presentation.theme,
                    voting_status: this.presentation.voting_status
                })
            }).then(r => r.json()).then(() => {
                this.autoSaved = true;
            });
        },

        createNewSlide(type) {
            this.newSlideModal = false;
            fetch('/presentations/' + this.presentation.id + '/slides', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ type: type })
            }).then(r => r.json()).then(data => {
                this.slides.push(data.slide);
                this.currentSlideId = data.slide.id;
            });
        },

        duplicateSlide(slideId) {
            fetch('/slides/' + slideId + '/duplicate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            }).then(r => r.json()).then(data => {
                this.slides.push(data.slide);
                this.currentSlideId = data.slide.id;
            });
        },

        deleteSlide(slideId) {
            if (this.slides.length <= 1) {
                alert('Presentasi minimal harus memiliki 1 slide.');
                return;
            }
            if (!confirm('Hapus slide ini?')) return;

            fetch('/slides/' + slideId, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            }).then(() => {
                const idx = this.slides.findIndex(s => s.id === slideId);
                this.slides.splice(idx, 1);
                this.currentSlideId = this.slides[0]?.id || null;
            });
        },

        reorderSlides() {
            const ids = this.slides.map(s => s.id);
            fetch('/presentations/' + this.presentation.id + '/slides/reorder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ slide_ids: ids })
            });
        },

        generateQrCode() {
            this.$nextTick(() => {
                const canvas = document.getElementById('share-qr-canvas');
                if (canvas && window.QRCode) {
                    window.QRCode.toCanvas(canvas, this.directParticipantUrl, {
                        width: 192,
                        margin: 1,
                        color: {
                            dark: '#0f172a',
                            light: '#ffffff'
                        }
                    });
                }
            });
        },

        copyParticipantLink() {
            navigator.clipboard.writeText(this.directParticipantUrl).then(() => {
                alert('Tautan peserta berhasil disalin ke clipboard!');
            });
        }
    }
}
</script>
@endsection
