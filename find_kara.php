<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Http;
use App\Models\Movie;

// Search DB for kara
echo "DB movies matching 'kara':" . PHP_EOL;
foreach (Movie::where('title', 'like', '%kara%')->get() as $m) {
    echo "  - [ID: {$m->id}] {$m->title} ({$m->release_year})" . PHP_EOL;
}

// Search 2026 pages on Moviezda
echo PHP_EOL . "Moviezda 2026 movies:" . PHP_EOL;
for ($p = 1; $p <= 3; $p++) {
    $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->get("https://moviezda.com/tamil-2026-movies/?page={$p}");
    if ($resp->successful()) {
        $c = new Crawler($resp->body(), "https://moviezda.com/tamil-2026-movies/?page={$p}");
        $c->filter('div.f a')->each(function($a) {
            $t = trim($a->text());
            if (stripos($t, 'kara') !== false || stripos($t, 'karan') !== false) {
                echo "  Match on 2026: {$t} => " . $a->attr('href') . PHP_EOL;
            }
        });
    }
}
