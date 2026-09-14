<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Language;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    public function index()
    {
        $movies = Movie::with(['genres', 'languages', 'links'])->latest()->paginate(15);
        return view('admin.movies.index', compact('movies'));
    }

    public function create()
    {
        $genres = Genre::all();
        $languages = Language::all();
        return view('admin.movies.form', compact('genres', 'languages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'release_year' => 'nullable|integer|min:1900|max:2099',
            'runtime' => 'nullable|integer|min:1|max:999',
            'status' => 'required|string|in:active,inactive',
            'genres' => 'nullable|array',
            'languages' => 'nullable|array',
        ]);
        
        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 1;
        while (Movie::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }
        $validated['slug'] = $slug;
        
        $movie = Movie::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'release_year' => $validated['release_year'] ?? null,
            'runtime' => $validated['runtime'] ?? null,
            'status' => $validated['status'],
        ]);

        if (!empty($validated['genres'])) {
            $movie->genres()->sync($validated['genres']);
        }
        if (!empty($validated['languages'])) {
            $movie->languages()->sync($validated['languages']);
        }

        return redirect()->route('admin.movies.edit', $movie)->with('success', 'Movie created successfully.');
    }

    public function edit(Movie $movie)
    {
        $movie->load(['links', 'genres', 'languages']);
        $genres = Genre::all();
        $languages = Language::all();
        return view('admin.movies.form', compact('movie', 'genres', 'languages'));
    }

    public function update(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'release_year' => 'nullable|integer|min:1900|max:2099',
            'runtime' => 'nullable|integer|min:1|max:999',
            'status' => 'required|string|in:active,inactive',
            'genres' => 'nullable|array',
            'languages' => 'nullable|array',
        ]);
        
        if ($movie->title !== $validated['title']) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $counter = 1;
            while (Movie::where('slug', $slug)->where('id', '!=', $movie->id)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }
            $validated['slug'] = $slug;
        } else {
            $validated['slug'] = $movie->slug;
        }
        
        $movie->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'release_year' => $validated['release_year'] ?? null,
            'runtime' => $validated['runtime'] ?? null,
            'status' => $validated['status'],
        ]);

        if (isset($validated['genres'])) {
            $movie->genres()->sync($validated['genres']);
        }
        if (isset($validated['languages'])) {
            $movie->languages()->sync($validated['languages']);
        }

        return redirect()->route('admin.movies.edit', $movie)->with('success', 'Movie updated successfully.');
    }

    public function destroy(Movie $movie)
    {
        $movie->delete();
        return redirect()->route('admin.movies.index')->with('success', 'Movie deleted.');
    }
}
