<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movie extends Model
{
    /** @use HasFactory<\Database\Factories\MovieFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function links()
    {
        return $this->hasMany(MovieLink::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class);
    }

    public function logs()
    {
        return $this->hasMany(DownloadLog::class);
    }

    public function getCleanTitleAttribute(): string
    {
        $title = $this->title;
        $title = preg_replace('/\s+(Full|Web\s*Series)\b/i', '', $title);
        $title = preg_replace('/\s*\(\d{4}\)\s*$/', '', $title);
        return trim(preg_replace('/\s+/', ' ', $title));
    }

    public function getCleanDescriptionAttribute(): string
    {
        $desc = trim($this->description ?? '');

        // If synopsis has a "Synopsis: " prefix from page, clean it up
        if (stripos($desc, 'Synopsis:') === 0) {
            $desc = trim(substr($desc, 9));
        }

        // Check if description is a scraping artifact or raw URL
        $isArtifact = empty($desc)
            || stripos($desc, 'Scraped from') !== false
            || stripos($desc, 'Source:') !== false
            || preg_match('/https?:\/\//i', $desc)
            || preg_match('/^(Tamil\s*(Movies?|Dubbed|Collection|HD))\b/i', $desc);

        if ($isArtifact) {
            $year = $this->release_year ? " ({$this->release_year})" : '';
            $genreName = $this->genres->first()?->name;
            $genreText = $genreName ? " in the {$genreName} genre" : '';
            return "Experience the full theatrical narrative of {$this->clean_title}{$year}{$genreText}. Stream and download verified authorized prints in high definition across multiple resolutions.";
        }

        // Strip any accidental trailing source URL
        $desc = preg_replace('/Source:\s*https?:\/\/\S+/i', '', $desc);
        return trim($desc);
    }
}
