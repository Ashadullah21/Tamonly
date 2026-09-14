<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\MovieLink;
use Illuminate\Http\Request;

class MovieLinkController extends Controller
{
    public function store(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'quality' => 'required|string|max:255',
            'resolution' => 'nullable|string|max:255',
            'file_size' => 'nullable|string|max:255',
            'download_url' => 'required|url',
            'source_name' => 'nullable|string|max:255',
        ]);
        
        $movie->links()->create($validated);
        return back()->with('success', 'Link added.');
    }

    public function destroy(Movie $movie, MovieLink $link)
    {
        $link->delete();
        return back()->with('success', 'Link deleted.');
    }
}
