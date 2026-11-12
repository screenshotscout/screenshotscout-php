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
