<?php

namespace App\Http\Controllers;

use App\Models\Presentation;
use App\Models\Slide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class SlideController extends Controller
{
    /**
     * Add a new slide to presentation.
     */
    public function store(Request $request, Presentation $presentation): JsonResponse|RedirectResponse
    {
        if ($presentation->user_id !== Auth::id()) {
            abort(403);
        }

        $type = $request->input('type', 'multiple_choice');
        $maxOrder = $presentation->slides()->max('order_index') ?? 0;

        $defaults = $this->getDefaultsForType($type);

        $slide = Slide::create([
            'presentation_id' => $presentation->id,
            'order_index' => $maxOrder + 1,
            'type' => $type,
            'question' => $defaults['question'],
            'description' => $defaults['description'] ?? '',
            'options' => $defaults['options'] ?? [],
            'settings' => $defaults['settings'] ?? [],
        ]);

        // If this is the only slide, set it as current
        if ($presentation->slides()->count() === 1 || ! $presentation->current_slide_id) {
            $presentation->update(['current_slide_id' => $slide->id]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'slide' => $slide,
                'redirect_url' => route('presentations.edit', ['presentation' => $presentation->id, 'slide' => $slide->id]),
            ]);
        }

        return redirect()->route('presentations.edit', ['presentation' => $presentation->id, 'slide' => $slide->id]);
    }

    /**
     * Update slide configuration.
     */
    public function update(Request $request, Slide $slide): JsonResponse|RedirectResponse
    {
        if ($slide->presentation->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => ['sometimes', 'required', 'string'],
            'question' => ['sometimes', 'nullable', 'string', 'max:500'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'options' => ['sometimes', 'nullable', 'array'],
            'settings' => ['sometimes', 'nullable', 'array'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);

        $slide->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'slide' => $slide,
                'results' => $slide->getAggregatedResults(),
            ]);
        }

        return back()->with('success', 'Slide berhasil diperbarui.');
    }

    /**
     * Delete slide.
     */
    public function destroy(Slide $slide): JsonResponse|RedirectResponse
    {
        $presentation = $slide->presentation;
        if ($presentation->user_id !== Auth::id()) {
            abort(403);
        }

        // Prevent deleting if it's the only slide
        if ($presentation->slides()->count() <= 1) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Presentasi minimal harus memiliki 1 slide.'], 422);
            }

            return back()->with('error', 'Presentasi minimal harus memiliki 1 slide.');
        }

        $slideId = $slide->id;
        $slide->delete();

        // If deleted slide was current, switch to first remaining
        if ($presentation->current_slide_id === $slideId) {
            $first = $presentation->slides()->first();
            $presentation->update(['current_slide_id' => $first?->id]);
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('presentations.edit', $presentation->id);
    }

    /**
     * Upload an image (content image or background image) for a slide.
     */
    public function uploadImage(Request $request, Slide $slide): JsonResponse
    {
        if ($slide->presentation->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'target' => ['required', 'string', 'in:content,background'],
        ]);

        $file = $request->file('image');
        $target = $request->input('target');

        $uploadPath = public_path('uploads/slides');
        File::ensureDirectoryExists($uploadPath);

        $filename = 'slide_'.$slide->id.'_'.$target.'_'.time().'.'.$file->getClientOriginalExtension();
        $file->move($uploadPath, $filename);

        $url = asset('uploads/slides/'.$filename);

        $settings = $slide->settings ?? [];
        if ($target === 'background') {
            $settings['background_image'] = $url;
        } else {
            $settings['content_image'] = $url;
        }

        $slide->settings = $settings;
        $slide->save();

        return response()->json([
            'success' => true,
            'url' => $url,
            'target' => $target,
            'settings' => $slide->settings,
            'message' => 'Gambar berhasil diunggah.',
        ]);
    }

    /**
     * Remove an image from a slide.
     */
    public function removeImage(Request $request, Slide $slide): JsonResponse
    {
        if ($slide->presentation->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'target' => ['required', 'string', 'in:content,background'],
        ]);

        $target = $request->input('target');
        $settings = $slide->settings ?? [];

        if ($target === 'background') {
            unset($settings['background_image']);
        } else {
            unset($settings['content_image']);
        }

        $slide->settings = $settings;
        $slide->save();

        return response()->json([
            'success' => true,
            'target' => $target,
            'settings' => $slide->settings,
            'message' => 'Gambar berhasil dihapus.',
        ]);
    }

    /**
     * Duplicate slide.
     */
    public function duplicate(Slide $slide): JsonResponse|RedirectResponse
    {
        $presentation = $slide->presentation;
        if ($presentation->user_id !== Auth::id()) {
            abort(403);
        }

        $newSlide = $slide->replicate();
        $newSlide->question = ($slide->question ?: 'Slide').' (Salinan)';
        $newSlide->order_index = $slide->order_index + 1;
        $newSlide->save();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'slide' => $newSlide,
                'redirect_url' => route('presentations.edit', ['presentation' => $presentation->id, 'slide' => $newSlide->id]),
            ]);
        }

        return redirect()->route('presentations.edit', ['presentation' => $presentation->id, 'slide' => $newSlide->id]);
    }

    /**
     * Reorder slides.
     */
    public function reorder(Request $request, Presentation $presentation): JsonResponse
    {
        if ($presentation->user_id !== Auth::id()) {
            abort(403);
        }

        $order = $request->validate([
            'slide_ids' => ['required', 'array'],
            'slide_ids.*' => ['integer', 'exists:slides,id'],
        ])['slide_ids'];

        foreach ($order as $index => $id) {
            Slide::where('id', $id)->where('presentation_id', $presentation->id)->update([
                'order_index' => $index + 1,
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get slide data and aggregated results.
     */
    public function show(Slide $slide): JsonResponse
    {
        return response()->json([
            'slide' => $slide,
            'results' => $slide->getAggregatedResults(),
        ]);
    }

    /**
     * Get default schema for slide type.
     */
    private function getDefaultsForType(string $type): array
    {
        switch ($type) {
            case 'multiple_choice':
                return [
                    'question' => 'Pertanyaan pilihan ganda Anda',
                    'description' => 'Pilih opsi yang paling tepat',
                    'options' => [
                        ['id' => 'opt_1', 'text' => 'Opsi 1', 'color' => '#6366f1', 'is_correct' => false],
                        ['id' => 'opt_2', 'text' => 'Opsi 2', 'color' => '#10b981', 'is_correct' => false],
                        ['id' => 'opt_3', 'text' => 'Opsi 3', 'color' => '#f59e0b', 'is_correct' => false],
                    ],
                    'settings' => ['chart_type' => 'bars', 'show_percentage' => true],
                ];

            case 'word_cloud':
                return [
                    'question' => 'Gambarkan topik ini dalam kata kunci!',
                    'description' => 'Ketik hingga 3 kata',
                    'options' => [],
                    'settings' => ['max_words' => 3],
                ];

            case 'open_ended':
                return [
                    'question' => 'Bagikan pendapat atau ide Anda',
                    'description' => 'Tuliskan jawaban bebas di bawah',
                    'options' => [],
                    'settings' => ['layout' => 'cards'],
                ];

            case 'scales':
                return [
                    'question' => 'Seberapa setuju Anda dengan pernyataan ini?',
                    'description' => 'Beri nilai 1 (Sangat Tidak Setuju) sampai 5 (Sangat Setuju)',
                    'options' => [
                        ['id' => 's1', 'text' => 'Pernyataan 1'],
                        ['id' => 's2', 'text' => 'Pernyataan 2'],
                        ['id' => 's3', 'text' => 'Pernyataan 3'],
                    ],
                    'settings' => ['low_label' => 'Sangat Tidak Setuju', 'high_label' => 'Sangat Setuju'],
                ];

            case 'ranking':
                return [
                    'question' => 'Urutkan prioritas hal berikut ini:',
                    'description' => 'Susun dari urutan paling prioritas ke bawah',
                    'options' => [
                        ['id' => 'r1', 'text' => 'Prioritas A'],
                        ['id' => 'r2', 'text' => 'Prioritas B'],
                        ['id' => 'r3', 'text' => 'Prioritas C'],
                    ],
                    'settings' => [],
                ];

            case 'qa':
                return [
                    'question' => 'Sesi Tanya Jawab (Audience Q&A) 💬',
                    'description' => 'Ajukan pertanyaan atau beri upvote ke pertanyaan teman',
                    'options' => [],
                    'settings' => ['allow_anonymous' => true],
                ];

            case 'quiz':
                return [
                    'question' => 'Pertanyaan Kuis Interaktif ⏱️',
                    'description' => 'Pilih jawaban yang benar secepat mungkin!',
                    'options' => [
                        ['id' => 'q1', 'text' => 'Jawaban A', 'color' => '#ef4444', 'is_correct' => true],
                        ['id' => 'q2', 'text' => 'Jawaban B', 'color' => '#3b82f6', 'is_correct' => false],
                        ['id' => 'q3', 'text' => 'Jawaban C', 'color' => '#f59e0b', 'is_correct' => false],
                        ['id' => 'q4', 'text' => 'Jawaban D', 'color' => '#10b981', 'is_correct' => false],
                    ],
                    'settings' => ['timer_seconds' => 15, 'points' => 1000],
                ];

            case 'leaderboard':
                return [
                    'question' => 'Papan Skor Peringkat Juara 🏆',
                    'description' => 'Peserta dengan poin dan kecepatan tertinggi',
                    'options' => [],
                    'settings' => [],
                ];

            case 'content_heading':
                return [
                    'question' => 'Judul Presentasi atau Pengantar',
                    'description' => 'Tuliskan deskripsi atau petunjuk pembuka di sini',
                    'options' => [],
                    'settings' => [],
                ];

            case 'content_paragraph':
                return [
                    'question' => 'Ringkasan Materi atau Pembahasan',
                    'description' => 'Tuliskan poin-poin penjelasan penting yang ingin Anda bagikan kepada peserta.',
                    'options' => [],
                    'settings' => [],
                ];

            default:
                return [
                    'question' => 'Pertanyaan Baru',
                    'description' => '',
                    'options' => [],
                    'settings' => [],
                ];
        }
    }
}
