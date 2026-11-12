<?php

declare(strict_types=1);

namespace ScreenshotScout;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ScreenshotScout\Exception\ApiException;
use ScreenshotScout\Exception\ConfigurationException;
use ScreenshotScout\Exception\ResponseDecodingException;
use ScreenshotScout\Exception\ScreenshotScoutException;
use ScreenshotScout\Exception\SerializationException;
use ScreenshotScout\Exception\TransportException;
use ScreenshotScout\Internal\CaptureSerializer;
use ScreenshotScout\Internal\Credentials;
use ScreenshotScout\Internal\DefaultBinaryCaptureResponse;
use ScreenshotScout\Internal\DefaultJsonCaptureResponse;
use ScreenshotScout\Internal\Environment;
use ScreenshotScout\Internal\JsonCodec;
use ScreenshotScout\Internal\SerializedCaptureOptions;
use ScreenshotScout\Internal\WirePair;

/** Reusable blocking Screenshot Scout client with an optional custom PSR-18 transport. */
final class Client
{
    private const CAPTURE_ENDPOINT = 'https://api.screenshotscout.com/v1/capture';
    private const ACCESS_KEY_PATTERN = '#^[A-Za-z0-9\-._~+/]+=*$#D';
    /** Longer than the longest capture Screenshot Scout allows (240 seconds). */
    private const STREAM_TIMEOUT_SECONDS = 300;

    /** The keys are kept as \SensitiveParameterValue, which hides them from var_dump() and similar output. */
    private readonly \SensitiveParameterValue $accessKey;
    private readonly ?\SensitiveParameterValue $secretKey;
    private readonly bool $secretKeyFromEnvironment;
    private readonly ClientInterface $httpClient;

    /**
     * Creates a client using the default Guzzle transport or an optional caller-owned PSR-18 client.
     *
     * When both keys are null, the client reads SCREENSHOTSCOUT_ACCESS_KEY and, if it's set,
     * SCREENSHOTSCOUT_SECRET_KEY. Passing either key means neither variable is read.
     */
    public function __construct(
        #[\SensitiveParameter]
        ?string $accessKey = null,
        #[\SensitiveParameter]
        ?string $secretKey = null,
        ?ClientInterface $httpClient = null,
    ) {
        $credentials = Credentials::resolve($accessKey, $secretKey, Environment::read(...));

        $accessKey = $credentials->accessKey;
        if (null === $accessKey || '' === trim($accessKey)) {
            throw new ConfigurationException('A non-blank Screenshot Scout access key is required.');
        }
        if (1 !== preg_match(self::ACCESS_KEY_PATTERN, $accessKey)) {
            throw new ConfigurationException('The Screenshot Scout access key is not a valid Bearer credential.');
        }

        $secretKey = $credentials->secretKey;
        if (null !== $secretKey && '' === trim($secretKey)) {
            throw new ConfigurationException(
                'The Screenshot Scout secret key must be a non-blank string when provided.',
            );
        }

        $this->accessKey = new \SensitiveParameterValue($accessKey);
        $this->secretKey = null === $secretKey ? null : new \SensitiveParameterValue($secretKey);
        $this->secretKeyFromEnvironment = $credentials->fromEnvironment && null !== $secretKey;
        $this->httpClient = $httpClient ?? self::defaultHttpClient();
    }

    /**
     * Sends one GET or POST request and waits for the final inline capture response. The method defaults
     * to POST, or GET when the client has a secret key. A client with a secret key signs its requests and
     * refuses POST, because POST requests can't be signed.
     */
    public function capture(
        string $url,
        ?CaptureOptions $options = null,
        ?CaptureHttpMethod $method = null,
    ): CaptureResponse {
        $serialized = CaptureSerializer::serialize($url, $options);
        $expectedKind = self::expectedResponseKind($options);
        if (null !== $this->secretKey && CaptureHttpMethod::POST === $method) {
            throw new ConfigurationException(sprintf(
                "POST requests can't be signed. This client has a secret key%s: send this capture with method GET, "
                . "or use a client without a secret key and an API key that doesn't require signed requests.",
                $this->secretKeyFromEnvironment ? ' from ' . Credentials::SECRET_KEY_VARIABLE : '',
            ));
        }
        // Without a method, a client that signs sends GET, because POST requests can't be signed.
        $method ??= null === $this->secretKey ? CaptureHttpMethod::POST : CaptureHttpMethod::GET;
        $signature = $this->createSignature($serialized->pairs);
        $request = $this->createRequest($serialized, $method, $signature);

        try {
            $response = $this->httpClient->sendRequest($request);
            $rawResponse = self::bufferResponse($response);
        } catch (ScreenshotScoutException $exception) {
            throw $exception;
        } catch (\Throwable $cause) {
            throw new TransportException(
                'Screenshot Scout request failed before a complete HTTP response body was received.',
                $cause,
            );
        }

        if ($rawResponse->statusCode() < 200 || 299 < $rawResponse->statusCode()) {
            throw self::createApiException($rawResponse);
        }

        return self::decodeSuccessResponse($rawResponse, $expectedKind);
    }

    /** Builds a sensitive GET capture URL without performing network I/O. */
    public function buildCaptureUrl(string $url, ?CaptureOptions $options = null): string
    {
        $serialized = CaptureSerializer::serialize($url, $options);
        $signature = $this->createSignature($serialized->pairs);
        $pairs = [new WirePair('access_key', $this->accessKey()), ...$serialized->pairs];
        if (null !== $signature) {
            $pairs[] = new WirePair('signature', $signature);
        }

        return self::CAPTURE_ENDPOINT . '?' . CaptureSerializer::encodeQuery($pairs);
    }

    private function accessKey(): string
    {
        /** @var string $accessKey */
        $accessKey = $this->accessKey->getValue();

        return $accessKey;
    }

    private function secretKey(): ?string
    {
        /** @var string|null $secretKey */
        $secretKey = $this->secretKey?->getValue();

        return $secretKey;
    }

    /** @param list<WirePair> $pairs */
    private function createSignature(array $pairs): ?string
    {
        $secretKey = $this->secretKey();
        if (null === $secretKey) {
            return null;
        }

        try {
            $canonical = CaptureSerializer::buildCanonicalQuery($pairs, $this->accessKey());

            return hash_hmac('sha256', $canonical, $secretKey);
        } catch (ScreenshotScoutException $exception) {
            throw $exception;
        } catch (\Throwable $cause) {
            throw new SerializationException('The capture request could not be signed.', null, $cause);
        }
    }

    private function createRequest(
        SerializedCaptureOptions $serialized,
        CaptureHttpMethod $method,
        ?string $signature,
    ): RequestInterface {
        try {
            if (CaptureHttpMethod::GET === $method) {
                $pairs = $serialized->pairs;
                if (null !== $signature) {
                    $pairs[] = new WirePair('signature', $signature);
                }
                $request = new Request(
                    CaptureHttpMethod::GET->value,
                    self::CAPTURE_ENDPOINT . '?' . CaptureSerializer::encodeQuery($pairs),
                );
            } else {
                $json = CaptureSerializer::serializeJsonBody($serialized->body);
                $request = new Request(
                    CaptureHttpMethod::POST->value,
                    self::CAPTURE_ENDPOINT,
                    ['Content-Type' => 'application/json'],
                    $json,
                );
            }

            return $request->withHeader('Authorization', 'Bearer ' . $this->accessKey());
        } catch (ScreenshotScoutException $exception) {
            throw $exception;
        } catch (\Throwable $cause) {
            throw new SerializationException(
                'The Screenshot Scout HTTP request could not be constructed.',
                null,
                $cause,
            );
        }
    }

    private static function bufferResponse(ResponseInterface $response): RawResponse
    {
        $stream = $response->getBody();
        if ($stream->isSeekable()) {
            $stream->rewind();
        }
        $body = $stream->getContents();

        $headers = array_map(
            static fn(array $values): array => array_values($values),
            $response->getHeaders(),
        );
        $contentType = $response->hasHeader('Content-Type')
            ? $response->getHeaderLine('Content-Type')
            : null;

        return new RawResponse(
            $response->getStatusCode(),
            $response->getReasonPhrase(),
            $headers,
            $contentType,
            $body,
        );
    }

    private static function createApiException(RawResponse $rawResponse): ApiException
    {
        $hasResponseBody = false;
        $responseBody = null;
        $nativeObject = null;
        try {
            $decoded = JsonCodec::decode($rawResponse->body());
            $hasResponseBody = true;
            $responseBody = $decoded->value;
            if ($decoded->nativeValue instanceof \stdClass) {
                $nativeObject = $decoded->nativeValue;
            }
        } catch (\JsonException) {
            // Raw response access does not depend on successful JSON parsing.
        }

        $errorCode = null;
        $errorMessage = null;
        $errors = null;
        if (null !== $nativeObject) {
            if (isset($nativeObject->error_code) && is_string($nativeObject->error_code)) {
                $errorCode = $nativeObject->error_code;
            }
            if (isset($nativeObject->error_message) && is_string($nativeObject->error_message)) {
                $errorMessage = $nativeObject->error_message;
            }
            if (isset($nativeObject->errors) && is_array($nativeObject->errors)) {
                $normalizedErrors = is_array($responseBody) ? ($responseBody['errors'] ?? null) : null;
                if (is_array($normalizedErrors) && array_is_list($normalizedErrors)) {
                    $errors = $normalizedErrors;
                }
            }
        }

        return new ApiException(
            $rawResponse,
            $hasResponseBody,
            $responseBody,
            $errorCode,
            $errorMessage,
            $errors,
        );
    }

    private static function decodeSuccessResponse(
        RawResponse $rawResponse,
        ?string $expectedKind,
    ): CaptureResponse {
        $actualKind = self::isJsonMediaType($rawResponse->contentType()) ? 'json' : 'binary';
        if (null !== $expectedKind && $actualKind !== $expectedKind) {
            throw new ResponseDecodingException(
                sprintf(
                    'Screenshot Scout returned a successful %s response when %s was requested.',
                    $actualKind,
                    $expectedKind,
                ),
                $rawResponse,
                new \UnexpectedValueException(sprintf(
                    'Expected a %s response but received %s.',
                    $expectedKind,
                    $actualKind,
                )),
            );
        }

        return 'json' === $actualKind
            ? self::decodeJsonResponse($rawResponse)
            : self::decodeBinaryResponse($rawResponse);
    }

    private static function decodeBinaryResponse(RawResponse $rawResponse): BinaryCaptureResponse
    {
        return new DefaultBinaryCaptureResponse(
            $rawResponse,
            self::firstHeader($rawResponse, 'Screenshot-Scout-Screenshot-URL'),
            self::firstHeader($rawResponse, 'Screenshot-Scout-Screenshot-URL-Expires-At'),
            self::firstHeader($rawResponse, 'Screenshot-Scout-Cache-Status'),
        );
    }

    private static function decodeJsonResponse(RawResponse $rawResponse): JsonCaptureResponse
    {
        try {
            $decoded = JsonCodec::decode($rawResponse->body());
        } catch (\JsonException $cause) {
            throw new ResponseDecodingException(
                'Screenshot Scout returned a successful JSON response that could not be decoded.',
                $rawResponse,
                $cause,
            );
        }

        $value = JsonCodec::objectFields($decoded);
        if (null === $value) {
            throw new ResponseDecodingException(
                'Screenshot Scout returned a successful JSON response that was not an object.',
                $rawResponse,
                new \UnexpectedValueException('Expected a JSON object.'),
            );
        }

        $screenshotUrl = self::readOptionalString($value, 'screenshot_url', $rawResponse);
        $screenshotUrlExpiresAt = self::readOptionalString(
            $value,
            'screenshot_url_expires_at',
            $rawResponse,
        );
        $cacheStatus = self::readOptionalString($value, 'cache_status', $rawResponse);
        $additionalFields = array_diff_key($value, array_flip([
            'screenshot_url',
            'screenshot_url_expires_at',
            'cache_status',
        ]));

        return new DefaultJsonCaptureResponse(
            new CaptureResult(
                $screenshotUrl,
                $screenshotUrlExpiresAt,
                $cacheStatus,
                $additionalFields,
            ),
            $rawResponse,
        );
    }

    /** @param array<array-key, mixed> $object */
    private static function readOptionalString(
        array $object,
        string $key,
        RawResponse $rawResponse,
    ): ?string {
        if (!array_key_exists($key, $object)) {
            return null;
        }
        if (!is_string($object[$key])) {
            throw new ResponseDecodingException(
                sprintf('Screenshot Scout returned a non-string "%s" JSON field.', $key),
                $rawResponse,
                new \UnexpectedValueException(sprintf('Expected "%s" to be a string.', $key)),
            );
        }

        return $object[$key];
    }

    private static function expectedResponseKind(?CaptureOptions $options): ?string
    {
        $responseType = $options?->responseType();
        if (null === $responseType || CaptureResponseType::BINARY === $responseType) {
            return 'binary';
        }

        return CaptureResponseType::JSON === $responseType ? 'json' : null;
    }

    private static function isJsonMediaType(?string $contentType): bool
    {
        if (null === $contentType) {
            return false;
        }
        $separator = strpos($contentType, ';');
        $mediaType = strtolower(trim(false === $separator ? $contentType : substr($contentType, 0, $separator)));

        return 'application/json' === $mediaType || str_ends_with($mediaType, '+json');
    }

    private static function firstHeader(RawResponse $response, string $name): ?string
    {
        return $response->header($name)[0] ?? null;
    }

    private static function defaultHttpClient(): ClientInterface
    {
        return new GuzzleClient(
            self::defaultHttpClientOptions(function_exists('curl_exec') || function_exists('curl_multi_exec')),
        );
    }

    /**
     * Without the cURL extension, Guzzle sends requests through PHP streams, which stop waiting after
     * default_socket_timeout (60 seconds unless configured). With cURL, there is no limit by default.
     *
     * @return array<string, int>
     */
    private static function defaultHttpClientOptions(bool $curlAvailable): array
    {
        return $curlAvailable ? [] : ['timeout' => self::STREAM_TIMEOUT_SECONDS];
    }
}
