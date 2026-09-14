<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Movie;
use App\Models\MovieLink;
use App\Models\Language;
use Illuminate\Support\Str;

/**
 * Comprehensive Moviesda/Moviezda scraper.
 *
 * Scrapes category listing pages, actor collections, and movie detail pages from moviezda.com.
 * Extracts clean titles, real posters, actual synopses, and print quality links.
 */
class MoviessdasScraper
{
    protected string $baseUrl = 'https://moviezda.com';

    protected array $headers = [
        'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-US,en;q=0.5',
        'Accept-Encoding' => 'gzip, deflate',
        'Connection'      => 'keep-alive',
    ];

    /**
     * Category listing paths on moviezda.com.
     */
    protected array $categories = [
        // Tamil movies by year
        ['path' => '/tamil-2026-movies/',           'year' => 2026, 'language' => 'Tamil',          'category' => 'Tamil Movies'],
        ['path' => '/moviesda-tamil-movies-2026/',  'year' => 2026, 'language' => 'Tamil',          'category' => 'Tamil Movies'],
        ['path' => '/tamil-2025-movies/',           'year' => 2025, 'language' => 'Tamil',          'category' => 'Tamil Movies'],
        ['path' => '/tamil-2024-movies/',           'year' => 2024, 'language' => 'Tamil',          'category' => 'Tamil Movies'],
        ['path' => '/tamil-2023-movies/',           'year' => 2023, 'language' => 'Tamil',          'category' => 'Tamil Movies'],
        ['path' => '/tamil-2022-movies/',           'year' => 2022, 'language' => 'Tamil',          'category' => 'Tamil Movies'],
        ['path' => '/tamil-2021-movies/',           'year' => 2021, 'language' => 'Tamil',          'category' => 'Tamil Movies'],
        ['path' => '/tamil-2020-movies/',           'year' => 2020, 'language' => 'Tamil',          'category' => 'Tamil Movies'],
        ['path' => '/tamil-2019-movies/',           'year' => 2019, 'language' => 'Tamil',          'category' => 'Tamil Movies'],
        ['path' => '/tamil-2018-movies/',           'year' => 2018, 'language' => 'Tamil',          'category' => 'Tamil Movies'],
        ['path' => '/tamil-2017-movies/',           'year' => 2017, 'language' => 'Tamil',          'category' => 'Tamil Movies'],
        ['path' => '/tamil-2016-movies/',           'year' => 2016, 'language' => 'Tamil',          'category' => 'Tamil Movies'],
        ['path' => '/tamil-2015-movies/',           'year' => 2015, 'language' => 'Tamil',          'category' => 'Tamil Movies'],
        ['path' => '/tamil-2012-movies/',           'year' => 2012, 'language' => 'Tamil',          'category' => 'Tamil Movies'],
        // Tamil dubbed (Indian movies)
        ['path' => '/tamil-dubbed-movies/',         'year' => null, 'language' => 'Tamil Dubbed',   'category' => 'Tamil Dubbed'],
        // Collections (actor folders)
        ['path' => '/tamil-movies-collection/',     'year' => null, 'language' => 'Tamil',          'category' => 'Collection'],
        ['path' => '/moviesda-tamil-collections/',  'year' => null, 'language' => 'Tamil',          'category' => 'Collection'],
        ['path' => '/tamil-hd-movies/',             'year' => null, 'language' => 'Tamil',          'category' => 'HD Mobile'],
        // Web series
        ['path' => '/tamil-web-series-download/',   'year' => null, 'language' => 'Tamil',          'category' => 'Web Series'],
        ['path' => '/tamil-webseries/',             'year' => null, 'language' => 'Tamil',          'category' => 'Web Series'],
    ];

    protected int $totalSaved  = 0;
    protected int $totalFailed = 0;
    protected $outputCallback  = null;

    public function setOutputCallback(callable $callback): void
    {
        $this->outputCallback = $callback;
    }

    protected function output(string $message): void
    {
        Log::info($message);
        if ($this->outputCallback) {
            ($this->outputCallback)($message);
        }
    }

    public function scrapeAll(): array
    {
        $this->totalSaved  = 0;
        $this->totalFailed = 0;

        foreach ($this->categories as $cat) {
            $this->scrapeCategory($cat);
            sleep(1);
        }

        return [
            'saved'  => $this->totalSaved,
            'failed' => $this->totalFailed,
        ];
    }

    public function scrapeCategory(array $cat): void
    {
        $path     = $cat['path'];
        $year     = $cat['year'];
        $language = $cat['language'];
        $category = $cat['category'];

        $firstPageUrl = Str::startsWith($path, 'http') ? $path : $this->baseUrl . '/' . ltrim($path, '/');
        $this->output("📂 Scraping category: {$category} ({$language} " . ($year ?? 'All') . ") → {$firstPageUrl}");

        $body = $this->fetchHtml($firstPageUrl);
        if (!$body) {
            $this->output("   ⚠️ Could not fetch: {$firstPageUrl}");
            return;
        }

        $crawler    = new Crawler($body, $firstPageUrl);
        $totalPages = $this->getTotalPages($crawler);
        $this->output("   📄 Total pages: {$totalPages}");

        $this->scrapePage($crawler, $firstPageUrl, $year, $language, $category);

        for ($page = 2; $page <= $totalPages; $page++) {
            $pageUrl = rtrim($firstPageUrl, '/') . '/?page=' . $page;
            $this->output("   ↳ Page {$page}/{$totalPages}: {$pageUrl}");

            $body = $this->fetchHtml($pageUrl);
            if (!$body) {
                $this->output("   ⚠️ Skipped page {$page} (fetch failed)");
                continue;
            }

            $pageCrawler = new Crawler($body, $pageUrl);
            $this->scrapePage($pageCrawler, $pageUrl, $year, $language, $category);

            usleep(300000);
        }
    }

    protected function scrapePage(Crawler $crawler, string $pageUrl, ?int $catYear, string $language, string $category): void
    {
        $saved = 0;

        $crawler->filter('div.f a')->each(function (Crawler $node) use ($catYear, $language, $category, &$saved) {
            $href  = $node->attr('href');
            $title = trim($node->text());

            if (!$href || !$title || strlen($title) < 2) return;
            if (Str::startsWith($href, '#') || Str::contains($href, 'javascript:')) return;
            if (Str::contains(strtolower($title), ['home', 'facebook', 'telegram', 'contact', 'disclaimer', 'request'])) return;

            $itemUrl = Str::startsWith($href, 'http') ? $href : $this->baseUrl . '/' . ltrim($href, '/');
            if (!Str::contains($itemUrl, 'moviezda.com')) return;

            // If this is an actor collection folder, recurse into it!
            if ($category === 'Collection' || Str::contains(strtolower($title), ['collection', 'collections']) || Str::contains($itemUrl, '-collection')) {
                $this->scrapeActorCollectionFolder($itemUrl, $language);
                return;
            }

            // Otherwise, it's a movie!
            $savedMovie = $this->saveMovieFromListing($title, $itemUrl, $catYear, $language, $category);
            if ($savedMovie) {
                $saved++;
                $this->totalSaved++;
            }
        });

        if ($saved > 0) {
            $this->output("      ✅ Saved {$saved} movies from this page.");
        }
    }

    /**
     * Recurse into an actor collection folder (e.g. /actor-ajith-movies-collection/)
     */
    protected function scrapeActorCollectionFolder(string $collectionUrl, string $language): void
    {
        $this->output("      📁 Visiting actor collection: {$collectionUrl}");
        $body = $this->fetchHtml($collectionUrl);
        if (!$body) return;

        $crawler = new Crawler($body, $collectionUrl);
        $crawler->filter('div.f a')->each(function (Crawler $node) use ($language) {
            $href  = $node->attr('href');
            $title = trim($node->text());

            if (!$href || !$title || strlen($title) < 2) return;
            if (Str::contains(strtolower($title), ['home', 'telegram', 'disclaimer'])) return;

            $movieUrl = Str::startsWith($href, 'http') ? $href : $this->baseUrl . '/' . ltrim($href, '/');
            if (!Str::contains($movieUrl, 'moviezda.com')) return;

            $this->saveMovieFromListing($title, $movieUrl, null, $language, 'Actor Collection');
        });
    }

    /**
     * Parse and upsert a movie entry from a listing.
     */
    public function saveMovieFromListing(string $title, string $movieUrl, ?int $catYear, string $language, string $category): ?Movie
    {
        // Extract year from title or URL
        $year = $catYear;
        if (preg_match('/\((\d{4})\)/', $title, $m)) {
            $year = (int) $m[1];
        } elseif (!$year && preg_match('/-(\d{4})-/', $movieUrl, $m)) {
            $year = (int) $m[1];
        }

        // Clean title
        $cleanTitle = trim(preg_replace('/\s*\(\d{4}\)\s*/', '', $title));
        $cleanTitle = trim(preg_replace('/\s+(Full|Web\s*Series)\b/i', '', $cleanTitle));
        $cleanTitle = trim(preg_replace('/\s+/', ' ', $cleanTitle));
        if (empty($cleanTitle)) return null;

        // URL slug identifier
        $urlPath    = trim(parse_url($movieUrl, PHP_URL_PATH) ?? '', '/');
        $slug       = Str::slug($cleanTitle);
        $identifier = $urlPath ?: $slug . ($year ? '-' . $year : '');

        $isTamilDubbed = Str::contains(strtolower($language), 'dubbed') ||
                         Str::contains(strtolower($movieUrl), 'dubbed') ||
                         Str::contains(strtolower($title), 'dubbed');

        $cleanDesc = $isTamilDubbed
            ? "Experience the complete Tamil dubbed edition of {$cleanTitle}" . ($year ? " ({$year})" : "") . ". Watch and download authorized high definition prints across multi-resolution formats."
            : "Experience the official Tamil cinematic story of {$cleanTitle}" . ($year ? " ({$year})" : "") . ". Stream and download authorized prints in verified high definition formats.";

        try {
            // Check existing movie by identifier or by clean title + year
            $movie = Movie::withTrashed()
                ->where('source_identifier', $identifier)
                ->orWhere(function ($q) use ($cleanTitle, $year) {
                    $q->where('title', $cleanTitle);
                    if ($year) $q->where('release_year', $year);
                })
                ->first();

            if ($movie) {
                if ($movie->trashed()) {
                    $movie->restore();
                }
                
                // If description contains scraping artifact, update it
                $needsDescUpdate = empty($movie->description)
                    || Str::contains($movie->description, 'Scraped from')
                    || Str::contains($movie->description, 'Source:');

                $movie->update([
                    'title'        => $cleanTitle,
                    'release_year' => $year ?: $movie->release_year,
                    'description'  => $needsDescUpdate ? $cleanDesc : $movie->description,
                    'source_url'   => $movieUrl,
                    'status'       => 'active',
                ]);
            } else {
                // Generate unique slug
                $uniqueSlug = $slug;
                $counter = 1;
                while (Movie::withTrashed()->where('slug', $uniqueSlug)->exists()) {
                    $uniqueSlug = "{$slug}-{$counter}";
                    $counter++;
                }

                $movie = Movie::create([
                    'title'             => $cleanTitle,
                    'slug'              => $uniqueSlug,
                    'description'       => $cleanDesc,
                    'release_year'      => $year,
                    'status'            => 'active',
                    'source_url'        => $movieUrl,
                    'source_identifier' => $identifier,
                    'view_count'        => rand(500, 2500),
                ]);
            }

            // Attach Language
            $langName = $isTamilDubbed ? 'Tamil Dubbed' : 'Tamil';
            $lang = Language::firstOrCreate(['slug' => Str::slug($langName)], ['name' => $langName]);
            if (!$movie->languages()->where('languages.id', $lang->id)->exists()) {
                $movie->languages()->syncWithoutDetaching([$lang->id]);
            }

            return $movie;
        } catch (\Exception $e) {
            $this->totalFailed++;
            Log::warning("Could not save [{$cleanTitle}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Enrich a movie with real details (poster, synopsis, download qualities) by visiting its Moviezda page.
     */
    public function enrichMovieDetails(Movie $movie): void
    {
        if (!$movie->source_url || !Str::contains($movie->source_url, 'moviezda.com')) {
            return;
        }

        $body = $this->fetchHtml($movie->source_url);
        if (!$body) return;

        $crawler = new Crawler($body, $movie->source_url);

        // 1. Extract Poster
        $posterUrl = null;
        $crawler->filter('img')->each(function (Crawler $img) use (&$posterUrl) {
            if ($posterUrl) return;
            $src = $img->attr('src');
            if ($src && (Str::contains($src, '/posters/') || Str::contains($src, '/uploads/'))) {
                $posterUrl = Str::startsWith($src, 'http') ? $src : $this->baseUrl . '/' . ltrim($src, '/');
            }
        });

        // 2. Extract Synopsis
        $synopsis = null;
        $crawler->filter('div, p')->each(function (Crawler $node) use (&$synopsis) {
            if ($synopsis) return;
            $text = trim($node->text());
            if (Str::startsWith(strtolower($text), 'synopsis:') || Str::startsWith(strtolower($text), 'plot:')) {
                $clean = trim(preg_replace('/^(synopsis|plot):\s*/i', '', $text));
                if (strlen($clean) > 15) {
                    $synopsis = $clean;
                }
            }
        });

        $updates = [];
        if ($posterUrl && empty($movie->poster_path)) {
            $updates['poster_path'] = $posterUrl;
        }
        if ($synopsis) {
            $updates['description'] = $synopsis;
        }

        if (!empty($updates)) {
            $movie->update($updates);
        }

        // 3. Extract download print links if movie has none
        if ($movie->links()->count() === 0) {
            $crawler->filter('div.f a, div.download a')->each(function (Crawler $a) use ($movie) {
                $text = trim($a->text());
                $href = $a->attr('href');
                if (preg_match('/(1080p|720p|480p|360p|Original|HD|PreDVD)/i', $text)) {
                    $linkUrl = Str::startsWith($href, 'http') ? $href : $this->baseUrl . '/' . ltrim($href, '/');
                    $res = 'Standard HD';
                    $size = '1.2 GB';
                    if (str_contains($text, '1080p')) { $res = '1920x1080'; $size = '2.4 GB'; }
                    elseif (str_contains($text, '720p')) { $res = '1280x720'; $size = '1.2 GB'; }
                    elseif (str_contains($text, '480p')) { $res = '854x480'; $size = '650 MB'; }
                    elseif (str_contains($text, '360p')) { $res = '640x360'; $size = '450 MB'; }

                    MovieLink::updateOrCreate(
                        ['movie_id' => $movie->id, 'quality' => $text],
                        [
                            'resolution'   => $res,
                            'file_size'    => $size,
                            'download_url' => $linkUrl,
                            'status'       => true,
                            'source_name'  => 'Moviesda'
                        ]
                    );
                }
            });
        }
    }

    protected function getTotalPages(Crawler $crawler): int
    {
        try {
            $span = $crawler->filter('#totalPages');
            if ($span->count() > 0) {
                return max(1, (int) trim($span->text()));
            }

            $lastPage = 1;
            $crawler->filter('ul.pagination li a')->each(function (Crawler $a) use (&$lastPage) {
                $text = trim($a->text());
                if (is_numeric($text)) {
                    $lastPage = max($lastPage, (int) $text);
                }
            });
            return $lastPage;
        } catch (\Exception $e) {
            return 1;
        }
    }

    public function fetchHtml(string $url): ?string
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $response = Http::withHeaders($this->headers)
                    ->timeout(20)
                    ->get($url);

                if ($response->successful()) {
                    return $response->body();
                }
            } catch (\Exception $e) {
                // retry
            }
            if ($attempt < 3) usleep(300000);
        }
        return null;
    }
}
