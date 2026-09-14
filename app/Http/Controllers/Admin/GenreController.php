<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::latest()->get();
        return view('admin.genres.index', compact('genres'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Genre::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);
        return back()->with('success', 'Genre created.');
    }

    public function destroy(Genre $genre)
    {
        $genre->delete();
        return back()->with('success', 'Genre deleted.');
    }
}
