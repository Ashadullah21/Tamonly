<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Movie;
use App\Models\MovieLink;
use App\Models\Language;
use Illuminate\Support\Str;

/**
 * Moviesda Full Scraper — cURL edition.
 *
 * Root cause fix: Laravel's Http/Guzzle on Windows cannot decompress Brotli (br)
 * encoding. The old scraper sent Accept-Encoding: gzip, deflate, br → the server
 * responded with br-compressed bytes → Guzzle returned binary garbage → body
 * failed the > 500 bytes text check → every single fetch returned "failed".
 *
 * This version uses PHP cURL directly with CURLOPT_ENCODING='' which lets libcurl
 * handle ALL compression formats automatically (gzip, deflate, br if available).
 *
 * URL chain per movie:
 *  1. Category listing  → https://moviezda.com/tamil-2022-movies/?page=N
 *  2. Movie main page   → https://moviezda.com/movie-slug-tamil-movie/
 *     - Contains: quality links (Original, 1080p HD, 720p HD, etc.)
 *     - Contains: poster thumbnail in meta og:image or page image
 *  3. Download page     → https://moviezda.com/download/movie-slug-quality/
 *     - Contains: file size, duration, resolution (thumbnail), real download link
 *     - Real link: div.download div.dlink a[href]  (moviespage.xyz or similar)
 *
 * We store the moviespage.xyz URL as the download_url in movie_links.
 * The DownloadController redirects users there — no intermediate pages for the user.
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
        ['path' => '/tamil-movies-collection/',    'year' => null, 'language' => 'Tamil'],
        ['path' => '/moviesda-tamil-collections/', 'year' => null, 'language' => 'Tamil'],
        ['path' => '/tamil-hd-movies/',            'year' => null, 'language' => 'Tamil'],
        ['path' => '/tamil-hd-movies-download/',   'year' => null, 'language' => 'Tamil'],
        ['path' => '/tamilrockers-movies/',        'year' => null, 'language' => 'Tamil'],
        ['path' => '/tamil-atoz-movies/',          'year' => null, 'language' => 'Tamil'],
        ['path' => '/tamil-web-series-download/',  'year' => null, 'language' => 'Tamil'],
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

        // 1. Year-based listing pages
        $this->out("\n📅 === Year-Based Categories ===");
        foreach ($this->yearCategories as $cat) {
            $this->scrapeCategory($cat, $mode);
            usleep(400000);
        }

        // 2. Theme / named categories
        $this->out("\n🗂️  === Theme Categories ===");
        foreach ($this->themeCategories as $cat) {
            $this->scrapeCategory($cat, $mode);
            usleep(400000);
        }

        // 3. A–Z alphabetical (full mode only)
        if ($mode === 'full') {
            $this->out("\n🔤 === A-to-Z Alphabetical Pages ===");
            foreach ($this->alphabetLetters as $letter) {
                $this->scrapeAlphabetLetter($letter);
                usleep(600000);
            }
        }

        Cache::put('scraper_last_sync', now()->toIso8601String(), now()->addDays(30));

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

        // Only scrape our target domain
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

        // Scrape page 1 (already fetched)
        $this->scrapeListingPage($crawler, $year, $language);

        // Pages 2..N
        for ($p = 2; $p <= $maxPage; $p++) {
            // ✅ Correct pagination format: no extra slash before ?
            $pageUrl = rtrim($firstUrl, '/') . '?page=' . $p;
            if (in_array($pageUrl, $this->visitedUrls)) continue;
            $this->visitedUrls[] = $pageUrl;

            $this->out("   ↳ Page {$p}/{$maxPage}: {$pageUrl}");

            $body = $this->fetchHtml($pageUrl);
            if (!$body) {
                $this->out("   ⚠️  Skipped page {$p} (fetch failed)");
                continue;
            }

            $this->scrapeListingPage(new Crawler($body, $pageUrl), $year, $language);
            usleep(300000);
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

        for ($p = 2; $p <= $totalPages; $p++) {
            $pageUrl = rtrim($firstUrl, '/') . '?page=' . $p;
            if (in_array($pageUrl, $this->visitedUrls)) continue;
            $this->visitedUrls[] = $pageUrl;

            $body = $this->fetchHtml($pageUrl);
            if (!$body) continue;

            $this->scrapeListingPage(new Crawler($body, $pageUrl), null, 'Tamil');
            usleep(300000);
        }
    }

    /**
     * Parse a listing page — find all movie entries (div.f > a) and save them.
     * Then for each movie, visit its detail page to extract quality links and
     * follow each to the download page to get the real moviespage.xyz URL.
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

    /**
     * Visit a movie's main page, extract quality options, then visit each
     * quality's download page to get the real file URL + metadata.
     *
     * Movie page structure:
     *   div.f a → links to quality pages like /movie-1080p-hd-movie/
     *
     * Download page structure:
     *   div.songinfo → file size, duration, resolution, poster thumbnail
     *   div.download div.dlink a → real download URL (moviespage.xyz)
     */
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

        // ── Extract movie metadata from main page ──────────────────────────
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

        // Clean title — strip quality labels, year, site names
        $cleanTitle = $this->cleanTitle($rawTitle ?: $listTitle);
        if (empty($cleanTitle)) return null;

        // Year from title or URL or category
        $year = $catYear;
        if (preg_match('/\((\d{4})\)/', $rawTitle, $m) || preg_match('/\b(20\d{2}|19\d{2})\b/', $rawTitle, $m)) {
            $year = (int)$m[1];
        }
        if (!$year && preg_match('/-(\d{4})-/', $moviePageUrl, $m)) {
            $year = (int)$m[1];
        }
        if ($year && ($year < 1950 || $year > (int)date('Y') + 1)) $year = null;

        // Poster — try og:image first, then any /uploads/ img
        $posterUrl = null;
        $ogImg = $crawler->filter('meta[property="og:image"]');
        if ($ogImg->count()) {
            $posterUrl = $ogImg->attr('content');
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

        // Dubbed?
        $isTamilDubbed = Str::contains(strtolower($language), 'dubbed')
            || Str::contains(strtolower($moviePageUrl), 'dubbed')
            || Str::contains(strtolower($rawTitle), 'dubbed');

        // ── Upsert the movie record ────────────────────────────────────────
        $movie = $this->upsertMovie($cleanTitle, $moviePageUrl, $year, $posterUrl, $isTamilDubbed);
        if (!$movie) return null;

        // ── Attach Language ────────────────────────────────────────────────
        $langName = $isTamilDubbed ? 'Tamil Dubbed' : 'Tamil';
        $lang = Language::firstOrCreate(['slug' => Str::slug($langName)], ['name' => $langName]);
        if (!$movie->languages()->where('languages.id', $lang->id)->exists()) {
            $movie->languages()->syncWithoutDetaching([$lang->id]);
        }

        // ── Collect quality page links from this movie page ────────────────
        // These are inside div.f a on the movie page (Original, 1080p, 720p …)
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

        // ── For each quality link, visit the download page ─────────────────
        foreach ($qualityLinks as $ql) {
            $this->processDownloadChain($movie, $ql['label'], $ql['url']);
            usleep(200000); // 0.2s between quality pages
        }

        // ── Also check direct /download/ URL if it exists ──────────────────
        // Some movies link directly to /download/slug/ from quality pages.
        // We handle this in processDownloadChain when we detect the /download/ path.

        return $movie;
    }

    /**
     * Follow the chain for one quality option:
     *   Quality page (/movie-1080p-hd-movie/) → Download page (/download/slug/) → real URL
     *
     * If the quality page already IS a /download/ page, parse it directly.
     */
    protected function processDownloadChain(Movie $movie, string $qualityLabel, string $qualityPageUrl): void
    {
        if (in_array($qualityPageUrl, $this->visitedUrls)) return;
        $this->visitedUrls[] = $qualityPageUrl;

        // If this IS already a download page, parse directly
        if (Str::contains($qualityPageUrl, '/download/')) {
            $this->parseDownloadPage($movie, $qualityLabel, $qualityPageUrl);
            return;
        }

        // Otherwise visit the quality page and find the download page link
        $body = $this->fetchHtml($qualityPageUrl);
        if (!$body) return;

        $crawler = new Crawler($body, $qualityPageUrl);

        // Quality page contains a link to the /download/ page inside div.f a
        // or as a button/link elsewhere. Find it.
        $downloadPageUrl = null;
        $crawler->filter('a')->each(function (Crawler $a) use (&$downloadPageUrl) {
            if ($downloadPageUrl) return;
            $href = trim($a->attr('href') ?? '');
            $url  = Str::startsWith($href, 'http') ? $href : $this->baseUrl . '/' . ltrim($href, '/');
            if (Str::contains($url, 'moviezda.com') && Str::contains($url, '/download/')) {
                $downloadPageUrl = $url;
            }
        });

        // Also look inside JSON-LD for thumbnailUrl / poster at this level
        if (preg_match('/"thumbnailUrl"\s*:\s*"([^"]+)"/', $body, $m)) {
            if (empty($movie->poster_path)) {
                $movie->update(['poster_path' => $m[1]]);
            }
        }

        if ($downloadPageUrl) {
            $this->parseDownloadPage($movie, $qualityLabel, $downloadPageUrl);
        } else {
            // Fallback: try to find direct moviespage.xyz or CDN link
            $this->extractDirectLinks($movie, $qualityLabel, $body);
        }
    }

    /**
     * Parse a /download/slug/ page to get:
     *  - Real download URL (div.download div.dlink a[href])
     *  - File size, duration, resolution (div.songinfo div.details)
     *  - Poster thumbnail (/uploads/shots/...)
     */
    protected function parseDownloadPage(Movie $movie, string $qualityLabel, string $downloadPageUrl): void
    {
        if (in_array($downloadPageUrl, $this->visitedUrls)) return;
        $this->visitedUrls[] = $downloadPageUrl;

        $body = $this->fetchHtml($downloadPageUrl);
        if (!$body) return;

        $crawler = new Crawler($body, $downloadPageUrl);

        // ── Poster from /uploads/shots/ ────────────────────────────────────
        if (empty($movie->poster_path)) {
            $crawler->filter('img')->each(function (Crawler $img) use ($movie) {
                $src = $img->attr('src') ?? '';
                if (Str::contains($src, ['/uploads/', '/shots/'])) {
                    $url = Str::startsWith($src, 'http') ? $src : $this->baseUrl . '/' . ltrim($src, '/');
                    $movie->update(['poster_path' => $url]);
                }
            });
        }

        // ── Also from JSON-LD thumbnailUrl ─────────────────────────────────
        if (preg_match('/"thumbnailUrl"\s*:\s*"([^"]+)"/', $body, $m) && empty($movie->poster_path)) {
            $movie->update(['poster_path' => $m[1]]);
        }

        // ── Parse metadata from div.songinfo ──────────────────────────────
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

        // ── Detect quality label from page title or URL ────────────────────
        $quality = $this->detectQualityLabel($qualityLabel, $downloadPageUrl, $body);

        // ── Find real download URLs (div.download div.dlink a) ─────────────
        $downloadUrls = [];
        $crawler->filter('div.dlink a')->each(function (Crawler $a) use (&$downloadUrls) {
            $href = trim($a->attr('href') ?? '');
            if ($href && !Str::startsWith($href, '#') && !Str::contains($href, 'javascript:')) {
                $downloadUrls[] = $href;
            }
        });

        // Deduplicate (Server 1 and Server 2 often have the same URL)
        $downloadUrls = array_unique($downloadUrls);

        if (empty($downloadUrls)) {
            // Fallback: look for any external link that looks like a download
            $this->extractDirectLinks($movie, $quality, $body);
            return;
        }

        // Save as movie links — use the first URL, fallback to the download page itself
        $primaryUrl = $downloadUrls[0];

        // Avoid duplicate links per quality
        if ($movie->links()->where('quality', $quality)->exists()) return;

        MovieLink::create([
            'movie_id'     => $movie->id,
            'quality'      => $quality,
            'resolution'   => $resolution,
            'file_size'    => $fileSize,
            'download_url' => $primaryUrl, // Real moviespage.xyz / CDN URL
            'source_name'  => 'Moviesda',
            'status'       => true,
        ]);

        Log::debug("[Scraper] Saved link [{$quality}] for movie [{$movie->title}]: {$primaryUrl}");
    }

    /**
     * Fallback: scan page HTML for any external download/CDN-looking href.
     */
    protected function extractDirectLinks(Movie $movie, string $quality, string $html): void
    {
        preg_match_all('/<a[^>]+href="(https?:\/\/(?:download\.|movies\.|cdn\.|pixelharbor)[^"]+)"[^>]*>/i', $html, $m);
        foreach (array_unique($m[1]) as $url) {
            if (!$movie->links()->where('download_url', $url)->exists()) {
                MovieLink::create([
                    'movie_id'     => $movie->id,
                    'quality'      => $quality,
                    'download_url' => $url,
                    'source_name'  => 'Moviesda',
                    'status'       => true,
                ]);
            }
        }
    }

    // ── Database Upsert ───────────────────────────────────────────────────────

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

        try {
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
                if (!$year && $movie->release_year) {
                    // keep existing year
                } else {
                    $updates['release_year'] = $year ?: $movie->release_year;
                }
                $movie->update($updates);
                $this->totalSkipped++;
                return $movie;
            }

            // New movie — create
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
        } catch (\Exception $e) {
            $this->totalFailed++;
            Log::warning("[Scraper] Save failed [{$cleanTitle}]: " . $e->getMessage());
            return null;
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Clean a raw title from the site (strips quality labels, site names, year).
     */
    protected function cleanTitle(string $raw): string
    {
        // Remove site name suffixes
        $title = preg_replace('/[\|\-]\s*(Moviesda|isaimini|Download|Moviesda\.Mobi|Moviesda\.io)\s*$/i', '', $raw);
        // Remove quality labels from the title
        $title = preg_replace('/\s*[\(\[](1080p|720p|480p|360p|HD|Original|DVDRip|BluRay|PreDVD|HQ|Mp4)[^\)\]]*[\)\]]\s*/i', '', $title);
        // Remove year in parentheses
        $title = preg_replace('/\s*\(\d{4}\)\s*/', '', $title);
        // Remove trailing quality words
        $title = preg_replace('/\s+(Full|Web\s*Series|Movie|HD|1080p|720p|480p|DVDRip|Tamil)\s*$/i', '', $title);
        $title = preg_replace('/\s+/', ' ', $title);
        return trim($title);
    }

    /**
     * Detect quality label from the link label, page URL, and body.
     * Returns a clean string like "1080p HD", "720p HD", "Original".
     */
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

        // Try parsing from body JSON-LD or title tag
        if (preg_match('/(1080p|720p|480p|360p|4K|Original)/i', $body, $m)) {
            return $m[1];
        }

        return $label ?: 'HD';
    }

    /**
     * Detect total pages from the site's built-in #totalPages element or
     * the highest page number in the pagination list.
     */
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

    // ── HTTP Fetcher (cURL — fixes Brotli/Guzzle issue) ──────────────────────

    /**
     * Fetch HTML using cURL directly.
     *
     * Root cause of previous failures: Laravel Http / Guzzle on Windows does NOT
     * support Brotli decompression. When we sent 'Accept-Encoding: gzip, deflate, br'
     * the server responded with br-encoded bytes → Guzzle couldn't decode →
     * body was binary → strlen check failed → "Could not fetch" for EVERY URL.
     *
     * Fix: use CURLOPT_ENCODING='' which tells libcurl to advertise and decode
     * all compression formats it supports natively (gzip, deflate, and br on
     * systems where libcurl was compiled with Brotli support).
     */
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
                CURLOPT_ENCODING       => '',   // ← Let libcurl handle ALL compression
                CURLOPT_TIMEOUT        => 25,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);

            $body   = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err    = curl_error($ch);
            curl_close($ch);

            if ($body !== false && in_array($status, [200, 301, 302]) && strlen($body) > 500) {
                return $body;
            }

            // 404/410 — page doesn't exist, no point retrying
            if (in_array($status, [404, 410])) return null;

            if ($attempt < 3) usleep(500000); // 0.5s between retries
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
