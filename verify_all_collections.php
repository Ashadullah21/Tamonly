<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Movie;
use App\Models\Language;
use App\Models\MovieLink;
use Illuminate\Support\Str;

$tamilDubbedMaster = [
    2026 => [
        'title' => 'Avatar: Fire and Ash (Tamil Dubbed)',
        'search' => '2026 avatar',
        'year' => 2026,
        'poster' => 'https://image.tmdb.org/t/p/w500/sh7Rg8Er3tFcN9BpKIPOMvALgZd.jpg',
        'desc' => 'The continued journey of Jake Sully and Neytiri as they face the fire tribe of Pandora.',
        'qualities' => ['1080p HD (3.2 GB)', '720p HD (1.4 GB)', '480p HD (600 MB)'],
    ],
    2025 => [
        'title' => 'Captain America: Brave New World (Tamil Dubbed)',
        'search' => '2025 captain america',
        'year' => 2025,
        'poster' => 'https://image.tmdb.org/t/p/w500/pzIddUEMWhWzfvLI3TwxUG2wYqq.jpg',
        'desc' => 'Sam Wilson takes up the mantle of Captain America and navigates an international crisis.',
        'qualities' => ['1080p HD (2.8 GB)', '720p HD (1.2 GB)', '480p HD (550 MB)'],
    ],
    2024 => [
        'title' => 'Deadpool & Wolverine (Tamil Dubbed)',
        'search' => '2024 deadpool',
        'year' => 2024,
        'poster' => 'https://image.tmdb.org/t/p/w500/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg',
        'desc' => 'Wolverine is recovering from his injuries when he crosses paths with the loudmouth Deadpool.',
        'qualities' => ['1080p HD (2.9 GB)', '720p HD (1.3 GB)', '480p HD (580 MB)'],
    ],
    2023 => [
        'title' => 'Oppenheimer (Tamil Dubbed)',
        'search' => '2023 oppenheimer',
        'year' => 2023,
        'poster' => 'https://image.tmdb.org/t/p/w500/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg',
        'desc' => 'The story of American scientist J. Robert Oppenheimer and his role in the development of the atomic bomb.',
        'qualities' => ['1080p HD (3.5 GB)', '720p HD (1.5 GB)', '480p HD (700 MB)'],
    ],
    2022 => [
        'title' => 'Doctor Strange in the Multiverse of Madness (Tamil Dubbed)',
        'search' => '2022 doctor strange',
        'year' => 2022,
        'poster' => 'https://image.tmdb.org/t/p/w500/9Gtg2DzBhmYamXBS1oKAhiwbBKS.jpg',
        'desc' => 'Doctor Strange teams up with a mysterious teenage girl from his dreams who can travel across multiverses.',
        'qualities' => ['1080p HD (2.7 GB)', '720p HD (1.2 GB)', '480p HD (520 MB)'],
    ],
    2021 => [
        'title' => 'Spider-Man: No Way Home (Tamil Dubbed)',
        'search' => '2021 spiderman no way home',
        'year' => 2021,
        'poster' => 'https://image.tmdb.org/t/p/w500/1g0dhYtq4irTY1GPXvft6k4YLjm.jpg',
        'desc' => 'With Spider-Man identity revealed, Peter asks Doctor Strange for help, unraveling the multiverse.',
        'qualities' => ['1080p HD (3.0 GB)', '720p HD (1.4 GB)', '480p HD (600 MB)'],
    ],
    2020 => [
        'title' => 'Tenet (Tamil Dubbed)',
        'search' => '2020 tenet',
        'year' => 2020,
        'poster' => 'https://image.tmdb.org/t/p/w500/aCIFMriQ2UdRAJ1yY9PtW9BmICQ.jpg',
        'desc' => 'Armed with only one word, Tenet, a secret agent journeys through a twilight world of international espionage.',
        'qualities' => ['1080p HD (3.1 GB)', '720p HD (1.3 GB)', '480p HD (580 MB)'],
    ],
    2019 => [
        'title' => 'Avengers: Endgame (Tamil Dubbed)',
        'search' => '2019 Avengers end game',
        'year' => 2019,
        'poster' => 'https://image.tmdb.org/t/p/w500/or06FN3Dka5tukK1e9sl16pB3iy.jpg',
        'desc' => 'After Thanos wiped out half of all life, the remaining Avengers assemble once more to reverse his actions.',
        'qualities' => ['1080p HD (3.8 GB)', '720p HD (1.6 GB)', '480p HD (720 MB)'],
    ],
    2018 => [
        'title' => 'Avengers: Infinity War (Tamil Dubbed)',
        'search' => '2018 infinity war',
        'year' => 2018,
        'poster' => 'https://image.tmdb.org/t/p/w500/7WsyChQLEftFiDOVTGkv3hFpyyt.jpg',
        'desc' => 'The Avengers and their allies must be willing to sacrifice all in an attempt to defeat the powerful Thanos.',
        'qualities' => ['1080p HD (3.4 GB)', '720p HD (1.5 GB)', '480p HD (650 MB)'],
    ],
    2017 => [
        'title' => 'Logan (Tamil Dubbed)',
        'search' => '2017 logan',
        'year' => 2017,
        'poster' => 'https://image.tmdb.org/t/p/w500/fnbjcRDYn6YviCcePDnGdyAkYsB.jpg',
        'desc' => 'In the near future, a weary Logan cares for an ailing Professor X in a hideout on the Mexican border.',
        'qualities' => ['1080p HD (2.6 GB)', '720p HD (1.2 GB)', '480p HD (500 MB)'],
    ],
    2016 => [
        'title' => 'Deadpool (Tamil Dubbed)',
        'search' => '2016 deadpool',
        'year' => 2016,
        'poster' => 'https://image.tmdb.org/t/p/w500/inVq3meux0qqEazvxTXBa4JeuVa.jpg',
        'desc' => 'A wisecracking mercenary gets experimented on and becomes immortal but ugly, and sets out to track down the man who ruined his looks.',
        'qualities' => ['1080p HD (2.4 GB)', '720p HD (1.1 GB)', '480p HD (480 MB)'],
    ],
    2015 => [
        'title' => 'Jurassic World (Tamil Dubbed)',
        'search' => '2015 jurassic world',
        'year' => 2015,
        'poster' => 'https://image.tmdb.org/t/p/w500/A0LZHXPRzo424Wz4hKqSg1eY3w.jpg',
        'desc' => 'A new theme park built on the original site of Jurassic Park creates a genetically modified hybrid dinosaur.',
        'qualities' => ['1080p HD (2.7 GB)', '720p HD (1.2 GB)', '480p HD (530 MB)'],
    ],
    2014 => [
        'title' => 'The Amazing Spider-Man 2 (Tamil Dubbed)',
        'search' => '2014 amazing spiderman 2',
        'year' => 2014,
        'poster' => 'https://image.tmdb.org/t/p/w500/c3e98w9c2Fm0CqWfHh93x2d3j0Y.jpg',
        'desc' => 'When New York is put under siege by Oscorp, it is up to Spider-Man to save the city he swore to protect.',
        'qualities' => ['1080p HD (2.8 GB)', '720p HD (1.3 GB)', '480p HD (550 MB)'],
    ],
    2013 => [
        'title' => 'Iron Man 3 (Tamil Dubbed)',
        'search' => '2013 iron man 3',
        'year' => 2013,
        'poster' => 'https://image.tmdb.org/t/p/w500/qhPtAc1TKbMPqNvcdXS499999.jpg',
        'desc' => 'When Tony Stark world is torn apart by a formidable terrorist called the Mandarin, he starts an odyssey of rebuilding.',
        'qualities' => ['1080p HD (2.6 GB)', '720p HD (1.2 GB)', '480p HD (510 MB)'],
    ],
    2012 => [
        'title' => 'The Avengers (Tamil Dubbed)',
        'search' => '2012 avengers',
        'year' => 2012,
        'poster' => 'https://image.tmdb.org/t/p/w500/RYMX2wcKCBAr24UyPD7xwmjaTn.jpg',
        'desc' => 'Earth mightiest heroes must come together and learn to fight as a team to stop the mischievous Loki.',
        'qualities' => ['1080p HD (2.9 GB)', '720p HD (1.3 GB)', '480p HD (560 MB)'],
    ],
    2011 => [
        'title' => 'Transformers: Dark of the Moon (Tamil Dubbed)',
        'search' => '2011 transformers',
        'year' => 2011,
        'poster' => 'https://image.tmdb.org/t/p/w500/cKXspWn9zG1XlK8rT9s9Z4A.jpg',
        'desc' => 'The Autobots learn of a Cybertronian spacecraft hidden on the moon, and race against the Decepticons to reach it.',
        'qualities' => ['1080p HD (3.0 GB)', '720p HD (1.4 GB)', '480p HD (590 MB)'],
    ],
    2010 => [
        'title' => 'Inception (Tamil Dubbed)',
        'search' => '2010 inception',
        'year' => 2010,
        'poster' => 'https://image.tmdb.org/t/p/w500/oYuLEt3zVCKqZ7pqtfWpcEtqqr.jpg',
        'desc' => 'A thief who steals corporate secrets through dream-sharing technology is given the inverse task of planting an idea.',
        'qualities' => ['1080p HD (3.1 GB)', '720p HD (1.4 GB)', '480p HD (600 MB)'],
    ],
    2009 => [
        'title' => 'Avatar (Tamil Dubbed)',
        'search' => '2009 avatar',
        'year' => 2009,
        'poster' => 'https://image.tmdb.org/t/p/w500/kyeqWdyUXW608qlYkRqosgbbJyK.jpg',
        'desc' => 'A paraplegic Marine dispatched to the moon Pandora on a unique mission becomes torn between following orders and protecting the world.',
        'qualities' => ['1080p HD (3.2 GB)', '720p HD (1.5 GB)', '480p HD (620 MB)'],
    ],
    2008 => [
        'title' => 'The Dark Knight (Tamil Dubbed)',
        'search' => '2008 dark knight',
        'year' => 2008,
        'poster' => 'https://image.tmdb.org/t/p/w500/qJ2tW6WMUDux911r6m7haRef0WH.jpg',
        'desc' => 'When the menace known as the Joker wreaks havoc and chaos on Gotham City, Batman must accept one of the greatest tests.',
        'qualities' => ['1080p HD (3.0 GB)', '720p HD (1.3 GB)', '480p HD (580 MB)'],
    ],
    2007 => [
        'title' => 'Spider-Man 3 (Tamil Dubbed)',
        'search' => '2007 spider man 3',
        'year' => 2007,
        'poster' => 'https://image.tmdb.org/t/p/w500/2jLxGvdz7J3PzYtXJ8tG3a2bB2.jpg',
        'desc' => 'A strange black entity from another world bonds with Peter Parker, causing inner turmoil as he faces new villains.',
        'qualities' => ['1080p HD (2.7 GB)', '720p HD (1.2 GB)', '480p HD (520 MB)'],
    ],
    2006 => [
        'title' => 'Casino Royale (Tamil Dubbed)',
        'search' => '2006 casino royale',
        'year' => 2006,
        'poster' => 'https://image.tmdb.org/t/p/w500/zlWBfl28h5kR1r7K2z6k6bB4.jpg',
        'desc' => 'James Bond goes on his first mission as a 00 agent to defeat a private banker funding terrorists in a high-stakes poker game.',
        'qualities' => ['1080p HD (2.8 GB)', '720p HD (1.3 GB)', '480p HD (540 MB)'],
    ],
    2005 => [
        'title' => 'King Kong (Tamil Dubbed)',
        'search' => '2005 king kong',
        'year' => 2005,
        'poster' => 'https://image.tmdb.org/t/p/w500/3Uhp0M3w6yUu9A0qU6b2qB.jpg',
        'desc' => 'A theatrical filmmaker and his crew travel to Skull Island, where they encounter a colossal ape named Kong.',
        'qualities' => ['1080p HD (3.3 GB)', '720p HD (1.5 GB)', '480p HD (650 MB)'],
    ],
    2004 => [
        'title' => 'Spider-Man 2 (Tamil Dubbed)',
        'search' => '2004 spider man',
        'year' => 2004,
        'poster' => 'https://image.tmdb.org/t/p/w500/olxpyq94zk2HQOPTe0y27u.jpg',
        'desc' => 'Peter Parker is beset with troubles in his failing personal life as he battles a brilliant scientist named Doctor Octopus.',
        'qualities' => ['1080p HD (2.6 GB)', '720p HD (1.2 GB)', '480p HD (510 MB)'],
    ],
    2003 => [
        'title' => 'The Matrix Reloaded (Tamil Dubbed)',
        'search' => '2003 matrix reloaded',
        'year' => 2003,
        'poster' => 'https://image.tmdb.org/t/p/w500/9TGHDvWr2q7a6k8J0.jpg',
        'desc' => 'Freedom fighters Neo, Trinity and Morpheus lead the revolt against the Machine Army as Zion falls under attack.',
        'qualities' => ['1080p HD (2.8 GB)', '720p HD (1.3 GB)', '480p HD (530 MB)'],
    ],
    2002 => [
        'title' => 'Spider-Man (Tamil Dubbed)',
        'search' => '2002 spider man',
        'year' => 2002,
        'poster' => 'https://image.tmdb.org/t/p/w500/gh4cZbhZxyTbgxQPxD0dYD.jpg',
        'desc' => 'After being bitten by a genetically-modified spider, a shy teenager gains spider-like abilities and must battle the Green Goblin.',
        'qualities' => ['1080p HD (2.5 GB)', '720p HD (1.1 GB)', '480p HD (490 MB)'],
    ],
    2001 => [
        'title' => 'The Fast and the Furious (Tamil Dubbed)',
        'search' => '2001 fast and furious',
        'year' => 2001,
        'poster' => 'https://image.tmdb.org/t/p/w500/gqYlq5nE6qUe7a8g0.jpg',
        'desc' => 'Los Angeles street racer Dominic Toretto falls under suspicion of theft by an undercover cop who infiltrates his crew.',
        'qualities' => ['1080p HD (2.4 GB)', '720p HD (1.1 GB)', '480p HD (470 MB)'],
    ],
    2000 => [
        'title' => 'Gladiator (Tamil Dubbed)',
        'search' => '2000 gladiator',
        'year' => 2000,
        'poster' => 'https://image.tmdb.org/t/p/w500/ty8TGRuvJLPUmAR1H1nRIsgwvim.jpg',
        'desc' => 'A former Roman General sets out to exact vengeance against the corrupt emperor who murdered his family.',
        'qualities' => ['1080p HD (2.9 GB)', '720p HD (1.3 GB)', '480p HD (560 MB)'],
    ],
    1999 => [
        'title' => 'The Matrix (Tamil Dubbed)',
        'search' => '1999 matrix',
        'year' => 1999,
        'poster' => 'https://image.tmdb.org/t/p/w500/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg',
        'desc' => 'When a beautiful stranger leads computer hacker Neo to a forbidding underworld, he discovers the shocking truth about reality.',
        'qualities' => ['1080p HD (2.7 GB)', '720p HD (1.2 GB)', '480p HD (520 MB)'],
    ],
    1998 => [
        'title' => 'Titanic (Tamil Dubbed)',
        'search' => '1998 titanic',
        'year' => 1998,
        'poster' => 'https://image.tmdb.org/t/p/w500/9xjZS2rlVxm8SFx8kxrUp3IGZXW.jpg',
        'desc' => 'A seventeen-year-old aristocrat falls in love with a kind but poor artist aboard the luxurious, ill-fated R.M.S. Titanic.',
        'qualities' => ['1080p HD (3.6 GB)', '720p HD (1.6 GB)', '480p HD (700 MB)'],
    ],
    1997 => [
        'title' => 'Men in Black (Tamil Dubbed)',
        'search' => '1997 men in black',
        'year' => 1997,
        'poster' => 'https://image.tmdb.org/t/p/w500/uUQpnZ9h6h5W6kK9v2b2.jpg',
        'desc' => 'A police officer joins a secret organization that polices and monitors extraterrestrial interactions on Earth.',
        'qualities' => ['1080p HD (2.3 GB)', '720p HD (1.0 GB)', '480p HD (450 MB)'],
    ],
    1996 => [
        'title' => 'Independence Day (Tamil Dubbed)',
        'search' => '1996 independence day',
        'year' => 1996,
        'poster' => 'https://image.tmdb.org/t/p/w500/p0BPQG9QYv4zK6x1j5.jpg',
        'desc' => 'The aliens are coming and their goal is to invade and destroy Earth. Fighting superior technology, mankind best weapon is the will to survive.',
        'qualities' => ['1080p HD (2.8 GB)', '720p HD (1.3 GB)', '480p HD (540 MB)'],
    ],
    1995 => [
        'title' => 'Jumanji (Tamil Dubbed)',
        'search' => '1995 jumanji',
        'year' => 1995,
        'poster' => 'https://image.tmdb.org/t/p/w500/vgpXjvV4tq2P2b1x4k3.jpg',
        'desc' => 'When two kids play an old magical board game, they free a man trapped in it for decades and unleash a jungle world.',
        'qualities' => ['1080p HD (2.4 GB)', '720p HD (1.1 GB)', '480p HD (460 MB)'],
    ],
    1994 => [
        'title' => 'The Lion King (Tamil Dubbed)',
        'search' => '1994 lion king',
        'year' => 1994,
        'poster' => 'https://image.tmdb.org/t/p/w500/sKCr78jn99flvmjAQ.jpg',
        'desc' => 'Lion prince Simba and his father are targeted by his bitter uncle, who wants to ascend the throne himself.',
        'qualities' => ['1080p HD (2.2 GB)', '720p HD (1.0 GB)', '480p HD (420 MB)'],
    ],
    1993 => [
        'title' => 'Jurassic Park (Tamil Dubbed)',
        'search' => '1993 jurassic park',
        'year' => 1993,
        'poster' => 'https://image.tmdb.org/t/p/w500/oU7Oq2kFAAlGqbU4VoAE36g4ho1.jpg',
        'desc' => 'A pragmatic paleontologist touring an almost complete theme park on an island in Central America is tasked with protecting kids.',
        'qualities' => ['1080p HD (2.7 GB)', '720p HD (1.2 GB)', '480p HD (520 MB)'],
    ],
    1992 => [
        'title' => 'Aladdin (Tamil Dubbed)',
        'search' => '1992 aladdin',
        'year' => 1992,
        'poster' => 'https://image.tmdb.org/t/p/w500/k9b6aB2C4o2p2b2q.jpg',
        'desc' => 'A kind-hearted street urchin and a power-hungry Grand Vizier vie for a magic lamp that has the power to make their deepest wishes come true.',
        'qualities' => ['1080p HD (2.1 GB)', '720p HD (950 MB)', '480p HD (400 MB)'],
    ],
    1991 => [
        'title' => 'Terminator 2: Judgment Day (Tamil Dubbed)',
        'search' => '1991 terminator 2',
        'year' => 1991,
        'poster' => 'https://image.tmdb.org/t/p/w500/5M0j0B18abtVI5P9O.jpg',
        'desc' => 'A cyborg, identical to the one who failed to kill Sarah Connor, must now protect her ten-year-old son John from an advanced cyborg.',
        'qualities' => ['1080p HD (2.9 GB)', '720p HD (1.3 GB)', '480p HD (560 MB)'],
    ],
    1990 => [
        'title' => 'Home Alone (Tamil Dubbed)',
        'search' => '1990 home alone',
        'year' => 1990,
        'poster' => 'https://image.tmdb.org/t/p/w500/9wSbe4CwObACCQVaUV.jpg',
        'desc' => 'An eight-year-old troublemaker must protect his house from a pair of burglars when he is accidentally left home alone by his family.',
        'qualities' => ['1080p HD (2.3 GB)', '720p HD (1.0 GB)', '480p HD (440 MB)'],
    ],
];

$dubbedLang = Language::firstOrCreate(['slug' => 'tamil-dubbed'], ['name' => 'Tamil Dubbed']);

echo "=== SEEDING / VERIFYING TAMIL DUBBED (1990 - 2026) ===" . PHP_EOL;
foreach ($tamilDubbedMaster as $year => $item) {
    // Check if exists
    $movie = Movie::withTrashed()
        ->where('release_year', $year)
        ->where(function($q) use ($item) {
            $cleanT = trim(preg_replace('/\s*\(Tamil\s*Dubbed\)\s*/i', '', $item['title']));
            $q->where('title', 'like', '%' . $cleanT . '%');
        })
        ->first();

    if ($movie) {
        if ($movie->trashed()) $movie->restore();
        $movie->update([
            'status' => 'active',
            'release_year' => $year,
        ]);
        if (empty($movie->poster_path) || str_contains($movie->poster_path, 'placeholder')) {
            $movie->update(['poster_path' => $item['poster']]);
        }
        if (empty($movie->description) || str_contains($movie->description, 'Scraped from')) {
            $movie->update(['description' => $item['desc']]);
        }
    } else {
        $slug = Str::slug($item['title']);
        $c = 1;
        while (Movie::withTrashed()->where('slug', $slug)->exists()) {
            $slug = Str::slug($item['title']) . "-{$c}";
            $c++;
        }
        $movie = Movie::create([
            'title' => $item['title'],
            'slug' => $slug,
            'release_year' => $year,
            'description' => $item['desc'],
            'poster_path' => $item['poster'],
            'status' => 'active',
            'view_count' => rand(2500, 7500),
        ]);
    }

    $movie->languages()->syncWithoutDetaching([$dubbedLang->id]);

    // Ensure links exist
    if ($movie->links()->count() == 0) {
        foreach ($item['qualities'] as $qName) {
            $parts = explode(' ', $qName);
            MovieLink::create([
                'movie_id' => $movie->id,
                'quality' => $parts[0] . ' HD',
                'resolution' => $parts[0],
                'file_size' => trim(str_replace(['(', ')'], '', end($parts))),
                'download_url' => 'https://moviesda.com/download/' . $movie->slug . '/' . Str::slug($qName),
                'source_name' => 'Moviesda Direct Server',
                'status' => true,
            ]);
        }
    }

    echo "✅ [{$year}] '{$movie->clean_title}' (ID: {$movie->id})\n";
}

echo PHP_EOL . "=== TAMIL DUBBED SEEDING COMPLETE ===" . PHP_EOL;
