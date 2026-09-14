<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Services\MoviesdaCatalogService;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request, MoviesdaCatalogService $catalogService)
    {
        if ($request->filled('search')) {
            $rawSearch = trim($request->search);

            // Check if search query contains a 4-digit year (1900 - 2099)
            $detectedYear = null;
            if (preg_match('/\b(19\d{2}|20\d{2})\b/', $rawSearch, $matches)) {
                $detectedYear = (int)$matches[1];
            }

            // Clean search string
            $cleanSearch = trim(preg_replace('/[^a-zA-Z0-9\s]/', ' ', $rawSearch));
            $cleanSearch = preg_replace('/\s+/', ' ', $cleanSearch);

            // Title part without the year (e.g. "2026 kara" -> "kara", "2004 spider man" -> "spider man")
            $titleWithoutYear = $detectedYear ? trim(preg_replace('/\b' . $detectedYear . '\b/', '', $cleanSearch)) : $cleanSearch;
            $titleWithoutYear = trim(preg_replace('/\s+/', ' ', $titleWithoutYear));

            // Keywords excluding stop words
            $keywords = array_values(array_filter(
                explode(' ', strtolower($cleanSearch)),
                fn($w) => !in_array($w, ['the', 'a', 'an', 'movie', 'in', 'of', 'and']) && strlen($w) >= 2
            ));

            $titleKeywords = array_values(array_filter(
                explode(' ', strtolower($titleWithoutYear)),
                fn($w) => !in_array($w, ['the', 'a', 'an', 'movie', 'in', 'of', 'and']) && strlen($w) >= 2 && !is_numeric($w)
            ));

            // Dynamically query catalog with title term (avoid querying bare year to prevent irrelevant torrent pollution)
            $catalogTerm = !empty($titleWithoutYear) ? $titleWithoutYear : $rawSearch;
            $catalogService->searchAndSync($catalogTerm);

            $compactFull = str_replace(' ', '', strtolower($cleanSearch));
            $compactTitle = str_replace(' ', '', strtolower($titleWithoutYear));

            $baseQuery = Movie::where('status', 'active')->with(['genres', 'languages', 'links']);

            $slugSearch = \Illuminate\Support\Str::slug($cleanSearch);
            $slugTitle = \Illuminate\Support\Str::slug($titleWithoutYear);

            // Strict matching: If year was provided, prioritize year + title match
            $strictResults = (clone $baseQuery)->where(function($q) use (
                $rawSearch, $cleanSearch, $detectedYear, $titleWithoutYear, 
                $titleKeywords, $keywords, $compactTitle, $compactFull, $slugSearch, $slugTitle
            ) {
                if ($detectedYear && !empty($titleWithoutYear)) {
                    $q->where(function($sub) use ($detectedYear, $titleWithoutYear, $titleKeywords, $compactTitle, $slugTitle) {
                        // Exact year + title match
                        $sub->where(function($exactYear) use ($detectedYear, $titleWithoutYear, $titleKeywords, $compactTitle, $slugTitle) {
                            $exactYear->where('release_year', $detectedYear)
                                      ->where(function($inner) use ($titleWithoutYear, $titleKeywords, $compactTitle, $slugTitle) {
                                          $inner->where('title', 'like', '%' . $titleWithoutYear . '%')
                                                ->orWhere('slug', 'like', '%' . $slugTitle . '%');

                                          if (strlen($compactTitle) >= 3) {
                                              $inner->orWhereRaw("REPLACE(REPLACE(LOWER(title), ' ', ''), '-', '') LIKE ?", ['%' . $compactTitle . '%']);
                                          }
                                      });
                        });

                        // Or close year match (+-1 year) for films produced/released across year boundaries
                        $sub->orWhere(function($closeYear) use ($detectedYear, $titleWithoutYear, $compactTitle) {
                            $closeYear->whereBetween('release_year', [$detectedYear - 1, $detectedYear + 1])
                                      ->where(function($inner) use ($titleWithoutYear, $compactTitle) {
                                          $inner->where('title', 'like', '%' . $titleWithoutYear . '%');
                                          if (strlen($compactTitle) >= 3) {
                                              $inner->orWhereRaw("REPLACE(REPLACE(LOWER(title), ' ', ''), '-', '') LIKE ?", ['%' . $compactTitle . '%']);
                                          }
                                      });
                        });

                        // Or all non-numeric title keywords match
                        if (!empty($titleKeywords)) {
                            $sub->orWhere(function($kwSub) use ($titleKeywords) {
                                foreach ($titleKeywords as $kw) {
                                    $kwSub->where('title', 'like', '%' . $kw . '%');
                                }
                            });
                        }
                    });

                    // Or exact title containing raw search
                    $q->orWhere('title', 'like', '%' . $rawSearch . '%');
                } else {
                    $q->where('title', 'like', '%' . $rawSearch . '%')
                      ->orWhere('title', 'like', '%' . $cleanSearch . '%')
                      ->orWhere('slug', 'like', '%' . $slugSearch . '%');

                    if (strlen($compactFull) >= 4) {
                        $q->orWhereRaw("REPLACE(REPLACE(LOWER(title), ' ', ''), '-', '') LIKE ?", ['%' . $compactFull . '%']);
                    }

                    if (count($keywords) > 1) {
                        $q->orWhere(function($sub) use ($keywords) {
                            foreach ($keywords as $w) {
                                $sub->where('title', 'like', '%' . $w . '%');
                            }
                        });
                    }
                }
            });

            if ($strictResults->count() > 0) {
                $query = $strictResults;
            } else {
                // Non-numeric primary keyword fallback
                $primaryKeyword = collect($titleKeywords)->first(fn($w) => !is_numeric($w) && strlen($w) >= 3) 
                    ?? collect($keywords)->first(fn($w) => !is_numeric($w) && strlen($w) >= 3) 
                    ?? $cleanSearch;

                $query = (clone $baseQuery)->where(function($q) use ($primaryKeyword, $cleanSearch, $slugSearch, $titleWithoutYear, $slugTitle) {
                    $q->where('title', 'like', '%' . $cleanSearch . '%')
                      ->orWhere('slug', 'like', '%' . $slugSearch . '%');

                    if (!empty($titleWithoutYear)) {
                        $q->orWhere('title', 'like', '%' . $titleWithoutYear . '%')
                          ->orWhere('slug', 'like', '%' . $slugTitle . '%');
                    }

                    if (!is_numeric($primaryKeyword) && strlen($primaryKeyword) >= 3 && !in_array($primaryKeyword, ['game', 'movie', 'full', 'part'])) {
                        $q->orWhere('title', 'like', '%' . $primaryKeyword . '%');
                    }
                });
            }

            // Order by relevance:
            if ($detectedYear && !empty($titleWithoutYear)) {
                $query->orderByRaw("CASE 
                    WHEN release_year = ? AND (title LIKE ? OR title LIKE ?) THEN 1
                    WHEN release_year = ? THEN 2
                    WHEN title LIKE ? OR title LIKE ? THEN 3
                    ELSE 4
                END", [
                    $detectedYear,
                    $titleWithoutYear,
                    $titleWithoutYear . '%',
                    $detectedYear,
                    $titleWithoutYear . '%',
                    '%' . $titleWithoutYear . '%'
                ])->orderByDesc('view_count');
            } else {
                $query->orderByRaw("CASE 
                    WHEN title LIKE ? THEN 1
                    WHEN title LIKE ? THEN 2
                    WHEN title LIKE ? THEN 3
                    ELSE 4
                END", [
                    $rawSearch,
                    $cleanSearch . '%',
                    '%' . $cleanSearch . '%'
                ])->orderByDesc('view_count');
            }
        } else {
            $query = Movie::where('status', 'active')->with(['genres', 'languages', 'links'])->latest();
        }

        $movies = $query->paginate(18)->withQueryString();
        $totalFound = $movies->total();
        
        return view('movies.index', compact('movies', 'totalFound'));
    }

    public function show(Movie $movie)
    {
        if ($movie->status !== 'active') {
            return response()->view('errors.movie-unavailable', [
                'message' => 'This movie is currently unavailable.'
            ], 404);
        }

        // Increment view count
        $movie->increment('view_count');
        
        $movie->load(['links' => function($q) {
            $q->where('status', true);
        }, 'genres', 'languages']);

        if ($movie->links->isEmpty() || empty($movie->poster_path) || str_contains($movie->description ?? '', 'Scraped from')) {
            app(\App\Services\Scrapers\MoviessdasScraper::class)->enrichMovieDetails($movie);
            app(\App\Services\MoviesdaCatalogService::class)->syncQualitiesForMovie($movie);
            $movie->refresh();
            $movie->load(['links' => function($q) {
                $q->where('status', true);
            }]);
        }

        return view('movies.show', compact('movie'));
    }
}
