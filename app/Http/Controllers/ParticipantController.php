<?php

namespace App\Http\Controllers;

use App\Models\AudienceQuestion;
use App\Models\Presentation;
use App\Models\QuizParticipant;
use App\Models\Response;
use App\Models\Slide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ParticipantController extends Controller
{
    /**
     * Show join PIN entry page (menti.com style).
     */
    public function joinPage(): View
    {
        return view('participant.join');
    }

    /**
     * Process PIN entry and redirect to presentation.
     */
    public function joinSubmit(Request $request): RedirectResponse
    {
        $code = str_replace([' ', '-'], '', $request->input('code', ''));

        $presentation = Presentation::where('access_code', $code)->first();

        if (! $presentation) {
            return back()->withErrors(['code' => 'Kode presentasi tidak ditemukan. Silakan periksa kembali.'])->withInput();
        }

        return redirect()->route('participant.view', ['code' => $code]);
    }

    /**
     * Mobile-first participant view screen.
     */
    public function viewByCode(Request $request, string $code): View|RedirectResponse
    {
        $cleanCode = str_replace([' ', '-'], '', $code);
        $presentation = Presentation::with(['currentSlide'])->where('access_code', $cleanCode)->firstOrFail();

        // Ensure session ID exists for this participant
        $sessionId = $request->session()->get('participant_session_id');
        if (! $sessionId) {
            $sessionId = 'sess_'.Str::random(24);
            $request->session()->put('participant_session_id', $sessionId);
        }

        $nickname = $request->session()->get('participant_nickname', '');
        $avatar = $request->session()->get('participant_avatar', '🦊');

        return view('participant.view', [
            'presentation' => $presentation,
            'sessionId' => $sessionId,
            'nickname' => $nickname,
            'avatar' => $avatar,
        ]);
    }

    /**
     * API for participant screen to poll live state.
     */
    public function getParticipantState(Request $request, string $code): JsonResponse
    {
        $cleanCode = str_replace([' ', '-'], '', $code);
        $presentation = Presentation::with(['currentSlide'])->where('access_code', $cleanCode)->first();

        if (! $presentation) {
            return response()->json(['error' => 'Presentasi tidak ditemukan.'], 404);
        }

        $sessionId = $request->session()->get('participant_session_id');
        $currentSlide = $presentation->currentSlide;

        $hasVoted = false;
        $myVote = null;

        if ($currentSlide && $sessionId) {
            $existing = Response::where('slide_id', $currentSlide->id)
                ->where('participant_session', $sessionId)
                ->first();

            if ($existing) {
                $hasVoted = true;
                $myVote = $existing->response_value;
            }
        }

        // Questions for Q&A slide or modal
        $questions = $presentation->audienceQuestions()
            ->latest('upvotes')
            ->take(30)
            ->get();

        return response()->json([
            'presentation_id' => $presentation->id,
            'title' => $presentation->title,
            'access_code' => $presentation->access_code,
            'voting_status' => $presentation->voting_status,
            'current_slide_id' => $presentation->current_slide_id,
            'current_slide' => $currentSlide,
            'has_voted' => $hasVoted,
            'my_vote' => $myVote,
            'theme' => $presentation->theme,
            'questions' => $questions,
            'results' => $hasVoted ? $currentSlide?->getAggregatedResults() : null,
        ]);
    }

    /**
     * Submit vote response.
     */
    public function submitAnswer(Request $request, string $code): JsonResponse
    {
        $cleanCode = str_replace([' ', '-'], '', $code);
        $presentation = Presentation::where('access_code', $cleanCode)->firstOrFail();

        if ($presentation->voting_status === 'closed') {
            return response()->json(['error' => 'Voting untuk slide ini sudah ditutup oleh presenter.'], 403);
        }

        $sessionId = $request->session()->get('participant_session_id');
        if (! $sessionId) {
            $sessionId = 'sess_'.Str::random(24);
            $request->session()->put('participant_session_id', $sessionId);
        }

        $slideId = $request->input('slide_id');
        $slide = Slide::where('id', $slideId)->where('presentation_id', $presentation->id)->firstOrFail();

        $value = $request->input('value');
        if ($slide->type === 'word_cloud') {
            if (is_array($value)) {
                $value = array_values(array_filter(array_map(fn ($w) => mb_substr(trim((string) $w), 0, 25), $value)));
            } elseif (is_string($value)) {
                $value = mb_substr(trim($value), 0, 25);
            }
        } elseif ($slide->type === 'open_ended') {
            if (is_string($value)) {
                $value = mb_substr(trim($value), 0, 250);
            }
        }
        $nickname = $request->session()->get('participant_nickname', $request->input('name', 'Peserta'));
        $responseTimeMs = $request->input('response_time_ms', 0);

        $score = 0;

        // If quiz slide, calculate score based on correctness & speed
        if ($slide->type === 'quiz') {
            $correctOptions = collect($slide->options)->where('is_correct', true)->pluck('id')->toArray();
            $userAnswers = is_array($value) ? $value : [$value];
            $isCorrect = count(array_intersect($userAnswers, $correctOptions)) > 0;

            if ($isCorrect) {
                $timerSeconds = $slide->settings['timer_seconds'] ?? 15;
                $maxPoints = $slide->settings['points'] ?? 1000;
                $secondsTaken = min($timerSeconds, max(1, $responseTimeMs / 1000));
                // Speed bonus: faster answer yields more points
                $speedFactor = max(0.5, 1 - ($secondsTaken / ($timerSeconds * 2)));
                $score = (int) round($maxPoints * $speedFactor);

                // Update leaderboard participant
                $quizParticipant = QuizParticipant::firstOrCreate(
                    [
                        'presentation_id' => $presentation->id,
                        'session_id' => $sessionId,
                    ],
                    [
                        'nickname' => $nickname,
                        'avatar' => $request->session()->get('participant_avatar', '🎯'),
                        'total_score' => 0,
                    ]
                );
                $quizParticipant->increment('total_score', $score);
            }
        }

        // Save or update response
        $response = Response::updateOrCreate(
            [
                'slide_id' => $slide->id,
                'participant_session' => $sessionId,
            ],
            [
                'presentation_id' => $presentation->id,
                'participant_name' => $nickname,
                'response_value' => $value,
                'score' => $score,
                'response_time_ms' => $responseTimeMs,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Jawaban Anda berhasil dikirim!',
            'score' => $score,
            'results' => $slide->getAggregatedResults(),
        ]);
    }

    /**
     * Submit audience question (Q&A).
     */
    public function submitQuestion(Request $request, string $code): JsonResponse
    {
        $cleanCode = str_replace([' ', '-'], '', $code);
        $presentation = Presentation::where('access_code', $cleanCode)->firstOrFail();

        $sessionId = $request->session()->get('participant_session_id', 'sess_'.Str::random(24));
        $nickname = $request->session()->get('participant_nickname', 'Anonim');

        $text = $request->validate([
            'question_text' => ['required', 'string', 'max:500'],
        ])['question_text'];

        $question = AudienceQuestion::create([
            'presentation_id' => $presentation->id,
            'participant_session' => $sessionId,
            'participant_name' => $nickname,
            'question_text' => $text,
            'upvotes' => 1,
        ]);

        return response()->json([
            'success' => true,
            'question' => $question,
        ]);
    }

    /**
     * Upvote a question.
     */
    public function upvoteQuestion(Request $request, string $code, AudienceQuestion $question): JsonResponse
    {
        $question->increment('upvotes');

        return response()->json([
            'success' => true,
            'upvotes' => $question->upvotes,
        ]);
    }

    /**
     * Set participant profile (nickname & avatar).
     */
    public function saveProfile(Request $request, string $code): JsonResponse
    {
        $cleanCode = str_replace([' ', '-'], '', $code);
        $presentation = Presentation::where('access_code', $cleanCode)->firstOrFail();

        $validated = $request->validate([
            'nickname' => ['required', 'string', 'max:50'],
            'avatar' => ['nullable', 'string', 'max:10'],
        ]);

        $sessionId = $request->session()->get('participant_session_id');
        if (! $sessionId) {
            $sessionId = 'sess_'.Str::random(24);
            $request->session()->put('participant_session_id', $sessionId);
        }

        $request->session()->put('participant_nickname', $validated['nickname']);
        if (! empty($validated['avatar'])) {
            $request->session()->put('participant_avatar', $validated['avatar']);
        }

        // Also update QuizParticipant if already created
        QuizParticipant::updateOrCreate(
            [
                'presentation_id' => $presentation->id,
                'session_id' => $sessionId,
            ],
            [
                'nickname' => $validated['nickname'],
                'avatar' => $validated['avatar'] ?? '🦊',
            ]
        );

        return response()->json([
            'success' => true,
            'nickname' => $validated['nickname'],
            'avatar' => $validated['avatar'] ?? '🦊',
        ]);
    }
}
