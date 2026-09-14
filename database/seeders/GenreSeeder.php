<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            'Action',
            'Sci-Fi',
            'Thriller',
            'Crime',
            'Drama',
            'Adventure',
            'Comedy',
            'Horror',
            'Mystery',
            'Romance',
            'Animation',
            'Fantasy'
        ];

        foreach ($genres as $name) {
            Genre::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}
