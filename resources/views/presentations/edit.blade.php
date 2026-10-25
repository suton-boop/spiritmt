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
                <div class="hidden lg:flex items-center gap-1.5">
                    <select x-model="presentation.folder_id" @change="savePresentationMeta()"
                            class="bg-slate-800/90 hover:bg-slate-800 text-xs text-slate-300 font-medium border border-slate-700 rounded-lg px-2.5 py-1 focus:outline-none focus:border-indigo-500 transition-all cursor-pointer">
                        <option value="">📂 Tanpa Folder</option>
                        @foreach($folders as $f)
                            <option value="{{ $f->id }}">📁 {{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
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

            <!-- More Options (Export, Reset) -->
            <div class="relative" x-data="{ optionsOpen: false }">
                <button @click="optionsOpen = !optionsOpen" 
                        class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl border border-slate-700 transition-all" 
                        title="Opsi Kegiatan & Respon">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                </button>
                <div x-show="optionsOpen" @click.away="optionsOpen = false" x-cloak
                     class="absolute right-0 mt-2 w-52 bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 py-1.5 z-50 text-xs font-semibold text-slate-200">
                    <a :href="'/presentations/' + presentation.id + '/export'" class="flex items-center gap-2.5 px-3.5 py-2 hover:bg-slate-700 transition-colors">
                        <span>📥</span> Ekspor CSV Hasil
                    </a>
                    <button @click="optionsOpen = false; if(confirm('Reset semua respon peserta saat ini agar siap untuk kegiatan baru?')) { resetPresentationResponses(); }" class="w-full text-left flex items-center gap-2.5 px-3.5 py-2 text-rose-400 hover:bg-rose-500/10 transition-colors">
                        <span>🔄</span> Reset Respon Peserta
                    </button>
                </div>
            </div>

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
                 :class="getThemeClasses(presentation.theme)"
                 :style="getThemeCanvasStyle()">
                
                <!-- Slide Header (Join Instruction) -->
                <div x-show="isHeaderVisible()"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="flex items-center justify-between pb-4 border-b"
                     :class="presentation.theme === 'light_tech' ? 'border-blue-200/80' : 'border-white/10'">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl p-1 flex items-center justify-center shadow-sm flex-shrink-0"
                             :class="presentation.theme === 'light_tech' ? 'bg-white border border-blue-200 shadow-blue-500/10' : 'bg-slate-900/90 border border-slate-700/80'">
                            <img src="{{ asset('image/logo spiritmeter.png') }}" alt="Logo SpiritMeter" class="w-full h-full object-contain">
                        </div>
                        <div class="text-xs sm:text-sm font-medium tracking-wide"
                             :class="presentation.theme === 'light_tech' ? 'text-slate-600' : 'text-slate-200 opacity-80'">
                            Masuk ke <strong class="font-bold underline" :class="presentation.theme === 'light_tech' ? 'text-blue-600 decoration-blue-500' : ''" x-text="window.location.host + '/join'">{{ request()->getHost() }}/join</strong>
                        </div>
                    </div>
                    <div class="px-3 py-1 rounded-xl backdrop-blur-md text-xs sm:text-sm font-mono font-black border"
                         :class="presentation.theme === 'light_tech' ? 'bg-white/95 border-blue-200 text-blue-600 shadow-sm' : 'bg-white/10 border-white/15 text-amber-300'">
                        KODE: <span x-text="formattedCode"></span>
                    </div>
                </div>

                <!-- Slide Content Area (Dynamic based on Layout: center, banner, left, right, split_left, split_right, sidebar_left, sidebar_right) -->
                <div class="flex-1 flex w-full items-center justify-center py-4 px-2">
                    
                    <!-- A. Stacked Layouts (Center, Top Banner, or No Content Image) -->
                    <div x-show="!isSideLayout()" class="w-full flex flex-col items-center justify-center text-center max-w-3xl mx-auto transition-all">
                        <!-- Top Banner (if layout === 'banner' and image exists) -->
                        <div x-show="getImageLayout() === 'banner' && currentSlide.settings?.content_image" class="w-full max-h-36 sm:max-h-48 rounded-2xl overflow-hidden mb-4 shadow-xl border border-white/20 bg-slate-950 flex-shrink-0">
                            <img :src="currentSlide.settings?.content_image" alt="Banner Image" class="w-full h-full object-cover">
                        </div>

                        <!-- Question / Title -->
                        <h2 class="mb-2 max-w-2xl transition-colors"
                            :class="[presentation.theme === 'light_tech' ? 'text-slate-900' : 'text-white', getQuestionClasses(false)]"
                            x-text="currentSlide.question || 'Tulis pertanyaan Anda di panel samping'"></h2>

                        <p class="mb-4 max-w-xl transition-colors"
                           :class="[presentation.theme === 'light_tech' ? 'text-slate-600' : 'text-slate-200 opacity-70', getDescriptionClasses()]"
                           x-show="currentSlide.type !== 'content_paragraph' && currentSlide.description" x-text="currentSlide.description"></p>

                        <!-- Text Slide Content (Materi) Preview Card -->
                        <div x-show="currentSlide.type === 'content_paragraph' && currentSlide.description"
                             class="mb-4 max-w-xl w-full p-4 rounded-2xl border text-left shadow-lg transition-all"
                             :class="presentation.theme === 'light_tech' ? 'bg-white/90 border-blue-100 text-slate-800' : 'bg-slate-900/70 border-white/10 text-slate-200'">
                            <div :class="getContentParagraphClasses()" class="whitespace-pre-line leading-relaxed" x-text="currentSlide.description"></div>
                        </div>

                        <!-- Center Image (if layout !== 'banner' and image exists) -->
                        <div x-show="getImageLayout() !== 'banner' && currentSlide.settings?.content_image" class="mb-4 max-w-sm sm:max-w-md w-full">
                            <img :src="currentSlide.settings?.content_image" alt="Content Image" 
                                 class="max-h-36 sm:max-h-44 w-auto mx-auto rounded-2xl object-contain shadow-lg border border-white/20 bg-slate-900/40">
                        </div>

                        <!-- Type Specific Preview Widget -->
                        <div class="w-full max-w-xl mx-auto">
                            @include('presentations.partials.slide-preview')
                        </div>
                    </div>

                    <!-- B. Side Layouts (Left, Right, Split 50:50, Sidebar) -->
                    <div x-show="isSideLayout()" 
                         class="w-full flex flex-col md:flex-row items-center justify-center gap-6 lg:gap-8 max-w-5xl mx-auto transition-all"
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
                                    'w-full h-52 sm:h-64 object-cover rounded-2xl': getImageLayout() === 'split_left' || getImageLayout() === 'split_right' || getImageLayout() === 'sidebar_left' || getImageLayout() === 'sidebar_right',
                                    'max-h-52 sm:max-h-64 w-auto object-contain rounded-2xl': getImageLayout() === 'left' || getImageLayout() === 'right'
                                 }"
                                 class="shadow-xl border border-white/20 bg-slate-900/40">
                        </div>

                        <!-- Text & Widget Column -->
                        <div :class="{
                                'w-full md:w-1/2': getImageLayout() === 'split_left' || getImageLayout() === 'split_right',
                                'w-full md:w-3/4': getImageLayout() === 'sidebar_left' || getImageLayout() === 'sidebar_right',
                                'w-full md:w-7/12': getImageLayout() === 'left' || getImageLayout() === 'right'
                             }"
                             class="flex flex-col text-left">
                            <h2 class="mb-2 transition-colors"
                                :class="[presentation.theme === 'light_tech' ? 'text-slate-900' : 'text-white', getQuestionClasses(true)]"
                                x-text="currentSlide.question || 'Tulis pertanyaan Anda di panel samping'"></h2>

                            <p class="mb-4 transition-colors"
                               :class="[presentation.theme === 'light_tech' ? 'text-slate-600' : 'text-slate-200 opacity-70', getDescriptionClasses()]"
                               x-show="currentSlide.type !== 'content_paragraph' && currentSlide.description" x-text="currentSlide.description"></p>

                            <!-- Text Slide Content (Materi) Preview Card in Side Layout -->
                            <div x-show="currentSlide.type === 'content_paragraph' && currentSlide.description"
                                 class="mb-4 p-3.5 rounded-2xl border text-left shadow-lg transition-all"
                                 :class="presentation.theme === 'light_tech' ? 'bg-white/90 border-blue-100 text-slate-800' : 'bg-slate-900/70 border-white/10 text-slate-200'">
                                <div :class="getContentParagraphClasses()" class="whitespace-pre-line leading-relaxed" x-text="currentSlide.description"></div>
                            </div>

                            <!-- Type Specific Preview Widget -->
                            <div class="w-full">
                                @include('presentations.partials.slide-preview')
                            </div>
                        </div>

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
            <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                <button @click="previousSlide()" :disabled="currentSlideIndex <= 0"
                        class="p-2 bg-slate-800 hover:bg-slate-700 disabled:opacity-30 text-white rounded-xl transition-all"
                        title="Slide Sebelumnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <span class="text-xs font-bold text-slate-400">
                    Slide <span x-text="currentSlideIndex + 1"></span> dari <span x-text="slides.length"></span>
                </span>
                <button @click="nextSlide()" :disabled="currentSlideIndex >= slides.length - 1"
                        class="p-2 bg-slate-800 hover:bg-slate-700 disabled:opacity-30 text-white rounded-xl transition-all"
                        title="Slide Selanjutnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>

                <div class="h-4 w-px bg-slate-800 mx-1"></div>

                <!-- Toggle Button Header PIN -->
                <button type="button" @click="toggleSlideHeader()"
                        class="px-3 py-1.5 rounded-xl border text-xs font-bold transition-all flex items-center gap-2 shadow-sm"
                        :class="isHeaderVisible() 
                            ? 'bg-indigo-600/20 text-indigo-300 border-indigo-500/40 hover:bg-indigo-600/30' 
                            : 'bg-slate-800 text-slate-400 border-slate-700 hover:text-white'"
                        :title="isHeaderVisible() ? 'Klik untuk Sembunyikan Header PIN' : 'Klik untuk Tampilkan Header PIN'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="isHeaderVisible()">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!isHeaderVisible()">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                    </svg>
                    <span x-text="isHeaderVisible() ? 'Header PIN: Tampil' : 'Header PIN: Sembunyi'"></span>
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
                    
                    <!-- Question Type & Result Visualization Selector (Mentimeter Style: Bars, Donut, Pie, Dots) -->
                    <template x-if="currentSlide.type === 'multiple_choice' || currentSlide.type === 'quiz'">
                        <div class="space-y-3 pb-3 border-b border-slate-800">
                            <!-- Question Type Dropdown Button -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Tipe Pertanyaan</label>
                                <button type="button" @click="activeTab = 'type'" 
                                        class="w-full p-2.5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-indigo-500/50 flex items-center justify-between text-left transition-all group">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-6 h-6 rounded-lg bg-indigo-600/20 text-indigo-400 flex items-center justify-center text-xs font-bold">
                                            📊
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold text-white block" x-text="getTypeBadge(currentSlide.type)"></span>
                                        </div>
                                    </div>
                                    <svg class="w-4 h-4 text-slate-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>

                            <!-- 4 Segmented Buttons for Visualization Model: Bars, Donut, Pie, Dots (Mentimeter Style) -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Model Tampilan Hasil</label>
                                    <span class="text-[10px] text-indigo-400 font-semibold uppercase tracking-wider" x-text="getVisualizationType()"></span>
                                </div>
                                <div class="grid grid-cols-4 p-1 rounded-2xl bg-slate-900 border border-slate-800 gap-1">
                                    <!-- 1. Bars -->
                                    <button type="button" @click="setVisualizationType('bars')"
                                            :class="getVisualizationType() === 'bars' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800'"
                                            class="py-2 rounded-xl flex flex-col items-center justify-center gap-1 transition-all"
                                            title="Bars (Diagram Batang Vertikal)">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                            <rect x="3" y="10" width="4" height="11" rx="1"/>
                                            <rect x="10" y="4" width="4" height="17" rx="1"/>
                                            <rect x="17" y="13" width="4" height="8" rx="1"/>
                                        </svg>
                                        <span class="text-[9px] font-bold">Bars</span>
                                    </button>

                                    <!-- 2. Donut -->
                                    <button type="button" @click="setVisualizationType('donut')"
                                            :class="getVisualizationType() === 'donut' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800'"
                                            class="py-2 rounded-xl flex flex-col items-center justify-center gap-1 transition-all"
                                            title="Donut (Diagram Cincin Donut)">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <circle cx="12" cy="12" r="8"/>
                                            <circle cx="12" cy="12" r="3.5" fill="currentColor" stroke="none"/>
                                        </svg>
                                        <span class="text-[9px] font-bold">Donut</span>
                                    </button>

                                    <!-- 3. Pie -->
                                    <button type="button" @click="setVisualizationType('pie')"
                                            :class="getVisualizationType() === 'pie' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800'"
                                            class="py-2 rounded-xl flex flex-col items-center justify-center gap-1 transition-all"
                                            title="Pie (Diagram Lingkaran Pie)">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="9"/>
                                            <path d="M12 3v9l7 4" stroke-width="2"/>
                                        </svg>
                                        <span class="text-[9px] font-bold">Pie</span>
                                    </button>

                                    <!-- 4. Dots -->
                                    <button type="button" @click="setVisualizationType('dots')"
                                            :class="getVisualizationType() === 'dots' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800'"
                                            class="py-2 rounded-xl flex flex-col items-center justify-center gap-1 transition-all"
                                            title="Dots (Klaster Titik Respon)">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="7" cy="7" r="2.5"/>
                                            <circle cx="17" cy="7" r="2.5"/>
                                            <circle cx="7" cy="17" r="2.5"/>
                                            <circle cx="17" cy="17" r="2.5"/>
                                        </svg>
                                        <span class="text-[9px] font-bold">Dots</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Response Settings (Multiple Selection & Percentage Toggles) -->
                            <div class="space-y-2 pt-1">
                                <label class="flex items-center justify-between p-2.5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition-all cursor-pointer">
                                    <div>
                                        <span class="text-xs font-bold text-white block">Multiple selection</span>
                                        <span class="text-[10px] text-slate-400 block">Peserta dapat memilih lebih dari satu opsi</span>
                                    </div>
                                    <input type="checkbox" :checked="isMultipleSelection()" @change="toggleMultipleSelection()"
                                           class="w-4 h-4 rounded text-indigo-600 bg-slate-950 border-slate-700 focus:ring-indigo-500 cursor-pointer">
                                </label>

                                <label class="flex items-center justify-between p-2.5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition-all cursor-pointer">
                                    <div>
                                        <span class="text-xs font-bold text-white block">Show results as percentage</span>
                                        <span class="text-[10px] text-slate-400 block">Tampilkan angka respon sebagai persentase (%)</span>
                                    </div>
                                    <input type="checkbox" :checked="isPercentageMode()" @change="togglePercentageMode()"
                                           class="w-4 h-4 rounded text-indigo-600 bg-slate-950 border-slate-700 focus:ring-indigo-500 cursor-pointer">
                                </label>
                            </div>
                        </div>
                    </template>

                    <!-- Question Input -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Pertanyaan Slide</label>
                            <span class="text-[11px] font-mono" :class="(currentSlide.question || '').length > 100 ? 'text-amber-400 font-bold' : 'text-slate-400'">
                                <span x-text="(currentSlide.question || '').length"></span>/150
                            </span>
                        </div>
                        <textarea x-model="currentSlide.question" @input="debounceSaveSlide()" rows="2" maxlength="150"
                                  placeholder="Ketik pertanyaan atau judul slide di sini..."
                                  class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-sm font-semibold text-white focus:outline-none focus:border-indigo-500"></textarea>
                        <div x-show="(currentSlide.question || '').length > 100" class="text-[10px] text-amber-400/90 flex items-center gap-1 mt-1">
                            <span>💡 Tips: Teks > 100 karakter akan mengecil otomatis agar muat jelas di layar.</span>
                        </div>
                    </div>

                    <!-- Description / Text Slide (Materi) Input -->
                    <div x-show="currentSlide.type !== 'content_paragraph'">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Deskripsi / Petunjuk (Opsional)</label>
                            <span class="text-[11px] font-mono text-slate-400">
                                <span x-text="(currentSlide.description || '').length"></span>/250
                            </span>
                        </div>
                        <input type="text" x-model="currentSlide.description" @input="debounceSaveSlide()" maxlength="250"
                               placeholder="Petunjuk singkat untuk peserta..."
                               class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>

                    <!-- Slide Teks/Materi (Text Slide) Input for content_paragraph -->
                    <div x-show="currentSlide.type === 'content_paragraph'">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Isi Teks Materi</label>
                            <span class="text-[11px] font-mono" :class="(currentSlide.description || '').length > 400 ? 'text-amber-400 font-bold' : 'text-slate-400'">
                                <span x-text="(currentSlide.description || '').length"></span>/800
                            </span>
                        </div>
                        <textarea x-model="currentSlide.description" @input="debounceSaveSlide()" rows="4" maxlength="800"
                                  placeholder="Tuliskan poin materi atau penjelasan presentasi di sini..."
                                  class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500"></textarea>
                        <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1">
                            <span>💡 Maksimal 800 karakter</span>
                            <span>Rekomendasi ideal: 300–400</span>
                        </div>
                    </div>

                    <!-- Quick Image Attachment in Content Tab -->
                    <div class="p-3.5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition-all space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-white block">Content Image (Gambar Materi / Soal)</span>
                                    <span class="text-[10px] text-slate-400 block">Tampilkan gambar ilustrasi di dalam slide</span>
                                </div>
                            </div>
                            <button type="button" @click="openImageModal('content')"
                                    class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-indigo-600 hover:text-white text-slate-300 border border-slate-700 flex items-center justify-center transition-all text-base font-bold shadow-sm"
                                    title="Tambah / Ganti Gambar Materi">
                                +
                            </button>
                        </div>
                        <div x-show="currentSlide.settings?.content_image" class="pt-2 border-t border-slate-800/80 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <img :src="currentSlide.settings?.content_image" alt="Preview" class="w-8 h-8 rounded-lg object-cover border border-slate-700 bg-slate-950 flex-shrink-0">
                                <span class="text-[11px] text-emerald-400 font-medium truncate">✓ Gambar Terpasang</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="openImageModal('content')" class="text-[11px] font-bold text-indigo-400 hover:text-indigo-300 px-2 py-0.5 rounded hover:bg-slate-800">
                                    Ganti
                                </button>
                                <button type="button" @click="removeImage('content')" class="text-[11px] font-bold text-rose-400 hover:text-rose-300 px-2 py-0.5 rounded hover:bg-slate-800">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Multiple Choice / Quiz Options Section -->
                    <template x-if="currentSlide.type === 'multiple_choice' || currentSlide.type === 'quiz'">
                        <div class="space-y-3 pt-2 border-t border-slate-800">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Pilihan Jawaban</span>
                                    <span class="text-[10px] text-slate-500">Maks. 60 karakter (1-3 kata ideal)</span>
                                </div>
                                <button @click="addOption()" class="text-xs font-bold text-indigo-400 hover:text-indigo-300">
                                    + Tambah Opsi
                                </button>
                            </div>

                            <div class="space-y-2.5">
                                <template x-for="(opt, optIdx) in (currentSlide.options || [])" :key="opt.id || optIdx">
                                    <div class="flex items-center gap-2 bg-slate-900 p-2 rounded-xl border border-slate-800">
                                        <input type="color" x-model="opt.color" @change="saveSlide()" 
                                               class="w-6 h-6 rounded border-0 bg-transparent cursor-pointer flex-shrink-0">
                                        <input type="text" x-model="opt.text" @input="debounceSaveSlide()" maxlength="60"
                                               placeholder="Teks opsi..."
                                               class="flex-1 bg-transparent text-xs font-medium text-white focus:outline-none">
                                        <span class="text-[10px] font-mono text-slate-500 px-1 flex-shrink-0" :class="(opt.text || '').length > 45 ? 'text-amber-400 font-bold' : ''">
                                            <span x-text="(opt.text || '').length"></span>/60
                                        </span>
                                        
                                        <!-- Correct toggle for Quiz -->
                                        <button type="button" @click="opt.is_correct = !opt.is_correct; saveSlide()"
                                                :class="opt.is_correct ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/50' : 'text-slate-500 border-slate-700'"
                                                class="px-2 py-1 rounded text-[10px] font-bold border transition-all flex-shrink-0" title="Tandai Kunci Jawaban Benar">
                                            ✓ Benar
                                        </button>

                                        <button @click="removeOption(optIdx)" class="text-slate-500 hover:text-rose-400 p-1 flex-shrink-0">
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
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Pernyataan Skala</span>
                                    <span class="text-[10px] text-slate-500">Maks. 60 karakter per pernyataan</span>
                                </div>
                                <button @click="addScaleStatement()" class="text-xs font-bold text-indigo-400 hover:text-indigo-300">
                                    + Tambah
                                </button>
                            </div>

                            <div class="space-y-2">
                                <template x-for="(stmt, stmtIdx) in (currentSlide.options || [])" :key="stmt.id || stmtIdx">
                                    <div class="flex items-center gap-2 bg-slate-900 p-2 rounded-xl border border-slate-800">
                                        <input type="text" x-model="stmt.text" @input="debounceSaveSlide()" maxlength="60"
                                               class="flex-1 bg-transparent text-xs font-medium text-white focus:outline-none">
                                        <span class="text-[10px] font-mono text-slate-500 px-1 flex-shrink-0">
                                            <span x-text="(stmt.text || '').length"></span>/60
                                        </span>
                                        <button @click="removeOption(stmtIdx)" class="text-slate-500 hover:text-rose-400 p-1 flex-shrink-0">
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
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Item Prioritas</span>
                                    <span class="text-[10px] text-slate-500">Maks. 60 karakter per item</span>
                                </div>
                                <button @click="addOption()" class="text-xs font-bold text-indigo-400 hover:text-indigo-300">
                                    + Tambah
                                </button>
                            </div>

                            <div class="space-y-2">
                                <template x-for="(item, itemIdx) in (currentSlide.options || [])" :key="item.id || itemIdx">
                                    <div class="flex items-center gap-2 bg-slate-900 p-2 rounded-xl border border-slate-800">
                                        <span class="text-xs text-slate-500 font-mono flex-shrink-0" x-text="itemIdx + 1"></span>
                                        <input type="text" x-model="item.text" @input="debounceSaveSlide()" maxlength="60"
                                               class="flex-1 bg-transparent text-xs font-medium text-white focus:outline-none">
                                        <span class="text-[10px] font-mono text-slate-500 px-1 flex-shrink-0">
                                            <span x-text="(item.text || '').length"></span>/60
                                        </span>
                                        <button @click="removeOption(itemIdx)" class="text-slate-500 hover:text-rose-400 p-1 flex-shrink-0">
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
                            <!-- 1. Royal Blue & Gold (Hanover & Tyke style) -->
                            <button @click="presentation.theme = 'royal_gold'; savePresentationMeta()"
                                    :class="presentation.theme === 'royal_gold' ? 'ring-2 ring-amber-400' : ''"
                                    class="p-3 rounded-2xl bg-slate-900 border border-slate-700 text-left flex flex-col gap-1.5 col-span-2 hover:border-amber-400/50 transition-all">
                                <div class="h-7 rounded-lg bg-gradient-to-r from-[#0047ba] via-[#002f87] to-[#001744] border border-blue-500/40 flex items-center justify-between px-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-blue-400 shadow-sm"></span>
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-sm"></span>
                                    </div>
                                    <span class="text-[10px] font-black text-amber-300 uppercase tracking-wider bg-amber-400/20 px-2 py-0.5 rounded border border-amber-400/30">Hanover Gold</span>
                                </div>
                                <span class="text-xs font-bold text-white flex items-center justify-between">
                                    <span>Royal Blue & Gold</span>
                                    <span class="text-[10px] text-amber-400 font-semibold">Biru Royal & Emas</span>
                                </span>
                            </button>

                            <!-- 2. Azure Corporate (Wardiere style) -->
                            <button @click="presentation.theme = 'azure_tech'; savePresentationMeta()"
                                    :class="presentation.theme === 'azure_tech' ? 'ring-2 ring-sky-400' : ''"
                                    class="p-3 rounded-2xl bg-slate-900 border border-slate-700 text-left flex flex-col gap-1 hover:border-sky-400/50 transition-all">
                                <div class="h-6 rounded-lg bg-gradient-to-r from-[#005be2] via-[#0082e6] to-[#00a8e8] border border-sky-400/40 flex items-center justify-end px-2">
                                    <span class="w-2 h-2 rounded-full bg-white shadow-sm"></span>
                                </div>
                                <span class="text-xs font-bold text-white flex items-center justify-between">
                                    <span>Azure Corporate</span>
                                    <span class="text-[9px] text-sky-400">Wardiere</span>
                                </span>
                            </button>

                            <!-- 3. Cobalt & Orange (Build Faster style) -->
                            <button @click="presentation.theme = 'cobalt_orange'; savePresentationMeta()"
                                    :class="presentation.theme === 'cobalt_orange' ? 'ring-2 ring-orange-500' : ''"
                                    class="p-3 rounded-2xl bg-slate-900 border border-slate-700 text-left flex flex-col gap-1 hover:border-orange-500/50 transition-all">
                                <div class="h-6 rounded-lg bg-gradient-to-r from-[#1e2cb8] via-[#2435c5] to-[#ff5e0e] border border-orange-500/40 flex items-center justify-between px-2">
                                    <span class="text-[8px] font-black text-white/70 uppercase">Blueprint</span>
                                    <span class="w-2 h-2 rounded-full bg-[#ff5e0e] shadow-sm"></span>
                                </div>
                                <span class="text-xs font-bold text-white flex items-center justify-between">
                                    <span>Cobalt & Orange</span>
                                    <span class="text-[9px] text-orange-400">Blueprint</span>
                                </span>
                            </button>

                            <!-- 4. Light Tech -->
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
                        <!-- Presentation Folder Selector -->
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-xs font-medium text-slate-300">Folder Presentasi</span>
                            <select x-model="presentation.folder_id" @change="savePresentationMeta()"
                                    class="px-2.5 py-1 bg-slate-900 border border-slate-800 rounded-lg text-xs font-bold text-white focus:outline-none">
                                <option value="">📂 Tanpa Folder</option>
                                @foreach($folders as $f)
                                    <option value="{{ $f->id }}">📁 {{ $f->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-xs font-medium text-slate-300">Status Voting Slide</span>
                            <select x-model="presentation.voting_status" @change="savePresentationMeta()"
                                    class="px-2.5 py-1 bg-slate-900 border border-slate-800 rounded-lg text-xs font-bold text-white focus:outline-none">
                                <option value="open">Buka (Terima Suara)</option>
                                <option value="closed">Tutup (Kunci Voting)</option>
                            </select>
                        </label>

                        <!-- Toggle Checkbox Header PIN -->
                        <div class="p-3.5 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition-all">
                            <label class="flex items-start justify-between gap-3 cursor-pointer">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-white">Header Instruksi PIN</span>
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider"
                                              :class="isHeaderVisible() ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-400'"
                                              x-text="isHeaderVisible() ? 'Tampil' : 'Hidden'"></span>
                                    </div>
                                    <span class="text-[11px] text-slate-400 block mt-0.5 leading-snug">
                                        Tampilkan logo, tautan URL, dan kode PIN di bagian atas slide
                                    </span>
                                </div>
                                <input type="checkbox" 
                                       :checked="isHeaderVisible()"
                                       @change="toggleSlideHeader()"
                                       class="w-4 h-4 mt-0.5 rounded text-indigo-600 bg-slate-950 border-slate-700 focus:ring-indigo-500 cursor-pointer">
                            </label>
                        </div>

                        <!-- Media & Gambar Slide (Mentimeter Style) -->
                        <div class="pt-4 border-t border-slate-800 space-y-2.5">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Tata Letak & Media Slide</span>

                            <!-- 1. Content image row -->
                            <div class="p-3 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition-all flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-slate-800 text-slate-300 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div>
                                        <span class="text-xs font-bold text-white block">Content image</span>
                                        <span class="text-[10px] text-slate-400 block" x-text="currentSlide.settings?.content_image ? 'Gambar terpasang' : 'Gambar ilustrasi soal/materi'"></span>
                                    </div>
                                </div>

                                <!-- Right side: Trash + Thumbnail OR (+) button -->
                                <div class="flex items-center gap-2">
                                    <template x-if="currentSlide.settings?.content_image">
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" @click="removeImage('content')" 
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors"
                                                    title="Hapus Content Image">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                            <button type="button" @click="openImageModal('content')" class="relative group" title="Ganti Content Image">
                                                <img :src="currentSlide.settings?.content_image" alt="Content" class="w-8 h-8 rounded-lg object-cover border border-slate-700 group-hover:border-indigo-500 transition-colors bg-slate-950">
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="!currentSlide.settings?.content_image">
                                        <button type="button" @click="openImageModal('content')"
                                                class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-indigo-600 hover:text-white text-slate-300 border border-slate-700 flex items-center justify-center transition-all text-base font-bold shadow-sm"
                                                title="Tambah Content Image">
                                            +
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- 2. Layout Row (Mentimeter Popover Selector) -->
                            <div class="relative" x-data="{ layoutPopoverOpen: false }">
                                <div class="p-3 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition-all flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-slate-800 text-slate-300 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                                                <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                                                <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                                                <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                                            </svg>
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold text-white block">Layout</span>
                                            <span class="text-[10px] text-slate-400 block" x-text="getLayoutLabel(getImageLayout())"></span>
                                        </div>
                                    </div>

                                    <!-- Button triggering Layout Popover with mini preview -->
                                    <button type="button" @click="layoutPopoverOpen = !layoutPopoverOpen"
                                            class="h-8 px-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 flex items-center justify-center transition-all shadow-sm group"
                                            title="Pilih Tata Letak Gambar">
                                        <!-- Active layout miniature preview -->
                                        <template x-if="getImageLayout() === 'center'">
                                            <svg class="w-6 h-4 text-slate-200" viewBox="0 0 24 16" fill="none">
                                                <line x1="4" y1="6" x2="20" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <line x1="7" y1="11" x2="17" y2="11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </template>
                                        <template x-if="getImageLayout() === 'banner'">
                                            <svg class="w-6 h-4 text-slate-200" viewBox="0 0 24 16" fill="none">
                                                <rect x="2" y="2" width="20" height="12" rx="2" fill="currentColor"/>
                                                <line x1="6" y1="6" x2="18" y2="6" stroke="#0f172a" stroke-width="2" stroke-linecap="round"/>
                                                <line x1="7" y1="10" x2="17" y2="10" stroke="#0f172a" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </template>
                                        <template x-if="getImageLayout() === 'left'">
                                            <svg class="w-6 h-4 text-slate-200" viewBox="0 0 24 16" fill="none">
                                                <rect x="2" y="3" width="9" height="10" rx="1.5" fill="currentColor"/>
                                                <line x1="14" y1="6" x2="22" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <line x1="14" y1="11" x2="19" y2="11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </template>
                                        <template x-if="getImageLayout() === 'right'">
                                            <svg class="w-6 h-4 text-slate-200" viewBox="0 0 24 16" fill="none">
                                                <line x1="2" y1="6" x2="10" y2="6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <line x1="5" y1="11" x2="10" y2="11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <rect x="13" y="3" width="9" height="10" rx="1.5" fill="currentColor"/>
                                            </svg>
                                        </template>
                                        <template x-if="getImageLayout() === 'sidebar_left'">
                                            <svg class="w-6 h-4 text-slate-200" viewBox="0 0 24 16" fill="none">
                                                <rect x="2" y="2" width="4" height="12" rx="1" fill="currentColor"/>
                                                <line x1="9" y1="5" x2="22" y2="5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="9" y1="9" x2="18" y2="9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="9" y1="13" x2="20" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            </svg>
                                        </template>
                                        <template x-if="getImageLayout() === 'sidebar_right'">
                                            <svg class="w-6 h-4 text-slate-200" viewBox="0 0 24 16" fill="none">
                                                <line x1="2" y1="5" x2="15" y2="5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="4" y1="9" x2="15" y2="9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="2" y1="13" x2="13" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <rect x="18" y="2" width="4" height="12" rx="1" fill="currentColor"/>
                                            </svg>
                                        </template>
                                        <template x-if="getImageLayout() === 'split_left'">
                                            <svg class="w-6 h-4 text-slate-200" viewBox="0 0 24 16" fill="none">
                                                <rect x="2" y="2" width="9" height="12" rx="1.5" fill="currentColor"/>
                                                <line x1="14" y1="5" x2="22" y2="5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="14" y1="9" x2="20" y2="9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="14" y1="13" x2="21" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            </svg>
                                        </template>
                                        <template x-if="getImageLayout() === 'split_right'">
                                            <svg class="w-6 h-4 text-slate-200" viewBox="0 0 24 16" fill="none">
                                                <line x1="2" y1="5" x2="10" y2="5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="4" y1="9" x2="10" y2="9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="2" y1="13" x2="9" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <rect x="13" y="2" width="9" height="12" rx="1.5" fill="currentColor"/>
                                            </svg>
                                        </template>
                                    </button>
                                </div>

                                <!-- Mentimeter-style 2x4 Popover Grid -->
                                <div x-show="layoutPopoverOpen" @click.away="layoutPopoverOpen = false" x-cloak
                                     class="absolute right-0 top-full mt-2 z-50 w-56 p-2.5 rounded-2xl bg-slate-900 border border-slate-700 shadow-2xl space-y-2">
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-1">
                                        Pilihan Layout Gambar
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <!-- 1. Center -->
                                        <button type="button" @click="setImageLayout('center'); layoutPopoverOpen = false"
                                                :class="getImageLayout() === 'center' ? 'ring-2 ring-indigo-500 border-indigo-500 bg-indigo-950/60' : 'border-slate-800 bg-slate-950/80 hover:border-slate-600'"
                                                class="h-11 rounded-xl border flex items-center justify-center p-2 transition-all group"
                                                title="Tengah (Default)">
                                            <svg class="w-7 h-5 text-slate-300 group-hover:text-white" viewBox="0 0 28 20" fill="none">
                                                <line x1="5" y1="8" x2="23" y2="8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                                <line x1="8" y1="13" x2="20" y2="13" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                                            </svg>
                                        </button>

                                        <!-- 2. Banner -->
                                        <button type="button" @click="setImageLayout('banner'); layoutPopoverOpen = false"
                                                :class="getImageLayout() === 'banner' ? 'ring-2 ring-indigo-500 border-indigo-500 bg-indigo-950/60' : 'border-slate-800 bg-slate-950/80 hover:border-slate-600'"
                                                class="h-11 rounded-xl border flex items-center justify-center p-2 transition-all group"
                                                title="Banner Atas">
                                            <svg class="w-7 h-5 text-slate-300 group-hover:text-white" viewBox="0 0 28 20" fill="none">
                                                <rect x="2" y="2" width="24" height="16" rx="2.5" fill="currentColor"/>
                                                <line x1="6" y1="8" x2="22" y2="8" stroke="#0f172a" stroke-width="2" stroke-linecap="round"/>
                                                <line x1="8" y1="13" x2="20" y2="13" stroke="#0f172a" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </button>

                                        <!-- 3. Left -->
                                        <button type="button" @click="setImageLayout('left'); layoutPopoverOpen = false"
                                                :class="getImageLayout() === 'left' ? 'ring-2 ring-indigo-500 border-indigo-500 bg-indigo-950/60' : 'border-slate-800 bg-slate-950/80 hover:border-slate-600'"
                                                class="h-11 rounded-xl border flex items-center justify-center p-2 transition-all group"
                                                title="Gambar Kiri">
                                            <svg class="w-7 h-5 text-slate-300 group-hover:text-white" viewBox="0 0 28 20" fill="none">
                                                <rect x="3" y="4" width="10" height="12" rx="2" fill="currentColor"/>
                                                <line x1="16" y1="8" x2="25" y2="8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <line x1="16" y1="13" x2="22" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </button>

                                        <!-- 4. Right -->
                                        <button type="button" @click="setImageLayout('right'); layoutPopoverOpen = false"
                                                :class="getImageLayout() === 'right' ? 'ring-2 ring-indigo-500 border-indigo-500 bg-indigo-950/60' : 'border-slate-800 bg-slate-950/80 hover:border-slate-600'"
                                                class="h-11 rounded-xl border flex items-center justify-center p-2 transition-all group"
                                                title="Gambar Kanan">
                                            <svg class="w-7 h-5 text-slate-300 group-hover:text-white" viewBox="0 0 28 20" fill="none">
                                                <line x1="3" y1="8" x2="12" y2="8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <line x1="6" y1="13" x2="12" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <rect x="15" y="4" width="10" height="12" rx="2" fill="currentColor"/>
                                            </svg>
                                        </button>

                                        <!-- 5. Sidebar Left -->
                                        <button type="button" @click="setImageLayout('sidebar_left'); layoutPopoverOpen = false"
                                                :class="getImageLayout() === 'sidebar_left' ? 'ring-2 ring-indigo-500 border-indigo-500 bg-indigo-950/60' : 'border-slate-800 bg-slate-950/80 hover:border-slate-600'"
                                                class="h-11 rounded-xl border flex items-center justify-center p-2 transition-all group"
                                                title="Sidebar Kiri">
                                            <svg class="w-7 h-5 text-slate-300 group-hover:text-white" viewBox="0 0 28 20" fill="none">
                                                <rect x="3" y="3" width="4" height="14" rx="1" fill="currentColor"/>
                                                <line x1="10" y1="6" x2="25" y2="6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="10" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="10" y1="14" x2="23" y2="14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            </svg>
                                        </button>

                                        <!-- 6. Sidebar Right -->
                                        <button type="button" @click="setImageLayout('sidebar_right'); layoutPopoverOpen = false"
                                                :class="getImageLayout() === 'sidebar_right' ? 'ring-2 ring-indigo-500 border-indigo-500 bg-indigo-950/60' : 'border-slate-800 bg-slate-950/80 hover:border-slate-600'"
                                                class="h-11 rounded-xl border flex items-center justify-center p-2 transition-all group"
                                                title="Sidebar Kanan">
                                            <svg class="w-7 h-5 text-slate-300 group-hover:text-white" viewBox="0 0 28 20" fill="none">
                                                <line x1="3" y1="6" x2="18" y2="6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="5" y1="10" x2="18" y2="10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="3" y1="14" x2="15" y2="14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <rect x="21" y="3" width="4" height="14" rx="1" fill="currentColor"/>
                                            </svg>
                                        </button>

                                        <!-- 7. Split Left -->
                                        <button type="button" @click="setImageLayout('split_left'); layoutPopoverOpen = false"
                                                :class="getImageLayout() === 'split_left' ? 'ring-2 ring-indigo-500 border-indigo-500 bg-indigo-950/60' : 'border-slate-800 bg-slate-950/80 hover:border-slate-600'"
                                                class="h-11 rounded-xl border flex items-center justify-center p-2 transition-all group"
                                                title="Split 50:50 Kiri">
                                            <svg class="w-7 h-5 text-slate-300 group-hover:text-white" viewBox="0 0 28 20" fill="none">
                                                <rect x="2" y="3" width="11" height="14" rx="2" fill="currentColor"/>
                                                <line x1="16" y1="6" x2="25" y2="6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="16" y1="10" x2="22" y2="10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="16" y1="14" x2="24" y2="14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            </svg>
                                        </button>

                                        <!-- 8. Split Right -->
                                        <button type="button" @click="setImageLayout('split_right'); layoutPopoverOpen = false"
                                                :class="getImageLayout() === 'split_right' ? 'ring-2 ring-indigo-500 border-indigo-500 bg-indigo-950/60' : 'border-slate-800 bg-slate-950/80 hover:border-slate-600'"
                                                class="h-11 rounded-xl border flex items-center justify-center p-2 transition-all group"
                                                title="Split 50:50 Kanan">
                                            <svg class="w-7 h-5 text-slate-300 group-hover:text-white" viewBox="0 0 28 20" fill="none">
                                                <line x1="3" y1="6" x2="12" y2="6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="6" y1="10" x2="12" y2="10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <line x1="4" y1="14" x2="11" y2="14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                                <rect x="15" y="3" width="11" height="14" rx="2" fill="currentColor"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Background image row -->
                            <div class="p-3 rounded-2xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition-all flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-slate-800 text-slate-300 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-bold text-white">Background image</span>
                                            <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] flex items-center justify-center font-bold">★</span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 block" x-text="currentSlide.settings?.background_image ? 'Background visual aktif' : 'Latar belakang visual slide'"></span>
                                    </div>
                                </div>

                                <!-- Right side: Trash + Thumbnail OR (+) button -->
                                <div class="flex items-center gap-2">
                                    <template x-if="currentSlide.settings?.background_image">
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" @click="removeImage('background')" 
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors"
                                                    title="Hapus Background Image">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                            <button type="button" @click="openImageModal('background')" class="relative group" title="Ganti Background Image">
                                                <img :src="currentSlide.settings?.background_image" alt="Background" class="w-8 h-8 rounded-lg object-cover border border-slate-700 group-hover:border-indigo-500 transition-colors bg-slate-950">
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="!currentSlide.settings?.background_image">
                                        <button type="button" @click="openImageModal('background')"
                                                class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-indigo-600 hover:text-white text-slate-300 border border-slate-700 flex items-center justify-center transition-all text-base font-bold shadow-sm"
                                                title="Tambah Background Image">
                                            +
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
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
                <p class="text-[11px] text-slate-500 mt-1">Audiens dapat membuka <strong class="text-slate-300" x-text="window.location.host + '/join'">{{ request()->getHost() }}/join</strong> dan memasukkan kode ini.</p>
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

    <!-- Add / Change Slide Image Modal (Upload or Web URL) -->
    <div x-show="imageModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="if (!isUploadingImage) imageModal = false" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl text-white">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-white" x-text="imageTarget === 'content' ? 'Tambah Content Image' : 'Tambah Background Image'"></h3>
                        <p class="text-xs text-slate-400" x-text="imageTarget === 'content' ? 'Gambar akan tampil sebagai ilustrasi materi di dalam slide.' : 'Gambar akan menjadi latar belakang visual di layar slide.'"></p>
                    </div>
                </div>
                <button type="button" @click="imageModal = false" :disabled="isUploadingImage" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
            </div>

            <!-- Error message if any -->
            <div x-show="uploadError" class="mb-4 p-3 rounded-xl bg-rose-500/20 border border-rose-500/40 text-rose-300 text-xs font-semibold" x-text="uploadError"></div>

            <!-- Option Tabs inside modal: Upload File vs Web URL -->
            <div class="space-y-4">
                <!-- 1. Drag & Drop / File Upload Box -->
                <div class="p-6 rounded-2xl border-2 border-dashed border-slate-700 hover:border-indigo-500 bg-slate-950/60 text-center transition-all cursor-pointer relative group">
                    <input type="file" accept="image/png,image/jpeg,image/jpg,image/webp,image/gif"
                           @change="uploadImageFile($event)"
                           :disabled="isUploadingImage"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    
                    <div class="space-y-2 pointer-events-none">
                        <div class="w-12 h-12 mx-auto rounded-2xl bg-indigo-600/10 text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </div>
                        <div>
                            <span class="text-sm font-bold text-white block">Pilih atau Seret Gambar dari Komputer</span>
                            <span class="text-xs text-slate-400 block mt-0.5">PNG, JPG, WEBP, GIF (Maks. 5MB)</span>
                        </div>
                    </div>

                    <!-- Uploading Spinner -->
                    <div x-show="isUploadingImage" class="absolute inset-0 bg-slate-950/90 rounded-2xl flex flex-col items-center justify-center gap-2">
                        <div class="w-7 h-7 border-3 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-xs font-bold text-indigo-300">Mengunggah gambar...</span>
                    </div>
                </div>

                <div class="relative flex items-center justify-center">
                    <div class="border-t border-slate-800 w-full"></div>
                    <span class="bg-slate-900 px-3 text-[11px] uppercase font-bold text-slate-500 tracking-wider absolute">atau tautan url</span>
                </div>

                <!-- 2. Web URL Input -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">URL Gambar Langsung</label>
                    <div class="flex items-center gap-2">
                        <input type="url" x-model="imageUrlInput"
                               placeholder="https://contoh.com/gambar.jpg"
                               class="flex-1 px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                        <button type="button" @click="saveImageUrl()"
                                :disabled="!imageUrlInput.trim()"
                                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 text-white text-xs font-bold rounded-xl transition-all shadow-md flex-shrink-0">
                            Gunakan URL
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-500">Anda dapat menyalin tautan gambar dari internet atau Google.</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="mt-6 pt-4 border-t border-slate-800 flex justify-end">
                <button type="button" @click="imageModal = false" :disabled="isUploadingImage"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl transition-all">
                    Batal
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
        imageModal: false,
        imageTarget: 'content',
        imageUrlInput: '',
        isUploadingImage: false,
        uploadError: null,

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

        resetPresentationResponses() {
            fetch('/presentations/' + this.presentation.id + '/reset', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                alert('Semua respon peserta berhasil direset (0 respon). Siap digunakan untuk kegiatan baru!');
                window.location.reload();
            })
            .catch(() => {
                alert('Gagal mereset respon');
            });
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
                case 'royal_gold':
                    return 'bg-gradient-to-b from-[#0047ba] via-[#002f87] to-[#001744] border-blue-400/40 text-white shadow-blue-900/30';
                case 'azure_tech':
                    return 'bg-gradient-to-br from-[#005be2] via-[#0082e6] to-[#00a8e8] border-sky-400/50 text-white shadow-sky-900/30';
                case 'cobalt_orange':
                    return 'bg-gradient-to-br from-[#1e2cb8] via-[#2435c5] to-[#16208a] border-indigo-400/40 text-white shadow-indigo-900/30';
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

        getThemeCanvasStyle() {
            if (this.currentSlide.settings?.background_image) {
                return 'background-image: linear-gradient(rgba(15,23,42,0.65), rgba(15,23,42,0.85)), url(' + this.currentSlide.settings.background_image + '); background-size: cover; background-position: center;';
            }
            if (this.presentation.theme === 'royal_gold') {
                return 'background-image: linear-gradient(to right, rgba(255,255,255,0.06) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.06) 1px, transparent 1px); background-size: 32px 32px;';
            }
            if (this.presentation.theme === 'cobalt_orange') {
                return 'background-image: linear-gradient(to right, rgba(255,255,255,0.08) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.08) 1px, transparent 1px); background-size: 24px 24px;';
            }
            return '';
        },

        isHeaderVisible() {
            if (!this.currentSlide) return true;
            if (!this.currentSlide.settings) return true;
            return this.currentSlide.settings.show_header !== false;
        },

        toggleSlideHeader() {
            if (!this.currentSlide) return;
            if (!this.currentSlide.settings) {
                this.currentSlide.settings = {};
            }
            this.currentSlide.settings.show_header = !this.isHeaderVisible();
            this.saveSlide();
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
                if (len > 100) return 'text-base sm:text-lg font-extrabold leading-snug break-words';
                if (len > 60) return 'text-lg sm:text-xl font-extrabold leading-snug break-words';
                return 'text-xl sm:text-2xl font-extrabold tracking-tight leading-tight break-words';
            }
            if (len > 100) return 'text-lg sm:text-2xl font-extrabold leading-snug break-words';
            if (len > 60) return 'text-xl sm:text-3xl font-extrabold leading-snug break-words';
            return 'text-xl sm:text-3xl font-extrabold tracking-tight break-words';
        },

        getDescriptionClasses() {
            const len = (this.currentSlide && this.currentSlide.description ? this.currentSlide.description.length : 0);
            if (len > 100) return 'text-[11px] sm:text-xs break-words';
            return 'text-xs sm:text-sm break-words';
        },

        getContentParagraphClasses() {
            const len = (this.currentSlide && this.currentSlide.description ? this.currentSlide.description.length : 0);
            if (len > 400) return 'text-[11px] sm:text-xs break-words';
            if (len > 200) return 'text-xs sm:text-sm break-words';
            return 'text-sm sm:text-base font-medium break-words';
        },

        setImageLayout(layout) {
            if (!this.currentSlide) return;
            this.currentSlide.settings = this.currentSlide.settings || {};
            this.currentSlide.settings.image_layout = layout;
            this.saveSlide();
        },

        getLayoutLabel(layout) {
            const map = {
                'center': 'Tengah (Default)',
                'banner': 'Banner Atas',
                'left': 'Gambar di Kiri',
                'right': 'Gambar di Kanan',
                'sidebar_left': 'Sidebar Kiri',
                'sidebar_right': 'Sidebar Kanan',
                'split_left': 'Split 50:50 Kiri',
                'split_right': 'Split 50:50 Kanan'
            };
            return map[layout] || 'Tengah (Default)';
        },

        getVisualizationType() {
            return (this.currentSlide && this.currentSlide.settings && (this.currentSlide.settings.visualization_type || this.currentSlide.settings.chart_type)) || 'bars';
        },

        setVisualizationType(type) {
            if (!this.currentSlide) return;
            this.currentSlide.settings = this.currentSlide.settings || {};
            this.currentSlide.settings.visualization_type = type;
            this.currentSlide.settings.chart_type = type;
            this.saveSlide();
        },

        isPercentageMode() {
            return Boolean(this.currentSlide && this.currentSlide.settings && this.currentSlide.settings.show_percentage);
        },

        togglePercentageMode() {
            if (!this.currentSlide) return;
            this.currentSlide.settings = this.currentSlide.settings || {};
            this.currentSlide.settings.show_percentage = !this.isPercentageMode();
            this.saveSlide();
        },

        isMultipleSelection() {
            return Boolean(this.currentSlide && this.currentSlide.settings && this.currentSlide.settings.multiple_selection);
        },

        toggleMultipleSelection() {
            if (!this.currentSlide) return;
            this.currentSlide.settings = this.currentSlide.settings || {};
            this.currentSlide.settings.multiple_selection = !this.isMultipleSelection();
            this.saveSlide();
        },

        hasRealVotes() {
            const opts = (this.currentSlide && this.currentSlide.options) || [];
            return opts.some(o => (Number(o.count) || 0) > 0);
        },

        getDisplayOptions(customOptions = null) {
            const rawOptions = customOptions || (this.currentSlide && this.currentSlide.options) || [];
            if (!rawOptions || rawOptions.length === 0) {
                return [
                    { id: 1, text: 'Opsi 1', color: '#6366f1', count: 2, displayValue: this.isPercentageMode() ? '40%' : '2', barHeight: 80, dotCount: 3 },
                    { id: 2, text: 'Opsi 2', color: '#ef4444', count: 2, displayValue: this.isPercentageMode() ? '40%' : '2', barHeight: 80, dotCount: 3 },
                    { id: 3, text: 'Opsi 3', color: '#1e293b', count: 1, displayValue: this.isPercentageMode() ? '20%' : '1', barHeight: 40, dotCount: 2 }
                ];
            }

            const totalVotes = rawOptions.reduce((sum, o) => sum + (Number(o.count) || 0), 0);
            const isSample = totalVotes === 0;
            const sampleCounts = [2, 2, 1, 3, 2, 4];
            const maxVal = isSample ? Math.max(...sampleCounts.slice(0, rawOptions.length)) : Math.max(1, ...rawOptions.map(o => Number(o.count) || 0));

            return rawOptions.map((opt, idx) => {
                const count = isSample ? (sampleCounts[idx % sampleCounts.length] || 2) : (Number(opt.count) || 0);
                const percent = totalVotes > 0 ? Math.round((count / totalVotes) * 100) : (isSample ? Math.round((count / 5) * 100) : 0);
                const displayValue = this.isPercentageMode() ? (percent + '%') : count;
                const barHeight = Math.max(10, Math.round((count / maxVal) * 82));

                return {
                    id: opt.id || idx,
                    text: opt.text || ('Opsi ' + (idx + 1)),
                    color: opt.color || ['#6366f1', '#ef4444', '#1e293b', '#10b981', '#f59e0b', '#8b5cf6'][idx % 6],
                    is_correct: opt.is_correct,
                    count: count,
                    percentage: percent,
                    displayValue: displayValue,
                    barHeight: barHeight,
                    dotCount: Math.min(8, Math.max(1, count))
                };
            });
        },

        getPieSlices(isDonut = false, customOptions = null) {
            const displayOpts = this.getDisplayOptions(customOptions);
            if (!displayOpts || displayOpts.length === 0) return [];

            const total = displayOpts.reduce((sum, o) => sum + (Number(o.count) || 1), 0);
            let currentAngle = -90;
            const slices = [];
            const cx = 120, cy = 120;
            const rOuter = 100;
            const rInner = isDonut ? 52 : 0;

            displayOpts.forEach((opt, idx) => {
                const val = Number(opt.count) || 1;
                const percent = total > 0 ? (val / total) : (1 / displayOpts.length);
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
                    color: opt.color,
                    text: opt.displayValue,
                    textX: textX,
                    textY: textY,
                    showText: angleSize > 20
                });
            });

            return slices;
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
                            ' A ' + rOuter + ' ' + rOuter + ' 0 1 1 ' + cx + ' ' + (cy - rOuter) + ' Z';
                    } else {
                        d = 'M ' + cx + ' ' + cy + 
                            ' L ' + x1Outer + ' ' + y1Outer + 
                            ' A ' + rOuter + ' ' + rOuter + ' 0 ' + largeArc + ' 1 ' + x2Outer + ' ' + y2Outer + ' Z';
                    }
                }

                pathsSvg += '<path d="' + d + '" fill="' + color + '" stroke="rgba(15,23,42,0.4)" stroke-width="2" class="transition-all duration-700" />\n';

                if (angleSize > 25 && (val > 0 || isZero)) {
                    const textR = isDonut ? (rOuter + rInner) / 2 : rOuter * 0.65;
                    const textX = Number((cx + textR * Math.cos(midRad)).toFixed(2));
                    const textY = Number((cy + textR * Math.sin(midRad)).toFixed(2));
                    const textVal = isPercent ? (Math.round((val / total) * 100) + '%') : val;
                    textsSvg += '<text x="' + textX + '" y="' + textY + '" fill="#ffffff" font-size="13" font-weight="900" font-family="monospace" text-anchor="middle" dominant-baseline="central">' + textVal + '</text>\n';
                }
            });

            if (isDonut && total > 0) {
                textsSvg += '<text x="' + cx + '" y="' + (cy - 7) + '" fill="rgba(255,255,255,0.6)" font-size="10" font-weight="700" text-anchor="middle" dominant-baseline="central">TOTAL</text>\n' +
                            '<text x="' + cx + '" y="' + (cy + 10) + '" fill="#ffffff" font-size="16" font-weight="900" font-family="monospace" text-anchor="middle" dominant-baseline="central">' + total + '</text>\n';
            }

            return '<svg viewBox="0 0 240 240" class="w-full h-full drop-shadow-2xl" style="overflow:visible;">\n' + pathsSvg + textsSvg + '</svg>';
        },

        openImageModal(target) {
            this.imageTarget = target;
            this.imageUrlInput = (this.currentSlide.settings && this.currentSlide.settings[target + '_image']) || '';
            this.uploadError = null;
            this.imageModal = true;
        },

        uploadImageFile(event) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('image', file);
            formData.append('target', this.imageTarget);

            this.isUploadingImage = true;
            this.uploadError = null;

            fetch('/slides/' + this.currentSlide.id + '/image', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                this.isUploadingImage = false;
                if (!ok) {
                    this.uploadError = data.message || 'Gagal mengunggah gambar.';
                    return;
                }
                this.currentSlide.settings = data.settings;
                this.imageModal = false;
            })
            .catch(() => {
                this.isUploadingImage = false;
                this.uploadError = 'Terjadi kesalahan jaringan saat mengunggah.';
            });
        },

        saveImageUrl() {
            if (!this.imageUrlInput.trim()) return;
            this.currentSlide.settings = this.currentSlide.settings || {};
            this.currentSlide.settings[this.imageTarget + '_image'] = this.imageUrlInput.trim();
            this.saveSlide();
            this.imageModal = false;
        },

        removeImage(target) {
            if (!confirm('Hapus gambar ini dari slide?')) return;
            fetch('/slides/' + this.currentSlide.id + '/remove-image', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ target: target })
            })
            .then(r => r.json())
            .then(data => {
                this.currentSlide.settings = data.settings;
            });
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
                    folder_id: this.presentation.folder_id || null,
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
