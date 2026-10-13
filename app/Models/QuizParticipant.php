<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'presentation_id',
        'session_id',
        'nickname',
        'avatar',
        'total_score',
    ];

    protected function casts(): array
    {
        return [
            'total_score' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Presentation, $this>
     */
    public function presentation(): BelongsTo
    {
        return $this->belongsTo(Presentation::class);
    }
}
