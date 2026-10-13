<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudienceQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'presentation_id',
        'participant_session',
        'participant_name',
        'question_text',
        'upvotes',
        'is_answered',
    ];

    protected function casts(): array
    {
        return [
            'upvotes' => 'integer',
            'is_answered' => 'boolean',
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
