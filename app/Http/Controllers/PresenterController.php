<?php

namespace App\Http\Controllers;

use App\Models\Presentation;
use App\Models\Slide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PresenterController extends Controller
{
    /**
     * Display live presenter view (Fullscreen presentation mode).
     */
    public function present(Presentation $presentation): View
    {
        if ($presentation->user_id !== Auth::id()) {
            abort(403);
        }

        $presentation->load(['slides', 'currentSlide']);

        // Mark as actively presenting
        $presentation->update(['is_presenting' => true]);

        // If no current slide is set, set the first slide
        if (! $presentation->current_slide_id && $presentation->slides->isNotEmpty()) {
            $presentation->update(['current_slide_id' => $presentation->slides->first()->id]);
            $presentation->refresh();
        }

        return view('presenter.view', [
            'presentation' => $presentation,
        ]);
    }

    /**
     * Switch active slide for all participants.
     */
    public function setCurrentSlide(Request $request, Presentation $presentation): JsonResponse
    {
        if ($presentation->user_id !== Auth::id()) {
            abort(403);
        }

        $slideId = $request->validate([
            'slide_id' => ['required', 'exists:slides,id'],
        ])['slide_id'];

        $presentation->update(['current_slide_id' => $slideId]);

        $slide = Slide::find($slideId);

        return response()->json([
            'success' => true,
            'current_slide_id' => $slideId,
            'results' => $slide?->getAggregatedResults(),
        ]);
    }

    /**
     * Toggle open/closed voting for current slide.
     */
    public function toggleVoting(Request $request, Presentation $presentation): JsonResponse
    {
        if ($presentation->user_id !== Auth::id()) {
            abort(403);
        }

        $newStatus = $presentation->voting_status === 'open' ? 'closed' : 'open';
        $presentation->update(['voting_status' => $newStatus]);

        return response()->json([
            'success' => true,
            'voting_status' => $newStatus,
        ]);
    }

    /**
     * Toggle show/hide results on presenter screen.
     */
    public function toggleResults(Request $request, Presentation $presentation): JsonResponse
    {
        if ($presentation->user_id !== Auth::id()) {
            abort(403);
        }

        $presentation->update(['show_results' => ! $presentation->show_results]);

        return response()->json([
            'success' => true,
            'show_results' => $presentation->show_results,
        ]);
    }

    /**
     * API for presenter screen to fetch live state + results (polling every 1.5s).
     */
    public function getLiveState(Presentation $presentation): JsonResponse
    {
        $currentSlide = $presentation->currentSlide;

        $results = $currentSlide ? $currentSlide->getAggregatedResults() : null;

        $questions = $presentation->audienceQuestions()
            ->latest('upvotes')
            ->take(20)
            ->get();

        $totalResponses = $presentation->responses()->count();
        $slideResponses = $currentSlide ? $currentSlide->responses()->count() : 0;

        return response()->json([
            'presentation_id' => $presentation->id,
            'title' => $presentation->title,
            'access_code' => $presentation->access_code,
            'formatted_code' => $presentation->getFormattedCode(),
            'current_slide_id' => $presentation->current_slide_id,
            'voting_status' => $presentation->voting_status,
            'show_results' => $presentation->show_results,
            'theme' => $presentation->theme,
            'slide' => $currentSlide,
            'results' => $results,
            'questions' => $questions,
            'total_responses' => $totalResponses,
            'slide_responses' => $slideResponses,
        ]);
    }
}
