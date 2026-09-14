<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\MovieLink;
use App\Models\DownloadLog;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_movies' => Movie::count(),
            'total_links' => MovieLink::count(),
            'total_downloads' => DownloadLog::count(),
            'recent_movies' => Movie::latest()->take(5)->get(),
        ];
        
        return view('admin.dashboard', compact('stats'));
    }
}
