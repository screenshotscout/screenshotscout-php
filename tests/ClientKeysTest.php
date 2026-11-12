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

final class ClientKeysTest extends TestCase
{
    private const VARIABLES = ['SCREENSHOTSCOUT_ACCESS_KEY', 'SCREENSHOTSCOUT_SECRET_KEY'];

    /** @var array<string, array{bool, mixed, string|false}> */
    private array $savedVariables = [];

    /** Keys set in the developer's own environment must not reach these tests. */
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
        $clients = [
            new Client('ak_passed_in_code', 'sk_passed_in_code', new RecordingHttpClient()),
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
