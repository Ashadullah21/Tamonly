<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
 * ─────────────────────────────────────────────────────────────────────────────
 * Laravel Scheduler — Registered Tasks
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * NOTE ON RENDER FREE TIER:
 * The built-in Laravel scheduler requires `php artisan schedule:work` to be
 * running continuously. On Render Free Tier this is NOT reliable because the
 * container spins down after inactivity.
 *
 * RECOMMENDED approach: use an external cron service (cron-job.org is free)
 * to call GET /api/sync?token=YOUR_SCRAPE_SECRET once a day. This is more
 * reliable than relying on the scheduler being alive.
 *
 * This scheduler is kept as a backup for environments where the container
 * stays alive (e.g. Render Starter plan, VPS, or local development).
 * ─────────────────────────────────────────────────────────────────────────────
 */

// Incremental scrape: runs daily at 3:00 AM UTC.
// Checks only the first 2 pages of each category → catches new movies added
// to Moviesda since yesterday. Takes roughly 2–5 minutes. Safe for free tier.
Schedule::command('movies:scrape-all --mode=incremental')
    ->dailyAt('03:00')
    ->withoutOverlapping(30) // max 30-minute lock
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scraper.log'));

// Example: dev/debug helper
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
