<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Scrapers\MoviessdasScraper;

class ScrapeMoviesda extends Command
{
    protected $signature = 'movies:scrape-all
                            {--category= : Scrape only a specific category path (e.g. /tamil-2026-movies/)}
                            {--dry-run   : Show what would be scraped without saving}';

    protected $description = 'Comprehensive scrape of all movies from moviezda.com (all years, dubbed, collections) with pagination';

    public function handle(MoviessdasScraper $scraper): int
    {
        $this->info('');
        $this->info('🎬 Moviesda Comprehensive Scraper');
        $this->info('===================================');
        $this->info('Source: moviezda.com (mirror of moviesda.com)');
        $this->info('');

        // Wire output callback to Artisan console
        $scraper->setOutputCallback(function (string $message) {
            $this->line($message);
        });

        $this->info('Starting full catalog scrape...');
        $this->newLine();

        $startTime = microtime(true);
        $results   = $scraper->scrapeAll();
        $elapsed   = round(microtime(true) - $startTime, 1);

        $this->newLine();
        $this->info('===================================');
        $this->info("✅ Done in {$elapsed}s");
        $this->info("📥 Movies saved/updated : {$results['saved']}");
        $this->info("❌ Movies failed        : {$results['failed']}");

        // Show total in DB
        $total = \App\Models\Movie::count();
        $this->info("📊 Total in database    : {$total}");
        $this->info('===================================');
        $this->newLine();

        return Command::SUCCESS;
    }
}
