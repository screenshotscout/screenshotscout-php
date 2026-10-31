<?php

declare(strict_types=1);

use ScreenshotScout\BinaryCaptureResponse;
use ScreenshotScout\CaptureOptions;
use ScreenshotScout\Client;

require dirname(__DIR__) . '/vendor/autoload.php';

$accessKey = getenv('SCREENSHOTSCOUT_ACCESS_KEY');
if (!is_string($accessKey) || '' === trim($accessKey)) {
    throw new RuntimeException('Set SCREENSHOTSCOUT_ACCESS_KEY first.');
}

$client = new Client($accessKey);
$response = $client->capture(
    'https://example.com',
    (new CaptureOptions())->withFullPage(true),
);
if (!$response instanceof BinaryCaptureResponse) {
    throw new RuntimeException('Expected a binary capture response.');
}

file_put_contents('screenshot.png', $response->bytes());
echo ($response->screenshotUrl() ?? 'No screenshot URL returned') . PHP_EOL;
