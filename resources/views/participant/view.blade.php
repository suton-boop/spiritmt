<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $presentation->title }} - Peserta SpiritMeter</title>
    <link rel="icon" type="image/png" href="{{ asset('image/logo spiritmeter.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        [x-cloak] { display: none !important; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col justify-between overflow-x-hidden transition-colors duration-300"
      :class="state.theme === 'light_tech' ? 'bg-[#f0f6ff] text-slate-900' : 'bg-slate-950 text-white'"
      x-data="participantApp('{{ $presentation->access_code }}', '{{ $nickname }}', '{{ $avatar }}', '{{ csrf_token() }}')">

    <!-- Top Participant Header -->
    <header class="px-4 py-3 sticky top-0 z-30 flex items-center justify-between border-b transition-colors"
            :class="state.theme === 'light_tech' ? 'bg-white/95 border-blue-100 backdrop-blur-md shadow-sm' : 'bg-slate-900 border-slate-800'">
        <div class="flex items-center gap-2.5 truncate max-w-[65%]">
            <span class="w-7 h-7 rounded-lg p-0.5 flex items-center justify-center flex-shrink-0"
                  :class="state.theme === 'light_tech' ? 'bg-white border border-blue-200 shadow-sm' : 'bg-slate-950 border border-slate-800'">
                <img src="{{ asset('image/logo spiritmeter.png') }}" alt="Logo SpiritMeter" class="w-full h-full object-contain">
            </span>
            <span class="text-xs sm:text-sm font-bold truncate"
                  :class="state.theme === 'light_tech' ? 'text-slate-800' : 'text-slate-200'"
                  x-text="state.title || 'SpiritMeter'"></span>
        </div>

        <div class="flex items-center gap-2">
            <!-- Ask Question Floating / Modal trigger -->
            <button @click="showQaModal = true" class="p-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold flex items-center gap-1">
                <span>🙋</span>
                <span class="hidden sm:inline">Q&A</span>
            </button>

            <!-- Participant Avatar & Nickname Button -->
            <button @click="showProfileModal = true" class="px-2.5 py-1 bg-indigo-950 border border-indigo-800/80 rounded-xl text-xs font-bold flex items-center gap-1.5 text-indigo-300">
                <span x-text="avatar"></span>
                <span class="max-w-[80px] truncate" x-text="nickname || 'Peserta'"></span>
            </button>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 max-w-lg w-full mx-auto p-4 sm:p-6 flex flex-col justify-center">

        <!-- 1. VOTING CLOSED NOTICE -->
        <div x-show="state.voting_status === 'closed' && !hasVoted" 
             class="bg-amber-500/20 border border-amber-500/40 rounded-3xl p-6 text-center mb-4">
            <span class="text-3xl mb-2 block">🔒</span>
            <h3 class="text-lg font-black text-amber-200">Voting Ditutup</h3>
            <p class="text-xs text-amber-300/80 mt-1">Presenter telah menutup pengumpulan respon untuk slide ini.</p>
        </div>

        <!-- 2. ALREADY VOTED STATE (Waiting / Confirmation screen) -->
        <div x-show="hasVoted" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 text-center shadow-xl space-y-4">
            <div class="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-3xl mx-auto">
                ✓
            </div>
            
            <div>
                <h2 class="text-xl font-black text-white">Jawaban Terkirim!</h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">Terima kasih atas partisipasi Anda.</p>
            </div>

            <div class="py-4 border-y border-slate-800 flex items-center justify-center gap-2 text-xs text-slate-400">
                <div class="w-2 h-2 rounded-full bg-indigo-500 animate-ping"></div>
                <span>Menunggu presenter beralih ke slide berikutnya...</span>
            </div>

            <div class="pt-2">
                <button @click="showQaModal = true" class="w-full py-3 px-4 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition-all">
                    🙋 Punya Pertanyaan? Tanyakan di Q&A
                </button>
            </div>
        </div>

        <!-- 3. ACTIVE QUESTION FORM (When not voted yet) -->
        <div x-show="!hasVoted && state.voting_status === 'open'" class="space-y-6">
            
            <!-- Slide Question Header -->
            <div class="text-center space-y-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-400 bg-indigo-950/60 px-3 py-1 rounded-full border border-indigo-800/60">
                    <span x-text="getTypeBadge(currentSlide.type)"></span>
                </span>
                
                <h1 class="text-xl sm:text-2xl font-black text-white leading-snug"
                    x-text="currentSlide.question || 'Pertanyaan'"></h1>

                <p class="text-xs text-slate-400" x-show="currentSlide.description" x-text="currentSlide.description"></p>

                <!-- Content Image if any -->
                <div x-show="currentSlide.settings?.content_image" class="mt-3 max-w-sm mx-auto">
                    <img :src="currentSlide.settings?.content_image" alt="Ilustrasi Soal" 
                         class="max-h-48 w-auto mx-auto rounded-2xl object-contain shadow-lg border border-slate-700 bg-slate-900/60">
                </div>
            </div>

            <!-- Quiz Timer Bar (if Quiz) -->
            <div x-show="currentSlide.type === 'quiz'" class="w-full bg-slate-900 h-2.5 rounded-full overflow-hidden p-0.5 border border-slate-800">
                <div class="bg-amber-400 h-full rounded-full transition-all duration-1000"
                     :style="'width: ' + quizTimerPercent + '%;'"></div>
            </div>

            <!-- FORM WIDGETS BY TYPE -->

            <!-- A. MULTIPLE CHOICE / QUIZ -->
            <div x-show="currentSlide.type === 'multiple_choice' || currentSlide.type === 'quiz'" class="space-y-3">
                <template x-for="(opt, idx) in (currentSlide.options || [])" :key="opt.id || idx">
                    <button @click="submitSingleChoice(opt.id)"
                            :disabled="submitting"
                            :class="state.theme === 'light_tech' 
                                ? 'bg-white border-2 border-blue-100 hover:border-blue-500 hover:shadow-lg hover:shadow-blue-500/5 text-slate-900 shadow-sm' 
                                : 'bg-slate-900 border-2 border-slate-800 hover:border-indigo-500 text-white'"
                            class="w-full p-4 rounded-2xl active:scale-[0.98] transition-all text-left flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <span class="w-3.5 h-3.5 rounded-full flex-shrink-0 shadow-sm" :style="'background-color: ' + (opt.color || '#2563eb')"></span>
                            <span class="text-sm font-bold"
                                  :class="state.theme === 'light_tech' ? 'text-slate-900 group-hover:text-blue-600' : 'text-white group-hover:text-indigo-300'"
                                  x-text="opt.text"></span>
                        </div>
                        <span class="text-xs font-mono"
                              :class="state.theme === 'light_tech' ? 'text-blue-600 font-bold' : 'text-slate-500'">Pilih</span>
                    </button>
                </template>
            </div>

            <!-- B. WORD CLOUD (Up to 3 inputs) -->
            <div x-show="currentSlide.type === 'word_cloud'" class="space-y-4">
                <div class="space-y-2.5">
                    <input type="text" x-model="wordInputs[0]" placeholder="Kata pertama (wajib)..." maxlength="30"
                           class="w-full px-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-sm font-semibold text-white focus:outline-none focus:border-indigo-500">
                    <input type="text" x-model="wordInputs[1]" placeholder="Kata kedua (opsional)..." maxlength="30"
                           class="w-full px-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-sm font-semibold text-white focus:outline-none focus:border-indigo-500">
                    <input type="text" x-model="wordInputs[2]" placeholder="Kata ketiga (opsional)..." maxlength="30"
                           class="w-full px-4 py-3 bg-slate-900 border border-slate-800 rounded-xl text-sm font-semibold text-white focus:outline-none focus:border-indigo-500">
                </div>

                <button @click="submitWordCloud()" :disabled="!wordInputs[0] || submitting"
                        class="w-full py-4 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 text-white font-extrabold rounded-2xl shadow-xl shadow-indigo-600/30 transition-all text-sm">
                    Kirim Kata &rarr;
                </button>
            </div>

            <!-- C. SCALES (Likert Rating 1-5) -->
            <div x-show="currentSlide.type === 'scales'" class="space-y-4">
                <template x-for="(stmt, idx) in (currentSlide.options || [])" :key="stmt.id || idx">
                    <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800 space-y-2">
                        <div class="text-xs sm:text-sm font-bold text-white" x-text="stmt.text"></div>
                        <div class="flex items-center justify-between gap-1 pt-1">
                            <template x-for="val in [1, 2, 3, 4, 5]" :key="val">
                                <button type="button" @click="scaleAnswers[stmt.id] = val"
                                        :class="scaleAnswers[stmt.id] === val ? 'bg-indigo-600 text-white scale-105' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                                        class="flex-1 py-2 rounded-xl text-xs font-bold transition-all" x-text="val"></button>
                            </template>
                        </div>
                    </div>
                </template>

                <button @click="submitScales()" :disabled="submitting"
                        class="w-full py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-2xl shadow-xl shadow-indigo-600/30 transition-all text-sm">
                    Kirim Penilaian &rarr;
                </button>
            </div>

            <!-- D. RANKING -->
            <div x-show="currentSlide.type === 'ranking'" class="space-y-3">
                <p class="text-xs text-slate-400 text-center">Ketuk urutan prioritas dari nomor 1 ke bawah:</p>
                <template x-for="(item, idx) in (currentSlide.options || [])" :key="item.id || idx">
                    <button @click="toggleRankingItem(item.id)"
                            class="w-full p-3.5 rounded-2xl bg-slate-900 border border-slate-800 text-left flex items-center justify-between">
                        <span class="text-sm font-bold text-white" x-text="item.text"></span>
                        <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white font-mono font-bold flex items-center justify-center text-xs"
                              x-text="getRankingNumber(item.id)"></span>
                    </button>
                </template>

                <button @click="submitRanking()" :disabled="submitting || rankingList.length === 0"
                        class="w-full py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold rounded-2xl shadow-xl shadow-indigo-600/30 transition-all text-sm">
                    Kirim Urutan Prioritas &rarr;
                </button>
            </div>

            <!-- E. OPEN ENDED -->
            <div x-show="currentSlide.type === 'open_ended'" class="space-y-3">
                <textarea x-model="openEndedAnswer" rows="4" placeholder="Tuliskan jawaban atau pendapat Anda di sini..."
                          class="w-full p-4 bg-slate-900 border border-slate-800 rounded-2xl text-sm font-medium text-white focus:outline-none focus:border-indigo-500"></textarea>

                <button @click="submitOpenEnded()" :disabled="!openEndedAnswer.trim() || submitting"
                        class="w-full py-4 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 text-white font-extrabold rounded-2xl shadow-xl shadow-indigo-600/30 transition-all text-sm">
                    Kirim Jawaban &rarr;
                </button>
            </div>

            <!-- F. CONTENT SLIDE / LEADERBOARD (Informative only) -->
            <div x-show="currentSlide.type === 'content_heading' || currentSlide.type === 'content_paragraph' || currentSlide.type === 'leaderboard'" 
                 class="bg-slate-900 border border-slate-800 rounded-3xl p-6 text-center space-y-3">
                <span class="text-3xl block">👀</span>
                <h3 class="text-base font-bold text-white">Lihat Layar Utama Presenter</h3>
                <p class="text-xs text-slate-400">Slide ini adalah materi informasi atau papan leaderboard juara. Perhatikan layar depan presenter!</p>
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="py-3 px-4 text-center text-[11px] text-slate-600 border-t border-slate-900 bg-slate-950">
        SpiritMeter &bull; Live Interactive Audience
    </footer>

    <!-- PARTICIPANT PROFILE MODAL (Nickname & Avatar) -->
    <div x-show="showProfileModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="showProfileModal = false" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-sm w-full text-white">
            <h3 class="text-lg font-black mb-1">Profil Anda</h3>
            <p class="text-xs text-slate-400 mb-4">Pilih nama dan avatar emoji untuk kuis & Q&A.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Panggilan</label>
                    <input type="text" x-model="nicknameInput" maxlength="30"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-sm font-semibold text-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pilih Avatar</label>
                    <div class="grid grid-cols-6 gap-2 text-2xl">
                        <template x-for="emo in ['🦊', '🦄', '🐼', '🦁', '🚀', '⚡', '🦉', '🐱', '🐶', '🐯', '🎯', '🌟']" :key="emo">
                            <button @click="avatarInput = emo"
                                    :class="avatarInput === emo ? 'bg-indigo-600 scale-110' : 'bg-slate-800 hover:bg-slate-700'"
                                    class="h-10 rounded-xl flex items-center justify-center transition-all" x-text="emo"></button>
                        </template>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button @click="showProfileModal = false" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-white">Batal</button>
                    <button @click="saveProfile()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- AUDIENCE Q&A MODAL (Ask & Upvote) -->
    <div x-show="showQaModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
        <div @click.away="showQaModal = false" class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-md w-full text-white flex flex-col max-h-[85vh]">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
                <h3 class="text-base font-black">Audience Q&A 💬</h3>
                <button @click="showQaModal = false" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
            </div>

            <!-- Submit Question Box -->
            <div class="mb-4 space-y-2">
                <textarea x-model="newQuestionText" rows="2" placeholder="Ajukan pertanyaan Anda kepada presenter..."
                          class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500"></textarea>
                <button @click="submitQuestion()" :disabled="!newQuestionText.trim()"
                        class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 text-white text-xs font-bold rounded-xl transition-all">
                    Kirim Pertanyaan
                </button>
            </div>

            <!-- Questions List -->
            <div class="flex-1 overflow-y-auto space-y-2.5 pr-1">
                <template x-for="q in (state.questions || [])" :key="q.id">
                    <div class="p-3 bg-slate-950 rounded-xl border border-slate-800/80 flex items-center justify-between gap-3">
                        <div class="text-xs">
                            <p class="font-bold text-white" x-text="q.question_text"></p>
                            <span class="text-[10px] text-slate-400" x-text="'Oleh ' + (q.participant_name || 'Anonim')"></span>
                        </div>
                        <button @click="upvoteQuestion(q.id)" class="px-2.5 py-1 rounded-lg bg-indigo-950 text-indigo-300 border border-indigo-800 hover:bg-indigo-900 font-bold text-xs flex items-center gap-1">
                            ▲ <span x-text="q.upvotes"></span>
                        </button>
                    </div>
                </template>

                <div x-show="!state.questions || state.questions.length === 0" class="text-center py-6 text-xs text-slate-500">
                    Belum ada pertanyaan. Jadilah yang pertama bertanya!
                </div>
            </div>
        </div>
    </div>

    <script>
    function participantApp(accessCode, initialNickname, initialAvatar, csrfToken) {
        return {
            accessCode: accessCode,
            nickname: initialNickname || 'Peserta',
            avatar: initialAvatar || '🦊',
            nicknameInput: initialNickname || 'Peserta',
            avatarInput: initialAvatar || '🦊',
            state: {
                title: '',
                voting_status: 'open',
                current_slide_id: null,
                questions: []
            },
            currentSlide: {},
            hasVoted: false,
            submitting: false,
            startTime: Date.now(),
            showProfileModal: false,
            showQaModal: false,
            newQuestionText: '',

            // Inputs
            wordInputs: ['', '', ''],
            scaleAnswers: {},
            rankingList: [],
            openEndedAnswer: '',
            quizTimerPercent: 100,

            init() {
                this.fetchState();
                // Polling state every 1.5s
                setInterval(() => {
                    this.fetchState();
                }, 1500);
            },

            fetchState() {
                fetch('/m/' + this.accessCode + '/state')
                    .then(r => r.json())
                    .then(data => {
                        const previousSlideId = this.state.current_slide_id;
                        this.state = data;
                        this.currentSlide = data.current_slide || {};

                        // If presenter switched slide, reset local answer state!
                        if (previousSlideId !== null && previousSlideId !== data.current_slide_id) {
                            this.hasVoted = data.has_voted;
                            this.startTime = Date.now();
                            this.wordInputs = ['', '', ''];
                            this.scaleAnswers = {};
                            this.rankingList = [];
                            this.openEndedAnswer = '';
                        } else {
                            this.hasVoted = data.has_voted;
                        }
                    });
            },

            submitSingleChoice(optionId) {
                this.sendAnswer(optionId);
            },

            submitWordCloud() {
                const words = this.wordInputs.filter(w => w.trim() !== '');
                if (words.length > 0) {
                    this.sendAnswer(words);
                }
            },

            submitScales() {
                this.sendAnswer(this.scaleAnswers);
            },

            toggleRankingItem(id) {
                const idx = this.rankingList.indexOf(id);
                if (idx > -1) {
                    this.rankingList.splice(idx, 1);
                } else {
                    this.rankingList.push(id);
                }
            },

            getRankingNumber(id) {
                const idx = this.rankingList.indexOf(id);
                return idx > -1 ? '#' + (idx + 1) : '-';
            },

            submitRanking() {
                this.sendAnswer(this.rankingList);
            },

            submitOpenEnded() {
                this.sendAnswer(this.openEndedAnswer);
            },

            sendAnswer(val) {
                this.submitting = true;
                const elapsedMs = Date.now() - this.startTime;

                fetch('/m/' + this.accessCode + '/answer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        slide_id: this.currentSlide.id,
                        value: val,
                        response_time_ms: elapsedMs,
                        name: this.nickname
                    })
                }).then(r => r.json()).then(data => {
                    this.submitting = false;
                    this.hasVoted = true;
                }).catch(() => {
                    this.submitting = false;
                });
            },

            submitQuestion() {
                if (!this.newQuestionText.trim()) return;

                fetch('/m/' + this.accessCode + '/question', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        question_text: this.newQuestionText
                    })
                }).then(r => r.json()).then(data => {
                    this.newQuestionText = '';
                    this.fetchState();
                });
            },

            upvoteQuestion(questionId) {
                fetch('/m/' + this.accessCode + '/question/' + questionId + '/upvote', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                }).then(() => this.fetchState());
            },

            saveProfile() {
                fetch('/m/' + this.accessCode + '/profile', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        nickname: this.nicknameInput,
                        avatar: this.avatarInput
                    })
                }).then(r => r.json()).then(data => {
                    this.nickname = data.nickname;
                    this.avatar = data.avatar;
                    this.showProfileModal = false;
                });
            },

            getTypeBadge(type) {
                const map = {
                    'multiple_choice': 'Pilihan Ganda',
                    'word_cloud': 'Word Cloud',
                    'quiz': 'Kuis Cepat',
                    'scales': 'Rating Skala',
                    'ranking': 'Urutan Prioritas',
                    'open_ended': 'Teks Bebas',
                    'qa': 'Q&A',
                    'leaderboard': 'Papan Peringkat'
                };
                return map[type] || 'Polling';
            }
        }
    }
    </script>
</body>
</html>
