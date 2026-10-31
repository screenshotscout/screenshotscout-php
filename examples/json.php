<?php

declare(strict_types=1);

use ScreenshotScout\CaptureOptions;
use ScreenshotScout\CaptureResponseType;
use ScreenshotScout\Client;
use ScreenshotScout\JsonCaptureResponse;

require dirname(__DIR__) . '/vendor/autoload.php';

$accessKey = getenv('SCREENSHOTSCOUT_ACCESS_KEY');
if (!is_string($accessKey) || '' === trim($accessKey)) {
    throw new RuntimeException('Set SCREENSHOTSCOUT_ACCESS_KEY first.');
}

$client = new Client($accessKey);
$response = $client->capture(
    'https://example.com',
    (new CaptureOptions())->withResponseType(CaptureResponseType::JSON),
);
if (!$response instanceof JsonCaptureResponse) {
    throw new RuntimeException('Expected a JSON capture response.');
}

echo ($response->result()->screenshotUrl() ?? 'No screenshot URL returned') . PHP_EOL;
