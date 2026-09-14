<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Scrapers\SourceMovieScraper;

class ScrapeMovies extends Command
{
    protected $signature = 'movies:sync {url=https://moviezda.com/}';
    protected $description = 'Sync catalog from authorized source';

    public function handle(SourceMovieScraper $scraper)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $url = $this->argument('url');
        $this->info("Starting catalog synchronization for: {$url}");
        
        $scraper->syncCatalog($url);
        
        $this->info("Catalog synchronization completed.");
    }
}
