<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Movie;
use Illuminate\Support\Facades\Http;

$checkUrls = [
    'leo' => 'https://moviezda.com/leo-2023-tamil-movie/',
    'master' => 'https://moviezda.com/master-2021-tamil-movie/',
    'soorarai-pottru' => 'https://moviezda.com/soorarai-pottru-2020-tamil-movie/',
    'spiderman-no-way-home' => 'https://moviezda.com/spider-man-no-way-home-2021-tamil-movie/',
    'avengers-endgame' => 'https://moviezda.com/avengers-endgame-2019-tamil-movie/',
    'logan' => 'https://moviezda.com/logan-2017-tamil-movie/',
    'deadpool' => 'https://moviezda.com/deadpool-2016-tamil-movie/',
    'amazing-spiderman-2' => 'https://moviezda.com/the-amazing-spider-man-2-2014-tamil-movie/',
    'spiderman-2004' => 'https://moviezda.com/spider-man-2-2004-tamil-movie/',
];

foreach ($checkUrls as $name => $url) {
    $res = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->timeout(3)->get($url);
    echo "{$name} ({$url}): HTTP " . $res->status() . " (length: " . strlen($res->body()) . ")" . PHP_EOL;
}
