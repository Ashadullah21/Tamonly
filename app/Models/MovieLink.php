<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovieLink extends Model
{
    /** @use HasFactory<\Database\Factories\MovieLinkFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function logs()
    {
        return $this->hasMany(DownloadLog::class);
    }
}
