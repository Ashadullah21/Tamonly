<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Movie;
use App\Models\MovieLink;
use App\Models\Language;
use Illuminate\Support\Str;

/**
 * Comprehensive Moviesda scraper.
 *
 * Covers all year-based listing pages, A-to-Z alphabetical pages, actor
 * collections, Tamil dubbed (Indian), web series, and HD mobile pages.
 *
 * Modes:
 *   full        — Scrape ALL pages of every category (initial bulk import).
 *   incremental — Only scrape the first N pages of each category to detect
 *                 movies added since the last run. Safe for scheduled/cron use.
 */
class MoviessdasScraper
{
    protected string $baseUrl = 'https://moviezda.com';

    protected array $headers = [
        'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language' => 'en-US,en;q=0.9',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Connection'      => 'keep-alive',
        'DNT'             => '1',
        'Upgrade-Insecure-Requests' => '1',
    ];

    /**
     * How many pages to scan per category in incremental mode.
     * New movies always appear on page 1 (most recent first).
     */
    protected int $incrementalPageLimit = 2;

    /**
     * Year-based and named listing categories from moviezda.com.
     * NOTE: Do NOT include Tamil Dubbed [Hollywood] — that links externally
     *       to isaidub.world, a different domain that we cannot scrape.
     */
    protected array $yearCategories = [
        ['path' => '/tamil-2026-movies/',          'year' => 2026, 'language' => 'Tamil', 'category' => 'Tamil Movies'],
        ['path' => '/moviesda-tamil-movies-2026/', 'year' => 2026, 'language' => 'Tamil', 'category' => 'Tamil Movies'],
        ['path' => '/tamil-2025-movies/',          'year' => 2025, 'language' => 'Tamil', 'category' => 'Tamil Movies'],
        ['path' => '/tamil-2024-movies/',          'year' => 2024, 'language' => 'Tamil', 'category' => 'Tamil Movies'],
        ['path' => '/tamil-2023-movies/',          'year' => 2023, 'language' => 'Tamil', 'category' => 'Tamil Movies'],
        ['path' => '/tamil-2022-movies/',          'year' => 2022, 'language' => 'Tamil', 'category' => 'Tamil Movies'],
        ['path' => '/tamil-2021-movies/',          'year' => 2021, 'language' => 'Tamil', 'category' => 'Tamil Movies'],
        ['path' => '/tamil-2020-movies/',          'year' => 2020, 'language' => 'Tamil', 'category' => 'Tamil Movies'],
        ['path' => '/tamil-2019-movies/',          'year' => 2019, 'language' => 'Tamil', 'category' => 'Tamil Movies'],
        ['path' => '/tamil-2018-movies/',          'year' => 2018, 'language' => 'Tamil', 'category' => 'Tamil Movies'],
        ['path' => '/tamil-2017-movies/',          'year' => 2017, 'language' => 'Tamil', 'category' => 'Tamil Movies'],
        ['path' => '/tamil-2016-movies/',          'year' => 2016, 'language' => 'Tamil', 'category' => 'Tamil Movies'],
        ['path' => '/tamil-2015-movies/',          'year' => 2015, 'language' => 'Tamil', 'category' => 'Tamil Movies'],
        ['path' => '/tamil-2014-movies/',          'year' => 2014, 'language' => 'Tamil', 'category' => 'Tamil Movies'],
        ['path' => '/tamil-2013-movies/',          'year' => 2013, 'language' => 'Tamil', 'category' => 'Tamil Movies'],
        ['path' => '/tamil-2012-movies/',          'year' => 2012, 'language' => 'Tamil', 'category' => 'Tamil Movies'],
    ];

    /**
     * Non-year themed categories (no specific year, paginated).
     */
    protected array $themeCategories = [
        // Tamil dubbed Indian movies (NOT the external Hollywood isaidub.world link)
        ['path' => '/tamil-dubbed-movies/',         'year' => null, 'language' => 'Tamil Dubbed',   'category' => 'Tamil Dubbed'],
        // Actor-based collection folders (each subfolder is an actor page → list of movies)
        ['path' => '/tamil-movies-collection/',     'year' => null, 'language' => 'Tamil',           'category' => 'Collection'],
        ['path' => '/moviesda-tamil-collections/',  'year' => null, 'language' => 'Tamil',           'category' => 'Collection'],
        // HD mobile
        ['path' => '/tamil-hd-movies/',             'year' => null, 'language' => 'Tamil',           'category' => 'HD Mobile'],
        ['path' => '/tamil-hd-movies-download/',    'year' => null, 'language' => 'Tamil',           'category' => 'HD Mobile'],
        // Tamil daily / latest
        ['path' => '/tamilrockers-movies/',         'year' => null, 'language' => 'Tamil',           'category' => 'Tamil Daily'],
        ['path' => '/tamil-latest-updates/',        'year' => null, 'language' => 'Tamil',           'category' => 'Tamil Latest'],
        // A-to-Z master listing
        ['path' => '/tamil-atoz-movies/',           'year' => null, 'language' => 'Tamil',           'category' => 'Tamil A-Z'],
        // Web series
        ['path' => '/tamil-web-series-download/',   'year' => null, 'language' => 'Tamil',           'category' => 'Web Series'],
        ['path' => '/tamil-webseries/',             'year' => null, 'language' => 'Tamil',           'category' => 'Web Series'],
    ];

    /**
     * Alphabet letters for the A-to-Z sub-pages at /tamil-movies/{letter}/.
     */
    protected array $alphabetLetters = [
        'a','b','c','d','e','f','g','h','i','j','k','l','m',
        'n','o','p','q','r','s','t','u','v','w','x','y','z',
    ];

    protected int $totalSaved  = 0;
    protected int $totalFailed = 0;
    protected int $totalSkipped = 0;
    protected $outputCallback  = null;

    // Track processed URLs in this run to avoid recursion loops
    protected array $visitedUrls = [];

    public function setOutputCallback(callable $callback): void
    {
        $this->outputCallback = $callback;
    }

    protected function output(string $message): void
    {
        Log::info('[Scraper] ' . $message);
        if ($this->outputCallback) {
            ($this->outputCallback)($message);
        }
    }

    /**
     * Run a full scrape — all categories, all pages.
     */
    public function scrapeAll(): array
    {
        return $this->runScrape(mode: 'full');
    }

    /**
     * Run an incremental scrape — only page 1–2 of each category to catch new additions.
     * Designed for scheduled / cron use. Safe for Render Free Tier.
     */
    public function scrapeIncremental(): array
    {
        return $this->runScrape(mode: 'incremental');
    }

    protected function runScrape(string $mode): array
    {
        $this->totalSaved   = 0;
        $this->totalFailed  = 0;
        $this->totalSkipped = 0;
        $this->visitedUrls  = [];

        $this->output("🚀 Mode: {$mode}");

        // 1. Year-based categories
        $this->output("\n📅 === Year-Based Categories ===");
        foreach ($this->yearCategories as $cat) {
            $this->scrapeCategory($cat, $mode);
            usleep(500000); // 0.5s between categories
        }

        // 2. Theme categories (collections, HD, etc.)
        $this->output("\n🗂️  === Theme Categories ===");
        foreach ($this->themeCategories as $cat) {
            $this->scrapeCategory($cat, $mode);
            usleep(500000);
        }

        // 3. A-to-Z alphabetical sub-pages (full mode only — too slow for incremental)
        if ($mode === 'full') {
            $this->output("\n🔤 === A-to-Z Alphabetical Pages ===");
            foreach ($this->alphabetLetters as $letter) {
                $this->scrapeAlphabetLetter($letter);
                usleep(800000); // 0.8s between letters
            }
        }

        // Record last sync time
        Cache::put('scraper_last_sync', now()->toIso8601String(), now()->addDays(30));

        return [
            'saved'   => $this->totalSaved,
            'failed'  => $this->totalFailed,
            'skipped' => $this->totalSkipped,
            'mode'    => $mode,
        ];
    }

    /**
     * Scrape one category listing with full pagination support.
     *
     * FIX: The pagination URL must be built as:
     *   rtrim($url, '/') . '?page=' . $page
     * NOT with an extra slash:
     *   rtrim($url, '/') . '/?page=' . $page   ← was causing 404s
     */
    public function scrapeCategory(array $cat, string $mode = 'full'): void
    {
        $path     = $cat['path'];
        $year     = $cat['year'];
        $language = $cat['language'];
        $category = $cat['category'];

        // Build absolute first-page URL
        $firstPageUrl = Str::startsWith($path, 'http')
            ? $path
            : $this->baseUrl . '/' . ltrim($path, '/');

        // Skip external domains (safety guard)
        if (!Str::contains($firstPageUrl, 'moviezda.com')) {
            $this->output("   ⛔ Skipped external URL: {$firstPageUrl}");
            return;
        }

        // Skip already-visited URLs (prevents recursion loops)
        if (in_array($firstPageUrl, $this->visitedUrls)) {
            $this->output("   ↩️  Already visited: {$firstPageUrl}");
            return;
        }

        $this->output("📂 [{$category}] {$language} " . ($year ?? 'All') . " → {$firstPageUrl}");
        $this->visitedUrls[] = $firstPageUrl;

        $body = $this->fetchHtml($firstPageUrl);
        if (!$body) {
            $this->output("   ⚠️  Could not fetch: {$firstPageUrl}");
            return;
        }

        $crawler    = new Crawler($body, $firstPageUrl);
        $totalPages = $this->getTotalPages($crawler);

        // In incremental mode, cap pages to incrementalPageLimit
        $maxPage = $mode === 'incremental'
            ? min($totalPages, $this->incrementalPageLimit)
            : $totalPages;

        $this->output("   📄 Pages: {$totalPages} total, scanning: {$maxPage}");

        // Page 1 (already fetched)
        $this->scrapePage($crawler, $firstPageUrl, $year, $language, $category);

        // Pages 2..maxPage
        for ($page = 2; $page <= $maxPage; $page++) {
            // ✅ FIXED: No double slash — correct format is `?page=N` not `/?page=N`
            $pageUrl = rtrim($firstPageUrl, '/') . '?page=' . $page;
            $this->output("   ↳ Page {$page}/{$maxPage}: {$pageUrl}");

            if (in_array($pageUrl, $this->visitedUrls)) {
                continue;
            }
            $this->visitedUrls[] = $pageUrl;

            $body = $this->fetchHtml($pageUrl);
            if (!$body) {
                $this->output("   ⚠️  Skipped page {$page} (fetch failed)");
                continue;
            }

            $pageCrawler = new Crawler($body, $pageUrl);
            $this->scrapePage($pageCrawler, $pageUrl, $year, $language, $category);

            usleep(300000); // 0.3s between pages
        }
    }

    /**
     * Scrape a single A-to-Z letter page and all its paginated sub-pages.
     * URL pattern: /tamil-movies/{letter}/?page=N
     */
    protected function scrapeAlphabetLetter(string $letter): void
    {
        $firstUrl = $this->baseUrl . '/tamil-movies/' . $letter . '/';
        $this->output("🔤 Letter '{$letter}': {$firstUrl}");

        if (in_array($firstUrl, $this->visitedUrls)) {
            return;
        }

        $body = $this->fetchHtml($firstUrl);
        if (!$body) {
            $this->output("   ⚠️  Could not fetch letter '{$letter}'");
            return;
        }

        $this->visitedUrls[] = $firstUrl;
        $crawler    = new Crawler($body, $firstUrl);
        $totalPages = $this->getTotalPages($crawler);
        $this->output("   📄 Pages for '{$letter}': {$totalPages}");

        $this->scrapePage($crawler, $firstUrl, null, 'Tamil', 'Tamil A-Z');

        for ($page = 2; $page <= $totalPages; $page++) {
            $pageUrl = rtrim($firstUrl, '/') . '?page=' . $page;
            if (in_array($pageUrl, $this->visitedUrls)) continue;
            $this->visitedUrls[] = $pageUrl;

            $body = $this->fetchHtml($pageUrl);
            if (!$body) {
                $this->output("   ⚠️  Skipped letter '{$letter}' page {$page}");
                continue;
            }

            $pageCrawler = new Crawler($body, $pageUrl);
            $this->scrapePage($pageCrawler, $pageUrl, null, 'Tamil', 'Tamil A-Z');
            usleep(300000);
        }
    }

    /**
     * Extract all movie entries from a listing page.
     * The site renders listings as: <div class="f"> <img> <a href="...">Title</a> </div>
     */
    protected function scrapePage(
        Crawler $crawler,
        string  $pageUrl,
        ?int    $catYear,
        string  $language,
        string  $category
    ): void {
        $saved = 0;

        $crawler->filter('div.f a')->each(function (Crawler $node) use (
            $catYear, $language, $category, &$saved
        ) {
            $href  = trim($node->attr('href') ?? '');
            $title = trim($node->text());

            // Basic sanity checks
            if (!$href || !$title || strlen($title) < 2) return;
            if (Str::startsWith($href, '#') || Str::contains($href, 'javascript:')) return;

            // Skip nav / social links
            $lowerTitle = strtolower($title);
            $skipTitles = ['home', 'facebook', 'telegram', 'contact', 'disclaimer',
                           'request', 'search', 'moviesda', 'download now', 'next', 'prev'];
            foreach ($skipTitles as $skip) {
                if (str_contains($lowerTitle, $skip)) return;
            }

            // Skip pure numeric or single-character links (pagination artifacts)
            if (is_numeric($title) || strlen($title) === 1) return;

            // Build absolute URL
            $itemUrl = Str::startsWith($href, 'http')
                ? $href
                : $this->baseUrl . '/' . ltrim($href, '/');

            // Only process URLs from our target domain
            if (!Str::contains($itemUrl, 'moviezda.com')) return;

            // Skip external links that somehow slipped through
            if (Str::contains($itemUrl, ['isaidub', 'tamilrockers.', 'kuttymovies'])) return;

            // If this is a collection/actor folder, recurse into it
            $isCollection = $category === 'Collection'
                || Str::contains($lowerTitle, 'collection')
                || Str::contains($itemUrl, '-collection')
                || Str::contains($itemUrl, 'collection/');

            if ($isCollection) {
                $this->scrapeActorCollectionFolder($itemUrl, $language);
                return;
            }

            // Skip alphabet/letter navigation links (A, B, C ... Z)
            if (strlen($title) === 1 && ctype_alpha($title)) return;

            // It's a movie page link — save it
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
     * Recurse into an actor collection folder (e.g. /actor-ajith-movies-collection/).
     * Only processes direct movie links within the folder — does NOT recurse deeper.
     */
    protected function scrapeActorCollectionFolder(string $collectionUrl, string $language): void
    {
        if (in_array($collectionUrl, $this->visitedUrls)) return;
        if (!Str::contains($collectionUrl, 'moviezda.com')) return;

        $this->output("      📁 Collection: {$collectionUrl}");
        $this->visitedUrls[] = $collectionUrl;

        $body = $this->fetchHtml($collectionUrl);
        if (!$body) return;

        $crawler    = new Crawler($body, $collectionUrl);
        $totalPages = $this->getTotalPages($crawler);

        for ($page = 1; $page <= $totalPages; $page++) {
            $pageBody    = $page === 1 ? $body : $this->fetchHtml(rtrim($collectionUrl, '/') . '?page=' . $page);
            if (!$pageBody) continue;

            $pageCrawler = $page === 1 ? $crawler : new Crawler($pageBody, $collectionUrl);

            $pageCrawler->filter('div.f a')->each(function (Crawler $node) use ($language, $collectionUrl) {
                $href  = trim($node->attr('href') ?? '');
                $title = trim($node->text());

                if (!$href || !$title || strlen($title) < 2) return;
                if (is_numeric($title) || strlen($title) === 1) return;

                $lowerTitle = strtolower($title);
                if (str_contains($lowerTitle, 'home') || str_contains($lowerTitle, 'telegram')) return;

                $movieUrl = Str::startsWith($href, 'http')
                    ? $href
                    : $this->baseUrl . '/' . ltrim($href, '/');

                if (!Str::contains($movieUrl, 'moviezda.com')) return;
                // Do NOT recurse into sub-collections — only save direct movie links
                if (Str::contains($movieUrl, '-collection') || Str::contains($movieUrl, 'collection/')) return;
                if (in_array($movieUrl, $this->visitedUrls)) return;

                $this->saveMovieFromListing($title, $movieUrl, null, $language, 'Actor Collection');
                $this->totalSaved++;
            });

            if ($page < $totalPages) usleep(300000);
        }
    }

    /**
     * Parse a movie listing entry and upsert it into the database.
     * Deduplication: by source_identifier (URL path) first, then title+year.
     */
    public function saveMovieFromListing(
        string $title,
        string $movieUrl,
        ?int   $catYear,
        string $language,
        string $category
    ): ?Movie {
        // Guard: already visited this movie URL
        if (in_array($movieUrl, $this->visitedUrls)) {
            $this->totalSkipped++;
            return null;
        }

        // Extract year from title or URL
        $year = $catYear;
        if (preg_match('/\((\d{4})\)/', $title, $m)) {
            $year = (int) $m[1];
        } elseif (!$year && preg_match('/-(\d{4})-/', $movieUrl, $m)) {
            $year = (int) $m[1];
        }

        // Validate year range
        if ($year && ($year < 1950 || $year > (int)date('Y') + 1)) {
            $year = null;
        }

        // Clean title — remove year suffixes, trailing quality labels
        $cleanTitle = preg_replace('/\s*\(\d{4}\)\s*/', '', $title);
        $cleanTitle = preg_replace('/\s+(Full|Web\s*Series|Movie|HD|1080p|720p|480p)\b/i', '', $cleanTitle);
        $cleanTitle = preg_replace('/\s+/', ' ', $cleanTitle);
        $cleanTitle = trim($cleanTitle);
        if (empty($cleanTitle) || strlen($cleanTitle) < 2) return null;

        // Source identifier = URL path (unique per movie on the site)
        $urlPath    = trim(parse_url($movieUrl, PHP_URL_PATH) ?? '', '/');
        $slug       = Str::slug($cleanTitle);
        $identifier = $urlPath ?: $slug . ($year ? '-' . $year : '');

        $isTamilDubbed = Str::contains(strtolower($language), 'dubbed')
            || Str::contains(strtolower($movieUrl), 'dubbed')
            || Str::contains(strtolower($title), 'dubbed');

        $cleanDesc = $isTamilDubbed
            ? "Experience the complete Tamil dubbed edition of {$cleanTitle}" . ($year ? " ({$year})" : "") . ". Watch and download authorized high definition prints across multi-resolution formats."
            : "Experience the official Tamil cinematic release of {$cleanTitle}" . ($year ? " ({$year})" : "") . ". Stream and download authorized prints in verified high definition formats.";

        try {
            // Deduplication check — by identifier first, then by title+year
            $movie = Movie::withTrashed()
                ->where('source_identifier', $identifier)
                ->orWhere(function ($q) use ($cleanTitle, $year) {
                    $q->where('title', $cleanTitle);
                    if ($year) {
                        $q->where('release_year', $year);
                    }
                })
                ->first();

            if ($movie) {
                if ($movie->trashed()) {
                    $movie->restore();
                }

                $needsDescUpdate = empty($movie->description)
                    || Str::contains($movie->description, 'Scraped from')
                    || Str::contains($movie->description, 'Source:');

                $movie->update([
                    'title'             => $cleanTitle,
                    'release_year'      => $year ?: $movie->release_year,
                    'description'       => $needsDescUpdate ? $cleanDesc : $movie->description,
                    'source_url'        => $movieUrl,
                    'source_identifier' => $identifier,
                    'status'            => 'active',
                    'scraped_at'        => now(),
                ]);

                $this->totalSkipped++; // existing record, not a new save
            } else {
                // Generate a unique slug
                $uniqueSlug = $slug;
                $counter    = 1;
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
                    'view_count'        => rand(500, 3500),
                    'scraped_at'        => now(),
                ]);
            }

            // Attach language
            $langName = $isTamilDubbed ? 'Tamil Dubbed' : 'Tamil';
            $lang = Language::firstOrCreate(
                ['slug' => Str::slug($langName)],
                ['name' => $langName]
            );
            if (!$movie->languages()->where('languages.id', $lang->id)->exists()) {
                $movie->languages()->syncWithoutDetaching([$lang->id]);
            }

            // Mark this URL as processed so we don't re-visit it
            $this->visitedUrls[] = $movieUrl;

            return $movie;
        } catch (\Exception $e) {
            $this->totalFailed++;
            Log::warning("[Scraper] Could not save [{$cleanTitle}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Enrich an already-saved movie with poster, synopsis, and download links
     * by fetching its individual detail page.
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
            $src = $img->attr('src') ?? '';
            if ($src && (
                Str::contains($src, '/posters/')
                || Str::contains($src, '/uploads/')
                || Str::contains($src, '/images/')
            )) {
                $posterUrl = Str::startsWith($src, 'http')
                    ? $src
                    : $this->baseUrl . '/' . ltrim($src, '/');
            }
        });

        // 2. Extract Synopsis
        $synopsis = null;
        $crawler->filter('div, p')->each(function (Crawler $node) use (&$synopsis) {
            if ($synopsis) return;
            $text = trim($node->text());
            if (
                Str::startsWith(strtolower($text), 'synopsis:')
                || Str::startsWith(strtolower($text), 'plot:')
                || Str::startsWith(strtolower($text), 'story:')
            ) {
                $clean = trim(preg_replace('/^(synopsis|plot|story):\s*/i', '', $text));
                if (strlen($clean) > 20) {
                    $synopsis = $clean;
                }
            }
        });

        $updates = [];
        if ($posterUrl && empty($movie->poster_path)) {
            $updates['poster_path'] = $posterUrl;
        }
        if ($synopsis && (empty($movie->description) || strlen($movie->description) < 30)) {
            $updates['description'] = $synopsis;
        }

        if (!empty($updates)) {
            $movie->update($updates);
        }

        // 3. Extract download quality links if movie has none
        if ($movie->links()->count() === 0) {
            $crawler->filter('div.f a, div.download a, a')->each(function (Crawler $a) use ($movie) {
                $text = trim($a->text());
                $href = $a->attr('href') ?? '';

                if (!preg_match('/(1080p|720p|480p|360p|Original|HD|PreDVD|4K)/i', $text)) return;

                $linkUrl = Str::startsWith($href, 'http')
                    ? $href
                    : $this->baseUrl . '/' . ltrim($href, '/');

                if (!Str::contains($linkUrl, 'moviezda.com') && !Str::contains($linkUrl, 'download')) return;

                [$res, $size] = match (true) {
                    str_contains($text, '1080p') => ['1920x1080', '2.4 GB'],
                    str_contains($text, '720p')  => ['1280x720',  '1.2 GB'],
                    str_contains($text, '480p')  => ['854x480',   '650 MB'],
                    str_contains($text, '360p')  => ['640x360',   '450 MB'],
                    default                      => ['Standard HD', '1.0 GB'],
                };

                MovieLink::updateOrCreate(
                    ['movie_id' => $movie->id, 'quality' => $text],
                    [
                        'resolution'   => $res,
                        'file_size'    => $size,
                        'download_url' => $linkUrl,
                        'status'       => true,
                        'source_name'  => 'Moviesda',
                    ]
                );
            });
        }
    }

    /**
     * Detect the total number of pages for a listing using the site's
     * built-in pagination metadata: <span id="totalPages">N</span>
     * Falls back to reading the highest page number from pagination links.
     */
    protected function getTotalPages(Crawler $crawler): int
    {
        try {
            // Primary: dedicated totalPages span
            $span = $crawler->filter('#totalPages');
            if ($span->count() > 0) {
                $n = (int) trim($span->text());
                if ($n > 0) return $n;
            }

            // Fallback: find the highest numeric link in the pagination
            $lastPage = 1;
            $crawler->filter('ul.pagination li a')->each(function (Crawler $a) use (&$lastPage) {
                $text = trim($a->text());
                if (is_numeric($text) && (int)$text > $lastPage) {
                    $lastPage = (int)$text;
                }
            });

            return max(1, $lastPage);
        } catch (\Exception $e) {
            return 1;
        }
    }

    /**
     * Fetch HTML with retry logic (3 attempts, 0.5s back-off).
     * Returns null on all failures — caller must handle gracefully.
     */
    public function fetchHtml(string $url): ?string
    {
        // Skip external domains
        if (Str::contains($url, ['isaidub', 'tamilrockers.li', 'kuttymovies.'])) {
            return null;
        }

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $response = Http::withHeaders($this->headers)
                    ->timeout(25)
                    ->get($url);

                if ($response->successful() && strlen($response->body()) > 500) {
                    return $response->body();
                }

                // 404 / 410 — no point retrying
                if (in_array($response->status(), [404, 410])) {
                    return null;
                }
            } catch (\Exception $e) {
                Log::debug("[Scraper] Fetch attempt {$attempt} failed for {$url}: " . $e->getMessage());
            }

            if ($attempt < 3) usleep(500000); // 0.5s between retries
        }

        return null;
    }

    /**
     * Return stats counters (for use by callers after scraping).
     */
    public function getStats(): array
    {
        return [
            'saved'   => $this->totalSaved,
            'failed'  => $this->totalFailed,
            'skipped' => $this->totalSkipped,
        ];
    }
}
