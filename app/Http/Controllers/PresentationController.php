<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Presentation;
use App\Models\Slide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PresentationController extends Controller
{
    /**
     * Create a new blank presentation.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $presentation = Presentation::create([
            'user_id' => $user->id,
            'folder_id' => $request->input('folder_id'),
            'title' => $request->input('title') ?: 'Presentasi Baru',
            'access_code' => Presentation::generateAccessCode(),
            'theme' => 'default',
            'voting_status' => 'open',
            'show_results' => true,
        ]);

        // Create default first slide (Multiple choice)
        $slide = Slide::create([
            'presentation_id' => $presentation->id,
            'order_index' => 1,
            'type' => 'multiple_choice',
            'question' => 'Pertanyaan pertama Anda',
            'description' => 'Pilih salah satu jawaban yang sesuai',
            'options' => [
                ['id' => 'opt_1', 'text' => 'Pilihan 1', 'color' => '#6366f1', 'is_correct' => false],
                ['id' => 'opt_2', 'text' => 'Pilihan 2', 'color' => '#10b981', 'is_correct' => false],
                ['id' => 'opt_3', 'text' => 'Pilihan 3', 'color' => '#f59e0b', 'is_correct' => false],
            ],
            'settings' => ['chart_type' => 'bars', 'show_percentage' => true],
        ]);

        $presentation->update(['current_slide_id' => $slide->id]);

        return redirect()->route('presentations.edit', $presentation->id);
    }

    /**
     * Presentation Studio Editor.
     */
    public function edit(Presentation $presentation): View|RedirectResponse
    {
        if ($presentation->user_id !== Auth::id()) {
            abort(403);
        }

        $presentation->load(['slides', 'folder']);

        // Ensure there is at least one slide
        if ($presentation->slides->isEmpty()) {
            $slide = Slide::create([
                'presentation_id' => $presentation->id,
                'order_index' => 1,
                'type' => 'multiple_choice',
                'question' => 'Pertanyaan pertama Anda',
                'options' => [
                    ['id' => 'opt_1', 'text' => 'Opsi A', 'color' => '#6366f1', 'is_correct' => false],
                    ['id' => 'opt_2', 'text' => 'Opsi B', 'color' => '#10b981', 'is_correct' => false],
                ],
                'settings' => ['chart_type' => 'bars'],
            ]);
            $presentation->update(['current_slide_id' => $slide->id]);
            $presentation->refresh();
        }

        $folders = Folder::where('user_id', Auth::id())->get();

        return view('presentations.edit', [
            'presentation' => $presentation,
            'folders' => $folders,
        ]);
    }

    /**
     * Update presentation metadata (title, theme, voting_status).
     */
    public function update(Request $request, Presentation $presentation): JsonResponse|RedirectResponse
    {
        if ($presentation->user_id !== Auth::id()) {
            abort(403);
        }

        if ($request->has('folder_id') && (empty($request->folder_id) || $request->folder_id === 'none')) {
            $request->merge(['folder_id' => null]);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'theme' => ['sometimes', 'required', 'string'],
            'folder_id' => ['nullable', 'exists:folders,id'],
            'voting_status' => ['sometimes', 'in:open,closed'],
            'show_results' => ['sometimes', 'boolean'],
            'allow_questions' => ['sometimes', 'boolean'],
        ]);

        $presentation->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'presentation' => $presentation,
            ]);
        }

        return back()->with('success', 'Presentasi berhasil diperbarui.');
    }

    /**
     * Duplicate presentation and all slides.
     */
    public function duplicate(Presentation $presentation): RedirectResponse
    {
        if ($presentation->user_id !== Auth::id()) {
            abort(403);
        }

        $newPresentation = $presentation->replicate([
            'access_code',
            'current_slide_id',
            'is_presenting',
        ]);
        $newPresentation->title = $presentation->title.' (Duplikat)';
        $newPresentation->access_code = Presentation::generateAccessCode();
        $newPresentation->save();

        $firstNewSlideId = null;
        foreach ($presentation->slides as $slide) {
            $newSlide = $slide->replicate();
            $newSlide->presentation_id = $newPresentation->id;
            $newSlide->save();

            if (! $firstNewSlideId) {
                $firstNewSlideId = $newSlide->id;
            }
        }

        $newPresentation->update(['current_slide_id' => $firstNewSlideId]);

        return redirect()->route('dashboard')->with('success', 'Presentasi berhasil diduplikasi!');
    }

    /**
     * Delete presentation.
     */
    public function destroy(Presentation $presentation): RedirectResponse
    {
        if ($presentation->user_id !== Auth::id()) {
            abort(403);
        }

        $presentation->delete();

        return redirect()->route('dashboard')->with('success', 'Presentasi berhasil dihapus.');
    }

    /**
     * Reset responses & questions for fresh presentation.
     */
    public function resetResponses(Presentation $presentation): JsonResponse|RedirectResponse
    {
        if ($presentation->user_id !== Auth::id()) {
            abort(403);
        }

        $presentation->responses()->delete();
        $presentation->audienceQuestions()->delete();
        $presentation->quizParticipants()->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Hasil polling berhasil direset.']);
        }

        return back()->with('success', 'Semua respon berhasil direset.');
    }

    /**
     * Export presentation responses into CSV.
     */
    public function exportCsv(Presentation $presentation): StreamedResponse
    {
        if ($presentation->user_id !== Auth::id()) {
            abort(403);
        }

        $presentation->load(['slides.responses']);

        $filename = 'spiritmeter_'.str_replace(' ', '_', $presentation->title).'_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($presentation) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Slide #', 'Tipe Slide', 'Pertanyaan', 'Nama Peserta', 'Jawaban / Nilai', 'Skor', 'Waktu']);

            foreach ($presentation->slides as $slide) {
                foreach ($slide->responses as $resp) {
                    $val = is_array($resp->response_value) ? json_encode($resp->response_value, JSON_UNESCAPED_UNICODE) : (string) $resp->response_value;
                    fputcsv($handle, [
                        $slide->order_index,
                        $slide->type,
                        $slide->question,
                        $resp->participant_name ?: 'Anonim',
                        $val,
                        $resp->score,
                        $resp->created_at->toDateTimeString(),
                    ]);
                }
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Create presentation from built-in templates.
     */
    public function createFromTemplate(Request $request): RedirectResponse
    {
        $templateKey = $request->input('template');
        $user = Auth::user();

        $templates = [
            'icebreaker' => [
                'title' => 'Icebreaker & Check-in Tim',
                'theme' => 'default',
                'slides' => [
                    [
                        'type' => 'content_heading',
                        'question' => 'Selamat Datang di Sesi Pembuka! 👋',
                        'description' => 'Silakan masuk ke spiritmeter.test/join untuk bergabung.',
                    ],
                    [
                        'type' => 'word_cloud',
                        'question' => 'Gambarkan perasaanmu hari ini dalam 1 kata!',
                        'settings' => ['max_words' => 3],
                    ],
                    [
                        'type' => 'multiple_choice',
                        'question' => 'Berapa cangkir kopi yang sudah kamu minum hari ini?',
                        'options' => [
                            ['id' => 'opt_1', 'text' => '0 (Anti kafein 💧)', 'color' => '#3b82f6'],
                            ['id' => 'opt_2', 'text' => '1 cangkir standar ☕', 'color' => '#10b981'],
                            ['id' => 'opt_3', 'text' => '2-3 cangkir 🔥', 'color' => '#f59e0b'],
                            ['id' => 'opt_4', 'text' => 'Tak terhitung (Zombie mode 🧟)', 'color' => '#ef4444'],
                        ],
                    ],
                ],
            ],
            'feedback' => [
                'title' => 'Evaluasi & Umpan Balik Sesi',
                'theme' => 'emerald',
                'slides' => [
                    [
                        'type' => 'scales',
                        'question' => 'Bagaimana kepuasan Anda terhadap materi hari ini?',
                        'options' => [
                            ['id' => 's1', 'text' => 'Materi jelas dan aplikatif'],
                            ['id' => 's2', 'text' => 'Penyampaian interaktif dan menarik'],
                            ['id' => 's3', 'text' => 'Alokasi waktu memadai'],
                        ],
                        'settings' => ['low_label' => 'Kurang Puas', 'high_label' => 'Sangat Puas'],
                    ],
                    [
                        'type' => 'open_ended',
                        'question' => 'Apa ide atau saran terbaik Anda untuk perbaikan?',
                    ],
                ],
            ],
            'quiz' => [
                'title' => 'Live Trivia Battle',
                'theme' => 'vibrant',
                'slides' => [
                    [
                        'type' => 'quiz',
                        'question' => 'Siapa penemu bahasa pemrograman PHP?',
                        'options' => [
                            ['id' => 'q1', 'text' => 'Rasmus Lerdorf', 'is_correct' => true, 'color' => '#10b981'],
                            ['id' => 'q2', 'text' => 'Guido van Rossum', 'is_correct' => false, 'color' => '#3b82f6'],
                            ['id' => 'q3', 'text' => 'Brendan Eich', 'is_correct' => false, 'color' => '#f59e0b'],
                        ],
                        'settings' => ['timer_seconds' => 15, 'points' => 1000],
                    ],
                    [
                        'type' => 'leaderboard',
                        'question' => 'Papan Skor Peringkat Juara 🏆',
                    ],
                ],
            ],
        ];

        $tpl = $templates[$templateKey] ?? $templates['icebreaker'];

        $presentation = Presentation::create([
            'user_id' => $user->id,
            'title' => $tpl['title'],
            'access_code' => Presentation::generateAccessCode(),
            'theme' => $tpl['theme'] ?? 'default',
            'voting_status' => 'open',
            'show_results' => true,
        ]);

        $firstSlideId = null;
        foreach ($tpl['slides'] as $idx => $sData) {
            $slide = Slide::create([
                'presentation_id' => $presentation->id,
                'order_index' => $idx + 1,
                'type' => $sData['type'],
                'question' => $sData['question'],
                'description' => $sData['description'] ?? '',
                'options' => $sData['options'] ?? [],
                'settings' => $sData['settings'] ?? [],
            ]);
            if (! $firstSlideId) {
                $firstSlideId = $slide->id;
            }
        }

        $presentation->update(['current_slide_id' => $firstSlideId]);

        return redirect()->route('presentations.edit', $presentation->id)->with('success', 'Template berhasil dibuat!');
    }

    /**
     * Store folder.
     */
    public function storeFolder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        Folder::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'color' => $validated['color'] ?: '#6366f1',
        ]);

        return back()->with('success', 'Folder berhasil dibuat.');
    }

    /**
     * Delete folder.
     */
    public function destroyFolder(Folder $folder): RedirectResponse
    {
        if ($folder->user_id !== Auth::id()) {
            abort(403);
        }

        $folder->delete();

        return back()->with('success', 'Folder berhasil dihapus.');
    }
}
