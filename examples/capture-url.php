<?php

declare(strict_types=1);

use ScreenshotScout\CaptureOptions;
use ScreenshotScout\Client;

require dirname(__DIR__) . '/vendor/autoload.php';

// Reads SCREENSHOTSCOUT_ACCESS_KEY, and SCREENSHOTSCOUT_SECRET_KEY if it's set
$client = new Client();

echo $client->buildCaptureUrl(
    'https://example.com',
    (new CaptureOptions())
        ->withFullPage(true)
        ->withBlockAds(true),
) . PHP_EOL;
