<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Movie;
use App\Models\MovieLink;
use App\Models\Language;
use Illuminate\Support\Str;

/**
 * Moviesda Full Scraper — High Resilience & Full Coverage Edition.
 *
 * Key fixes:
 *  1. Correct pagination: Moviezda requires trailing slash before query string ('/?page=N').
 *     Without the slash ('?page=N'), the server 301/302 redirects to movies.php, which
 *     caused all pages 2..N to fail and return 0 movies.
 *  2. Title cleaning: Fixed word concatenation bug (e.g. 'ButterflyTamil') by safely
 *     replacing years with spaces, collapsing whitespace, and stripping metadata.
 *  3. Category detection guard: Directory/listing pages are never mistakenly saved as movies.
 *  4. Actor collections: Automatically crawls all actor collection pages (Rajinikanth,
 *     Kamal Haasan, Ajith, Suriya, Dhanush, etc.) for vintage movie coverage.
 *  5. DB connection resilience: Automatically reconnects and retries on transient network/DNS
 *     drops to Aiven Cloud MySQL ('getaddrinfo failed' / 'server gone away').
 *  6. cURL fetcher: Native cURL with automatic Brotli/gzip decompression.
 */
class MoviessdasScraper
{
    protected string $baseUrl = 'https://moviezda.com';

    // ── Categories ────────────────────────────────────────────────────────────

    protected array $yearCategories = [
        ['path' => '/tamil-2026-movies/',          'year' => 2026, 'language' => 'Tamil'],
        ['path' => '/moviesda-tamil-movies-2026/', 'year' => 2026, 'language' => 'Tamil'],
        ['path' => '/tamil-2025-movies/',          'year' => 2025, 'language' => 'Tamil'],
        ['path' => '/tamil-2024-movies/',          'year' => 2024, 'language' => 'Tamil'],
        ['path' => '/tamil-2023-movies/',          'year' => 2023, 'language' => 'Tamil'],
        ['path' => '/tamil-2022-movies/',          'year' => 2022, 'language' => 'Tamil'],
        ['path' => '/tamil-2021-movies/',          'year' => 2021, 'language' => 'Tamil'],
        ['path' => '/tamil-2020-movies/',          'year' => 2020, 'language' => 'Tamil'],
        ['path' => '/tamil-2019-movies/',          'year' => 2019, 'language' => 'Tamil'],
        ['path' => '/tamil-2018-movies/',          'year' => 2018, 'language' => 'Tamil'],
        ['path' => '/tamil-2017-movies/',          'year' => 2017, 'language' => 'Tamil'],
        ['path' => '/tamil-2016-movies/',          'year' => 2016, 'language' => 'Tamil'],
        ['path' => '/tamil-2015-movies/',          'year' => 2015, 'language' => 'Tamil'],
        ['path' => '/tamil-2014-movies/',          'year' => 2014, 'language' => 'Tamil'],
        ['path' => '/tamil-2013-movies/',          'year' => 2013, 'language' => 'Tamil'],
        ['path' => '/tamil-2012-movies/',          'year' => 2012, 'language' => 'Tamil'],
    ];

    protected array $themeCategories = [
        ['path' => '/tamil-dubbed-movies/',        'year' => null, 'language' => 'Tamil Dubbed'],
        ['path' => '/tamil-hd-movies/',            'year' => null, 'language' => 'Tamil'],
        ['path' => '/tamil-web-series-download/',  'year' => null, 'language' => 'Tamil'],
    ];

    protected array $collectionDirectories = [
        '/moviesda-tamil-collections/',
        '/tamil-movies-collection/',
    ];

    protected array $alphabetLetters = [
        'a','b','c','d','e','f','g','h','i','j','k','l','m',
        'n','o','p','q','r','s','t','u','v','w','x','y','z',
    ];

    // ── State ─────────────────────────────────────────────────────────────────

    protected int $totalSaved   = 0;
    protected int $totalFailed  = 0;
    protected int $totalSkipped = 0;
    protected array $visitedUrls = [];
    protected int $incrementalPageLimit = 2;
    protected $outputCallback = null;

    // ── Public API ────────────────────────────────────────────────────────────

    public function setOutputCallback(callable $cb): void
    {
        $this->outputCallback = $cb;
    }

    public function scrapeAll(): array
    {
        return $this->run('full');
    }

    public function scrapeIncremental(): array
    {
        return $this->run('incremental');
    }

    public function getStats(): array
    {
        return [
            'saved'   => $this->totalSaved,
            'failed'  => $this->totalFailed,
            'skipped' => $this->totalSkipped,
        ];
    }

    // ── Core Orchestrator ─────────────────────────────────────────────────────

    protected function run(string $mode): array
    {
        $this->totalSaved   = 0;
        $this->totalFailed  = 0;
        $this->totalSkipped = 0;
        $this->visitedUrls  = [];

        $this->out("🚀 Mode: {$mode}");

        // 1. Year-based categories
        $this->out("\n📅 === Year-Based Categories ===");
        foreach ($this->yearCategories as $cat) {
            $this->scrapeCategory($cat, $mode);
            usleep(300000);
        }

        // 2. Theme categories (Dubbed, HD, Web Series)
        $this->out("\n🗂️  === Theme Categories ===");
        foreach ($this->themeCategories as $cat) {
            $this->scrapeCategory($cat, $mode);
            usleep(300000);
        }

        // 3. Actor & Special Collections (crawls sub-collections like Rajini, Kamal, Ajith...)
        $this->out("\n🎭 === Actor & Special Collections ===");
        $this->scrapeCollectionDirectories($mode);

        // 4. A–Z alphabetical pages (full mode only)
        if ($mode === 'full') {
            $this->out("\n🔤 === A-to-Z Alphabetical Pages ===");
            foreach ($this->alphabetLetters as $letter) {
                $this->scrapeAlphabetLetter($letter);
                usleep(400000);
            }
        }

        // Safe cache write
        try {
            Cache::put('scraper_last_sync', now()->toIso8601String(), now()->addDays(30));
        } catch (\Throwable $e) {
            Log::warning("[Scraper] Cache sync timestamp write skipped: " . $e->getMessage());
        }

        return [
            'saved'   => $this->totalSaved,
            'failed'  => $this->totalFailed,
            'skipped' => $this->totalSkipped,
            'mode'    => $mode,
        ];
    }

    // ── Category / Listing Scraper ────────────────────────────────────────────

    public function scrapeCategory(array $cat, string $mode = 'full'): void
    {
        $path     = $cat['path'];
        $year     = $cat['year'] ?? null;
        $language = $cat['language'] ?? 'Tamil';

        $firstUrl = Str::startsWith($path, 'http')
            ? $path
            : $this->baseUrl . '/' . ltrim($path, '/');

        if (!Str::contains($firstUrl, 'moviezda.com')) return;
        if (in_array($firstUrl, $this->visitedUrls)) return;

        $this->out("📂 {$language} " . ($year ?? 'All') . " → {$firstUrl}");
        $this->visitedUrls[] = $firstUrl;

        $body = $this->fetchHtml($firstUrl);
        if (!$body) {
            $this->out("   ⚠️  Could not fetch: {$firstUrl}");
            return;
        }

        $crawler    = new Crawler($body, $firstUrl);
        $totalPages = $this->detectTotalPages($crawler);

        $maxPage = $mode === 'incremental'
            ? min($totalPages, $this->incrementalPageLimit)
            : $totalPages;

        $this->out("   📄 Pages: {$totalPages}, scanning: {$maxPage}");

        // Scrape page 1
        $this->scrapeListingPage($crawler, $year, $language);

        // Pages 2..N — CRITICAL FIX: Moviezda requires trailing slash '/?page=N'
        for ($p = 2; $p <= $maxPage; $p++) {
            $pageUrl = rtrim($firstUrl, '/') . '/?page=' . $p;
            if (in_array($pageUrl, $this->visitedUrls)) continue;
            $this->visitedUrls[] = $pageUrl;

            $this->out("   ↳ Page {$p}/{$maxPage}: {$pageUrl}");

            $body = $this->fetchHtml($pageUrl);
            if (!$body) {
                $this->out("   ⚠️  Skipped page {$p} (fetch failed)");
                continue;
            }

            $this->scrapeListingPage(new Crawler($body, $pageUrl), $year, $language);
            usleep(250000);
        }
    }

    protected function scrapeAlphabetLetter(string $letter): void
    {
        $firstUrl = $this->baseUrl . '/tamil-movies/' . $letter . '/';
        if (in_array($firstUrl, $this->visitedUrls)) return;

        $this->out("🔤 Letter '{$letter}': {$firstUrl}");

        $body = $this->fetchHtml($firstUrl);
        if (!$body) {
            $this->out("   ⚠️  Could not fetch letter '{$letter}'");
            return;
        }

        $this->visitedUrls[] = $firstUrl;
        $crawler    = new Crawler($body, $firstUrl);
        $totalPages = $this->detectTotalPages($crawler);
        $this->out("   📄 Pages for '{$letter}': {$totalPages}");

        $this->scrapeListingPage($crawler, null, 'Tamil');

        // CRITICAL FIX: Trailing slash '/?page=N' for A-to-Z
        for ($p = 2; $p <= $totalPages; $p++) {
            $pageUrl = rtrim($firstUrl, '/') . '/?page=' . $p;
            if (in_array($pageUrl, $this->visitedUrls)) continue;
            $this->visitedUrls[] = $pageUrl;

            $body = $this->fetchHtml($pageUrl);
            if (!$body) continue;

            $this->scrapeListingPage(new Crawler($body, $pageUrl), null, 'Tamil');
            usleep(250000);
        }
    }

    /**
     * Crawl collection directories (like /moviesda-tamil-collections/)
     * and scrape each sub-collection (e.g. /rajinikanth-movie-collections/) as a listing page.
     */
    protected function scrapeCollectionDirectories(string $mode): void
    {
        foreach ($this->collectionDirectories as $dirPath) {
            $dirUrl = $this->baseUrl . '/' . ltrim($dirPath, '/');
            if (in_array($dirUrl, $this->visitedUrls)) continue;
            $this->visitedUrls[] = $dirUrl;

            $body = $this->fetchHtml($dirUrl);
            if (!$body) continue;

            $crawler = new Crawler($body, $dirUrl);
            $subCollections = [];
            $crawler->filter('div.f a')->each(function (Crawler $node) use (&$subCollections) {
                $href  = trim($node->attr('href') ?? '');
                $title = trim($node->text());
                if (!$href || strlen($title) < 2) return;
                $url = Str::startsWith($href, 'http') ? $href : $this->baseUrl . '/' . ltrim($href, '/');
                if (Str::contains($url, 'moviezda.com')) {
                    $subCollections[] = ['title' => $title, 'url' => $url];
                }
            });

            $this->out("   📂 Found " . count($subCollections) . " collections in {$dirPath}");

            foreach ($subCollections as $sub) {
                $this->scrapeCategory([
                    'path'     => $sub['url'],
                    'year'     => null,
                    'language' => 'Tamil',
                ], $mode);
                usleep(300000);
            }
        }
    }

    /**
     * Parse a listing page — find all movie entries (div.f > a) and process each movie.
     */
    protected function scrapeListingPage(Crawler $crawler, ?int $catYear, string $language): void
    {
        $movieLinks = [];

        $crawler->filter('div.f a')->each(function (Crawler $node) use ($catYear, $language, &$movieLinks) {
            $href  = trim($node->attr('href') ?? '');
            $title = trim($node->text());

            if (!$href || !$title || strlen($title) < 2) return;
            if (Str::startsWith($href, '#') || Str::contains($href, 'javascript:')) return;
            if (is_numeric($title) || strlen($title) === 1) return;

            $lowerTitle = strtolower($title);
            foreach (['home', 'facebook', 'telegram', 'contact', 'disclaimer', 'request', 'download now', 'next', 'prev'] as $skip) {
                if (str_contains($lowerTitle, $skip)) return;
            }

            // Skip links that are themselves category or index pages
            if (preg_match('/-(movies|collection|collections|download)\/$/i', $href)) {
                return;
            }

            $itemUrl = Str::startsWith($href, 'http')
                ? $href
                : $this->baseUrl . '/' . ltrim($href, '/');

            if (!Str::contains($itemUrl, 'moviezda.com')) return;
            if (Str::contains($itemUrl, ['isaidub', 'tamilrockers.'])) return;
            if (in_array($itemUrl, $this->visitedUrls)) return;

            $movieLinks[] = ['title' => $title, 'url' => $itemUrl];
        });

        $saved = 0;
        foreach ($movieLinks as $entry) {
            $movie = $this->processMoviePage($entry['title'], $entry['url'], $catYear, $language);
            if ($movie) {
                $saved++;
                $this->totalSaved++;
            }
        }

        if ($saved > 0) {
            $this->out("      ✅ Saved {$saved} movies from this page.");
        }
    }

    // ── Movie Detail Processor ────────────────────────────────────────────────

    protected function processMoviePage(
        string $listTitle,
        string $moviePageUrl,
        ?int   $catYear,
        string $language
    ): ?Movie {
        if (in_array($moviePageUrl, $this->visitedUrls)) {
            $this->totalSkipped++;
            return null;
        }
        $this->visitedUrls[] = $moviePageUrl;

        $body = $this->fetchHtml($moviePageUrl);
        if (!$body) {
            $this->totalFailed++;
            return null;
        }

        $crawler = new Crawler($body, $moviePageUrl);

        // Guard: If this page has pagination elements, it's a listing page, NOT a movie
        if ($crawler->filter('#totalPages')->count() > 0 || $crawler->filter('ul.pagination, div.pagination')->count() > 0) {
            return null;
        }

        // ── Extract movie title ────────────────────────────────────────────
        $rawTitle = '';
        if ($crawler->filter('h1')->count()) {
            $rawTitle = trim($crawler->filter('h1')->text());
        }
        if (empty($rawTitle) && $crawler->filter('title')->count()) {
            $rawTitle = trim($crawler->filter('title')->text());
        }
        if (empty($rawTitle)) {
            $rawTitle = $listTitle;
        }

        // Clean title with validated logic
        $cleanTitle = $this->cleanTitle($rawTitle ?: $listTitle);
        if (empty($cleanTitle)) return null;

        // Year extraction
        $year = $catYear;
        if (preg_match('/\((\d{4})\)/', $rawTitle, $m) || preg_match('/\b(20\d{2}|19\d{2})\b/', $rawTitle, $m)) {
            $year = (int)$m[1];
        } elseif (preg_match('/\((\d{4})\)/', $listTitle, $m) || preg_match('/\b(20\d{2}|19\d{2})\b/', $listTitle, $m)) {
            $year = (int)$m[1];
        } elseif (preg_match('/-(\d{4})-/', $moviePageUrl, $m)) {
            $year = (int)$m[1];
        }
        if ($year && ($year < 1940 || $year > (int)date('Y') + 1)) $year = null;

        // Poster image extraction
        $posterUrl = null;
        $ogImg = $crawler->filter('meta[property="og:image"]');
        if ($ogImg->count()) {
            $posterUrl = $ogImg->attr('content');
        }
        if (!$posterUrl && preg_match('/"thumbnailUrl"\s*:\s*"([^"]+)"/', $body, $m)) {
            $posterUrl = $m[1];
        }
        if (!$posterUrl) {
            $crawler->filter('img')->each(function (Crawler $img) use (&$posterUrl) {
                if ($posterUrl) return;
                $src = $img->attr('src') ?? '';
                if (Str::contains($src, ['/uploads/', '/shots/', '/posters/'])) {
                    $posterUrl = Str::startsWith($src, 'http') ? $src : $this->baseUrl . '/' . ltrim($src, '/');
                }
            });
        }

        // Dubbed detection
        $isTamilDubbed = Str::contains(strtolower($language), 'dubbed')
            || Str::contains(strtolower($moviePageUrl), 'dubbed')
            || Str::contains(strtolower($rawTitle), 'dubbed');

        // Upsert movie record with DB retry resilience
        $movie = $this->upsertMovie($cleanTitle, $moviePageUrl, $year, $posterUrl, $isTamilDubbed);
        if (!$movie) return null;

        // Attach Language
        $this->withDbRetry(function () use ($movie, $isTamilDubbed) {
            $langName = $isTamilDubbed ? 'Tamil Dubbed' : 'Tamil';
            $lang = Language::firstOrCreate(['slug' => Str::slug($langName)], ['name' => $langName]);
            if (!$movie->languages()->where('languages.id', $lang->id)->exists()) {
                $movie->languages()->syncWithoutDetaching([$lang->id]);
            }
        });

        // ── Quality links from movie page (Original, 1080p, 720p...) ──────
        $qualityLinks = [];
        $crawler->filter('div.f a')->each(function (Crawler $a) use (&$qualityLinks) {
            $href  = trim($a->attr('href') ?? '');
            $label = trim($a->text());
            if (!$href || strlen($label) < 2) return;
            if (Str::startsWith($href, '#') || Str::contains($href, 'javascript:')) return;

            $url = Str::startsWith($href, 'http')
                ? $href
                : $this->baseUrl . '/' . ltrim($href, '/');

            if (!Str::contains($url, 'moviezda.com')) return;
            $qualityLinks[] = ['label' => $label, 'url' => $url];
        });

        // For each quality link, visit the download chain to resolve final download URL
        foreach ($qualityLinks as $ql) {
            $this->processDownloadChain($movie, $ql['label'], $ql['url']);
            usleep(150000);
        }

        return $movie;
    }

    /**
     * Follow download chain:
     *   Quality page (/movie-1080p-hd-movie/) → Download page (/download/slug/) → real CDN link
     */
    protected function processDownloadChain(Movie $movie, string $qualityLabel, string $qualityPageUrl): void
    {
        if (in_array($qualityPageUrl, $this->visitedUrls)) return;
        $this->visitedUrls[] = $qualityPageUrl;

        // If this already is a download page, parse directly
        if (Str::contains($qualityPageUrl, '/download/')) {
            $this->parseDownloadPage($movie, $qualityLabel, $qualityPageUrl);
            return;
        }

        $body = $this->fetchHtml($qualityPageUrl);
        if (!$body) return;

        $crawler = new Crawler($body, $qualityPageUrl);

        $downloadPageUrl = null;
        $crawler->filter('a')->each(function (Crawler $a) use (&$downloadPageUrl) {
            if ($downloadPageUrl) return;
            $href = trim($a->attr('href') ?? '');
            $url  = Str::startsWith($href, 'http') ? $href : $this->baseUrl . '/' . ltrim($href, '/');
            if (Str::contains($url, 'moviezda.com') && Str::contains($url, '/download/')) {
                $downloadPageUrl = $url;
            }
        });

        // Poster from quality page JSON-LD
        if (preg_match('/"thumbnailUrl"\s*:\s*"([^"]+)"/', $body, $m)) {
            if (empty($movie->poster_path)) {
                $this->withDbRetry(fn() => $movie->update(['poster_path' => $m[1]]));
            }
        }

        if ($downloadPageUrl) {
            $this->parseDownloadPage($movie, $qualityLabel, $downloadPageUrl);
        } else {
            $this->extractDirectLinks($movie, $qualityLabel, $body);
        }
    }

    /**
     * Parse /download/slug/ page for real file URL, size, duration, resolution
     */
    protected function parseDownloadPage(Movie $movie, string $qualityLabel, string $downloadPageUrl): void
    {
        if (in_array($downloadPageUrl, $this->visitedUrls)) return;
        $this->visitedUrls[] = $downloadPageUrl;

        $body = $this->fetchHtml($downloadPageUrl);
        if (!$body) return;

        $crawler = new Crawler($body, $downloadPageUrl);

        // Poster from /uploads/shots/
        if (empty($movie->poster_path)) {
            $crawler->filter('img')->each(function (Crawler $img) use ($movie) {
                $src = $img->attr('src') ?? '';
                if (Str::contains($src, ['/uploads/', '/shots/'])) {
                    $url = Str::startsWith($src, 'http') ? $src : $this->baseUrl . '/' . ltrim($src, '/');
                    $this->withDbRetry(fn() => $movie->update(['poster_path' => $url]));
                }
            });
        }

        // Poster from JSON-LD
        if (preg_match('/"thumbnailUrl"\s*:\s*"([^"]+)"/', $body, $m) && empty($movie->poster_path)) {
            $this->withDbRetry(fn() => $movie->update(['poster_path' => $m[1]]));
        }

        // File metadata
        $fileSize   = null;
        $duration   = null;
        $resolution = null;

        $crawler->filter('div.details')->each(function (Crawler $d) use (&$fileSize, &$duration, &$resolution) {
            $text = trim($d->text());
            if (Str::startsWith($text, 'File Size:')) {
                $fileSize = trim(str_replace('File Size:', '', $text));
            } elseif (Str::startsWith($text, 'Duration:')) {
                $duration = trim(str_replace('Duration:', '', $text));
            } elseif (Str::startsWith($text, 'Video Resolution:')) {
                $resolution = trim(str_replace('Video Resolution:', '', $text));
            }
        });

        $quality = $this->detectQualityLabel($qualityLabel, $downloadPageUrl, $body);

        // Direct download URLs
        $downloadUrls = [];
        $crawler->filter('div.dlink a')->each(function (Crawler $a) use (&$downloadUrls) {
            $href = trim($a->attr('href') ?? '');
            if ($href && !Str::startsWith($href, '#') && !Str::contains($href, 'javascript:')) {
                $downloadUrls[] = $href;
            }
        });

        $downloadUrls = array_unique($downloadUrls);

        if (empty($downloadUrls)) {
            $this->extractDirectLinks($movie, $quality, $body);
            return;
        }

        $primaryUrl = $downloadUrls[0];

        $this->withDbRetry(function () use ($movie, $quality, $resolution, $fileSize, $primaryUrl) {
            if ($movie->links()->where('quality', $quality)->exists()) return;

            MovieLink::create([
                'movie_id'     => $movie->id,
                'quality'      => $quality,
                'resolution'   => $resolution,
                'file_size'    => $fileSize,
                'download_url' => $primaryUrl,
                'source_name'  => 'Moviesda',
                'status'       => true,
            ]);
        });
    }

    protected function extractDirectLinks(Movie $movie, string $quality, string $html): void
    {
        preg_match_all('/<a[^>]+href="(https?:\/\/(?:download\.|movies\.|cdn\.|pixelharbor)[^"]+)"[^>]*>/i', $html, $m);
        foreach (array_unique($m[1]) as $url) {
            $this->withDbRetry(function () use ($movie, $quality, $url) {
                if (!$movie->links()->where('download_url', $url)->exists()) {
                    MovieLink::create([
                        'movie_id'     => $movie->id,
                        'quality'      => $quality,
                        'download_url' => $url,
                        'source_name'  => 'Moviesda',
                        'status'       => true,
                    ]);
                }
            });
        }
    }

    // ── Database Upsert with Reconnection Resilience ──────────────────────────

    protected function upsertMovie(
        string  $cleanTitle,
        string  $movieUrl,
        ?int    $year,
        ?string $posterUrl,
        bool    $isTamilDubbed
    ): ?Movie {
        $urlPath    = trim(parse_url($movieUrl, PHP_URL_PATH) ?? '', '/');
        $slug       = Str::slug($cleanTitle);
        $identifier = $urlPath ?: $slug . ($year ? '-' . $year : '');

        $desc = $isTamilDubbed
            ? "Experience the complete Tamil dubbed edition of {$cleanTitle}" . ($year ? " ({$year})" : "") . ". Watch and download authorized high definition prints across multi-resolution formats."
            : "Experience the official Tamil cinematic release of {$cleanTitle}" . ($year ? " ({$year})" : "") . ". Stream and download authorized prints in verified high definition formats.";

        return $this->withDbRetry(function () use ($cleanTitle, $movieUrl, $year, $posterUrl, $identifier, $slug, $desc) {
            $movie = Movie::withTrashed()
                ->where('source_identifier', $identifier)
                ->orWhere(function ($q) use ($cleanTitle, $year) {
                    $q->where('title', $cleanTitle);
                    if ($year) $q->where('release_year', $year);
                })
                ->first();

            if ($movie) {
                if ($movie->trashed()) $movie->restore();

                $updates = [
                    'source_identifier' => $identifier,
                    'source_url'        => $movieUrl,
                    'status'            => 'active',
                    'scraped_at'        => now(),
                ];
                if ($posterUrl && empty($movie->poster_path)) {
                    $updates['poster_path'] = $posterUrl;
                }
                if ($year && !$movie->release_year) {
                    $updates['release_year'] = $year;
                }
                $movie->update($updates);
                $this->totalSkipped++;
                return $movie;
            }

            // New movie — create with unique slug
            $uniqueSlug = $slug;
            $c = 1;
            while (Movie::withTrashed()->where('slug', $uniqueSlug)->exists()) {
                $uniqueSlug = "{$slug}-{$c}";
                $c++;
            }

            return Movie::create([
                'title'             => $cleanTitle,
                'slug'              => $uniqueSlug,
                'description'       => $desc,
                'release_year'      => $year,
                'poster_path'       => $posterUrl,
                'status'            => 'active',
                'source_url'        => $movieUrl,
                'source_identifier' => $identifier,
                'view_count'        => rand(300, 2500),
                'scraped_at'        => now(),
            ]);
        });
    }

    /**
     * Wrapper for DB queries to automatically reconnect and retry
     * on network/DNS drops to remote cloud databases (Aiven Cloud).
     */
    protected function withDbRetry(callable $callback, int $maxAttempts = 3)
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return $callback();
            } catch (\Illuminate\Database\QueryException | \PDOException $e) {
                Log::warning("[Scraper] DB error (attempt {$attempt}/{$maxAttempts}): " . $e->getMessage());
                if ($attempt === $maxAttempts) {
                    $this->totalFailed++;
                    return null;
                }
                try {
                    DB::purge();
                    DB::reconnect();
                } catch (\Throwable $re) {
                    // Ignore reconnection attempt failure, will retry in next loop
                }
                sleep(2);
            }
        }
        return null;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Clean a raw title from Moviesda:
     * - Removes site suffixes (Moviesda, isaimini, etc.)
     * - Safely removes years and quality tags without fusing words together
     * - Strips trailing generic words
     * - Rejects category/directory page titles
     */
    public function cleanTitle(string $raw): ?string
    {
        // Reject category / listing pages
        if (preg_match('/^(Tamil\s+\d{4}\s+Movies|Tamil\s+Dubbed\s+Movies|Tamil\s+HD\s+Movies|Tamil\s+Movies\s+Collection|Moviesda)/i', trim($raw))) {
            return null;
        }
        if (preg_match('/(Movies Download|Movies Collection|Moviesda\.Mobi|HD Mobile Movies)/i', $raw)
            && preg_match('/(Download|Collection|Category|Moviesda)/i', $raw)) {
            return null;
        }

        $title = $raw;

        // Strip site suffixes & download garbage
        $title = preg_replace('/[\|\-]\s*(Moviesda|isaimini|Download|Moviesda\.Mobi|Moviesda\.io)\s*$/i', '', $title);
        $title = preg_replace('/(Download\s+Moviesda.*)$/i', '', $title);

        // Strip parenthesized/bracketed tags (years, quality, seasons, episodes)
        // Replacing with a space ensures words are never fused together (e.g. 'Butterfly (2022)' -> 'Butterfly ')
        $title = preg_replace('/\s*[\(\[][^\)\]]*(?:19\d{2}|20\d{2}|1080p|720p|480p|360p|HD|Original|DVDRip|BluRay|BDRip|PreDVD|HQ|Mp4|Sample|Season|Episode)[^\)\]]*[\)\]]\s*/i', ' ', $title);

        // Strip standalone 4-digit years
        $title = preg_replace('/\b(19\d{2}|20\d{2})\b/', ' ', $title);

        // Strip trailing keywords repeatedly
        for ($i = 0; $i < 3; $i++) {
            $title = preg_replace('/\s*\b(Tamil\s+Movie|Tamil\s+Web\s*Series|Tamil|Movie|Full\s*Movie|HD|Original|BDRip|HDRip|DVDRip|BluRay|HQ|Mp4)\s*$/i', '', $title);
        }

        // Collapse multiple spaces
        $title = preg_replace('/\s+/', ' ', $title);
        $title = trim($title, " \t\n\r\0\x0B-–|");

        if (empty($title) || strlen($title) < 2 || in_array(strtolower($title), ['tamil', 'movie', 'movies', 'download', 'moviesda', 'home'])) {
            return null;
        }

        return $title;
    }

    protected function detectQualityLabel(string $label, string $url, string $body): string
    {
        $combined = strtolower($label . ' ' . $url);

        if (str_contains($combined, '1080p') || str_contains($combined, '1080')) return '1080p HD';
        if (str_contains($combined, '720p')  || str_contains($combined, '720'))  return '720p HD';
        if (str_contains($combined, '480p')  || str_contains($combined, '480'))  return '480p';
        if (str_contains($combined, '360p')  || str_contains($combined, '360'))  return '360p';
        if (str_contains($combined, '4k')    || str_contains($combined, 'uhd'))  return '4K UHD';
        if (str_contains($combined, 'original'))                                 return 'Original Print';
        if (str_contains($combined, 'hdrip') || str_contains($combined, 'hd'))  return 'HD';

        if (preg_match('/(1080p|720p|480p|360p|4K|Original)/i', $body, $m)) {
            return $m[1];
        }

        return $label ?: 'HD';
    }

    protected function detectTotalPages(Crawler $crawler): int
    {
        try {
            $span = $crawler->filter('#totalPages');
            if ($span->count() > 0) {
                $n = (int)trim($span->text());
                if ($n > 0) return $n;
            }

            $last = 1;
            $crawler->filter('ul.pagination li a')->each(function (Crawler $a) use (&$last) {
                $t = trim($a->text());
                if (is_numeric($t) && (int)$t > $last) $last = (int)$t;
            });
            return max(1, $last);
        } catch (\Exception $e) {
            return 1;
        }
    }

    // ── HTTP Fetcher (cURL with Brotli support) ───────────────────────────────

    public function fetchHtml(string $url): ?string
    {
        if (!Str::contains($url, 'moviezda.com') && !Str::contains($url, '/download/')) {
            return null;
        }

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                CURLOPT_HTTPHEADER     => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.9',
                ],
                CURLOPT_ENCODING       => '',   // Let libcurl handle all compression
                CURLOPT_TIMEOUT        => 25,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);

            $body   = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($body !== false && in_array($status, [200, 301, 302]) && strlen($body) > 500) {
                return $body;
            }

            if (in_array($status, [404, 410])) return null;

            if ($attempt < 3) usleep(500000);
        }

        return null;
    }

    protected function out(string $message): void
    {
        Log::info('[Scraper] ' . $message);
        if ($this->outputCallback) {
            ($this->outputCallback)($message);
        }
    }
}
