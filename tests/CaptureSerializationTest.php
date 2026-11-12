<?php

declare(strict_types=1);

namespace ScreenshotScout\Tests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ScreenshotScout\CaptureHttpMethod;
use ScreenshotScout\CaptureOptions;
use ScreenshotScout\CaptureResponse;
use ScreenshotScout\CaptureResponseType;
use ScreenshotScout\Client;
use ScreenshotScout\Exception\ConfigurationException;
use ScreenshotScout\Exception\SerializationException;

final class CaptureSerializationTest extends TestCase
{
    public function testConfigurationValidatesCredentials(): void
    {
        $this->expectConfigurationException(
            static fn(): Client => new Client('   '),
        );
        $this->expectConfigurationException(
            static fn(): Client => new Client('key with spaces'),
        );
        $this->expectConfigurationException(
            static fn(): Client => new Client('key', ''),
        );

        self::assertInstanceOf(Client::class, new Client('key'));
    }

    public function testPostIsDefaultAndSerializesTheCompleteTypedOptionSurface(): void
    {
        $transport = new RecordingHttpClient();
        $client = $this->client($transport, 'access-key');
        $options = (new CaptureOptions())
            ->withFormat('future-format')
            ->withResponseType('future-response')
            ->withCountry('')
            ->withProxy('')
            ->withGeolocationLatitude(0)
            ->withGeolocationLongitude(0)
            ->withGeolocationAccuracy(0)
            ->withCookies(['session=a', 'session=a', ''])
            ->withHeaders(['X-Test:one', 'X-Test:one', ''])
            ->withTimeout(0)
            ->withWaitUntil('future-wait-state')
            ->withNavigationTimeout(0)
            ->withDelay(0)
            ->withDevice('')
            ->withDeviceViewportWidth(0)
            ->withDeviceViewportHeight(0)
            ->withDeviceScaleFactor(0)
            ->withDeviceIsMobile(false)
            ->withDeviceHasTouch(false)
            ->withDeviceUserAgent('')
            ->withTimezone('')
            ->withMediaType('future-media')
            ->withColorScheme('future-scheme')
            ->withReducedMotion(false)
            ->withFullPage(false)
            ->withFullPagePreScroll(false)
            ->withFullPagePreScrollStep(0)
            ->withFullPagePreScrollStepDelay(0)
            ->withFullPageMaxHeight(0)
            ->withBlockCookieBanners(false)
            ->withBlockAds(false)
            ->withBlockChatWidgets(false)
            ->withHideSelectors(['.same', '.same', ''])
            ->withClickSelectors(['#first', '#first', ''])
            ->withClickAllSelectors(['.all', '.all', ''])
            ->withInjectCss(['', 'body { color: red; }'])
            ->withInjectJs(['', "document.title = 'x';"])
            ->withBypassCsp(false)
            ->withSelector('')
            ->withClipX(0)
            ->withClipY(0)
            ->withClipWidth(0)
            ->withClipHeight(0)
            ->withImageWidth(0)
            ->withImageHeight(0)
            ->withImageMode('future-image-mode')
            ->withImageAnchor('future-anchor')
            ->withImageAllowUpscale(false)
            ->withImageBackground('')
            ->withImageQuality(0)
            ->withPdfPaperFormat('future-paper')
            ->withPdfLandscape(false)
            ->withPdfPrintBackground(false)
            ->withPdfMargin('')
            ->withPdfMarginTop('')
            ->withPdfMarginRight('')
            ->withPdfMarginBottom('')
            ->withPdfMarginLeft('')
            ->withPdfScale(0)
            ->withPdfPageRanges('')
            ->withCache(false)
            ->withCacheTtl(0)
            ->withCacheKey('')
            ->withStorageMode('future-storage')
            ->withStorageEndpoint('')
            ->withStorageBucket('')
            ->withStorageRegion('')
            ->withStorageObjectKey('');

        $client->capture('', $options);

        self::assertCount(1, $transport->requests());
        $request = $transport->requests()[0];
        self::assertSame('POST', $request->getMethod());
        self::assertSame('https://api.screenshotscout.com/v1/capture', (string) $request->getUri());
        self::assertSame('Bearer access-key', $request->getHeaderLine('Authorization'));
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));
        self::assertSame([
            'url' => '',
            'format' => 'future-format',
            'response_type' => 'future-response',
            'country' => '',
            'proxy' => '',
            'geolocation_latitude' => 0,
            'geolocation_longitude' => 0,
            'geolocation_accuracy' => 0,
            'cookies' => ['session=a', 'session=a', ''],
            'headers' => ['X-Test:one', 'X-Test:one', ''],
            'timeout' => 0,
            'wait_until' => 'future-wait-state',
            'navigation_timeout' => 0,
            'delay' => 0,
            'device' => '',
            'device_viewport_width' => 0,
            'device_viewport_height' => 0,
            'device_scale_factor' => 0,
            'device_is_mobile' => false,
            'device_has_touch' => false,
            'device_user_agent' => '',
            'timezone' => '',
            'media_type' => 'future-media',
            'color_scheme' => 'future-scheme',
            'reduced_motion' => false,
            'full_page' => false,
            'full_page_pre_scroll' => false,
            'full_page_pre_scroll_step' => 0,
            'full_page_pre_scroll_step_delay' => 0,
            'full_page_max_height' => 0,
            'block_cookie_banners' => false,
            'block_ads' => false,
            'block_chat_widgets' => false,
            'hide_selectors' => ['.same', '.same', ''],
            'click_selectors' => ['#first', '#first', ''],
            'click_all_selectors' => ['.all', '.all', ''],
            'inject_css' => ['', 'body { color: red; }'],
            'inject_js' => ['', "document.title = 'x';"],
            'bypass_csp' => false,
            'selector' => '',
            'clip_x' => 0,
            'clip_y' => 0,
            'clip_width' => 0,
            'clip_height' => 0,
            'image_width' => 0,
            'image_height' => 0,
            'image_mode' => 'future-image-mode',
            'image_anchor' => 'future-anchor',
            'image_allow_upscale' => false,
            'image_background' => '',
            'image_quality' => 0,
            'pdf_paper_format' => 'future-paper',
            'pdf_landscape' => false,
            'pdf_print_background' => false,
            'pdf_margin' => '',
            'pdf_margin_top' => '',
            'pdf_margin_right' => '',
            'pdf_margin_bottom' => '',
            'pdf_margin_left' => '',
            'pdf_scale' => 0,
            'pdf_page_ranges' => '',
            'cache' => false,
            'cache_ttl' => 0,
            'cache_key' => '',
            'storage_mode' => 'future-storage',
            'storage_endpoint' => '',
            'storage_bucket' => '',
            'storage_region' => '',
            'storage_object_key' => '',
        ], self::decodeRequestBody($request));
    }

    public function testNullsAndEmptyRepeatedCollectionsAreOmittedWithoutDefaults(): void
    {
        $transport = new RecordingHttpClient();
        $options = (new CaptureOptions())
            ->withFormat(null)
            ->withCookies([])
            ->withHeaders([])
            ->withHideSelectors([])
            ->withClickSelectors([])
            ->withClickAllSelectors([])
            ->withInjectCss([])
            ->withInjectJs([]);

        $this->client($transport)->capture('https://example.com', $options);

        self::assertSame(['url' => 'https://example.com'], self::decodeRequestBody($transport->requests()[0]));
    }

    public function testGetAndCaptureUrlPreserveRepeatedValuesAndKeepExecutedCredentialsInBearerHeader(): void
    {
        $transport = new RecordingHttpClient();
        $client = $this->client($transport, 'query-key');
        $options = (new CaptureOptions())
            ->withCookies(['a=1', 'a=1'])
            ->withHeaders(['X-Test:one', 'X-Test:one'])
            ->withDelay(0)
            ->withFullPage(false)
            ->withCacheKey('');

        $client->capture('https://example.com/a path', $options, CaptureHttpMethod::GET);
        $built = $client->buildCaptureUrl('https://example.com/a path', $options);

        $request = $transport->requests()[0];
        self::assertSame('GET', $request->getMethod());
        self::assertSame('', (string) $request->getBody());
        self::assertSame('', $request->getHeaderLine('Content-Type'));
        self::assertSame('Bearer query-key', $request->getHeaderLine('Authorization'));
        self::assertSame(
            'url=https%3A%2F%2Fexample.com%2Fa%20path&cookies=a%3D1&cookies=a%3D1&headers=X-Test%3Aone&headers=X-Test%3Aone&delay=0&full_page=false&cache_key=',
            $request->getUri()->getQuery(),
        );
        self::assertStringNotContainsString('access_key=', (string) $request->getUri());
        self::assertSame(
            'https://api.screenshotscout.com/v1/capture?access_key=query-key&url=https%3A%2F%2Fexample.com%2Fa%20path&cookies=a%3D1&cookies=a%3D1&headers=X-Test%3Aone&headers=X-Test%3Aone&delay=0&full_page=false&cache_key=',
            $built,
        );
        self::assertCount(1, $transport->requests());
    }

    public function testGetAndGeneratedUrlSignaturesMatchTheApiAlgorithm(): void
    {
        $transport = new RecordingHttpClient(
            static fn(): Response => new Response(200, ['Content-Type' => 'application/json'], '{}'),
        );
        $client = $this->client($transport, 'ak_test', 'sk_test');
        $options = (new CaptureOptions())
            ->withResponseType(CaptureResponseType::JSON)
            ->withFullPage(false)
            ->withDelay(0)
            ->withHeaders(['X-Test:one', 'X-Test:two']);
        $target = 'https://example.com/a b?x=1&y=2';
        $expected = '6419f2ac8c0440f1e341b547630606c9dc74dacfbb2df8e122fc9ae26831d893';

        $client->capture($target, $options, CaptureHttpMethod::GET);
        $built = $client->buildCaptureUrl($target, $options);

        parse_str($transport->requests()[0]->getUri()->getQuery(), $getQuery);
        parse_str((string) parse_url($built, PHP_URL_QUERY), $builtQuery);
        self::assertSame($expected, $getQuery['signature']);
        self::assertSame($expected, $builtQuery['signature']);
        self::assertSame('ak_test', $builtQuery['access_key']);
        self::assertArrayNotHasKey('access_key', $getQuery);
        self::assertStringNotContainsString('sk_test', $built);
    }

    public function testAsterisksArePercentEncodedAndSignedRequestsMatchTheApiCanonicalization(): void
    {
        $transport = new RecordingHttpClient();
        $client = $this->client($transport, 'ak_test', 'sk_test');
        $options = (new CaptureOptions())
            ->withFormat('a*b')
            ->withHideSelectors(['div > *', '*'])
            ->withSelector('*');
        $target = 'https://example.com/*';
        $signature = '3630654706e17fa9a911b0cb9a2b06dc746012ec2015d5dfd955e52f018c7437';

        $client->capture($target, $options, CaptureHttpMethod::GET);
        $built = $client->buildCaptureUrl($target, $options);

        self::assertSame(
            'url=https%3A%2F%2Fexample.com%2F%2A&format=a%2Ab&hide_selectors=div%20%3E%20%2A&hide_selectors=%2A&selector=%2A&signature=' . $signature,
            $transport->requests()[0]->getUri()->getQuery(),
        );
        self::assertSame(
            'https://api.screenshotscout.com/v1/capture?access_key=ak_test&url=https%3A%2F%2Fexample.com%2F%2A&format=a%2Ab&hide_selectors=div%20%3E%20%2A&hide_selectors=%2A&selector=%2A&signature=' . $signature,
            $built,
        );
    }

    public function testFloatingPointOptionsUseEcmaScriptFormattingInQueriesAndJson(): void
    {
        $cases = [
            [0.0, '0'],
            [-0.0, '0'],
            [2.0, '2'],
            [1.25, '1.25'],
            [0.000001, '0.000001'],
            [0.0000001, '1e-7'],
            [10_000_000.0, '10000000'],
            [100_000_000_000_000_000_000.0, '100000000000000000000'],
            [1e21, '1e+21'],
            [-1.2e-7, '-1.2e-7'],
            [1_000_000_000_000_000_128.0, '1000000000000000100'],
            [5e-324, '5e-324'],
            [PHP_FLOAT_MAX, '1.7976931348623157e+308'],
        ];

        foreach ($cases as [$value, $expected]) {
            $transport = new RecordingHttpClient();
            $client = $this->client($transport);
            $options = (new CaptureOptions())->withPdfScale($value);
            $client->capture('https://example.com', $options, CaptureHttpMethod::GET);
            $client->capture('https://example.com', $options);

            parse_str($transport->requests()[0]->getUri()->getQuery(), $query);
            self::assertSame($expected, $query['pdf_scale']);
            self::assertSame(
                sprintf('{"url":"https://example.com","pdf_scale":%s}', $expected),
                (string) $transport->requests()[1]->getBody(),
            );
        }
    }

    public function testFloatingPointFormattingDoesNotDependOnHostSerializePrecision(): void
    {
        $previousPrecision = ini_get('serialize_precision');
        if (false === $previousPrecision) {
            self::fail('Expected serialize_precision to be readable.');
        }
        if (false === ini_set('serialize_precision', '17')) {
            self::fail('Expected serialize_precision to be configurable.');
        }

        try {
            $transport = new RecordingHttpClient();
            $client = $this->client($transport, 'ak_test', 'sk_test');
            $unsignedClient = $this->client($transport, 'ak_test');
            $options = (new CaptureOptions())->withPdfScale(0.1);
            $signature = '84a10fad5c4d35b023eea997a9425082a6a7d466586e2b7f9eae58524e687419';

            $client->capture('https://example.com', $options, CaptureHttpMethod::GET);
            $unsignedClient->capture('https://example.com', $options);
            $built = $client->buildCaptureUrl('https://example.com', $options);

            self::assertSame(
                'url=https%3A%2F%2Fexample.com&pdf_scale=0.1&signature=' . $signature,
                $transport->requests()[0]->getUri()->getQuery(),
            );
            self::assertSame(
                '{"url":"https://example.com","pdf_scale":0.1}',
                (string) $transport->requests()[1]->getBody(),
            );
            self::assertSame(
                'https://api.screenshotscout.com/v1/capture?access_key=ak_test&url=https%3A%2F%2Fexample.com&pdf_scale=0.1&signature=' . $signature,
                $built,
            );
            self::assertSame('17', ini_get('serialize_precision'));
        } finally {
            self::assertNotFalse(ini_set('serialize_precision', $previousPrecision));
        }
    }

    public function testClientWithASecretKeyRefusesAnExplicitPostWithoutCallingTheTransport(): void
    {
        $transport = new RecordingHttpClient();
        $client = $this->client($transport, 'ak_test', 'sk_test');

        $this->expectConfigurationException(
            static fn(): CaptureResponse => $client->capture('https://example.com', null, CaptureHttpMethod::POST),
            "POST requests can't be signed. This client has a secret key: send this capture with method GET, "
            . "or use a client without a secret key and an API key that doesn't require signed requests.",
        );

        self::assertCount(0, $transport->requests());
    }

    public function testClientWithASecretKeySendsASignedGetWhenNoMethodIsChosen(): void
    {
        $transport = new RecordingHttpClient(
            static fn(): Response => new Response(200, ['Content-Type' => 'application/json'], '{}'),
        );
        $client = $this->client($transport, 'ak_test', 'sk_test');
        $options = (new CaptureOptions())
            ->withResponseType(CaptureResponseType::JSON)
            ->withFullPage(false)
            ->withDelay(0)
            ->withHeaders(['X-Test:one', 'X-Test:two']);

        $client->capture('https://example.com/a b?x=1&y=2', $options);

        self::assertCount(1, $transport->requests());
        $request = $transport->requests()[0];
        self::assertSame('GET', $request->getMethod());
        self::assertSame('', (string) $request->getBody());
        self::assertSame('Bearer ak_test', $request->getHeaderLine('Authorization'));
        parse_str($request->getUri()->getQuery(), $query);
        self::assertSame('6419f2ac8c0440f1e341b547630606c9dc74dacfbb2df8e122fc9ae26831d893', $query['signature']);
    }

    public function testUnsafeStructuresAndValuesFailBeforeCallingTheTransport(): void
    {
        $transport = new RecordingHttpClient();
        $client = $this->client($transport);

        try {
            $client->capture('https://example.com', (new CaptureOptions())->withPdfScale(NAN));
            self::fail('Expected a non-finite number to fail.');
        } catch (SerializationException $exception) {
            self::assertSame('pdfScale', $exception->option());
        }

        try {
            $client->capture('https://example.com', (new CaptureOptions())->withFormat("\xFF"));
            self::fail('Expected invalid UTF-8 to fail.');
        } catch (SerializationException $exception) {
            self::assertSame('format', $exception->option());
        }

        $invalidRepeated = new CaptureOptions();
        $values = new \ReflectionProperty(CaptureOptions::class, 'values');
        $values->setValue($invalidRepeated, ['headers' => ['X-Test:one', 1]]);
        try {
            $client->capture('https://example.com', $invalidRepeated);
            self::fail('Expected a non-string repeated value to fail.');
        } catch (SerializationException $exception) {
            self::assertSame('headers', $exception->option());
        }

        self::assertCount(0, $transport->requests());
    }

    public function testCaptureOptionsAreImmutableAndPreserveCallerOrderAndDuplicates(): void
    {
        $base = new CaptureOptions();
        $cookies = ['a=1', 'a=1'];
        $configured = $base->withCookies($cookies)->withFullPage(false);
        $cookies[] = 'b=2';

        self::assertNull($base->cookies());
        self::assertNull($base->fullPage());
        self::assertSame(['a=1', 'a=1'], $configured->cookies());
        self::assertFalse($configured->fullPage());
    }

    /** @param callable(): mixed $operation */
    private function expectConfigurationException(callable $operation, ?string $message = null): void
    {
        try {
            $operation();
            self::fail('Expected client configuration to fail.');
        } catch (ConfigurationException $exception) {
            if (null !== $message) {
                self::assertSame($message, $exception->getMessage());
            }
        }
    }

    private function client(
        RecordingHttpClient $transport,
        string $accessKey = 'key',
        ?string $secretKey = null,
    ): Client {
        return new Client($accessKey, $secretKey, httpClient: $transport);
    }

    /** @return array<array-key, mixed> */
    private static function decodeRequestBody(\Psr\Http\Message\RequestInterface $request): array
    {
        $decoded = json_decode((string) $request->getBody(), false, 512, JSON_THROW_ON_ERROR);
        self::assertInstanceOf(\stdClass::class, $decoded);

        return get_object_vars($decoded);
    }
}
