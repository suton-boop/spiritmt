<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slide extends Model
{
    use HasFactory;

    protected $fillable = [
        'presentation_id',
        'order_index',
        'type',
        'question',
        'description',
        'options',
        'settings',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'settings' => 'array',
            'order_index' => 'integer',
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
     * @return HasMany<Response, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    /**
     * Calculate aggregated results for presentation and live display
     */
    public function getAggregatedResults(): array
    {
        $allResponses = $this->responses()->get();
        $totalResponses = $allResponses->count();

        switch ($this->type) {
            case 'multiple_choice':
            case 'quiz':
                $options = $this->options ?? [];
                $counts = [];
                foreach ($options as $opt) {
                    $counts[$opt['id'] ?? $opt['text']] = 0;
                }

                foreach ($allResponses as $resp) {
                    $val = $resp->response_value;
                    if (is_array($val)) {
                        foreach ($val as $selectedId) {
                            if (isset($counts[$selectedId])) {
                                $counts[$selectedId]++;
                            }
                        }
                    } elseif ($val !== null && isset($counts[$val])) {
                        $counts[$val]++;
                    }
                }

                $optionResults = [];
                foreach ($options as $opt) {
                    $key = $opt['id'] ?? $opt['text'];
                    $count = $counts[$key] ?? 0;
                    $percent = $totalResponses > 0 ? round(($count / $totalResponses) * 100, 1) : 0;
                    $optionResults[] = [
                        'id' => $key,
                        'text' => $opt['text'] ?? '',
                        'count' => $count,
                        'percentage' => $percent,
                        'is_correct' => $opt['is_correct'] ?? false,
                        'color' => $opt['color'] ?? '#6366f1',
                    ];
                }

                return [
                    'type' => $this->type,
                    'total' => $totalResponses,
                    'options' => $optionResults,
                ];

            case 'word_cloud':
                $wordCounts = [];
                foreach ($allResponses as $resp) {
                    $words = $resp->response_value;
                    if (is_array($words)) {
                        foreach ($words as $w) {
                            $clean = trim((string) $w);
                            if ($clean !== '') {
                                $cleanLower = mb_strtolower($clean);
                                $wordCounts[$cleanLower] = ($wordCounts[$cleanLower] ?? 0) + 1;
                            }
                        }
                    } elseif (is_string($words) && trim($words) !== '') {
                        $cleanLower = mb_strtolower(trim($words));
                        $wordCounts[$cleanLower] = ($wordCounts[$cleanLower] ?? 0) + 1;
                    }
                }

                arsort($wordCounts);
                $cloud = [];
                foreach ($wordCounts as $text => $weight) {
                    $cloud[] = ['text' => $text, 'weight' => $weight];
                }

                return [
                    'type' => 'word_cloud',
                    'total' => $totalResponses,
                    'words' => array_slice($cloud, 0, 50),
                ];

            case 'open_ended':
                $messages = [];
                foreach ($allResponses as $resp) {
                    $text = is_string($resp->response_value) ? $resp->response_value : ($resp->response_value['text'] ?? '');
                    if (trim((string) $text) !== '') {
                        $messages[] = [
                            'id' => $resp->id,
                            'name' => $resp->participant_name ?: 'Anonim',
                            'text' => $text,
                            'created_at' => $resp->created_at->diffForHumans(),
                        ];
                    }
                }

                return [
                    'type' => 'open_ended',
                    'total' => $totalResponses,
                    'entries' => $messages,
                ];

            case 'scales':
                $options = $this->options ?? [];
                $scores = [];
                foreach ($options as $opt) {
                    $scores[$opt['id'] ?? $opt['text']] = ['sum' => 0, 'count' => 0];
                }

                foreach ($allResponses as $resp) {
                    $ratings = $resp->response_value;
                    if (is_array($ratings)) {
                        foreach ($ratings as $optId => $val) {
                            if (isset($scores[$optId])) {
                                $scores[$optId]['sum'] += (float) $val;
                                $scores[$optId]['count']++;
                            }
                        }
                    }
                }

                $statementResults = [];
                foreach ($options as $opt) {
                    $key = $opt['id'] ?? $opt['text'];
                    $c = $scores[$key]['count'] ?? 0;
                    $avg = $c > 0 ? round($scores[$key]['sum'] / $c, 2) : 0;
                    $statementResults[] = [
                        'id' => $key,
                        'text' => $opt['text'] ?? '',
                        'average' => $avg,
                        'respondents' => $c,
                    ];
                }

                return [
                    'type' => 'scales',
                    'total' => $totalResponses,
                    'statements' => $statementResults,
                ];

            case 'ranking':
                $options = $this->options ?? [];
                $optionCount = count($options);
                $points = [];
                foreach ($options as $opt) {
                    $points[$opt['id'] ?? $opt['text']] = 0;
                }

                foreach ($allResponses as $resp) {
                    $ranked = $resp->response_value;
                    if (is_array($ranked)) {
                        foreach ($ranked as $rankIndex => $optId) {
                            if (isset($points[$optId])) {
                                $weight = max(1, $optionCount - $rankIndex);
                                $points[$optId] += $weight;
                            }
                        }
                    }
                }

                $rankedResults = [];
                foreach ($options as $opt) {
                    $key = $opt['id'] ?? $opt['text'];
                    $rankedResults[] = [
                        'id' => $key,
                        'text' => $opt['text'] ?? '',
                        'score' => $points[$key] ?? 0,
                    ];
                }

                usort($rankedResults, fn ($a, $b) => $b['score'] <=> $a['score']);

                return [
                    'type' => 'ranking',
                    'total' => $totalResponses,
                    'items' => $rankedResults,
                ];

            case 'leaderboard':
                $leaderboard = $this->presentation->quizParticipants()
                    ->orderByDesc('total_score')
                    ->take(10)
                    ->get()
                    ->map(fn ($p) => [
                        'nickname' => $p->nickname,
                        'avatar' => $p->avatar,
                        'score' => $p->total_score,
                    ])
                    ->toArray();

                return [
                    'type' => 'leaderboard',
                    'total' => count($leaderboard),
                    'leaderboard' => $leaderboard,
                ];

            default:
                return [
                    'type' => $this->type,
                    'total' => $totalResponses,
                ];
        }
    }
}
