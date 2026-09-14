<?php

namespace App\Services\Scrapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Movie;
use App\Models\MovieLink;
use Illuminate\Support\Str;

class SourceMovieScraper
{
    protected $headers = [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36',
        'Accept-Encoding' => 'gzip, deflate',
    ];

    public function syncCatalog(string $url)
    {
        Log::info("Starting catalog sync from: {$url}");
        
        try {
            $response = Http::withHeaders($this->headers)->get($url);
            if (!$response->successful()) {
                Log::error("Failed to fetch catalog root: {$url}");
                return;
            }

            $crawler = new Crawler($response->body(), $url);
            $categories = $crawler->filter('a')->links();

            foreach ($categories as $catLink) {
                $href = $catLink->getUri();
                $title = trim($catLink->getNode()->textContent);

                if (Str::contains(strtolower($title), ['movies', 'collection', 'series']) && !Str::contains(strtolower($title), ['home', 'page'])) {
                    $this->getMovieListing($href);
                }
            }
            
            Log::info("Sync complete.");
        } catch (\Exception $e) {
            Log::error("Sync failed: " . $e->getMessage());
        }
    }

    protected function getMovieListing(string $categoryUrl)
    {
        try {
            $response = Http::withHeaders($this->headers)->get($categoryUrl);
            if (!$response->successful()) return;

            $crawler = new Crawler($response->body(), $categoryUrl);
            $movies = $crawler->filter('a')->links();

            foreach ($movies as $movieLink) {
                $href = $movieLink->getUri();
                $title = trim($movieLink->getNode()->textContent);

                // Skip generic buttons or links
                if (Str::contains(strtolower($title), ['download now', 'telegram', 'disclaimer'])) {
                    continue;
                }

                if (Str::contains($href, 'movie') && strlen($title) > 3 && !Str::contains(strtolower($title), ['home', 'page'])) {
                    $this->getMovieDetails($href);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to get listing for {$categoryUrl}: " . $e->getMessage());
        }
    }

    protected function getMovieDetails(string $movieUrl)
    {
        try {
            $sourceIdentifier = trim(parse_url($movieUrl, PHP_URL_PATH), '/');

            $response = Http::withHeaders($this->headers)->get($movieUrl);
            if ($response->successful()) {
                $crawler = new Crawler($response->body(), $movieUrl);
                
                // Extract proper title from h1 or title tag
                $rawTitle = $crawler->filter('title')->count() > 0 
                    ? $crawler->filter('title')->text() 
                    : ($crawler->filter('h1')->count() > 0 ? $crawler->filter('h1')->text() : 'Unknown Movie');
                
                $rawTitle = str_replace(['Moviesda', 'Tamil', 'Movie', 'Download', 'HD', '1080p'], '', $rawTitle);
                $cleanTitle = trim(preg_replace('/\(?\d{4}\)?/', '', $rawTitle));
                $cleanTitle = trim($cleanTitle, " -\t\n\r\0\x0B");

                $year = preg_match('/\(?(\d{4})\)?/', $rawTitle, $matches) ? $matches[1] : null;

                $cleanTitle = trim(preg_replace('/\s+(Full|Web\s*Series)\b/i', '', $cleanTitle));

                // Extract synopsis if present
                $synopsis = null;
                $crawler->filter('div, p')->each(function (Crawler $node) use (&$synopsis) {
                    if ($synopsis) return;
                    $text = trim($node->text());
                    if (stripos($text, 'Synopsis:') === 0 || stripos($text, 'Plot:') === 0) {
                        $synopsis = trim(preg_replace('/^(Synopsis|Plot):\s*/i', '', $text));
                    }
                });

                // Extract poster if present
                $poster = null;
                $crawler->filter('img')->each(function (Crawler $img) use (&$poster) {
                    if ($poster) return;
                    $src = $img->attr('src');
                    if ($src && (str_contains($src, '/posters/') || str_contains($src, '/uploads/'))) {
                        $poster = str_starts_with($src, 'http') ? $src : 'https://moviezda.com/' . ltrim($src, '/');
                    }
                });

                $desc = $synopsis ?: "Experience the full theatrical narrative of {$cleanTitle}" . ($year ? " ({$year})" : "") . ". Stream and download in high definition across verified authorized formats.";
                
                $movieData = [
                    'title' => $cleanTitle ?: 'Unknown',
                    'slug' => $sourceIdentifier, // Use unique source identifier as our slug to prevent DB collisions
                    'description' => $desc,
                    'poster_path' => $poster,
                    'release_year' => $year,
                    'status' => 'active',
                    'source_url' => $movieUrl,
                    'source_identifier' => $sourceIdentifier,
                ];

                $movie = $this->saveMovie($movieData);
                $this->parseLinks($crawler, $movie->id);
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to parse movie details for {$movieUrl}: " . $e->getMessage());
        }
    }

    protected function parseLinks(Crawler $crawler, int $movieId)
    {
        $links = $crawler->filter('a')->links();
        foreach ($links as $link) {
            $href = $link->getUri();
            $title = trim($link->getNode()->textContent);
            
            if (Str::contains(strtolower($title), ['original', '1080p', '720p', '480p', 'mp4'])) {
                $finalUrl = $this->resolveFinalLink($href);
                
                if ($finalUrl) {
                    MovieLink::updateOrCreate(
                        ['movie_id' => $movieId, 'quality' => $title],
                        [
                            'download_url' => $finalUrl,
                            'status' => true,
                            'file_size' => 'Unknown'
                        ]
                    );
                }
            }
        }
    }

    protected function resolveFinalLink(string $startUrl): ?string
    {
        $url = $startUrl;
        $visited = [];
        
        for ($i = 0; $i < 6; $i++) {
            if (in_array($url, $visited)) break;
            $visited[] = $url;
            
            try {
                $resp = Http::withHeaders($this->headers)->get($url);
                if (!$resp->successful()) break;
                
                $crawler = new Crawler($resp->body(), $url);
                $links = $crawler->filter('a')->links();
                
                $nextUrl = null;
                foreach ($links as $link) {
                    $href = $link->getUri();
                    $title = trim($link->getNode()->textContent);
                    
                    if (Str::endsWith($href, '#') || Str::contains($href, 'javascript')) continue;
                    
                    if (Str::endsWith(strtolower($href), '.mp4') || Str::contains(strtolower($href), '.mp4?')) {
                        return $href;
                    }
                    
                    if (
                        Str::contains($href, 'download') || 
                        Str::contains($href, 'moviespage.xyz') || 
                        Str::contains($href, 'downloadpage.xyz') || 
                        Str::contains($href, 'cloudforge-tool') ||
                        Str::contains(strtolower($title), ['download', 'server', 'hd', 'sd']) ||
                        preg_match('/(1080p|720p|480p|360p)/i', $href)
                    ) {
                        $nextUrl = $href;
                        break; 
                    }
                }
                
                if ($nextUrl) {
                    $url = $nextUrl;
                } else {
                    break;
                }
            } catch (\Exception $e) {
                break;
            }
        }
        
        return $url;
    }

    protected function saveMovie(array $data): Movie
    {
        // Upsert by source_identifier
        $identifier = $data['source_identifier'] ?? $data['slug'];
        
        return Movie::updateOrCreate(
            ['source_identifier' => $identifier],
            $data
        );
    }
}
