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

        // Call Tamil Dubbed seeder
        require_once base_path('verify_all_collections.php');
    }
}
