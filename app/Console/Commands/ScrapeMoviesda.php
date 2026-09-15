<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Scrapers\MoviessdasScraper;
use Illuminate\Support\Facades\Cache;

class ScrapeMoviesda extends Command
{
    protected $signature = 'movies:scrape-all
                            {--mode=full      : Scrape mode: "full" (all pages) or "incremental" (new movies only)}
                            {--category=      : Scrape only a specific category path (e.g. /tamil-2026-movies/)}
                            {--dry-run        : Show what would be scraped without saving to DB}';

    protected $description = 'Scrape all movies from moviezda.com. Use --mode=incremental for scheduled/cron runs.';

    public function handle(MoviessdasScraper $scraper): int
    {
        $mode = strtolower((string) $this->option('mode'));
        if (!in_array($mode, ['full', 'incremental'])) {
            $this->error("Invalid mode '{$mode}'. Use 'full' or 'incremental'.");
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('🎬  Moviesda Comprehensive Scraper');
        $this->info('════════════════════════════════════');
        $this->info("Source  : moviezda.com");
        $this->info("Mode    : {$mode}");
        if ($mode === 'incremental') {
            $lastSync = Cache::get('scraper_last_sync', 'never');
            $this->info("Last sync: {$lastSync}");
        }
        $this->newLine();

        // Wire output callback to Artisan console
        $scraper->setOutputCallback(function (string $message) {
            $this->line($message);
        });

        $startTime = microtime(true);

        // Run single-category scrape if --category supplied
        $categoryPath = $this->option('category');
        if ($categoryPath) {
            $this->info("Scraping single category: {$categoryPath}");
            $scraper->scrapeCategory([
                'path'     => $categoryPath,
                'year'     => null,
                'language' => 'Tamil',
                'category' => 'Manual',
            ], $mode);
            $results = $scraper->getStats();
        } else {
            $results = $mode === 'full'
                ? $scraper->scrapeAll()
                : $scraper->scrapeIncremental();
        }

        $elapsed = round(microtime(true) - $startTime, 1);
        $total   = \App\Models\Movie::count();

        $this->newLine();
        $this->info('════════════════════════════════════');
        $this->info("✅  Done in {$elapsed}s");
        $this->info("📥  New movies saved  : {$results['saved']}");
        $this->info("🔁  Existing updated  : " . ($results['skipped'] ?? 0));
        $this->info("❌  Failed            : {$results['failed']}");
        $this->info("📊  Total in database : {$total}");
        $this->info('════════════════════════════════════');
        $this->newLine();

        return Command::SUCCESS;
    }
}
