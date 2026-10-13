<?php

namespace Database\Seeders;

use App\Models\AudienceQuestion;
use App\Models\Folder;
use App\Models\Presentation;
use App\Models\QuizParticipant;
use App\Models\Response;
use App\Models\Slide;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@spiritmeter.com'],
            [
                'name' => 'Admin SpiritMeter',
                'password' => Hash::make('password'),
                'is_superadmin' => true,
            ]
        );

        $folderIcebreaker = Folder::create([
            'user_id' => $user->id,
            'name' => 'Icebreakers & Meetings',
            'color' => '#8b5cf6',
        ]);

        $folderEducation = Folder::create([
            'user_id' => $user->id,
            'name' => 'Workshop & Training',
            'color' => '#10b981',
        ]);

        // Presentation 1: Team Icebreaker
        $p1 = Presentation::create([
            'user_id' => $user->id,
            'folder_id' => $folderIcebreaker->id,
            'title' => 'Team Icebreaker & Weekly Check-in',
            'access_code' => '12345678',
            'theme' => 'default',
            'voting_status' => 'open',
            'show_results' => true,
            'is_presenting' => false,
            'allow_questions' => true,
        ]);

        // Slide 1.1: Content Heading
        $s1_1 = Slide::create([
            'presentation_id' => $p1->id,
            'order_index' => 1,
            'type' => 'content_heading',
            'question' => 'Selamat Datang di Sesi Interaktif SpiritMeter! 🚀',
            'description' => 'Buka spiritmeter.test/join di ponsel Anda atau scan QR code untuk berpartisipasi langsung secara real-time.',
            'options' => [],
            'settings' => ['theme' => 'default'],
        ]);

        // Slide 1.2: Word Cloud
        $s1_2 = Slide::create([
            'presentation_id' => $p1->id,
            'order_index' => 2,
            'type' => 'word_cloud',
            'question' => 'Gambarkan perasaan atau energimu hari ini dalam 1 kata!',
            'description' => 'Ketik hingga 3 kata di ponsel Anda',
            'options' => [],
            'settings' => ['max_words' => 3],
        ]);

        // Slide 1.3: Multiple Choice
        $s1_3 = Slide::create([
            'presentation_id' => $p1->id,
            'order_index' => 3,
            'type' => 'multiple_choice',
            'question' => 'Dari mana kamu bekerja atau mengikuti sesi hari ini?',
            'description' => 'Pilih salah satu opsi di bawah ini',
            'options' => [
                ['id' => 'opt_1', 'text' => 'Kantor / On-site 🏢', 'color' => '#6366f1', 'is_correct' => false],
                ['id' => 'opt_2', 'text' => 'Rumah / WFH 🏠', 'color' => '#10b981', 'is_correct' => false],
                ['id' => 'opt_3', 'text' => 'Kafe / Coworking Space ☕', 'color' => '#f59e0b', 'is_correct' => false],
                ['id' => 'opt_4', 'text' => 'Luar Kota / Traveling ✈️', 'color' => '#ec4899', 'is_correct' => false],
            ],
            'settings' => ['chart_type' => 'bars', 'show_percentage' => true],
        ]);

        // Slide 1.4: Scales
        $s1_4 = Slide::create([
            'presentation_id' => $p1->id,
            'order_index' => 4,
            'type' => 'scales',
            'question' => 'Evaluasi Semangat dan Kesiapan Tim Minggu Ini',
            'description' => 'Beri penilaian skala 1 (Sangat Rendah) hingga 5 (Sangat Tinggi)',
            'options' => [
                ['id' => 'scale_1', 'text' => 'Fokus pada target utama sprint'],
                ['id' => 'scale_2', 'text' => 'Kolaborasi dan komunikasi lancar'],
                ['id' => 'scale_3', 'text' => 'Tingkat energi & antusiasme tim'],
            ],
            'settings' => ['low_label' => 'Perlu Ditingkatkan', 'high_label' => 'Sangat Baik'],
        ]);

        // Slide 1.5: Open Ended
        $s1_5 = Slide::create([
            'presentation_id' => $p1->id,
            'order_index' => 5,
            'type' => 'open_ended',
            'question' => 'Apa satu hal seru atau pencapaian terbaikmu minggu lalu?',
            'description' => 'Bagikan cerita singkatmu di sini',
            'options' => [],
            'settings' => ['layout' => 'cards'],
        ]);

        // Slide 1.6: Q&A
        $s1_6 = Slide::create([
            'presentation_id' => $p1->id,
            'order_index' => 6,
            'type' => 'qa',
            'question' => 'Sesi Tanya Jawab Terbuka (Q&A) 💬',
            'description' => 'Kirim pertanyaan dan dukung pertanyaan teman dengan tombol upvote',
            'options' => [],
            'settings' => ['allow_anonymous' => true],
        ]);

        $p1->update(['current_slide_id' => $s1_1->id]);

        // Seed some sample responses for Presentation 1
        $sampleWordResponses = [
            ['Semangat', 'Fokus', 'Optimis'],
            ['Kopi', 'Semangat', 'Bugar'],
            ['Ngantuk', 'Produktif', 'Semangat'],
            ['Bahagia', 'Ceria', 'Fokus'],
            ['Kreatif', 'Siap', 'Semangat'],
            ['Semangat', 'Inovatif', 'Tenang'],
        ];
        foreach ($sampleWordResponses as $idx => $words) {
            Response::create([
                'presentation_id' => $p1->id,
                'slide_id' => $s1_2->id,
                'participant_session' => 'sess_seed_'.$idx,
                'participant_name' => 'Peserta '.($idx + 1),
                'response_value' => $words,
            ]);
        }

        // Multiple choice sample responses
        $mcOptions = ['opt_1', 'opt_1', 'opt_2', 'opt_2', 'opt_2', 'opt_3', 'opt_4'];
        foreach ($mcOptions as $idx => $opt) {
            Response::create([
                'presentation_id' => $p1->id,
                'slide_id' => $s1_3->id,
                'participant_session' => 'sess_seed_'.$idx,
                'participant_name' => 'Peserta '.($idx + 1),
                'response_value' => [$opt],
            ]);
        }

        // Scales sample responses
        for ($i = 0; $i < 5; $i++) {
            Response::create([
                'presentation_id' => $p1->id,
                'slide_id' => $s1_4->id,
                'participant_session' => 'sess_seed_'.$i,
                'participant_name' => 'Peserta '.($i + 1),
                'response_value' => [
                    'scale_1' => rand(3, 5),
                    'scale_2' => rand(4, 5),
                    'scale_3' => rand(3, 5),
                ],
            ]);
        }

        // Open Ended sample responses
        $sampleStories = [
            'Berhasil rilis modul fitur baru tepat waktu dan tanpa bug!',
            'Menyelesaikan sertifikasi cloud computing bersama rekan satu divisi.',
            'Bisa tidur 8 jam penuh di akhir pekan dan jogging 5K!',
        ];
        foreach ($sampleStories as $idx => $story) {
            Response::create([
                'presentation_id' => $p1->id,
                'slide_id' => $s1_5->id,
                'participant_session' => 'sess_story_'.$idx,
                'participant_name' => 'Rekan '.($idx + 1),
                'response_value' => ['text' => $story],
            ]);
        }

        // Audience Questions sample
        AudienceQuestion::create([
            'presentation_id' => $p1->id,
            'participant_session' => 'sess_seed_1',
            'participant_name' => 'Budi Santoso',
            'question_text' => 'Kapan jadwal rilis versi 2.0 untuk aplikasi SpiritMeter ini?',
            'upvotes' => 12,
            'is_answered' => false,
        ]);
        AudienceQuestion::create([
            'presentation_id' => $p1->id,
            'participant_session' => 'sess_seed_2',
            'participant_name' => 'Siti Rahma',
            'question_text' => 'Apakah peserta bisa mengunduh ringkasan hasil polling setelah presentasi?',
            'upvotes' => 8,
            'is_answered' => true,
        ]);

        // Presentation 2: Live Quiz Trivia Showdown
        $p2 = Presentation::create([
            'user_id' => $user->id,
            'folder_id' => $folderEducation->id,
            'title' => 'Live Quiz & Trivia Showdown 🏆',
            'access_code' => '87654321',
            'theme' => 'vibrant',
            'voting_status' => 'open',
            'show_results' => true,
            'is_presenting' => false,
            'allow_questions' => true,
        ]);

        $s2_1 = Slide::create([
            'presentation_id' => $p2->id,
            'order_index' => 1,
            'type' => 'content_heading',
            'question' => 'Battle Pengetahuan: SpiritMeter Trivia ⚔️',
            'description' => 'Jawab secepat dan setepat mungkin untuk mengumpulkan poin tertinggi!',
            'options' => [],
            'settings' => [],
        ]);

        $s2_2 = Slide::create([
            'presentation_id' => $p2->id,
            'order_index' => 2,
            'type' => 'quiz',
            'question' => 'Apa nama framework PHP paling populer di dunia saat ini?',
            'description' => 'Waktu menjawab: 15 detik',
            'options' => [
                ['id' => 'q1_a', 'text' => 'Laravel ⚡', 'color' => '#ef4444', 'is_correct' => true],
                ['id' => 'q1_b', 'text' => 'Symfony', 'color' => '#3b82f6', 'is_correct' => false],
                ['id' => 'q1_c', 'text' => 'CodeIgniter', 'color' => '#f59e0b', 'is_correct' => false],
                ['id' => 'q1_d', 'text' => 'Yii', 'color' => '#10b981', 'is_correct' => false],
            ],
            'settings' => ['timer_seconds' => 15, 'points' => 1000],
        ]);

        $s2_3 = Slide::create([
            'presentation_id' => $p2->id,
            'order_index' => 3,
            'type' => 'quiz',
            'question' => 'Berapa jumlah warna primer dalam teori warna aditif (RGB)?',
            'description' => 'Waktu menjawab: 15 detik',
            'options' => [
                ['id' => 'q2_a', 'text' => '2 Warna', 'color' => '#ef4444', 'is_correct' => false],
                ['id' => 'q2_b', 'text' => '3 Warna (Merah, Hijau, Biru) 🎨', 'color' => '#10b981', 'is_correct' => true],
                ['id' => 'q2_c', 'text' => '4 Warna', 'color' => '#3b82f6', 'is_correct' => false],
                ['id' => 'q2_d', 'text' => '5 Warna', 'color' => '#f59e0b', 'is_correct' => false],
            ],
            'settings' => ['timer_seconds' => 15, 'points' => 1000],
        ]);

        $s2_4 = Slide::create([
            'presentation_id' => $p2->id,
            'order_index' => 4,
            'type' => 'leaderboard',
            'question' => 'Papan Skor Peringkat Juara 🥇🥈🥉',
            'description' => 'Top 10 Peserta dengan Skor dan Kecepatan Terbaik',
            'options' => [],
            'settings' => [],
        ]);

        $p2->update(['current_slide_id' => $s2_1->id]);

        // Seed quiz participants
        $participants = [
            ['Rian Pratama', '🦊', 1940],
            ['Alya Zahra', '🦄', 1880],
            ['Dimas Arya', '🚀', 1750],
            ['Nadia Putri', '🐼', 1620],
            ['Fajar Kusuma', '🦁', 1490],
        ];

        foreach ($participants as $idx => [$name, $avatar, $score]) {
            QuizParticipant::create([
                'presentation_id' => $p2->id,
                'session_id' => 'sess_quiz_'.$idx,
                'nickname' => $name,
                'avatar' => $avatar,
                'total_score' => $score,
            ]);
        }
    }
}
