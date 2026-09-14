<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::latest()->get();
        return view('admin.languages.index', compact('languages'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Language::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);
        return back()->with('success', 'Language created.');
    }

    public function destroy(Language $language)
    {
        $language->delete();
        return back()->with('success', 'Language deleted.');
    }
}
