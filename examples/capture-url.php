<?php

declare(strict_types=1);

use ScreenshotScout\CaptureOptions;
use ScreenshotScout\Client;

require dirname(__DIR__) . '/vendor/autoload.php';

$accessKey = getenv('SCREENSHOTSCOUT_ACCESS_KEY');
if (!is_string($accessKey) || '' === trim($accessKey)) {
    throw new RuntimeException('Set SCREENSHOTSCOUT_ACCESS_KEY first.');
}

$secretKey = getenv('SCREENSHOTSCOUT_SECRET_KEY');
$secretKey = is_string($secretKey) && '' !== $secretKey ? $secretKey : null;

$client = new Client(
    $accessKey,
    secretKey: $secretKey,
);

echo $client->buildCaptureUrl(
    'https://example.com',
    (new CaptureOptions())
        ->withFullPage(true)
        ->withBlockAds(true),
) . PHP_EOL;
