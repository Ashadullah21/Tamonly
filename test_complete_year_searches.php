<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\MoviesdaCatalogService;
use App\Http\Controllers\MovieController;
use Illuminate\Http\Request;

$catalogService = app(MoviesdaCatalogService::class);
$controller = app(MovieController::class);

$tamilOriginals = [
    2026 => '2026 kara',
    2025 => '2025 good bad ugly',
    2024 => '2024 vettaiyan',
    2023 => '2023 leo',
    2022 => '2022 thunivu',
    2021 => '2021 master',
    2020 => '2020 soorarai pottru',
    2019 => '2019 asuran',
    2018 => '2018 vada chennai',
    2017 => '2017 mersal',
    2016 => '2016 kabali',
    2015 => '2015 baahubali',
    2014 => '2014 kaththi',
    2013 => '2013 vishwaroopam',
    2012 => '2012 thuppakki',
    2011 => '2011 mankatha',
    2010 => '2010 enthiran',
    2009 => '2009 ayan',
    2008 => '2008 dasavathaaram',
    2007 => '2007 sivaji',
    2006 => '2006 varalaru',
    2005 => '2005 chandramukhi',
    2004 => '2004 ghilli',
    2003 => '2003 saamy',
    2002 => '2002 baba',
    2001 => '2001 aalavandhan',
    2000 => '2000 kandukondain',
    1999 => '1999 padayappa',
    1998 => '1998 jeans',
    1997 => '1997 arunachalam',
    1996 => '1996 indian',
    1995 => '1995 baashha',
    1994 => '1994 kadhalan',
    1993 => '1993 gentleman',
    1992 => '1992 roja',
    1991 => '1991 thalapathi',
    1990 => '1990 michael madana kama rajan',
];

$tamilDubbed = [
    2026 => '2026 avatar',
    2025 => '2025 captain america',
    2024 => '2024 deadpool',
    2023 => '2023 oppenheimer',
    2022 => '2022 doctor strange',
    2021 => '2021 spiderman no way home',
    2020 => '2020 tenet',
    2019 => '2019 Avengers end game',
    2018 => '2018 infinity war',
    2017 => '2017 logan',
    2016 => '2016 deadpool',
    2015 => '2015 jurassic world',
    2014 => '2014 amazing spiderman 2',
    2013 => '2013 iron man 3',
    2012 => '2012 avengers',
    2011 => '2011 transformers',
    2010 => '2010 inception',
    2009 => '2009 avatar',
    2008 => '2008 dark knight',
    2007 => '2007 spider man 3',
    2006 => '2006 casino royale',
    2005 => '2005 king kong',
    2004 => '2004 spider man',
    2003 => '2003 matrix reloaded',
    2002 => '2002 spider man',
    2001 => '2001 fast and furious',
    2000 => '2000 gladiator',
    1999 => '1999 matrix',
    1998 => '1998 titanic',
    1997 => '1997 men in black',
    1996 => '1996 independence day',
    1995 => '1995 jumanji',
    1994 => '1994 lion king',
    1993 => '1993 jurassic park',
    1992 => '1992 aladdin',
    1991 => '1991 terminator 2',
    1990 => '1990 home alone',
];

echo "==========================================================" . PHP_EOL;
echo "   SEARCH VALIDATION: TAMIL MOVIES (1990 - 2026)" . PHP_EOL;
echo "==========================================================" . PHP_EOL;
$tamilFailures = 0;
foreach ($tamilOriginals as $year => $q) {
    $request = Request::create('/movies', 'GET', ['search' => $q]);
    $view = $controller->index($request, $catalogService);
    $data = $view->getData();
    $found = $data['movies'];
    $top = $found->first();
    if ($top) {
        $langs = $top->languages->pluck('name')->implode(', ');
        echo "✅ [{$year}] '{$q}' => '{$top->clean_title}' ({$top->release_year}) [ID: {$top->id}]" . PHP_EOL;
    } else {
        echo "❌ [{$year}] '{$q}' => NOT FOUND!" . PHP_EOL;
        $tamilFailures++;
    }
}

echo PHP_EOL . "==========================================================" . PHP_EOL;
echo "   SEARCH VALIDATION: TAMIL DUBBED (1990 - 2026)" . PHP_EOL;
echo "==========================================================" . PHP_EOL;
$dubbedFailures = 0;
foreach ($tamilDubbed as $year => $q) {
    $request = Request::create('/movies', 'GET', ['search' => $q]);
    $view = $controller->index($request, $catalogService);
    $data = $view->getData();
    $found = $data['movies'];
    $top = $found->first();
    if ($top) {
        $langs = $top->languages->pluck('name')->implode(', ');
        echo "✅ [{$year}] '{$q}' => '{$top->clean_title}' ({$top->release_year}) [ID: {$top->id}] | Lang: {$langs}" . PHP_EOL;
    } else {
        echo "❌ [{$year}] '{$q}' => NOT FOUND!" . PHP_EOL;
        $dubbedFailures++;
    }
}

echo PHP_EOL . "==========================================================" . PHP_EOL;
echo "SUMMARY:" . PHP_EOL;
echo "Tamil Movies Failures: {$tamilFailures} / 37" . PHP_EOL;
echo "Tamil Dubbed Failures: {$dubbedFailures} / 37" . PHP_EOL;
if ($tamilFailures === 0 && $dubbedFailures === 0) {
    echo "🎉 ALL 74 YEAR SEARCHES (1990-2026) PASSED 100%!" . PHP_EOL;
}
echo "==========================================================" . PHP_EOL;
