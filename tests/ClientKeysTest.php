<?php

declare(strict_types=1);

namespace ScreenshotScout\Tests;

use GuzzleHttp\Psr7\Exception\MalformedUriException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use ScreenshotScout\CaptureHttpMethod;
use ScreenshotScout\CaptureOptions;
use ScreenshotScout\CaptureResponse;
use ScreenshotScout\CaptureResponseType;
use ScreenshotScout\Client;
use ScreenshotScout\Exception\ConfigurationException;
use ScreenshotScout\Exception\SerializationException;
use ScreenshotScout\Exception\TransportException;

final class ClientKeysTest extends TestCase
{
    private const VARIABLES = ['SCREENSHOTSCOUT_ACCESS_KEY', 'SCREENSHOTSCOUT_SECRET_KEY', 'SCREENSHOTSCOUT_BASE_URL'];

    /** @var array<string, array{bool, mixed, string|false}> */
    private array $savedVariables = [];

    /** Variables set in the developer's own environment must not reach these tests. */
    protected function setUp(): void
    {
        foreach (self::VARIABLES as $name) {
            $this->savedVariables[$name] = [array_key_exists($name, $_ENV), $_ENV[$name] ?? null, getenv($name)];
            unset($_ENV[$name]);
            putenv($name);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->savedVariables as $name => [$inSuperglobal, $superglobalValue, $processValue]) {
            if ($inSuperglobal) {
                $_ENV[$name] = $superglobalValue;
            } else {
                unset($_ENV[$name]);
            }
            putenv(false === $processValue ? $name : $name . '=' . $processValue);
        }
    }

    public function testWithoutKeysTheClientReadsBothVariablesOnce(): void
    {
        putenv('SCREENSHOTSCOUT_ACCESS_KEY=ak_test');
        putenv('SCREENSHOTSCOUT_SECRET_KEY=sk_test');
        $transport = new RecordingHttpClient(
            static fn(): Response => new Response(200, ['Content-Type' => 'application/json'], '{}'),
        );
        $client = new Client(httpClient: $transport);
        putenv('SCREENSHOTSCOUT_ACCESS_KEY=ak_changed');
        putenv('SCREENSHOTSCOUT_SECRET_KEY=sk_changed');

        $client->capture(
            'https://example.com/a b?x=1&y=2',
            (new CaptureOptions())
                ->withResponseType(CaptureResponseType::JSON)
                ->withFullPage(false)
                ->withDelay(0)
                ->withHeaders(['X-Test:one', 'X-Test:two']),
        );

        $request = $transport->requests()[0];
        self::assertSame('GET', $request->getMethod());
        self::assertSame('Bearer ak_test', $request->getHeaderLine('Authorization'));
        parse_str($request->getUri()->getQuery(), $query);
        self::assertSame('6419f2ac8c0440f1e341b547630606c9dc74dacfbb2df8e122fc9ae26831d893', $query['signature']);
    }

    public function testAClientWithNoArgumentsReadsTheEnvironment(): void
    {
        putenv('SCREENSHOTSCOUT_ACCESS_KEY=ak_env');

        self::assertSame(
            'https://api.screenshotscout.com/v1/capture?access_key=ak_env&url=https%3A%2F%2Fexample.com',
            (new Client())->buildCaptureUrl('https://example.com'),
        );
    }

    public function testAStringInTheEnvSuperglobalComesBeforeGetenv(): void
    {
        putenv('SCREENSHOTSCOUT_ACCESS_KEY=ak_process');
        self::assertSame('ak_process', self::accessKeyOfANewClient());

        $_ENV['SCREENSHOTSCOUT_ACCESS_KEY'] = 'ak_superglobal';
        self::assertSame('ak_superglobal', self::accessKeyOfANewClient());

        $_ENV['SCREENSHOTSCOUT_ACCESS_KEY'] = 1;
        self::assertSame('ak_process', self::accessKeyOfANewClient());

        $_ENV['SCREENSHOTSCOUT_ACCESS_KEY'] = '0';
        self::assertSame('0', self::accessKeyOfANewClient());
    }

    public function testAnEmptySecretVariableMeansNoSecretKey(): void
    {
        putenv('SCREENSHOTSCOUT_ACCESS_KEY=ak_env');
        putenv('SCREENSHOTSCOUT_SECRET_KEY=');
        $transport = new RecordingHttpClient();

        (new Client(httpClient: $transport))->capture('https://example.com');

        self::assertSame('POST', $transport->requests()[0]->getMethod());
    }

    public function testMissingOrBlankVariablesAreErrorsThatNameThem(): void
    {
        $missing = 'A non-blank Screenshot Scout access key is required. Pass one, or set SCREENSHOTSCOUT_ACCESS_KEY.';
        $this->expectConfigurationException(static fn(): Client => new Client(), $missing);

        putenv('SCREENSHOTSCOUT_SECRET_KEY=sk_env');
        $this->expectConfigurationException(static fn(): Client => new Client(), $missing);

        putenv('SCREENSHOTSCOUT_ACCESS_KEY= ');
        $this->expectConfigurationException(
            static fn(): Client => new Client(),
            'SCREENSHOTSCOUT_ACCESS_KEY is set but blank.',
        );
    }

    public function testPassingEitherKeyMeansTheEnvironmentIsNeverRead(): void
    {
        putenv('SCREENSHOTSCOUT_ACCESS_KEY=ak_env');
        putenv('SCREENSHOTSCOUT_SECRET_KEY=sk_env');
        $transport = new RecordingHttpClient();

        (new Client('ak_code', httpClient: $transport))->capture('https://example.com');

        $request = $transport->requests()[0];
        self::assertSame('POST', $request->getMethod());
        self::assertSame('Bearer ak_code', $request->getHeaderLine('Authorization'));
        $this->expectConfigurationException(
            static fn(): Client => new Client(secretKey: 'sk_code'),
            'A non-blank Screenshot Scout access key is required.',
        );
    }

    public function testAnExplicitPostNamesTheVariableTheSecretKeyCameFrom(): void
    {
        putenv('SCREENSHOTSCOUT_ACCESS_KEY=ak_env');
        putenv('SCREENSHOTSCOUT_SECRET_KEY=sk_env');
        $transport = new RecordingHttpClient();
        $client = new Client(httpClient: $transport);

        $this->expectConfigurationException(
            static fn(): CaptureResponse => $client->capture('https://example.com', null, CaptureHttpMethod::POST),
            "POST requests can't be signed. This client has a secret key from SCREENSHOTSCOUT_SECRET_KEY: send this "
            . "capture with method GET, or use a client without a secret key and an API key that doesn't require "
            . 'signed requests.',
        );

        self::assertCount(0, $transport->requests());
    }

    public function testDebugOutputDoesNotRevealTheKeys(): void
    {
        putenv('SCREENSHOTSCOUT_ACCESS_KEY=ak_from_environment');
        putenv('SCREENSHOTSCOUT_SECRET_KEY=sk_from_environment');
        putenv('SCREENSHOTSCOUT_BASE_URL=http://localhost:8787');
        $clients = [
            new Client(
                'ak_passed_in_code',
                'sk_passed_in_code',
                new RecordingHttpClient(),
                'https://proxy.example.com',
            ),
            new Client(httpClient: new RecordingHttpClient()),
        ];

        ob_start();
        var_dump($clients);
        $output = ob_get_clean() . print_r($clients, true) . var_export($clients, true);

        self::assertStringContainsString('SensitiveParameterValue', $output);
        foreach (['ak_passed_in_code', 'sk_passed_in_code', 'ak_from_environment', 'sk_from_environment'] as $key) {
            self::assertStringNotContainsString($key, $output);
        }
    }

    public function testStackTracesDoNotRevealTheKeysPassedToTheConstructor(): void
    {
        $previous = ini_set('zend.exception_ignore_args', '0');
        try {
            new Client('ak_passed_in_code', '   ');
            self::fail('Expected a blank secret key to fail.');
        } catch (ConfigurationException $exception) {
            $arguments = $exception->getTrace()[0]['args'] ?? [];
            self::assertInstanceOf(\SensitiveParameterValue::class, $arguments[0] ?? null);
            self::assertInstanceOf(\SensitiveParameterValue::class, $arguments[1] ?? null);
        } finally {
            if (false !== $previous) {
                ini_set('zend.exception_ignore_args', $previous);
            }
        }
    }

    public function testABaseUrlSendsPostGetAndBuiltUrlsToThatServer(): void
    {
        $transport = new RecordingHttpClient();
        $client = new Client('ak_test', httpClient: $transport, baseUrl: 'http://localhost:8787/');

        $client->capture('https://example.com');
        $client->capture('https://example.com', null, CaptureHttpMethod::GET);

        $post = $transport->requests()[0];
        self::assertSame('POST', $post->getMethod());
        self::assertSame('http://localhost:8787/v1/capture', (string) $post->getUri());
        $get = $transport->requests()[1];
        self::assertSame('GET', $get->getMethod());
        self::assertSame('http://localhost:8787/v1/capture?url=https%3A%2F%2Fexample.com', (string) $get->getUri());
        self::assertSame(
            'http://localhost:8787/v1/capture?access_key=ak_test&url=https%3A%2F%2Fexample.com',
            $client->buildCaptureUrl('https://example.com'),
        );
    }

    public function testABaseUrlKeepsItsPathAndLeavesSigningUnchanged(): void
    {
        $transport = new RecordingHttpClient(
            static fn(): Response => new Response(200, ['Content-Type' => 'application/json'], '{}'),
        );
        $client = new Client('ak_test', 'sk_test', $transport, 'https://proxy.example.com/screenshotscout//');
        $options = (new CaptureOptions())
            ->withResponseType(CaptureResponseType::JSON)
            ->withFullPage(false)
            ->withDelay(0)
            ->withHeaders(['X-Test:one', 'X-Test:two']);
        $target = 'https://example.com/a b?x=1&y=2';

        $client->capture($target, $options);
        $built = $client->buildCaptureUrl($target, $options);

        $request = $transport->requests()[0];
        self::assertSame('GET', $request->getMethod());
        foreach ([(string) $request->getUri(), $built] as $url) {
            self::assertStringStartsWith('https://proxy.example.com/screenshotscout/v1/capture?', $url);
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            self::assertSame('6419f2ac8c0440f1e341b547630606c9dc74dacfbb2df8e122fc9ae26831d893', $query['signature']);
        }
    }

    public function testAnEmptyBaseUrlMeansTheScreenshotScoutApi(): void
    {
        foreach (['', null] as $baseUrl) {
            $transport = new RecordingHttpClient();

            (new Client('key', httpClient: $transport, baseUrl: $baseUrl))->capture('https://example.com');

            self::assertSame(
                'https://api.screenshotscout.com/v1/capture',
                (string) $transport->requests()[0]->getUri(),
            );
        }
    }

    public function testWithoutABaseUrlTheClientReadsTheVariableOnceEvenWithKeysPassed(): void
    {
        putenv('SCREENSHOTSCOUT_ACCESS_KEY=ak_env');
        putenv('SCREENSHOTSCOUT_BASE_URL=http://localhost:8787');
        $transport = new RecordingHttpClient();
        $client = new Client('ak_code', httpClient: $transport);
        $clientWithKeysFromEnvironment = new Client();
        putenv('SCREENSHOTSCOUT_BASE_URL=http://changed.example');

        $client->capture('https://example.com');

        self::assertSame('http://localhost:8787/v1/capture', (string) $transport->requests()[0]->getUri());
        self::assertSame(
            'http://localhost:8787/v1/capture?access_key=ak_code&url=https%3A%2F%2Fexample.com',
            $client->buildCaptureUrl('https://example.com'),
        );
        self::assertSame(
            'http://localhost:8787/v1/capture?access_key=ak_env&url=https%3A%2F%2Fexample.com',
            $clientWithKeysFromEnvironment->buildCaptureUrl('https://example.com'),
        );
    }

    public function testABaseUrlInTheEnvSuperglobalComesBeforeGetenv(): void
    {
        putenv('SCREENSHOTSCOUT_BASE_URL=http://process.example');
        $_ENV['SCREENSHOTSCOUT_BASE_URL'] = 'http://superglobal.example';

        self::assertSame(
            'http://superglobal.example/v1/capture?access_key=key&url=https%3A%2F%2Fexample.com',
            (new Client('key'))->buildCaptureUrl('https://example.com'),
        );
    }

    public function testABaseUrlPassedInCodeWinsOverTheVariable(): void
    {
        putenv('SCREENSHOTSCOUT_BASE_URL=http://localhost:8787');
        $transport = new RecordingHttpClient();
        $client = new Client('key', httpClient: $transport, baseUrl: 'https://proxy.example.com');

        $client->capture('https://example.com');

        self::assertSame('https://proxy.example.com/v1/capture', (string) $transport->requests()[0]->getUri());
    }

    public function testAnEmptyVariableMeansTheScreenshotScoutApiAndABlankOneIsAnError(): void
    {
        putenv('SCREENSHOTSCOUT_BASE_URL=');
        $transport = new RecordingHttpClient();

        (new Client('key', httpClient: $transport))->capture('https://example.com');

        self::assertSame('https://api.screenshotscout.com/v1/capture', (string) $transport->requests()[0]->getUri());

        putenv("SCREENSHOTSCOUT_BASE_URL= \t");
        $this->expectConfigurationException(
            static fn(): Client => new Client('key'),
            'SCREENSHOTSCOUT_BASE_URL is set but blank.',
        );
    }

    public function testKeyErrorsComeBeforeABlankBaseUrlVariable(): void
    {
        putenv('SCREENSHOTSCOUT_BASE_URL= ');

        $this->expectConfigurationException(
            static fn(): Client => new Client(),
            'A non-blank Screenshot Scout access key is required. Pass one, or set SCREENSHOTSCOUT_ACCESS_KEY.',
        );
        $this->expectConfigurationException(
            static fn(): Client => new Client('key', '   '),
            'The Screenshot Scout secret key must be a non-blank string when provided.',
        );
    }

    public function testATransportFailureNamesTheVariableOnlyWhenTheBaseUrlCameFromIt(): void
    {
        $nativeCause = new NativeClientException('connection refused');
        $transport = new RecordingHttpClient(static function () use ($nativeCause): ResponseInterface {
            throw $nativeCause;
        });
        $message = 'Screenshot Scout request failed before a complete HTTP response body was received.';
        $messageNamingTheVariable = $message . ' The base URL comes from SCREENSHOTSCOUT_BASE_URL.';
        $clientWithDefaultBaseUrl = new Client('key', httpClient: $transport);
        putenv('SCREENSHOTSCOUT_BASE_URL=http://localhost:8787');
        $cases = [
            [new Client('key', httpClient: $transport), $messageNamingTheVariable],
            [new Client('key', httpClient: $transport, baseUrl: 'http://localhost:8787'), $message],
            [$clientWithDefaultBaseUrl, $message],
        ];

        foreach ($cases as [$client, $expectedMessage]) {
            try {
                $client->capture('https://example.com');
                self::fail('Expected a transport exception.');
            } catch (TransportException $exception) {
                self::assertSame($expectedMessage, $exception->getMessage());
                self::assertSame($nativeCause, $exception->getPrevious());
            }
        }
    }

    public function testABaseUrlThatCantBeParsedFailsBeforeSendingAndBuiltUrlsUseItAsGiven(): void
    {
        $transport = new RecordingHttpClient();
        $message = 'The Screenshot Scout HTTP request could not be constructed.';
        $messageNamingTheVariable = $message . ' The base URL comes from SCREENSHOTSCOUT_BASE_URL.';
        $clientWithBaseUrlPassed = new Client('key', httpClient: $transport, baseUrl: 'http://:80');
        putenv('SCREENSHOTSCOUT_BASE_URL=http://:80');
        $cases = [
            [$clientWithBaseUrlPassed, $message],
            [new Client('key', httpClient: $transport), $messageNamingTheVariable],
        ];

        foreach ($cases as [$client, $expectedMessage]) {
            try {
                $client->capture('https://example.com');
                self::fail('Expected building the request to fail.');
            } catch (SerializationException $exception) {
                self::assertSame($expectedMessage, $exception->getMessage());
                self::assertInstanceOf(MalformedUriException::class, $exception->getPrevious());
            }
        }
        self::assertCount(0, $transport->requests());
        self::assertSame(
            'http://:80/v1/capture?access_key=key&url=https%3A%2F%2Fexample.com',
            $clientWithBaseUrlPassed->buildCaptureUrl('https://example.com'),
        );
    }

    private static function accessKeyOfANewClient(): mixed
    {
        $url = (new Client())->buildCaptureUrl('https://example.com');
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return $query['access_key'] ?? null;
    }

    /** @param callable(): mixed $operation */
    private function expectConfigurationException(callable $operation, string $message): void
    {
        try {
            $operation();
            self::fail('Expected client configuration to fail.');
        } catch (ConfigurationException $exception) {
            self::assertSame($message, $exception->getMessage());
        }
    }
}
