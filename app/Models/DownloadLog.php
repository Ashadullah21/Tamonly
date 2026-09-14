<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadLog extends Model
{
    /** @use HasFactory<\Database\Factories\DownloadLogFactory> */
    use HasFactory;

    protected $guarded = [];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function link()
    {
        return $this->belongsTo(MovieLink::class, 'movie_link_id');
    }
}
