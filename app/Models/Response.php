<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Response extends Model
{
    use HasFactory;

    protected $fillable = [
        'presentation_id',
        'slide_id',
        'participant_session',
        'participant_name',
        'response_value',
        'score',
        'response_time_ms',
    ];

    protected function casts(): array
    {
        return [
            'response_value' => 'array',
            'score' => 'integer',
            'response_time_ms' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Presentation, $this>
     */
    public function presentation(): BelongsTo
    {
        return $this->belongsTo(Presentation::class);
    }

    /**
     * @return BelongsTo<Slide, $this>
     */
    public function slide(): BelongsTo
    {
        return $this->belongsTo(Slide::class);
    }
}
