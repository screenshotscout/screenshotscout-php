<?php

declare(strict_types=1);

namespace ScreenshotScout\Tests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use ScreenshotScout\BinaryCaptureResponse;
use ScreenshotScout\CaptureOptions;
use ScreenshotScout\CaptureResponseType;
use ScreenshotScout\Client;
use ScreenshotScout\Exception\ApiException;
use ScreenshotScout\Exception\ResponseDecodingException;
use ScreenshotScout\Exception\TransportException;
use ScreenshotScout\JsonCaptureResponse;

final class ResponseAndFailureTest extends TestCase
{
    public function testDefaultBinaryResponseRetainsBytesMetadataAndRawResponse(): void
    {
        $body = "\x00\x01\x02\xFF";
        $transport = $this->respondingWith(new Response(201, [
            'Content-Type' => 'image/png',
            'Screenshot-Scout-Cache-Status' => 'HIT',
            'Screenshot-Scout-Screenshot-URL' => 'https://cdn.example/screenshot.png',
            'Screenshot-Scout-Screenshot-URL-Expires-At' => '2026-07-14T00:00:00Z',
            'Set-Cookie' => ['a=1; Path=/', 'b=2; Path=/'],
        ], $body, '1.1', 'Created'));

        $response = $this->client($transport)->capture('https://example.com');

        self::assertInstanceOf(BinaryCaptureResponse::class, $response);
        self::assertSame($body, $response->bytes());
        self::assertSame('https://cdn.example/screenshot.png', $response->screenshotUrl());
        self::assertSame('2026-07-14T00:00:00Z', $response->screenshotUrlExpiresAt());
        self::assertSame('HIT', $response->cacheStatus());
        self::assertSame(201, $response->rawResponse()->statusCode());
        self::assertSame('Created', $response->rawResponse()->reasonPhrase());
        self::assertSame('image/png', $response->rawResponse()->contentType());
        self::assertSame($body, $response->rawResponse()->body());
        self::assertSame(
            ['a=1; Path=/', 'b=2; Path=/'],
            $response->rawResponse()->header('set-cookie'),
        );
    }

    public function testJsonResponseRetainsDocumentedAndAdditionalFields(): void
    {
        $body = json_encode([
            'screenshot_url' => 'https://cdn.example/screenshot.png',
            'cache_status' => 'miss',
            'request_id' => 'request-123',
            'future' => ['nested' => true],
        ], JSON_THROW_ON_ERROR);
        $transport = $this->respondingWith(new Response(
            200,
            ['Content-Type' => 'application/vnd.screenshotscout+json; charset=utf-8'],
            $body,
        ));

        $response = $this->client($transport)->capture(
            'https://example.com',
            (new CaptureOptions())->withResponseType(CaptureResponseType::JSON),
        );

        self::assertInstanceOf(JsonCaptureResponse::class, $response);
        self::assertSame('https://cdn.example/screenshot.png', $response->result()->screenshotUrl());
        self::assertNull($response->result()->screenshotUrlExpiresAt());
        self::assertSame('miss', $response->result()->cacheStatus());
        self::assertSame([
            'request_id' => 'request-123',
            'future' => ['nested' => true],
        ], $response->result()->additionalFields());
        self::assertSame($body, $response->rawResponse()->body());
    }

    public function testKnownResponseTypesRejectSuccessfulResponsesWithTheWrongMediaType(): void
    {
        $jsonBody = '{"screenshot_url":"https://cdn.example/a.png"}';
        $jsonTransport = $this->respondingWith(new Response(
            200,
            ['Content-Type' => 'application/json'],
            $jsonBody,
        ));
        try {
            $this->client($jsonTransport)->capture('https://example.com');
            self::fail('Expected the binary request to reject a JSON response.');
        } catch (ResponseDecodingException $exception) {
            self::assertStringContainsString('json response when binary was requested', $exception->getMessage());
            self::assertSame($jsonBody, $exception->rawResponse()->body());
        }

        $binaryBody = "\x01\x02\x03";
        $binaryTransport = $this->respondingWith(new Response(
            200,
            ['Content-Type' => 'image/png'],
            $binaryBody,
        ));
        try {
            $this->client($binaryTransport)->capture(
                'https://example.com',
                (new CaptureOptions())->withResponseType(CaptureResponseType::JSON),
            );
            self::fail('Expected the JSON request to reject a binary response.');
        } catch (ResponseDecodingException $exception) {
            self::assertStringContainsString('binary response when json was requested', $exception->getMessage());
            self::assertSame($binaryBody, $exception->rawResponse()->body());
        }
    }

    public function testOpenResponseTypeUsesTheActualMediaType(): void
    {
        $transport = $this->respondingWith(new Response(
            200,
            ['Content-Type' => 'application/json'],
            '{"screenshot_url":"https://cdn.example/a.png"}',
        ));

        $response = $this->client($transport)->capture(
            'https://example.com',
            (new CaptureOptions())->withResponseType('future-response'),
        );

        self::assertInstanceOf(JsonCaptureResponse::class, $response);
        self::assertSame('https://cdn.example/a.png', $response->result()->screenshotUrl());
    }

    public function testMalformedSuccessfulJsonIsADecodingFailureWithRawAccess(): void
    {
        foreach (['{', '[]', '{"screenshot_url":123}', '{"screenshot_url":null}'] as $body) {
            $transport = $this->respondingWith(new Response(
                200,
                ['Content-Type' => 'application/json'],
                $body,
            ));
            try {
                $this->client($transport)->capture(
                    'https://example.com',
                    (new CaptureOptions())->withResponseType(CaptureResponseType::JSON),
                );
                self::fail('Expected malformed JSON response data to fail.');
            } catch (ResponseDecodingException $exception) {
                self::assertSame(200, $exception->rawResponse()->statusCode());
                self::assertSame($body, $exception->rawResponse()->body());
            }
        }
    }

    public function testApiFailuresRetainParsedFieldsAdditionalFieldsAndRawResponse(): void
    {
        $errorBody = [
            'error_code' => 'invalid_options',
            'error_message' => 'One or more options are invalid.',
            'errors' => [['option' => 'format', 'message' => 'Unsupported.']],
            'request_id' => 'request-456',
        ];
        $encoded = json_encode($errorBody, JSON_THROW_ON_ERROR);
        $transport = $this->respondingWith(new Response(400, [
            'Content-Type' => 'application/json; charset=utf-8',
            'X-Request-Id' => 'request-456',
        ], $encoded, '1.1', 'Bad Request'));

        try {
            $this->client($transport)->capture('not-semantically-validated');
            self::fail('Expected an API exception.');
        } catch (ApiException $exception) {
            self::assertSame(400, $exception->statusCode());
            self::assertSame('invalid_options', $exception->errorCode());
            self::assertSame('One or more options are invalid.', $exception->errorMessage());
            self::assertSame($errorBody['errors'], $exception->errors());
            self::assertSame($errorBody, $exception->responseBody());
            self::assertTrue($exception->hasResponseBody());
            self::assertSame('Bad Request', $exception->rawResponse()->reasonPhrase());
            self::assertSame(['request-456'], $exception->rawResponse()->header('x-request-id'));
            self::assertSame($encoded, $exception->rawResponse()->body());
        }
    }

    public function testValidJsonNullApiBodyRemainsDistinguishableFromInvalidJson(): void
    {
        $nullTransport = $this->respondingWith(new Response(
            500,
            ['Content-Type' => 'application/json'],
            'null',
        ));
        try {
            $this->client($nullTransport)->capture('https://example.com');
            self::fail('Expected an API exception.');
        } catch (ApiException $exception) {
            self::assertTrue($exception->hasResponseBody());
            self::assertNull($exception->responseBody());
        }

        $textTransport = $this->respondingWith(new Response(500, [], 'not json'));
        try {
            $this->client($textTransport)->capture('https://example.com');
            self::fail('Expected an API exception.');
        } catch (ApiException $exception) {
            self::assertFalse($exception->hasResponseBody());
            self::assertNull($exception->responseBody());
        }
    }

    public function testRedirectApiFailureIsReturnedRawWithoutRetrying(): void
    {
        $transport = $this->respondingWith(new Response(
            302,
            ['Location' => 'https://other.example/v1/capture'],
            'redirect',
        ));

        try {
            $this->client($transport)->capture('https://example.com');
            self::fail('Expected a redirect response to be an API exception.');
        } catch (ApiException $exception) {
            self::assertSame(302, $exception->statusCode());
            self::assertNull($exception->errorCode());
            self::assertFalse($exception->hasResponseBody());
            self::assertSame('redirect', $exception->rawResponse()->body());
        }
        self::assertCount(1, $transport->requests());
    }

    public function testTransportFailuresRetainTheirNativeCauseAndAreNotRetried(): void
    {
        $nativeCause = new NativeClientException('socket closed');
        $transport = new RecordingHttpClient(static function () use ($nativeCause): ResponseInterface {
            throw $nativeCause;
        });

        try {
            $this->client($transport)->capture('https://example.com');
            self::fail('Expected a transport exception.');
        } catch (TransportException $exception) {
            self::assertSame($nativeCause, $exception->getPrevious());
        }
        self::assertCount(1, $transport->requests());
    }

    private function respondingWith(ResponseInterface $response): RecordingHttpClient
    {
        return new RecordingHttpClient(static fn(): ResponseInterface => $response);
    }

    private function client(RecordingHttpClient $transport): Client
    {
        return new Client('key', httpClient: $transport);
    }
}
