<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use App\Services\Scrapers\MoviessdasScraper;

/**
 * Webhook endpoint for external cron services (cron-job.org, GitHub Actions, etc.)
 * to trigger an incremental movie sync without requiring SSH access to the server.
 *
 * Usage:
 *   GET /api/sync?token=YOUR_SCRAPE_SECRET
 *
 * Configure the secret in your .env as:
 *   SCRAPE_SECRET=your-long-random-secret-here
 *
 * This runs an INCREMENTAL scrape (page 1-2 of each category only) which
 * is fast (~2-5 minutes) and safe for Render Free Tier.
 */
class ScrapeWebhookController extends Controller
{
    public function sync(Request $request, MoviessdasScraper $scraper): JsonResponse
    {
        // --- Token authentication ---
        $secret = config('app.scrape_secret', env('SCRAPE_SECRET'));

        if (empty($secret)) {
            return response()->json([
                'success' => false,
                'error'   => 'SCRAPE_SECRET is not configured on this server.',
            ], 500);
        }

        $providedToken = $request->query('token') ?? $request->header('X-Scrape-Token');

        if (!$providedToken || !hash_equals($secret, $providedToken)) {
            return response()->json([
                'success' => false,
                'error'   => 'Unauthorized. Invalid or missing token.',
            ], 401);
        }

        // --- Rate-limit: prevent triggering twice within 15 minutes ---
        if (Cache::has('scraper_running')) {
            return response()->json([
                'success' => false,
                'error'   => 'A scrape is already running. Try again in a few minutes.',
            ], 429);
        }

        // Mark as running (TTL 20 minutes — scrape should finish well within this)
        Cache::put('scraper_running', true, now()->addMinutes(20));

        $startTime = microtime(true);
        $before    = \App\Models\Movie::count();

        try {
            $results = $scraper->scrapeIncremental();
        } catch (\Throwable $e) {
            Cache::forget('scraper_running');
            \Illuminate\Support\Facades\Log::error('[SyncWebhook] Scrape failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => 'Scrape failed: ' . $e->getMessage(),
            ], 500);
        }

        Cache::forget('scraper_running');

        $after   = \App\Models\Movie::count();
        $elapsed = round(microtime(true) - $startTime, 1);

        return response()->json([
            'success'         => true,
            'mode'            => 'incremental',
            'elapsed_seconds' => $elapsed,
            'movies_before'   => $before,
            'movies_after'    => $after,
            'new_movies_added'=> max(0, $after - $before),
            'saved'           => $results['saved'],
            'failed'          => $results['failed'],
            'last_sync'       => now()->toIso8601String(),
        ]);
    }

    /**
     * Health check endpoint — returns 200 when the app is alive.
     * Ping this every 5 minutes from UptimeRobot or cron-job.org
     * to keep the Render Free Tier container warm.
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status'     => 'ok',
            'app'        => config('app.name'),
            'movies'     => \App\Models\Movie::where('status', 'active')->count(),
            'last_sync'  => Cache::get('scraper_last_sync', 'never'),
            'time'       => now()->toIso8601String(),
        ]);
    }
}
