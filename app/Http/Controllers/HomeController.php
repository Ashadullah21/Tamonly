<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Eager-load relationships to avoid N+1 queries
        $latest = Movie::where('status', 'active')
            ->with(['genres', 'languages', 'links'])
            ->latest()
            ->take(12)
            ->get();

        $popular = Movie::where('status', 'active')
            ->where(function ($q) {
                $q->whereHas('languages', function ($l) {
                    $l->where('name', 'like', '%Tamil%');
                })->orWhere('source_url', 'like', '%moviezda%')
                  ->orWhere('title', 'like', '%Tamil%');
            })
            ->whereNotNull('poster_path')
            ->where('poster_path', '!=', '')
            ->with(['genres', 'languages', 'links'])
            ->orderBy('view_count', 'desc')
            ->take(6)
            ->get();

        if ($popular->count() < 6) {
            $popular = Movie::where('status', 'active')
                ->with(['genres', 'languages', 'links'])
                ->orderBy('view_count', 'desc')
                ->take(6)
                ->get();
        }
        
        return view('home', compact('latest', 'popular'));
    }
}
