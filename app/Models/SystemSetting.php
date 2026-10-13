<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'name', 'description', 'type', 'value', 'is_enabled', 'group'])]
class SystemSetting extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    /**
     * Check if a feature is enabled.
     */
    public static function isEnabled(string $key, bool $default = true): bool
    {
        $setting = static::where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        return (bool) $setting->is_enabled;
    }

    /**
     * Seed default system features if not already existing.
     */
    public static function seedDefaultFeatures(): void
    {
        $defaults = [
            [
                'key' => 'feature_registration',
                'name' => 'Pendaftaran Akun Baru',
                'description' => 'Izinkan pengguna baru mendaftar akun presenter di halaman /register.',
                'group' => 'auth',
                'is_enabled' => true,
            ],
            [
                'key' => 'feature_quiz',
                'name' => 'Kuis & Gamifikasi Realtime',
                'description' => 'Aktifkan fitur slide kuis pilihan ganda dengan timer hitung mundur dan pemberian poin otomatis.',
                'group' => 'slides',
                'is_enabled' => true,
            ],
            [
                'key' => 'feature_word_cloud',
                'name' => 'Word Cloud Dinamis',
                'description' => 'Izinkan audiens mengirimkan kata respons yang otomatis membesar sesuai frekuensi kata.',
                'group' => 'slides',
                'is_enabled' => true,
            ],
            [
                'key' => 'feature_qa',
                'name' => 'Tanya Jawab Audiens (Q&A)',
                'description' => 'Audiens dapat mengirim pertanyaan ke presenter dan melakukan upvote pada pertanyaan populer.',
                'group' => 'engagement',
                'is_enabled' => true,
            ],
            [
                'key' => 'feature_leaderboard',
                'name' => 'Papan Peringkat / Leaderboard',
                'description' => 'Tampilkan papan skor juara kuis interaktif lengkap dengan podium juara dan selebrasi konfeti.',
                'group' => 'gamification',
                'is_enabled' => true,
            ],
            [
                'key' => 'feature_quick_demo',
                'name' => 'Login Demo 1-Klik',
                'description' => 'Izinkan tombol "1-Klik Masuk Presenter Demo" di halaman login untuk eksplorasi cepat.',
                'group' => 'auth',
                'is_enabled' => true,
            ],
        ];

        foreach ($defaults as $item) {
            static::firstOrCreate(
                ['key' => $item['key']],
                $item
            );
        }
    }
}
