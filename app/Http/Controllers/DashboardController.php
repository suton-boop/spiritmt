<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Presentation;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display presentations dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $folderId = $request->query('folder');
        $search = $request->query('search');

        $query = Presentation::with(['folder', 'slides'])
            ->withCount(['slides', 'responses'])
            ->where('user_id', $user->id);

        if ($folderId) {
            $query->where('folder_id', $folderId);
        }

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        $presentations = $query->latest()->paginate(12)->withQueryString();

        $folders = Folder::where('user_id', $user->id)
            ->withCount('presentations')
            ->get();

        $totalPresentations = Presentation::where('user_id', $user->id)->count();
        $totalResponses = Response::whereHas('presentation', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->count();

        return view('dashboard.index', [
            'presentations' => $presentations,
            'folders' => $folders,
            'activeFolderId' => $folderId,
            'search' => $search,
            'totalPresentations' => $totalPresentations,
            'totalResponses' => $totalResponses,
        ]);
    }
}
