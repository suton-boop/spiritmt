<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Presentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'folder_id',
        'title',
        'access_code',
        'current_slide_id',
        'theme',
        'voting_status',
        'show_results',
        'is_presenting',
        'allow_questions',
    ];

    protected function casts(): array
    {
        return [
            'show_results' => 'boolean',
            'is_presenting' => 'boolean',
            'allow_questions' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Folder, $this>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * @return HasMany<Slide, $this>
     */
    public function slides(): HasMany
    {
        return $this->hasMany(Slide::class)->orderBy('order_index');
    }

    /**
     * @return BelongsTo<Slide, $this>
     */
    public function currentSlide(): BelongsTo
    {
        return $this->belongsTo(Slide::class, 'current_slide_id');
    }

    /**
     * @return HasMany<Response, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    /**
     * @return HasMany<AudienceQuestion, $this>
     */
    public function audienceQuestions(): HasMany
    {
        return $this->hasMany(AudienceQuestion::class)->orderByDesc('upvotes');
    }

    /**
     * @return HasMany<QuizParticipant, $this>
     */
    public function quizParticipants(): HasMany
    {
        return $this->hasMany(QuizParticipant::class)->orderByDesc('total_score');
    }

    /**
     * Generate unique numeric code like "48291048" or formatted "4829 1048"
     */
    public static function generateAccessCode(): string
    {
        do {
            $code = (string) random_int(10000000, 99999999);
        } while (static::where('access_code', $code)->exists());

        return $code;
    }

    /**
     * Formatted access code with space (e.g. "1234 5678")
     */
    public function getFormattedCode(): string
    {
        $code = str_replace(' ', '', $this->access_code);
        if (strlen($code) === 8) {
            return substr($code, 0, 4).' '.substr($code, 4, 4);
        }

        return $code;
    }
}
