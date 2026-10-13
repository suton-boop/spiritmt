<?php

namespace App\Http\Controllers;

use App\Models\Presentation;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Mentimeter-style Landing Page.
     */
    public function index(): View
    {
        $featuredPresentations = Presentation::with('slides')
            ->where('is_presenting', true)
            ->latest()
            ->take(3)
            ->get();

        return view('home', [
            'featuredPresentations' => $featuredPresentations,
        ]);
    }
}
