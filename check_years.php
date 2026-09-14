<?php
require __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;

echo "Checking Moviezda year paths:" . PHP_EOL;

for ($y = 2026; $y >= 1990; $y--) {
    $url = "https://moviezda.com/tamil-{$y}-movies/";
    $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->timeout(3)->get($url);
    if ($resp->successful() && strlen($resp->body()) > 2000 && stripos($resp->body(), '404') === false) {
        echo "  ✅ {$y}: {$url} exists!" . PHP_EOL;
    } else {
        // Try alternate pattern
        $alt = "https://moviezda.com/moviesda-tamil-movies-{$y}/";
        $respAlt = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])->timeout(2)->get($alt);
        if ($respAlt->successful() && strlen($respAlt->body()) > 2000) {
            echo "  ✅ {$y}: {$alt} exists!" . PHP_EOL;
        } else {
            echo "  ❌ {$y}: not found via /tamil-{$y}-movies/" . PHP_EOL;
        }
    }
}
