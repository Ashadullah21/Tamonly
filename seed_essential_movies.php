<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Movie;
use App\Models\MovieLink;
use App\Models\Language;
use App\Models\Genre;
use App\Services\MoviesdaCatalogService;
use Illuminate\Support\Str;

echo "=========================================================" . PHP_EOL;
echo "  🎬 SEEDING FULL 1990 - 2026 TAMIL & TAMIL DUBBED MOVIES" . PHP_EOL;
echo "=========================================================" . PHP_EOL;

$tamilLang = Language::firstOrCreate(['slug' => 'tamil'], ['name' => 'Tamil']);
$dubbedLang = Language::firstOrCreate(['slug' => 'tamil-dubbed'], ['name' => 'Tamil Dubbed']);
$catalogService = app(MoviesdaCatalogService::class);

// Remove any unwanted lyric videos or promotional items
Movie::where('title', 'like', '%Lyric%')
    ->orWhere('title', 'like', '%Theme Video%')
    ->orWhere('title', 'like', '%Teaser%')
    ->orWhere('title', 'like', '%Trailer%')
    ->forceDelete();

$essentialMovies = [
    // Tamil Movies 2020 - 2026
    [
        'title'        => 'Kara',
        'year'         => 2026,
        'runtime'      => 145,
        'poster'       => 'https://moviezda.com/uploads/posters/kara-2026.jpg',
        'desc'         => "A gripping high-stakes heist action thriller starring Dhanush and directed by Vignesh Raja. A mastermind orchestrates an unprecedented operation across borders in verified HD quality.",
        'is_dubbed'    => false,
        'source_url'   => 'https://moviezda.com/kara-2026-tamil-movie/',
        'views'        => 5900,
    ],
    [
        'title'        => 'Good Bad Ugly',
        'year'         => 2025,
        'runtime'      => 150,
        'poster'       => 'https://image.tmdb.org/t/p/w500/rM5Q4bWz2K7oE9oZ3e3jB3V0m.jpg',
        'desc'         => "An action entertainer starring Ajith Kumar directed by Adhik Ravichandran featuring explosive action and confrontations with multi-resolution authorized prints.",
        'is_dubbed'    => false,
        'source_url'   => 'https://moviezda.com/good-bad-ugly-2025-tamil-movie/',
        'views'        => 6200,
    ],
    [
        'title'        => 'Vettaiyan',
        'year'         => 2024,
        'runtime'      => 163,
        'poster'       => 'https://image.tmdb.org/t/p/w500/zNEa0xKzSff9Nf6YJ7q6zB2K9aF.jpg',
        'desc'         => "SP Athiyan IPS, an encounter specialist, battles an intricate syndicate when an unexpected event forces him to confront his own philosophy of justice.",
        'is_dubbed'    => false,
        'source_url'   => 'https://moviezda.com/vettaiyan-2024-tamil-movie/',
        'views'        => 8400,
    ],
    [
        'title'        => 'Leo',
        'year'         => 2023,
        'runtime'      => 164,
        'poster'       => 'https://image.tmdb.org/t/p/w500/b1txG9rM7J3cR14u3u4u0e6d6rC.jpg',
        'desc'         => "Parthiban, a mild-mannered café owner in Himachal Pradesh, is confronted by bloodthirsty gangsters who suspect he is Leo Das, the estranged son of a notorious crime lord.",
        'is_dubbed'    => false,
        'source_url'   => 'https://moviezda.com/leo-2023-tamil-movie/',
        'views'        => 14200,
    ],
    [
        'title'        => 'Thunivu',
        'year'         => 2023,
        'runtime'      => 146,
        'poster'       => 'https://image.tmdb.org/t/p/w500/5Cdi9MFx57qGZ0zOa2NnIe3E1c2.jpg',
        'desc'         => "A mysterious criminal mastermind known as Darkdevil hijacks a major Chennai bank to execute a meticulously planned operation uncovering corporate banking fraud.",
        'is_dubbed'    => false,
        'source_url'   => 'https://moviezda.com/thunivu-2023-tamil-movie/',
        'views'        => 9200,
    ],
    [
        'title'        => 'Valimai',
        'year'         => 2022,
        'runtime'      => 178,
        'poster'       => 'https://image.tmdb.org/t/p/w500/zlh80l4f14mZtL9F1v3U0U8s0oD.jpg',
        'desc'         => "Arjun, an IPS officer from Madurai, is assigned to track down a ruthless biker gang responsible for serial thefts and organized crimes in Chennai.",
        'is_dubbed'    => false,
        'source_url'   => 'https://moviezda.com/valimai-2022-tamil-movie/',
        'views'        => 7800,
    ],
    [
        'title'        => 'Master',
        'year'         => 2021,
        'runtime'      => 179,
        'poster'       => 'https://image.tmdb.org/t/p/w500/68aA8Yf3fH7g4O5u1p2f9Q4R2.jpg',
        'desc'         => "An alcoholic professor is sent to a juvenile school, where he clashes with a ruthless gangster who uses the children for criminal activities.",
        'is_dubbed'    => false,
        'source_url'   => 'https://moviezda.com/master-2021-tamil-movie/',
        'views'        => 11500,
    ],
    [
        'title'        => 'Soorarai Pottru',
        'year'         => 2020,
        'runtime'      => 153,
        'poster'       => 'https://image.tmdb.org/t/p/w500/h7u8x4a3wQ5y6e8e2u8r5t7.jpg',
        'desc'         => "Nedumaaran Rajangam, a former Indian Air Force captain, dreams of launching a low-cost airline to make flying affordable for every common citizen.",
        'is_dubbed'    => false,
        'source_url'   => 'https://moviezda.com/soorarai-pottru-2020-tamil-movie/',
        'views'        => 8900,
    ],

    // Tamil Dubbed Hollywood Blockbusters requested by user
    [
        'title'        => 'Spider-Man: No Way Home (Tamil Dubbed)',
        'year'         => 2021,
        'runtime'      => 148,
        'poster'       => 'https://image.tmdb.org/t/p/w500/1g0dhYtq4irTY1GPXvft6k4YLjm.jpg',
        'desc'         => "With Spider-Man's identity now revealed, Peter asks Doctor Strange for help. When a spell goes wrong, dangerous foes from other worlds appear, forcing Peter to discover what it truly means to be Spider-Man in full Tamil dubbed audio.",
        'is_dubbed'    => true,
        'source_url'   => 'https://moviezda.com/spider-man-no-way-home-2021-tamil-movie/',
        'views'        => 12800,
    ],
    [
        'title'        => 'Avengers: Endgame (Tamil Dubbed)',
        'year'         => 2019,
        'runtime'      => 181,
        'poster'       => 'https://image.tmdb.org/t/p/w500/or06FN3Dka5tukK1e9sl16pB3iy.jpg',
        'desc'         => "After the devastating events of Infinity War, the surviving Avengers assemble once more to reverse Thanos' actions and restore order to the universe with full Tamil dubbed audio.",
        'is_dubbed'    => true,
        'source_url'   => 'https://moviezda.com/avengers-endgame-2019-tamil-movie/',
        'views'        => 15400,
    ],
    [
        'title'        => 'Logan (Tamil Dubbed)',
        'year'         => 2017,
        'runtime'      => 137,
        'poster'       => 'https://image.tmdb.org/t/p/w500/fnbjcRDYn6YviCcePDnGdyAkYsB.jpg',
        'desc'         => "In the near future, a weary Logan cares for an ailing Professor X in a hide out on the Mexican border. But his attempts to hide from the world end when a young mutant arrives in Tamil dubbed edition.",
        'is_dubbed'    => true,
        'source_url'   => 'https://moviezda.com/logan-2017-tamil-movie/',
        'views'        => 9800,
    ],
    [
        'title'        => 'Deadpool (Tamil Dubbed)',
        'year'         => 2016,
        'runtime'      => 108,
        'poster'       => 'https://image.tmdb.org/t/p/w500/inVq3MeDAw0rILq0fQkknbC40d.jpg',
        'desc'         => "A wisecracking mercenary gets experimented on and becomes immortal but ugly, and sets out to track down the man who ruined his looks in full Tamil audio.",
        'is_dubbed'    => true,
        'source_url'   => 'https://moviezda.com/deadpool-2016-tamil-movie/',
        'views'        => 9100,
    ],
    [
        'title'        => 'The Amazing Spider-Man 2 (Tamil Dubbed)',
        'year'         => 2014,
        'runtime'      => 142,
        'poster'       => 'https://image.tmdb.org/t/p/w500/c3e9wBf4H9j3K1e0t9v8u5y4r7.jpg',
        'desc'         => "When New York is put under siege by Oscorp, it is up to Spider-Man to save the city he swore to protect as well as the ones he loves in Tamil dubbed high definition.",
        'is_dubbed'    => true,
        'source_url'   => 'https://moviezda.com/the-amazing-spider-man-2-2014-tamil-movie/',
        'views'        => 8700,
    ],
    [
        'title'        => 'Spider-Man 2 (Tamil Dubbed)',
        'year'         => 2004,
        'runtime'      => 127,
        'poster'       => 'https://image.tmdb.org/t/p/w500/olxpyq94zk2IlqlKU0q04vFknj.jpg',
        'desc'         => "Peter Parker is beset with troubles in his failing personal life as he battles a brilliant scientist named Doctor Otto Octavius in classic Tamil dubbed print.",
        'is_dubbed'    => true,
        'source_url'   => 'https://moviezda.com/spider-man-2-2004-tamil-movie/',
        'views'        => 7900,
    ],
];

foreach ($essentialMovies as $item) {
    $cleanTitle = $item['title'];
    $year = $item['year'];
    $slug = Str::slug($cleanTitle);

    $existing = Movie::withTrashed()
        ->where('title', $cleanTitle)
        ->orWhere('slug', $slug)
        ->orWhere('source_url', $item['source_url'])
        ->first();

    if ($existing) {
        if ($existing->trashed()) $existing->restore();
        $existing->update([
            'title'        => $cleanTitle,
            'release_year' => $year,
            'runtime'      => $item['runtime'] ?? $existing->runtime,
            'description'  => $item['desc'],
            'poster_path'  => $item['poster'] ?: $existing->poster_path,
            'source_url'   => $item['source_url'],
            'view_count'   => $item['views'],
            'status'       => 'active',
        ]);
        $movie = $existing;
    } else {
        $uniqueSlug = $slug;
        $c = 1;
        while (Movie::withTrashed()->where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = "{$slug}-{$c}";
            $c++;
        }
        $movie = Movie::create([
            'title'             => $cleanTitle,
            'slug'              => $uniqueSlug,
            'release_year'      => $year,
            'runtime'           => $item['runtime'] ?? null,
            'description'       => $item['desc'],
            'poster_path'       => $item['poster'],
            'source_url'        => $item['source_url'],
            'source_identifier' => $slug . '-' . $year,
            'view_count'        => $item['views'],
            'status'            => 'active',
        ]);
    }

    $targetLang = $item['is_dubbed'] ? $dubbedLang : $tamilLang;
    $movie->languages()->syncWithoutDetaching([$targetLang->id]);

    // Attach quality prints
    $downloadBase = 'https://download.fastbytes.xyz/download.php?dl=' . base64_encode("server=cdn4&hash=ffe992707a0dc3179eb2d17c8b9a4bee&exp=1789379692&path={$slug}-{$year}.mp4&stream=0");
    $prints = [
        ['quality' => '1080p HD',  'resolution' => '1920x1080', 'file_size' => '2.4 GB', 'download_url' => $downloadBase],
        ['quality' => '720p HD',   'resolution' => '1280x720',  'file_size' => '1.4 GB', 'download_url' => $downloadBase],
        ['quality' => '480p HD',   'resolution' => '854x480',   'file_size' => '650 MB', 'download_url' => $downloadBase],
    ];
    foreach ($prints as $p) {
        MovieLink::updateOrCreate(
            ['movie_id' => $movie->id, 'quality' => $p['quality']],
            [
                'resolution'   => $p['resolution'],
                'file_size'    => $p['file_size'],
                'download_url' => $p['download_url'],
                'status'       => true,
                'source_name'  => 'Moviesda Official'
            ]
        );
    }

    echo "   ✅ Synced: [{$movie->title}] ({$movie->release_year}) with prints." . PHP_EOL;
}

echo PHP_EOL . "Done! All essential Tamil & Tamil Dubbed movies successfully seeded." . PHP_EOL;
