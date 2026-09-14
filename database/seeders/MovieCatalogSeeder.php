<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\MovieLink;
use App\Models\Language;
use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MovieCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $tamilLang = Language::firstOrCreate(['slug' => 'tamil'], ['name' => 'Tamil']);
        $dubbedLang = Language::firstOrCreate(['slug' => 'tamil-dubbed'], ['name' => 'Tamil Dubbed']);

        $tamilOriginals = [
            [
                'title' => 'Kara',
                'year' => 2026,
                'runtime' => 145,
                'poster' => 'https://moviezda.com/uploads/posters/kara-2026.jpg',
                'desc' => "A gripping high-stakes heist action thriller starring Dhanush and directed by Vignesh Raja. A mastermind orchestrates an unprecedented operation across borders in verified HD quality.",
                'views' => 5900,
            ],
            [
                'title' => 'Good Bad Ugly',
                'year' => 2025,
                'runtime' => 150,
                'poster' => 'https://image.tmdb.org/t/p/w500/rM5Q4bWz2K7oE9oZ3e3jB3V0m.jpg',
                'desc' => "An action entertainer starring Ajith Kumar directed by Adhik Ravichandran featuring explosive action and confrontations with multi-resolution authorized prints.",
                'views' => 6200,
            ],
            [
                'title' => 'Vettaiyan',
                'year' => 2024,
                'runtime' => 163,
                'poster' => 'https://image.tmdb.org/t/p/w500/zNEa0xKzSff9Nf6YJ7q6zB2K9aF.jpg',
                'desc' => "SP Athiyan IPS, an encounter specialist, battles an intricate syndicate when an unexpected event forces him to confront his own philosophy of justice.",
                'views' => 8400,
            ],
            [
                'title' => 'LEO',
                'year' => 2023,
                'runtime' => 164,
                'poster' => 'https://image.tmdb.org/t/p/w500/pGqsmSmv519D9p9uf4EGNoW33Hq.jpg',
                'desc' => "Parthiban is a mild-mannered cafe owner in Theog, who gets thrust into the limelight after an act of self-defense, catching the attention of a drug cartel.",
                'views' => 9500,
            ],
            [
                'title' => 'Thunivu',
                'year' => 2023,
                'runtime' => 146,
                'poster' => 'https://image.tmdb.org/t/p/w500/c77oE4XU9l72GvN83yvR90Y4f2n.jpg',
                'desc' => "A mysterious mastermind and his team form a plan and commit a bank heist to find the corporate corruption behind customer mutual funds.",
                'views' => 7800,
            ],
            [
                'title' => 'Valimai',
                'year' => 2022,
                'runtime' => 178,
                'poster' => 'https://image.tmdb.org/t/p/w500/5bO3qRsmQ7r0Uu0GqK9U0a4.jpg',
                'desc' => "Arjun, an IPS officer from Madurai, travels to Chennai to track down a ruthless biker gang named Satan's Slave involved in heinous drug trafficking.",
                'views' => 6400,
            ],
            [
                'title' => 'Master',
                'year' => 2021,
                'runtime' => 179,
                'poster' => 'https://image.tmdb.org/t/p/w500/e9KqG1JvE6LgK4xQ9eZ3e3jB3V0m.jpg',
                'desc' => "An alcoholic professor is sent to a juvenile school, where he clashes with a ruthless gangster who uses the children for criminal activities.",
                'views' => 8900,
            ],
            [
                'title' => 'Soorarai Pottru',
                'year' => 2020,
                'runtime' => 153,
                'poster' => 'https://image.tmdb.org/t/p/w500/6A6W77r8t9y3uK4xQ9eZ3e3jB3V0m.jpg',
                'desc' => "Nedumaaran Rajangam sets out to make the common man fly and in the process takes on the world's most capital-intensive industry with the help of his friends and family.",
                'views' => 8100,
            ],
            [
                'title' => 'Asuran',
                'year' => 2019,
                'runtime' => 141,
                'poster' => 'https://image.tmdb.org/t/p/w500/7a5w3r9xY6k4xQ9eZ3e3jB3V0m.jpg',
                'desc' => "The teenage son of a farmer from an underprivileged caste kills a rich, upper-caste landlord. How the pacifist father saves his son forms the rest of the story.",
                'views' => 7500,
            ],
            [
                'title' => 'VadaChennai',
                'year' => 2018,
                'runtime' => 164,
                'poster' => 'https://image.tmdb.org/t/p/w500/8gVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "A young carrom player in north Chennai becomes a reluctant participant in a war between two feuding gangsters.",
                'views' => 7200,
            ],
            [
                'title' => 'Mersal',
                'year' => 2017,
                'runtime' => 172,
                'poster' => 'https://image.tmdb.org/t/p/w500/4gVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "A magician and a doctor, both brothers, seek revenge on corrupt medical professionals who caused their parents' deaths.",
                'views' => 7600,
            ],
            [
                'title' => 'Kabali',
                'year' => 2016,
                'runtime' => 153,
                'poster' => 'https://image.tmdb.org/t/p/w500/2gVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "An aged Malaysian gangster is released from prison and sets out to redeem himself, protect his family, and fight for oppressed plantation laborers.",
                'views' => 7300,
            ],
            [
                'title' => 'Baahubali: The Beginning',
                'year' => 2015,
                'runtime' => 159,
                'poster' => 'https://image.tmdb.org/t/p/w500/9bVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "In ancient India, an adventurous and daring man becomes involved in a decades-old feud between two warring people.",
                'views' => 8400,
            ],
            [
                'title' => 'Kaththi',
                'year' => 2014,
                'runtime' => 166,
                'poster' => 'https://image.tmdb.org/t/p/w500/5bVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "A petty thief takes on the identity of his lookalike, an activist fighting against an unethical multinational company seizing farmer lands.",
                'views' => 7100,
            ],
            [
                'title' => 'Vishwaroopam',
                'year' => 2013,
                'runtime' => 148,
                'poster' => 'https://image.tmdb.org/t/p/w500/3bVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "A classical dance instructor in New York is suspected of being involved in espionage, revealing a dangerous past as an undercover raw agent.",
                'views' => 6700,
            ],
            [
                'title' => 'Thuppakki',
                'year' => 2012,
                'runtime' => 165,
                'poster' => 'https://image.tmdb.org/t/p/w500/1bVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "An army captain on vacation in Mumbai tracks down and eliminates a sleeper cell terrorist network planning serial bombings across the city.",
                'views' => 8200,
            ],
            [
                'title' => 'Mankatha',
                'year' => 2011,
                'runtime' => 155,
                'poster' => 'https://moviezda.com/uploads/posters/mankatha-2011.jpg',
                'desc' => "Suspended cop Vinayak Mahadev joins hands with four young men to pull off a 500-crore IPL cricket betting robbery in Mumbai.",
                'views' => 9600,
            ],
            [
                'title' => 'Enthiran',
                'year' => 2010,
                'runtime' => 177,
                'poster' => 'https://image.tmdb.org/t/p/w500/7cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "A brilliant scientist creates Chitti, a humanoid robot with superhuman capabilities, whose newly installed emotions cause chaos when he falls in love with his creator's fiancée.",
                'views' => 8800,
            ],
            [
                'title' => 'Ayan',
                'year' => 2009,
                'runtime' => 162,
                'poster' => 'https://image.tmdb.org/t/p/w500/8gVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "A young man working for a smuggling syndicate finds his loyalties tested when family secrets and rivalries collide.",
                'views' => 6900,
            ],
            [
                'title' => 'Dasavathaaram',
                'year' => 2008,
                'runtime' => 188,
                'poster' => 'https://image.tmdb.org/t/p/w500/9cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "A scientist realizes the danger of a bio-weapon and flees to India to prevent a catastrophe, intersecting with nine other unique individuals.",
                'views' => 6500,
            ],
            [
                'title' => 'Sivaji',
                'year' => 2007,
                'runtime' => 185,
                'poster' => 'https://image.tmdb.org/t/p/w500/4cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "A software systems architect returns to India to provide free education and healthcare, taking on corrupt politicians and black money tycoons.",
                'views' => 8700,
            ],
            [
                'title' => 'Varalaru',
                'year' => 2006,
                'runtime' => 160,
                'poster' => 'https://image.tmdb.org/t/p/w500/2cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "A father, an estranged son, and an ambitious youth clash in a multi-generational drama of revenge, identity, and dance.",
                'views' => 5800,
            ],
            [
                'title' => 'Chandramukhi',
                'year' => 2005,
                'runtime' => 166,
                'poster' => 'https://image.tmdb.org/t/p/w500/1cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "Creepy happenings in an abandoned mansion are attributed to the ghost of an ancient dancer named Chandramukhi, prompting a psychiatrist to intervene.",
                'views' => 7400,
            ],
            [
                'title' => 'Ghilli',
                'year' => 2004,
                'runtime' => 166,
                'poster' => 'https://image.tmdb.org/t/p/w500/6cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "A state-level Kabaddi player rescues a young woman from an influential faction leader in Madurai and protects her in Chennai.",
                'views' => 9100,
            ],
            [
                'title' => 'Saamy',
                'year' => 2003,
                'runtime' => 160,
                'poster' => 'https://image.tmdb.org/t/p/w500/8cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "DCP Aarusaamy, an unconventional cop who takes bribes from local politicians while maintaining law and order, takes on underworld don Perumal Pichai.",
                'views' => 6800,
            ],
            [
                'title' => 'Baba',
                'year' => 2002,
                'runtime' => 170,
                'poster' => 'https://image.tmdb.org/t/p/w500/3cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "An atheist who receives seven magical mantras from Mahavatar Babaji must use them to make crucial decisions about his destiny and the society around him.",
                'views' => 5400,
            ],
            [
                'title' => 'Aalavandhan',
                'year' => 2001,
                'runtime' => 175,
                'poster' => 'https://image.tmdb.org/t/p/w500/5cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "Major Vijay Kumar must stop his mentally unstable twin brother Nandu, who escapes from an asylum convinced that Vijay's fiancée is his abusive stepmother.",
                'views' => 6100,
            ],
            [
                'title' => 'Kandukondain Kandukondain',
                'year' => 2000,
                'runtime' => 158,
                'poster' => 'https://image.tmdb.org/t/p/w500/7cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "Three sisters with distinct romantic aspirations navigate family hardships and relationships in this musical adaptation of Sense and Sensibility.",
                'views' => 5900,
            ],
            [
                'title' => 'Padayappa',
                'year' => 1999,
                'runtime' => 180,
                'poster' => 'https://image.tmdb.org/t/p/w500/9cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "An engineer rises from poverty to great prosperity, drawing the lifelong obsession and vengeance of an arrogant woman whose marriage proposal he rejected.",
                'views' => 8600,
            ],
            [
                'title' => 'Jeans',
                'year' => 1998,
                'runtime' => 175,
                'poster' => 'https://image.tmdb.org/t/p/w500/2cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "An Indian restaurant owner in Los Angeles wants his twin sons to marry twin sisters, leading the lover of one son to pretend she has a twin sister.",
                'views' => 6400,
            ],
            [
                'title' => 'Arunachalam',
                'year' => 1997,
                'runtime' => 165,
                'poster' => 'https://image.tmdb.org/t/p/w500/4cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "A simple village man discovers he is the son of a billionaire, but must spend 30 crores in 30 days without acquiring any assets to inherit the 3000-crore fortune.",
                'views' => 6200,
            ],
            [
                'title' => 'Indian',
                'year' => 1996,
                'runtime' => 185,
                'poster' => 'https://image.tmdb.org/t/p/w500/8cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "Senapathy, an elderly freedom fighter turned vigilante, uses ancient martial art Varma Kalai to root out corruption, even when it involves his own son.",
                'views' => 9300,
            ],
            [
                'title' => 'Baashha',
                'year' => 1995,
                'runtime' => 145,
                'poster' => 'https://image.tmdb.org/t/p/w500/6cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "Manikkam, an auto driver in Chennai, strives to keep his dark past as an underworld don in Mumbai hidden from his family.",
                'views' => 9800,
            ],
            [
                'title' => 'Kadhalan',
                'year' => 1994,
                'runtime' => 165,
                'poster' => 'https://image.tmdb.org/t/p/w500/1cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "A college student falls in love with the daughter of the state governor, uncovering a conspiracy involving terrorism and political assassinations.",
                'views' => 5900,
            ],
            [
                'title' => 'Gentleman',
                'year' => 1993,
                'runtime' => 160,
                'poster' => 'https://image.tmdb.org/t/p/w500/3cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "A modern Robin Hood steals from the rich to build free educational institutions for underprivileged students after a personal tragedy.",
                'views' => 6100,
            ],
            [
                'title' => 'Roja',
                'year' => 1992,
                'runtime' => 137,
                'poster' => 'https://image.tmdb.org/t/p/w500/5cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "A simple village woman goes to Kashmir with her cryptologist husband, embarking on a desperate quest to save him when he is abducted by militants.",
                'views' => 6700,
            ],
            [
                'title' => 'Thalapathi',
                'year' => 1991,
                'runtime' => 157,
                'poster' => 'https://image.tmdb.org/t/p/w500/7cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "A courageous slum dweller befriends an honorable don, tested by the arrival of an honest district collector who is his unknown biological brother.",
                'views' => 8900,
            ],
            [
                'title' => 'Michael Madana Kama Rajan',
                'year' => 1990,
                'runtime' => 162,
                'poster' => 'https://image.tmdb.org/t/p/w500/9cVwQW7Kq4g6rQZ6W2X6v1v1.jpg',
                'desc' => "Quadruplets separated at birth grow up in different walks of life as a criminal, a wealthy heir, a fire fighter, and a cook, colliding in hilarious chaos.",
                'views' => 7400,
            ],
        ];

        foreach ($tamilOriginals as $data) {
            $movie = Movie::withTrashed()->where('title', $data['title'])->first();
            if ($movie) {
                if ($movie->trashed()) $movie->restore();
                $movie->update([
                    'release_year' => $data['year'],
                    'runtime' => $data['runtime'],
                    'status' => 'active',
                ]);
            } else {
                $slug = Str::slug($data['title']);
                $c = 1;
                while (Movie::withTrashed()->where('slug', $slug)->exists()) {
                    $slug = Str::slug($data['title']) . "-{$c}";
                    $c++;
                }
                $movie = Movie::create([
                    'title' => $data['title'],
                    'slug' => $slug,
                    'release_year' => $data['year'],
                    'runtime' => $data['runtime'],
                    'description' => $data['desc'],
                    'poster_path' => $data['poster'],
                    'status' => 'active',
                    'view_count' => $data['views'],
                ]);
            }

            $movie->languages()->syncWithoutDetaching([$tamilLang->id]);

            if ($movie->links()->count() == 0) {
                foreach (['1080p HD (2.8 GB)', '720p HD (1.4 GB)', '480p HD (550 MB)'] as $q) {
                    $parts = explode(' ', $q);
                    MovieLink::create([
                        'movie_id' => $movie->id,
                        'quality' => $parts[0] . ' HD',
                        'resolution' => $parts[0],
                        'file_size' => trim(str_replace(['(', ')'], '', end($parts))),
                        'download_url' => 'https://moviesda.com/download/' . $movie->slug . '/' . Str::slug($q),
                        'source_name' => 'Moviesda Direct Server',
                        'status' => true,
                    ]);
                }
            }
        }

        // Seed Tamil Dubbed Collections (1990 - 2026)
        $this->seedTamilDubbed($dubbedLang);
    }

    protected function seedTamilDubbed(Language $dubbedLang): void
    {
        $tamilDubbedMaster = [
            2026 => [
                'title' => 'Avatar: Fire and Ash (Tamil Dubbed)',
                'year' => 2026,
                'poster' => 'https://image.tmdb.org/t/p/w500/sh7Rg8Er3tFcN9BpKIPOMvALgZd.jpg',
                'desc' => 'The continued journey of Jake Sully and Neytiri as they face the fire tribe of Pandora.',
                'qualities' => ['1080p HD (3.2 GB)', '720p HD (1.4 GB)', '480p HD (600 MB)'],
            ],
            2025 => [
                'title' => 'Captain America: Brave New World (Tamil Dubbed)',
                'year' => 2025,
                'poster' => 'https://image.tmdb.org/t/p/w500/pzIddUEMWhWzfvLI3TwxUG2wYqq.jpg',
                'desc' => 'Sam Wilson takes up the mantle of Captain America and navigates an international crisis.',
                'qualities' => ['1080p HD (2.8 GB)', '720p HD (1.2 GB)', '480p HD (550 MB)'],
            ],
            2024 => [
                'title' => 'Deadpool & Wolverine (Tamil Dubbed)',
                'year' => 2024,
                'poster' => 'https://image.tmdb.org/t/p/w500/8cdWjvZQUExUUTzyp4t6EDMubfO.jpg',
                'desc' => 'Wolverine is recovering from his injuries when he crosses paths with the loudmouth Deadpool.',
                'qualities' => ['1080p HD (2.9 GB)', '720p HD (1.3 GB)', '480p HD (580 MB)'],
            ],
            2023 => [
                'title' => 'Oppenheimer (Tamil Dubbed)',
                'year' => 2023,
                'poster' => 'https://image.tmdb.org/t/p/w500/8Gxv8gSFCU0XGDykEGv7zR1n2ua.jpg',
                'desc' => 'The story of American scientist J. Robert Oppenheimer and his role in the development of the atomic bomb.',
                'qualities' => ['1080p HD (3.5 GB)', '720p HD (1.5 GB)', '480p HD (700 MB)'],
            ],
            2022 => [
                'title' => 'Doctor Strange in the Multiverse of Madness (Tamil Dubbed)',
                'year' => 2022,
                'poster' => 'https://image.tmdb.org/t/p/w500/9Gtg2DzBhmYamXBS1oKAhiwbBKS.jpg',
                'desc' => 'Doctor Strange teams up with a mysterious teenage girl from his dreams who can travel across multiverses.',
                'qualities' => ['1080p HD (2.7 GB)', '720p HD (1.2 GB)', '480p HD (520 MB)'],
            ],
            2021 => [
                'title' => 'Spider-Man: No Way Home (Tamil Dubbed)',
                'year' => 2021,
                'poster' => 'https://image.tmdb.org/t/p/w500/1g0dhYtq4irTY1GPXvft6k4YLjm.jpg',
                'desc' => 'With Spider-Man identity revealed, Peter asks Doctor Strange for help, unraveling the multiverse.',
                'qualities' => ['1080p HD (3.0 GB)', '720p HD (1.4 GB)', '480p HD (600 MB)'],
            ],
            2020 => [
                'title' => 'Tenet (Tamil Dubbed)',
                'year' => 2020,
                'poster' => 'https://image.tmdb.org/t/p/w500/aCIFMriQ2UdRAJ1yY9PtW9BmICQ.jpg',
                'desc' => 'Armed with only one word, Tenet, a secret agent journeys through a twilight world of international espionage.',
                'qualities' => ['1080p HD (3.1 GB)', '720p HD (1.3 GB)', '480p HD (580 MB)'],
            ],
            2019 => [
                'title' => 'Avengers: Endgame (Tamil Dubbed)',
                'year' => 2019,
                'poster' => 'https://image.tmdb.org/t/p/w500/or06FN3Dka5tukK1e9sl16pB3iy.jpg',
                'desc' => 'After Thanos wiped out half of all life, the remaining Avengers assemble once more to reverse his actions.',
                'qualities' => ['1080p HD (3.8 GB)', '720p HD (1.6 GB)', '480p HD (720 MB)'],
            ],
            2018 => [
                'title' => 'Avengers: Infinity War (Tamil Dubbed)',
                'year' => 2018,
                'poster' => 'https://image.tmdb.org/t/p/w500/7WsyChQLEftFiDOVTGkv3hFpyyt.jpg',
                'desc' => 'The Avengers and their allies must be willing to sacrifice all in an attempt to defeat the powerful Thanos.',
                'qualities' => ['1080p HD (3.4 GB)', '720p HD (1.5 GB)', '480p HD (650 MB)'],
            ],
            2017 => [
                'title' => 'Logan (Tamil Dubbed)',
                'year' => 2017,
                'poster' => 'https://image.tmdb.org/t/p/w500/fnbjcRDYn6YviCcePDnGdyAkYsB.jpg',
                'desc' => 'In the near future, a weary Logan cares for an ailing Professor X in a hideout on the Mexican border.',
                'qualities' => ['1080p HD (2.6 GB)', '720p HD (1.2 GB)', '480p HD (500 MB)'],
            ],
            2016 => [
                'title' => 'Deadpool (Tamil Dubbed)',
                'year' => 2016,
                'poster' => 'https://image.tmdb.org/t/p/w500/inVq3meux0qqEazvxTXBa4JeuVa.jpg',
                'desc' => 'A wisecracking mercenary gets experimented on and becomes immortal but ugly, and sets out to track down the man who ruined his looks.',
                'qualities' => ['1080p HD (2.4 GB)', '720p HD (1.1 GB)', '480p HD (480 MB)'],
            ],
            2015 => [
                'title' => 'Jurassic World (Tamil Dubbed)',
                'year' => 2015,
                'poster' => 'https://image.tmdb.org/t/p/w500/A0LZHXPRzo424Wz4hKqSg1eY3w.jpg',
                'desc' => 'A new theme park built on the original site of Jurassic Park creates a genetically modified hybrid dinosaur.',
                'qualities' => ['1080p HD (2.7 GB)', '720p HD (1.2 GB)', '480p HD (530 MB)'],
            ],
            2014 => [
                'title' => 'The Amazing Spider-Man 2 (Tamil Dubbed)',
                'year' => 2014,
                'poster' => 'https://image.tmdb.org/t/p/w500/c3e98w9c2Fm0CqWfHh93x2d3j0Y.jpg',
                'desc' => 'When New York is put under siege by Oscorp, it is up to Spider-Man to save the city he swore to protect.',
                'qualities' => ['1080p HD (2.8 GB)', '720p HD (1.3 GB)', '480p HD (550 MB)'],
            ],
            2013 => [
                'title' => 'Iron Man 3 (Tamil Dubbed)',
                'year' => 2013,
                'poster' => 'https://image.tmdb.org/t/p/w500/qhPtAc1TKbMPqNvcdXS499999.jpg',
                'desc' => 'When Tony Stark world is torn apart by a formidable terrorist called the Mandarin, he starts an odyssey of rebuilding.',
                'qualities' => ['1080p HD (2.6 GB)', '720p HD (1.2 GB)', '480p HD (510 MB)'],
            ],
            2012 => [
                'title' => 'The Avengers (Tamil Dubbed)',
                'year' => 2012,
                'poster' => 'https://image.tmdb.org/t/p/w500/RYMX2wcKCBAr24UyPD7xwmjaTn.jpg',
                'desc' => 'Earth mightiest heroes must come together and learn to fight as a team to stop the mischievous Loki.',
                'qualities' => ['1080p HD (2.9 GB)', '720p HD (1.3 GB)', '480p HD (560 MB)'],
            ],
            2011 => [
                'title' => 'Transformers: Dark of the Moon (Tamil Dubbed)',
                'year' => 2011,
                'poster' => 'https://image.tmdb.org/t/p/w500/cKXspWn9zG1XlK8rT9s9Z4A.jpg',
                'desc' => 'The Autobots learn of a Cybertronian spacecraft hidden on the moon, and race against the Decepticons to reach it.',
                'qualities' => ['1080p HD (3.0 GB)', '720p HD (1.4 GB)', '480p HD (590 MB)'],
            ],
            2010 => [
                'title' => 'Inception (Tamil Dubbed)',
                'year' => 2010,
                'poster' => 'https://image.tmdb.org/t/p/w500/oYuLEt3zVCKqZ7pqtfWpcEtqqr.jpg',
                'desc' => 'A thief who steals corporate secrets through dream-sharing technology is given the inverse task of planting an idea.',
                'qualities' => ['1080p HD (3.1 GB)', '720p HD (1.4 GB)', '480p HD (600 MB)'],
            ],
            2009 => [
                'title' => 'Avatar (Tamil Dubbed)',
                'year' => 2009,
                'poster' => 'https://image.tmdb.org/t/p/w500/kyeqWdyUXW608qlYkRqosgbbJyK.jpg',
                'desc' => 'A paraplegic Marine dispatched to the moon Pandora on a unique mission becomes torn between following orders and protecting the world.',
                'qualities' => ['1080p HD (3.2 GB)', '720p HD (1.5 GB)', '480p HD (620 MB)'],
            ],
            2008 => [
                'title' => 'The Dark Knight (Tamil Dubbed)',
                'year' => 2008,
                'poster' => 'https://image.tmdb.org/t/p/w500/qJ2tW6WMUDux911r6m7haRef0WH.jpg',
                'desc' => 'When the menace known as the Joker wreaks havoc and chaos on Gotham City, Batman must accept one of the greatest tests.',
                'qualities' => ['1080p HD (3.0 GB)', '720p HD (1.3 GB)', '480p HD (580 MB)'],
            ],
            2007 => [
                'title' => 'Spider-Man 3 (Tamil Dubbed)',
                'year' => 2007,
                'poster' => 'https://image.tmdb.org/t/p/w500/2jLxGvdz7J3PzYtXJ8tG3a2bB2.jpg',
                'desc' => 'A strange black entity from another world bonds with Peter Parker, causing inner turmoil as he faces new villains.',
                'qualities' => ['1080p HD (2.7 GB)', '720p HD (1.2 GB)', '480p HD (520 MB)'],
            ],
            2006 => [
                'title' => 'Casino Royale (Tamil Dubbed)',
                'year' => 2006,
                'poster' => 'https://image.tmdb.org/t/p/w500/zlWBfl28h5kR1r7K2z6k6bB4.jpg',
                'desc' => 'James Bond goes on his first mission as a 00 agent to defeat a private banker funding terrorists in a high-stakes poker game.',
                'qualities' => ['1080p HD (2.8 GB)', '720p HD (1.3 GB)', '480p HD (540 MB)'],
            ],
            2005 => [
                'title' => 'King Kong (Tamil Dubbed)',
                'year' => 2005,
                'poster' => 'https://image.tmdb.org/t/p/w500/3Uhp0M3w6yUu9A0qU6b2qB.jpg',
                'desc' => 'A theatrical filmmaker and his crew travel to Skull Island, where they encounter a colossal ape named Kong.',
                'qualities' => ['1080p HD (3.3 GB)', '720p HD (1.5 GB)', '480p HD (650 MB)'],
            ],
            2004 => [
                'title' => 'Spider-Man 2 (Tamil Dubbed)',
                'year' => 2004,
                'poster' => 'https://image.tmdb.org/t/p/w500/olxpyq94zk2HQOPTe0y27u.jpg',
                'desc' => 'Peter Parker is beset with troubles in his failing personal life as he battles a brilliant scientist named Doctor Octopus.',
                'qualities' => ['1080p HD (2.6 GB)', '720p HD (1.2 GB)', '480p HD (510 MB)'],
            ],
            2003 => [
                'title' => 'The Matrix Reloaded (Tamil Dubbed)',
                'year' => 2003,
                'poster' => 'https://image.tmdb.org/t/p/w500/9TGHDvWr2q7a6k8J0.jpg',
                'desc' => 'Freedom fighters Neo, Trinity and Morpheus lead the revolt against the Machine Army as Zion falls under attack.',
                'qualities' => ['1080p HD (2.8 GB)', '720p HD (1.3 GB)', '480p HD (530 MB)'],
            ],
            2002 => [
                'title' => 'Spider-Man (Tamil Dubbed)',
                'year' => 2002,
                'poster' => 'https://image.tmdb.org/t/p/w500/gh4cZbhZxyTbgxQPxD0dYD.jpg',
                'desc' => 'After being bitten by a genetically-modified spider, a shy teenager gains spider-like abilities and must battle the Green Goblin.',
                'qualities' => ['1080p HD (2.5 GB)', '720p HD (1.1 GB)', '480p HD (490 MB)'],
            ],
            2001 => [
                'title' => 'The Fast and the Furious (Tamil Dubbed)',
                'year' => 2001,
                'poster' => 'https://image.tmdb.org/t/p/w500/gqYlq5nE6qUe7a8g0.jpg',
                'desc' => 'Los Angeles street racer Dominic Toretto falls under suspicion of theft by an undercover cop who infiltrates his crew.',
                'qualities' => ['1080p HD (2.4 GB)', '720p HD (1.1 GB)', '480p HD (470 MB)'],
            ],
            2000 => [
                'title' => 'Gladiator (Tamil Dubbed)',
                'year' => 2000,
                'poster' => 'https://image.tmdb.org/t/p/w500/ty8TGRuvJLPUmAR1H1nRIsgwvim.jpg',
                'desc' => 'A former Roman General sets out to exact vengeance against the corrupt emperor who murdered his family.',
                'qualities' => ['1080p HD (2.9 GB)', '720p HD (1.3 GB)', '480p HD (560 MB)'],
            ],
            1999 => [
                'title' => 'The Matrix (Tamil Dubbed)',
                'year' => 1999,
                'poster' => 'https://image.tmdb.org/t/p/w500/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg',
                'desc' => 'When a beautiful stranger leads computer hacker Neo to a forbidding underworld, he discovers the shocking truth about reality.',
                'qualities' => ['1080p HD (2.7 GB)', '720p HD (1.2 GB)', '480p HD (520 MB)'],
            ],
            1998 => [
                'title' => 'Titanic (Tamil Dubbed)',
                'year' => 1998,
                'poster' => 'https://image.tmdb.org/t/p/w500/9xjZS2rlVxm8SFx8kxrUp3IGZXW.jpg',
                'desc' => 'A seventeen-year-old aristocrat falls in love with a kind but poor artist aboard the luxurious, ill-fated R.M.S. Titanic.',
                'qualities' => ['1080p HD (3.6 GB)', '720p HD (1.6 GB)', '480p HD (700 MB)'],
            ],
            1997 => [
                'title' => 'Men in Black (Tamil Dubbed)',
                'year' => 1997,
                'poster' => 'https://image.tmdb.org/t/p/w500/uUQpnZ9h6h5W6kK9v2b2.jpg',
                'desc' => 'A police officer joins a secret organization that polices and monitors extraterrestrial interactions on Earth.',
                'qualities' => ['1080p HD (2.3 GB)', '720p HD (1.0 GB)', '480p HD (450 MB)'],
            ],
            1996 => [
                'title' => 'Independence Day (Tamil Dubbed)',
                'year' => 1996,
                'poster' => 'https://image.tmdb.org/t/p/w500/p0BPQG9QYv4zK6x1j5.jpg',
                'desc' => 'The aliens are coming and their goal is to invade and destroy Earth. Fighting superior technology, mankind best weapon is the will to survive.',
                'qualities' => ['1080p HD (2.8 GB)', '720p HD (1.3 GB)', '480p HD (540 MB)'],
            ],
            1995 => [
                'title' => 'Jumanji (Tamil Dubbed)',
                'year' => 1995,
                'poster' => 'https://image.tmdb.org/t/p/w500/vgpXjvV4tq2P2b1x4k3.jpg',
                'desc' => 'When two kids play an old magical board game, they free a man trapped in it for decades and unleash a jungle world.',
                'qualities' => ['1080p HD (2.4 GB)', '720p HD (1.1 GB)', '480p HD (460 MB)'],
            ],
            1994 => [
                'title' => 'The Lion King (Tamil Dubbed)',
                'year' => 1994,
                'poster' => 'https://image.tmdb.org/t/p/w500/sKCr78jn99flvmjAQ.jpg',
                'desc' => 'Lion prince Simba and his father are targeted by his bitter uncle, who wants to ascend the throne himself.',
                'qualities' => ['1080p HD (2.2 GB)', '720p HD (1.0 GB)', '480p HD (420 MB)'],
            ],
            1993 => [
                'title' => 'Jurassic Park (Tamil Dubbed)',
                'year' => 1993,
                'poster' => 'https://image.tmdb.org/t/p/w500/oU7Oq2kFAAlGqbU4VoAE36g4ho1.jpg',
                'desc' => 'A pragmatic paleontologist touring an almost complete theme park on an island in Central America is tasked with protecting kids.',
                'qualities' => ['1080p HD (2.7 GB)', '720p HD (1.2 GB)', '480p HD (520 MB)'],
            ],
            1992 => [
                'title' => 'Aladdin (Tamil Dubbed)',
                'year' => 1992,
                'poster' => 'https://image.tmdb.org/t/p/w500/k9b6aB2C4o2p2b2q.jpg',
                'desc' => 'A kind-hearted street urchin and a power-hungry Grand Vizier vie for a magic lamp that has the power to make their deepest wishes come true.',
                'qualities' => ['1080p HD (2.1 GB)', '720p HD (950 MB)', '480p HD (400 MB)'],
            ],
            1991 => [
                'title' => 'Terminator 2: Judgment Day (Tamil Dubbed)',
                'year' => 1991,
                'poster' => 'https://image.tmdb.org/t/p/w500/5M0j0B18abtVI5P9O.jpg',
                'desc' => 'A cyborg, identical to the one who failed to kill Sarah Connor, must now protect her ten-year-old son John from an advanced cyborg.',
                'qualities' => ['1080p HD (2.9 GB)', '720p HD (1.3 GB)', '480p HD (560 MB)'],
            ],
            1990 => [
                'title' => 'Home Alone (Tamil Dubbed)',
                'year' => 1990,
                'poster' => 'https://image.tmdb.org/t/p/w500/9wSbe4CwObACCQVaUV.jpg',
                'desc' => 'An eight-year-old troublemaker must protect his house from a pair of burglars when he is accidentally left home alone by his family.',
                'qualities' => ['1080p HD (2.3 GB)', '720p HD (1.0 GB)', '480p HD (440 MB)'],
            ],
        ];

        foreach ($tamilDubbedMaster as $year => $item) {
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
        }
    }
}
