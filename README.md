# Screenshot Scout PHP SDK

The official PHP SDK for the [Screenshot Scout](https://screenshotscout.com/) screenshot API.

`capture()` sends a screenshot request and waits until the result is ready.

## Requirements

- PHP 8.3 or newer

## Installation

Install the SDK with Composer:

```shell
composer require screenshotscout/sdk
```

## Get your API credentials

[Sign up for Screenshot Scout](https://screenshotscout.com/auth/signup) or sign in, then copy the
access key and secret key from the [API Keys page](https://screenshotscout.com/app/api-keys). The
access key is required. The secret key is optional and enables [signed requests](#signed-requests).

## Capture a screenshot

Pass the page URL and any capture settings to `capture()`. This example captures the full page and
saves the returned image:

```php
<?php

declare(strict_types=1);

use ScreenshotScout\BinaryCaptureResponse;
use ScreenshotScout\CaptureOptions;
use ScreenshotScout\Client;

require __DIR__ . '/vendor/autoload.php';

$client = new Client(
    accessKey: 'YOUR_ACCESS_KEY',
    secretKey: 'YOUR_SECRET_KEY',
);

$response = $client->capture(
    'https://example.com',
    (new CaptureOptions())->withFullPage(true),
);

if (!$response instanceof BinaryCaptureResponse) {
    throw new RuntimeException('Expected a binary response.');
}

file_put_contents('screenshot.png', $response->bytes());
echo ($response->screenshotUrl() ?? 'No screenshot URL returned') . PHP_EOL;
```

POST is used by default. `capture()` returns a `BinaryCaptureResponse` unless JSON is requested.
Use `bytes()` to access the screenshot or PDF contents.

## Request a JSON result

Set the response type to JSON when you want capture metadata instead of image or PDF bytes:

```php
<?php

declare(strict_types=1);

use ScreenshotScout\CaptureOptions;
use ScreenshotScout\CaptureResponseType;
use ScreenshotScout\Client;
use ScreenshotScout\JsonCaptureResponse;

require __DIR__ . '/vendor/autoload.php';

$client = new Client(
    accessKey: 'YOUR_ACCESS_KEY',
    secretKey: 'YOUR_SECRET_KEY',
);

$response = $client->capture(
    'https://example.com',
    (new CaptureOptions())->withResponseType(CaptureResponseType::JSON),
);

if (!$response instanceof JsonCaptureResponse) {
    throw new RuntimeException('Expected a JSON response.');
}

echo ($response->result()->screenshotUrl() ?? 'No screenshot URL returned') . PHP_EOL;
```

## Use GET

Pass `CaptureHttpMethod::GET` as the third argument. POST remains the default.

```php
<?php

declare(strict_types=1);

use ScreenshotScout\BinaryCaptureResponse;
use ScreenshotScout\CaptureFormat;
use ScreenshotScout\CaptureHttpMethod;
use ScreenshotScout\CaptureOptions;
use ScreenshotScout\Client;

require __DIR__ . '/vendor/autoload.php';

$client = new Client(
    accessKey: 'YOUR_ACCESS_KEY',
    secretKey: 'YOUR_SECRET_KEY',
);

$response = $client->capture(
    'https://example.com',
    (new CaptureOptions())->withFormat(CaptureFormat::WEBP),
    CaptureHttpMethod::GET,
);

if (!$response instanceof BinaryCaptureResponse) {
    throw new RuntimeException('Expected a binary response.');
}

file_put_contents('screenshot.webp', $response->bytes());
```

## Build a capture URL

`buildCaptureUrl()` creates a capture URL without sending a request. It accepts the same
`CaptureOptions` as `capture()`:

```php
<?php

declare(strict_types=1);

use ScreenshotScout\CaptureOptions;
use ScreenshotScout\Client;

require __DIR__ . '/vendor/autoload.php';

$client = new Client(
    accessKey: 'YOUR_ACCESS_KEY',
    secretKey: 'YOUR_SECRET_KEY',
);

$captureUrl = $client->buildCaptureUrl(
    'https://example.com',
    (new CaptureOptions())
        ->withFullPage(true)
        ->withBlockAds(true),
);

echo $captureUrl . PHP_EOL;
```

Generated capture URLs contain your access key, so treat them as sensitive. A configured secret key
signs them automatically. Before sharing generated URLs, enable **Require signed requests** on the
[API Keys page](https://screenshotscout.com/app/api-keys).

## Signed requests

Pass the API key's secret as the optional `secretKey` constructor argument. The client then signs
capture requests and generated capture URLs automatically. The secret key is never transmitted. See
the [signed requests guide](https://screenshotscout.com/docs/signed-requests).

## Capture options

`CaptureOptions` is immutable: each `with...()` method returns a changed copy. Available options
include:

- Output: `withFormat`, `withResponseType`
- Network and location: `withCountry`, `withProxy`, `withGeolocationLatitude`,
  `withGeolocationLongitude`, `withGeolocationAccuracy`
- Cookies and webpage headers: `withCookies`, `withHeaders`
- Timing: `withTimeout`, `withWaitUntil`, `withNavigationTimeout`, `withDelay`
- Device emulation: `withDevice`, `withDeviceViewportWidth`, `withDeviceViewportHeight`,
  `withDeviceScaleFactor`, `withDeviceIsMobile`, `withDeviceHasTouch`, `withDeviceUserAgent`
- Page behavior: `withTimezone`, `withMediaType`, `withColorScheme`, `withReducedMotion`
- Full page: `withFullPage`, `withFullPagePreScroll`, `withFullPagePreScrollStep`,
  `withFullPagePreScrollStepDelay`, `withFullPageMaxHeight`
- Blocking: `withBlockCookieBanners`, `withBlockAds`, `withBlockChatWidgets`
- DOM changes: `withHideSelectors`, `withClickSelectors`, `withClickAllSelectors`, `withInjectCss`,
  `withInjectJs`, `withBypassCsp`
- Framing: `withSelector`, `withClipX`, `withClipY`, `withClipWidth`, `withClipHeight`
- Image output: `withImageWidth`, `withImageHeight`, `withImageMode`, `withImageAnchor`,
  `withImageAllowUpscale`, `withImageBackground`, `withImageQuality`
- PDF: `withPdfPaperFormat`, `withPdfLandscape`, `withPdfPrintBackground`, `withPdfMargin`,
  `withPdfMarginTop`, `withPdfMarginRight`, `withPdfMarginBottom`, `withPdfMarginLeft`, `withPdfScale`,
  `withPdfPageRanges`
- Caching: `withCache`, `withCacheTtl`, `withCacheKey`
- Storage: `withStorageMode`, `withStorageEndpoint`, `withStorageBucket`, `withStorageRegion`,
  `withStorageObjectKey`

Use `CaptureFormat`, `CaptureResponseType`, `CaptureWaitUntil`, `CaptureMediaType`,
`CaptureColorScheme`, `CaptureImageMode`, `CaptureImageAnchor`, `CapturePdfPaperFormat`, and
`CaptureStorageMode` for documented values. The corresponding methods also accept strings. See the
[Screenshot Scout option reference](https://screenshotscout.com/docs/screenshot-options) for
allowed values and behavior.

## HTTP timeouts and customization

`withTimeout()` controls how long Screenshot Scout may spend capturing the page. It does not set the
PHP HTTP timeout. To configure the HTTP timeout, pass a custom PSR-18 client:

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use ScreenshotScout\BinaryCaptureResponse;
use ScreenshotScout\CaptureOptions;
use ScreenshotScout\Client;

require __DIR__ . '/vendor/autoload.php';

$client = new Client(
    accessKey: 'YOUR_ACCESS_KEY',
    secretKey: 'YOUR_SECRET_KEY',
    httpClient: new GuzzleClient(['timeout' => 300]),
);

$response = $client->capture(
    'https://example.com',
    (new CaptureOptions())->withTimeout(180),
);

if (!$response instanceof BinaryCaptureResponse) {
    throw new RuntimeException('Expected a binary response.');
}

file_put_contents('screenshot.png', $response->bytes());
```

The default HTTP client has no total request timeout. A custom client can also configure proxies,
TLS, and other HTTP settings.

## Raw responses and errors

Use `rawResponse()` to access the status, headers, content type, and response body. `ApiException`
also provides the underlying response.

```php
<?php

declare(strict_types=1);

use ScreenshotScout\Client;
use ScreenshotScout\Exception\ApiException;
use ScreenshotScout\Exception\ConfigurationException;
use ScreenshotScout\Exception\ResponseDecodingException;
use ScreenshotScout\Exception\SerializationException;
use ScreenshotScout\Exception\TransportException;

require __DIR__ . '/vendor/autoload.php';

try {
    $client = new Client(
        accessKey: 'YOUR_ACCESS_KEY',
        secretKey: 'YOUR_SECRET_KEY',
    );
    $client->capture('https://example.com');
    echo 'Capture succeeded.' . PHP_EOL;
} catch (ApiException $exception) {
    echo $exception->statusCode();
    echo $exception->errorCode();
    echo $exception->errorMessage();
    var_dump($exception->errors(), $exception->responseBody());
    echo $exception->rawResponse()->body();
} catch (TransportException $exception) {
    var_dump($exception->getPrevious());
} catch (ConfigurationException|SerializationException|ResponseDecodingException $exception) {
    echo $exception->getMessage();
}
```

All SDK exceptions implement `ScreenshotScout\Exception\ScreenshotScoutException`, which can be
caught to handle any SDK error. `TransportException::getPrevious()` returns the underlying HTTP
client exception.

## License

Licensed under the [MIT License](LICENSE).
