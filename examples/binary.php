<?php

declare(strict_types=1);

use ScreenshotScout\BinaryCaptureResponse;
use ScreenshotScout\CaptureOptions;
use ScreenshotScout\Client;

require dirname(__DIR__) . '/vendor/autoload.php';

$client = new Client(); // Reads SCREENSHOTSCOUT_ACCESS_KEY
$response = $client->capture(
    'https://example.com',
    (new CaptureOptions())->withFullPage(true),
);
if (!$response instanceof BinaryCaptureResponse) {
    throw new RuntimeException('Expected a binary capture response.');
}

file_put_contents('screenshot.png', $response->bytes());
echo ($response->screenshotUrl() ?? 'No screenshot URL returned') . PHP_EOL;
