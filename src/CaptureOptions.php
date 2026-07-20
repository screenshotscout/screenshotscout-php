<?php

declare(strict_types=1);

namespace ScreenshotScout;

/**
 * Immutable options for configuring a screenshot.
 *
 * Null values are omitted. Documented string constants are available from the Capture* classes,
 * while arbitrary strings remain supported for forward compatibility.
 */
final class CaptureOptions
{
    /** @var array<string, string|int|float|bool|list<string>|null> */
    private array $values = [];

    public function withFormat(?string $value): self
    {
        return $this->withValue('format', $value);
    }

    public function format(): ?string
    {
        return $this->stringValue('format');
    }

    public function withResponseType(?string $value): self
    {
        return $this->withValue('responseType', $value);
    }

    public function responseType(): ?string
    {
        return $this->stringValue('responseType');
    }

    public function withCountry(?string $value): self
    {
        return $this->withValue('country', $value);
    }

    public function country(): ?string
    {
        return $this->stringValue('country');
    }

    public function withProxy(?string $value): self
    {
        return $this->withValue('proxy', $value);
    }

    public function proxy(): ?string
    {
        return $this->stringValue('proxy');
    }

    public function withGeolocationLatitude(?float $value): self
    {
        return $this->withValue('geolocationLatitude', $value);
    }

    public function geolocationLatitude(): ?float
    {
        return $this->floatValue('geolocationLatitude');
    }

    public function withGeolocationLongitude(?float $value): self
    {
        return $this->withValue('geolocationLongitude', $value);
    }

    public function geolocationLongitude(): ?float
    {
        return $this->floatValue('geolocationLongitude');
    }

    public function withGeolocationAccuracy(?float $value): self
    {
        return $this->withValue('geolocationAccuracy', $value);
    }

    public function geolocationAccuracy(): ?float
    {
        return $this->floatValue('geolocationAccuracy');
    }

    /** @param list<string>|null $value */
    public function withCookies(?array $value): self
    {
        return $this->withValue('cookies', $value);
    }

    /** @return list<string>|null */
    public function cookies(): ?array
    {
        return $this->listValue('cookies');
    }

    /** @param list<string>|null $value */
    public function withHeaders(?array $value): self
    {
        return $this->withValue('headers', $value);
    }

    /** @return list<string>|null */
    public function headers(): ?array
    {
        return $this->listValue('headers');
    }

    public function withTimeout(?int $value): self
    {
        return $this->withValue('timeout', $value);
    }

    public function timeout(): ?int
    {
        return $this->intValue('timeout');
    }

    public function withWaitUntil(?string $value): self
    {
        return $this->withValue('waitUntil', $value);
    }

    public function waitUntil(): ?string
    {
        return $this->stringValue('waitUntil');
    }

    public function withNavigationTimeout(?int $value): self
    {
        return $this->withValue('navigationTimeout', $value);
    }

    public function navigationTimeout(): ?int
    {
        return $this->intValue('navigationTimeout');
    }

    public function withDelay(?int $value): self
    {
        return $this->withValue('delay', $value);
    }

    public function delay(): ?int
    {
        return $this->intValue('delay');
    }

    public function withDevice(?string $value): self
    {
        return $this->withValue('device', $value);
    }

    public function device(): ?string
    {
        return $this->stringValue('device');
    }

    public function withDeviceViewportWidth(?int $value): self
    {
        return $this->withValue('deviceViewportWidth', $value);
    }

    public function deviceViewportWidth(): ?int
    {
        return $this->intValue('deviceViewportWidth');
    }

    public function withDeviceViewportHeight(?int $value): self
    {
        return $this->withValue('deviceViewportHeight', $value);
    }

    public function deviceViewportHeight(): ?int
    {
        return $this->intValue('deviceViewportHeight');
    }

    public function withDeviceScaleFactor(?float $value): self
    {
        return $this->withValue('deviceScaleFactor', $value);
    }

    public function deviceScaleFactor(): ?float
    {
        return $this->floatValue('deviceScaleFactor');
    }

    public function withDeviceIsMobile(?bool $value): self
    {
        return $this->withValue('deviceIsMobile', $value);
    }

    public function deviceIsMobile(): ?bool
    {
        return $this->boolValue('deviceIsMobile');
    }

    public function withDeviceHasTouch(?bool $value): self
    {
        return $this->withValue('deviceHasTouch', $value);
    }

    public function deviceHasTouch(): ?bool
    {
        return $this->boolValue('deviceHasTouch');
    }

    public function withDeviceUserAgent(?string $value): self
    {
        return $this->withValue('deviceUserAgent', $value);
    }

    public function deviceUserAgent(): ?string
    {
        return $this->stringValue('deviceUserAgent');
    }

    public function withTimezone(?string $value): self
    {
        return $this->withValue('timezone', $value);
    }

    public function timezone(): ?string
    {
        return $this->stringValue('timezone');
    }

    public function withMediaType(?string $value): self
    {
        return $this->withValue('mediaType', $value);
    }

    public function mediaType(): ?string
    {
        return $this->stringValue('mediaType');
    }

    public function withColorScheme(?string $value): self
    {
        return $this->withValue('colorScheme', $value);
    }

    public function colorScheme(): ?string
    {
        return $this->stringValue('colorScheme');
    }

    public function withReducedMotion(?bool $value): self
    {
        return $this->withValue('reducedMotion', $value);
    }

    public function reducedMotion(): ?bool
    {
        return $this->boolValue('reducedMotion');
    }

    public function withFullPage(?bool $value): self
    {
        return $this->withValue('fullPage', $value);
    }

    public function fullPage(): ?bool
    {
        return $this->boolValue('fullPage');
    }

    public function withFullPagePreScroll(?bool $value): self
    {
        return $this->withValue('fullPagePreScroll', $value);
    }

    public function fullPagePreScroll(): ?bool
    {
        return $this->boolValue('fullPagePreScroll');
    }

    public function withFullPagePreScrollStep(?int $value): self
    {
        return $this->withValue('fullPagePreScrollStep', $value);
    }

    public function fullPagePreScrollStep(): ?int
    {
        return $this->intValue('fullPagePreScrollStep');
    }

    public function withFullPagePreScrollStepDelay(?int $value): self
    {
        return $this->withValue('fullPagePreScrollStepDelay', $value);
    }

    public function fullPagePreScrollStepDelay(): ?int
    {
        return $this->intValue('fullPagePreScrollStepDelay');
    }

    public function withFullPageMaxHeight(?int $value): self
    {
        return $this->withValue('fullPageMaxHeight', $value);
    }

    public function fullPageMaxHeight(): ?int
    {
        return $this->intValue('fullPageMaxHeight');
    }

    public function withBlockCookieBanners(?bool $value): self
    {
        return $this->withValue('blockCookieBanners', $value);
    }

    public function blockCookieBanners(): ?bool
    {
        return $this->boolValue('blockCookieBanners');
    }

    public function withBlockAds(?bool $value): self
    {
        return $this->withValue('blockAds', $value);
    }

    public function blockAds(): ?bool
    {
        return $this->boolValue('blockAds');
    }

    public function withBlockChatWidgets(?bool $value): self
    {
        return $this->withValue('blockChatWidgets', $value);
    }

    public function blockChatWidgets(): ?bool
    {
        return $this->boolValue('blockChatWidgets');
    }

    /** @param list<string>|null $value */
    public function withHideSelectors(?array $value): self
    {
        return $this->withValue('hideSelectors', $value);
    }

    /** @return list<string>|null */
    public function hideSelectors(): ?array
    {
        return $this->listValue('hideSelectors');
    }

    /** @param list<string>|null $value */
    public function withClickSelectors(?array $value): self
    {
        return $this->withValue('clickSelectors', $value);
    }

    /** @return list<string>|null */
    public function clickSelectors(): ?array
    {
        return $this->listValue('clickSelectors');
    }

    /** @param list<string>|null $value */
    public function withClickAllSelectors(?array $value): self
    {
        return $this->withValue('clickAllSelectors', $value);
    }

    /** @return list<string>|null */
    public function clickAllSelectors(): ?array
    {
        return $this->listValue('clickAllSelectors');
    }

    /** @param list<string>|null $value */
    public function withInjectCss(?array $value): self
    {
        return $this->withValue('injectCss', $value);
    }

    /** @return list<string>|null */
    public function injectCss(): ?array
    {
        return $this->listValue('injectCss');
    }

    /** @param list<string>|null $value */
    public function withInjectJs(?array $value): self
    {
        return $this->withValue('injectJs', $value);
    }

    /** @return list<string>|null */
    public function injectJs(): ?array
    {
        return $this->listValue('injectJs');
    }

    public function withBypassCsp(?bool $value): self
    {
        return $this->withValue('bypassCsp', $value);
    }

    public function bypassCsp(): ?bool
    {
        return $this->boolValue('bypassCsp');
    }

    public function withSelector(?string $value): self
    {
        return $this->withValue('selector', $value);
    }

    public function selector(): ?string
    {
        return $this->stringValue('selector');
    }

    public function withClipX(?int $value): self
    {
        return $this->withValue('clipX', $value);
    }

    public function clipX(): ?int
    {
        return $this->intValue('clipX');
    }

    public function withClipY(?int $value): self
    {
        return $this->withValue('clipY', $value);
    }

    public function clipY(): ?int
    {
        return $this->intValue('clipY');
    }

    public function withClipWidth(?int $value): self
    {
        return $this->withValue('clipWidth', $value);
    }

    public function clipWidth(): ?int
    {
        return $this->intValue('clipWidth');
    }

    public function withClipHeight(?int $value): self
    {
        return $this->withValue('clipHeight', $value);
    }

    public function clipHeight(): ?int
    {
        return $this->intValue('clipHeight');
    }

    public function withImageWidth(?int $value): self
    {
        return $this->withValue('imageWidth', $value);
    }

    public function imageWidth(): ?int
    {
        return $this->intValue('imageWidth');
    }

    public function withImageHeight(?int $value): self
    {
        return $this->withValue('imageHeight', $value);
    }

    public function imageHeight(): ?int
    {
        return $this->intValue('imageHeight');
    }

    public function withImageMode(?string $value): self
    {
        return $this->withValue('imageMode', $value);
    }

    public function imageMode(): ?string
    {
        return $this->stringValue('imageMode');
    }

    public function withImageAnchor(?string $value): self
    {
        return $this->withValue('imageAnchor', $value);
    }

    public function imageAnchor(): ?string
    {
        return $this->stringValue('imageAnchor');
    }

    public function withImageAllowUpscale(?bool $value): self
    {
        return $this->withValue('imageAllowUpscale', $value);
    }

    public function imageAllowUpscale(): ?bool
    {
        return $this->boolValue('imageAllowUpscale');
    }

    public function withImageBackground(?string $value): self
    {
        return $this->withValue('imageBackground', $value);
    }

    public function imageBackground(): ?string
    {
        return $this->stringValue('imageBackground');
    }

    public function withImageQuality(?int $value): self
    {
        return $this->withValue('imageQuality', $value);
    }

    public function imageQuality(): ?int
    {
        return $this->intValue('imageQuality');
    }

    public function withPdfPaperFormat(?string $value): self
    {
        return $this->withValue('pdfPaperFormat', $value);
    }

    public function pdfPaperFormat(): ?string
    {
        return $this->stringValue('pdfPaperFormat');
    }

    public function withPdfLandscape(?bool $value): self
    {
        return $this->withValue('pdfLandscape', $value);
    }

    public function pdfLandscape(): ?bool
    {
        return $this->boolValue('pdfLandscape');
    }

    public function withPdfPrintBackground(?bool $value): self
    {
        return $this->withValue('pdfPrintBackground', $value);
    }

    public function pdfPrintBackground(): ?bool
    {
        return $this->boolValue('pdfPrintBackground');
    }

    public function withPdfMargin(?string $value): self
    {
        return $this->withValue('pdfMargin', $value);
    }

    public function pdfMargin(): ?string
    {
        return $this->stringValue('pdfMargin');
    }

    public function withPdfMarginTop(?string $value): self
    {
        return $this->withValue('pdfMarginTop', $value);
    }

    public function pdfMarginTop(): ?string
    {
        return $this->stringValue('pdfMarginTop');
    }

    public function withPdfMarginRight(?string $value): self
    {
        return $this->withValue('pdfMarginRight', $value);
    }

    public function pdfMarginRight(): ?string
    {
        return $this->stringValue('pdfMarginRight');
    }

    public function withPdfMarginBottom(?string $value): self
    {
        return $this->withValue('pdfMarginBottom', $value);
    }

    public function pdfMarginBottom(): ?string
    {
        return $this->stringValue('pdfMarginBottom');
    }

    public function withPdfMarginLeft(?string $value): self
    {
        return $this->withValue('pdfMarginLeft', $value);
    }

    public function pdfMarginLeft(): ?string
    {
        return $this->stringValue('pdfMarginLeft');
    }

    public function withPdfScale(?float $value): self
    {
        return $this->withValue('pdfScale', $value);
    }

    public function pdfScale(): ?float
    {
        return $this->floatValue('pdfScale');
    }

    public function withCache(?bool $value): self
    {
        return $this->withValue('cache', $value);
    }

    public function cache(): ?bool
    {
        return $this->boolValue('cache');
    }

    public function withCacheTtl(?int $value): self
    {
        return $this->withValue('cacheTtl', $value);
    }

    public function cacheTtl(): ?int
    {
        return $this->intValue('cacheTtl');
    }

    public function withCacheKey(?string $value): self
    {
        return $this->withValue('cacheKey', $value);
    }

    public function cacheKey(): ?string
    {
        return $this->stringValue('cacheKey');
    }

    public function withStorageMode(?string $value): self
    {
        return $this->withValue('storageMode', $value);
    }

    public function storageMode(): ?string
    {
        return $this->stringValue('storageMode');
    }

    public function withStorageEndpoint(?string $value): self
    {
        return $this->withValue('storageEndpoint', $value);
    }

    public function storageEndpoint(): ?string
    {
        return $this->stringValue('storageEndpoint');
    }

    public function withStorageBucket(?string $value): self
    {
        return $this->withValue('storageBucket', $value);
    }

    public function storageBucket(): ?string
    {
        return $this->stringValue('storageBucket');
    }

    public function withStorageRegion(?string $value): self
    {
        return $this->withValue('storageRegion', $value);
    }

    public function storageRegion(): ?string
    {
        return $this->stringValue('storageRegion');
    }

    public function withStorageObjectKey(?string $value): self
    {
        return $this->withValue('storageObjectKey', $value);
    }

    public function storageObjectKey(): ?string
    {
        return $this->stringValue('storageObjectKey');
    }

    /** @param string|int|float|bool|list<string>|null $value */
    private function withValue(string $name, string|int|float|bool|array|null $value): self
    {
        $copy = clone $this;
        $copy->values[$name] = $value;

        return $copy;
    }

    private function stringValue(string $name): ?string
    {
        $value = $this->values[$name] ?? null;
        assert(null === $value || is_string($value));

        return $value;
    }

    private function intValue(string $name): ?int
    {
        $value = $this->values[$name] ?? null;
        assert(null === $value || is_int($value));

        return $value;
    }

    private function floatValue(string $name): ?float
    {
        $value = $this->values[$name] ?? null;
        assert(null === $value || is_float($value));

        return $value;
    }

    private function boolValue(string $name): ?bool
    {
        $value = $this->values[$name] ?? null;
        assert(null === $value || is_bool($value));

        return $value;
    }

    /** @return list<string>|null */
    private function listValue(string $name): ?array
    {
        $value = $this->values[$name] ?? null;
        assert(null === $value || is_array($value));

        return $value;
    }
}
