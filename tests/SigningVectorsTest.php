<?php

declare(strict_types=1);

namespace ScreenshotScout\Tests;

use PHPUnit\Framework\TestCase;
use ScreenshotScout\Internal\CaptureSerializer;
use ScreenshotScout\Internal\WirePair;

final class SigningVectorsTest extends TestCase
{
    public function testSignedTextAndSignatureMatchTheSharedSigningVectors(): void
    {
        // The API gateway and every SDK test against copies of the same file.
        $json = file_get_contents(__DIR__ . '/fixtures/signing-vectors.json');
        self::assertIsString($json);
        /**
         * @var array{vectors: list<array{
         *     name: string,
         *     access_key: string,
         *     secret_key: string,
         *     params: list<array{string, string}>,
         *     signed_text: string,
         *     signature: string,
         * }>} $fixture
         */
        $fixture = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        self::assertNotEmpty($fixture['vectors']);

        foreach ($fixture['vectors'] as $vector) {
            $pairs = [];
            foreach ($vector['params'] as [$name, $value]) {
                $pairs[] = new WirePair($name, $value);
            }

            $signedText = CaptureSerializer::buildCanonicalQuery($pairs, $vector['access_key']);

            self::assertSame($vector['signed_text'], $signedText, $vector['name']);
            self::assertSame(
                $vector['signature'],
                hash_hmac('sha256', $signedText, $vector['secret_key']),
                $vector['name'],
            );
        }
    }
}
