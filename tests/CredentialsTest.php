<?php

declare(strict_types=1);

namespace ScreenshotScout\Tests;

use PHPUnit\Framework\TestCase;
use ScreenshotScout\Exception\ConfigurationException;
use ScreenshotScout\Internal\Credentials;

final class CredentialsTest extends TestCase
{
    public function testWithNoKeysPassedEachVariableIsReadOnce(): void
    {
        $read = [];
        $lookup = static function (string $name) use (&$read): ?string {
            $read[] = $name;

            return ['SCREENSHOTSCOUT_ACCESS_KEY' => 'ak', 'SCREENSHOTSCOUT_SECRET_KEY' => 'sk'][$name] ?? null;
        };

        self::assertSame(
            ['accessKey' => 'ak', 'secretKey' => 'sk', 'fromEnvironment' => true],
            get_object_vars(Credentials::resolve(null, null, $lookup)),
        );
        self::assertSame(['SCREENSHOTSCOUT_ACCESS_KEY', 'SCREENSHOTSCOUT_SECRET_KEY'], $read);
    }

    public function testAnUnsetOrEmptySecretVariableMeansNoSecretKey(): void
    {
        $cases = [
            ['SCREENSHOTSCOUT_ACCESS_KEY' => 'ak'],
            ['SCREENSHOTSCOUT_ACCESS_KEY' => 'ak', 'SCREENSHOTSCOUT_SECRET_KEY' => ''],
        ];

        foreach ($cases as $variables) {
            self::assertSame(
                ['accessKey' => 'ak', 'secretKey' => null, 'fromEnvironment' => true],
                get_object_vars(Credentials::resolve(null, null, self::lookup($variables))),
            );
        }
    }

    public function testValuesThatPhpTreatsAsFalseAreKeys(): void
    {
        $lookup = self::lookup(['SCREENSHOTSCOUT_ACCESS_KEY' => '0', 'SCREENSHOTSCOUT_SECRET_KEY' => '0']);

        self::assertSame(
            ['accessKey' => '0', 'secretKey' => '0', 'fromEnvironment' => true],
            get_object_vars(Credentials::resolve(null, null, $lookup)),
        );
    }

    public function testAMissingOrEmptyAccessKeyVariableIsAnErrorThatNamesIt(): void
    {
        $cases = [
            [],
            ['SCREENSHOTSCOUT_ACCESS_KEY' => ''],
            ['SCREENSHOTSCOUT_SECRET_KEY' => 'sk'],
        ];

        foreach ($cases as $variables) {
            $this->expectResolveToFail(
                $variables,
                'A non-blank Screenshot Scout access key is required. Pass one, or set SCREENSHOTSCOUT_ACCESS_KEY.',
            );
        }
    }

    public function testAWhitespaceOnlyVariableIsAnErrorThatNamesIt(): void
    {
        $this->expectResolveToFail(
            ['SCREENSHOTSCOUT_ACCESS_KEY' => '  '],
            'SCREENSHOTSCOUT_ACCESS_KEY is set but blank.',
        );
        $this->expectResolveToFail(
            ['SCREENSHOTSCOUT_ACCESS_KEY' => 'ak', 'SCREENSHOTSCOUT_SECRET_KEY' => " \t"],
            'SCREENSHOTSCOUT_SECRET_KEY is set but blank.',
        );
    }

    public function testPassingEitherKeyMeansNoVariableIsRead(): void
    {
        $read = [];
        $lookup = static function (string $name) use (&$read): string {
            $read[] = $name;

            return 'from-environment';
        };

        self::assertSame(
            ['accessKey' => 'ak', 'secretKey' => null, 'fromEnvironment' => false],
            get_object_vars(Credentials::resolve('ak', null, $lookup)),
        );
        self::assertSame(
            ['accessKey' => null, 'secretKey' => 'sk', 'fromEnvironment' => false],
            get_object_vars(Credentials::resolve(null, 'sk', $lookup)),
        );
        self::assertSame(
            ['accessKey' => '', 'secretKey' => null, 'fromEnvironment' => false],
            get_object_vars(Credentials::resolve('', null, $lookup)),
        );
        self::assertSame([], $read);
    }

    public function testABaseUrlIsUsedAsGivenWithoutTrailingSlashesAndNoVariableIsRead(): void
    {
        $cases = [
            ['http://localhost:8787', 'http://localhost:8787/v1/capture'],
            ['http://localhost:8787/', 'http://localhost:8787/v1/capture'],
            ['https://proxy.example.com/screenshotscout//', 'https://proxy.example.com/screenshotscout/v1/capture'],
            ['https://proxy.example.com/a%20b', 'https://proxy.example.com/a%20b/v1/capture'],
            ['not a url', 'not a url/v1/capture'],
        ];

        foreach ($cases as [$baseUrl, $url]) {
            $read = [];
            $lookup = static function (string $name) use (&$read): string {
                $read[] = $name;

                return 'http://env.example';
            };

            self::assertSame(
                ['url' => $url, 'fromEnvironment' => false],
                get_object_vars(Credentials::resolveCaptureEndpoint($baseUrl, $lookup)),
            );
            self::assertSame([], $read);
        }
    }

    public function testWithoutABaseUrlTheBaseUrlVariableIsRead(): void
    {
        foreach ([null, ''] as $baseUrl) {
            $read = [];
            $lookup = static function (string $name) use (&$read): string {
                $read[] = $name;

                return 'http://localhost:8787/';
            };

            self::assertSame(
                ['url' => 'http://localhost:8787/v1/capture', 'fromEnvironment' => true],
                get_object_vars(Credentials::resolveCaptureEndpoint($baseUrl, $lookup)),
            );
            self::assertSame(['SCREENSHOTSCOUT_BASE_URL'], $read);
        }
    }

    public function testAnUnsetOrEmptyBaseUrlVariableMeansTheScreenshotScoutApi(): void
    {
        foreach ([[], ['SCREENSHOTSCOUT_BASE_URL' => '']] as $variables) {
            self::assertSame(
                ['url' => 'https://api.screenshotscout.com/v1/capture', 'fromEnvironment' => false],
                get_object_vars(Credentials::resolveCaptureEndpoint(null, self::lookup($variables))),
            );
        }
    }

    public function testAWhitespaceOnlyBaseUrlVariableIsAnErrorThatNamesIt(): void
    {
        try {
            Credentials::resolveCaptureEndpoint(null, self::lookup(['SCREENSHOTSCOUT_BASE_URL' => " \t"]));
            self::fail('Expected resolving the base URL to fail.');
        } catch (ConfigurationException $exception) {
            self::assertSame('SCREENSHOTSCOUT_BASE_URL is set but blank.', $exception->getMessage());
        }
    }

    /**
     * @param array<string, string> $variables
     *
     * @return \Closure(string): ?string
     */
    private static function lookup(array $variables): \Closure
    {
        return static fn(string $name): ?string => $variables[$name] ?? null;
    }

    /** @param array<string, string> $variables */
    private function expectResolveToFail(array $variables, string $message): void
    {
        try {
            Credentials::resolve(null, null, self::lookup($variables));
            self::fail('Expected resolving the keys to fail.');
        } catch (ConfigurationException $exception) {
            self::assertSame($message, $exception->getMessage());
        }
    }
}
