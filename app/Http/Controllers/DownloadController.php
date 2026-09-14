<?php

namespace App\Http\Controllers;

use App\Models\MovieLink;
use App\Models\DownloadLog;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function redirect(MovieLink $link)
    {
        // Verify link is active and valid
        if (!$link->status || empty($link->download_url)) {
            return response()->view('errors.link-unavailable', [
                'message' => 'This download option is currently unavailable.'
            ], 404);
        }

        // Track the download in logs
        DownloadLog::create([
            'movie_id' => $link->movie_id,
            'movie_link_id' => $link->id,
        ]);
        
        // Redirect securely to the authorized download URL
        return redirect()->away($link->download_url);
    }
}
