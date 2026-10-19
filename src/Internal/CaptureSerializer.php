<?php

declare(strict_types=1);

namespace ScreenshotScout\Internal;

use ScreenshotScout\CaptureOptions;
use ScreenshotScout\Exception\ScreenshotScoutException;
use ScreenshotScout\Exception\SerializationException;

/** @internal */
final class CaptureSerializer
{
    public static function serialize(string $targetUrl, ?CaptureOptions $options): SerializedCaptureOptions
    {
        try {
            $builder = new SerializationBuilder($targetUrl);
            if (null === $options) {
                return $builder->build();
            }

            $builder->add('format', 'format', $options->format());
            $builder->add('responseType', 'response_type', $options->responseType());
            $builder->add('country', 'country', $options->country());
            $builder->add('proxy', 'proxy', $options->proxy());
            $builder->add('geolocationLatitude', 'geolocation_latitude', $options->geolocationLatitude());
            $builder->add('geolocationLongitude', 'geolocation_longitude', $options->geolocationLongitude());
            $builder->add('geolocationAccuracy', 'geolocation_accuracy', $options->geolocationAccuracy());
            $builder->addRepeated('cookies', 'cookies', $options->cookies());
            $builder->addRepeated('headers', 'headers', $options->headers());
            $builder->add('timeout', 'timeout', $options->timeout());
            $builder->add('waitUntil', 'wait_until', $options->waitUntil());
            $builder->add('navigationTimeout', 'navigation_timeout', $options->navigationTimeout());
            $builder->add('delay', 'delay', $options->delay());
            $builder->add('device', 'device', $options->device());
            $builder->add('deviceViewportWidth', 'device_viewport_width', $options->deviceViewportWidth());
            $builder->add('deviceViewportHeight', 'device_viewport_height', $options->deviceViewportHeight());
            $builder->add('deviceScaleFactor', 'device_scale_factor', $options->deviceScaleFactor());
            $builder->add('deviceIsMobile', 'device_is_mobile', $options->deviceIsMobile());
            $builder->add('deviceHasTouch', 'device_has_touch', $options->deviceHasTouch());
            $builder->add('deviceUserAgent', 'device_user_agent', $options->deviceUserAgent());
            $builder->add('timezone', 'timezone', $options->timezone());
            $builder->add('mediaType', 'media_type', $options->mediaType());
            $builder->add('colorScheme', 'color_scheme', $options->colorScheme());
            $builder->add('reducedMotion', 'reduced_motion', $options->reducedMotion());
            $builder->add('fullPage', 'full_page', $options->fullPage());
            $builder->add('fullPagePreScroll', 'full_page_pre_scroll', $options->fullPagePreScroll());
            $builder->add('fullPagePreScrollStep', 'full_page_pre_scroll_step', $options->fullPagePreScrollStep());
            $builder->add('fullPagePreScrollStepDelay', 'full_page_pre_scroll_step_delay', $options->fullPagePreScrollStepDelay());
            $builder->add('fullPageMaxHeight', 'full_page_max_height', $options->fullPageMaxHeight());
            $builder->add('blockCookieBanners', 'block_cookie_banners', $options->blockCookieBanners());
            $builder->add('blockAds', 'block_ads', $options->blockAds());
            $builder->add('blockChatWidgets', 'block_chat_widgets', $options->blockChatWidgets());
            $builder->addRepeated('hideSelectors', 'hide_selectors', $options->hideSelectors());
            $builder->addRepeated('clickSelectors', 'click_selectors', $options->clickSelectors());
            $builder->addRepeated('clickAllSelectors', 'click_all_selectors', $options->clickAllSelectors());
            $builder->addRepeated('injectCss', 'inject_css', $options->injectCss());
            $builder->addRepeated('injectJs', 'inject_js', $options->injectJs());
            $builder->add('bypassCsp', 'bypass_csp', $options->bypassCsp());
            $builder->add('selector', 'selector', $options->selector());
            $builder->add('clipX', 'clip_x', $options->clipX());
            $builder->add('clipY', 'clip_y', $options->clipY());
            $builder->add('clipWidth', 'clip_width', $options->clipWidth());
            $builder->add('clipHeight', 'clip_height', $options->clipHeight());
            $builder->add('imageWidth', 'image_width', $options->imageWidth());
            $builder->add('imageHeight', 'image_height', $options->imageHeight());
            $builder->add('imageMode', 'image_mode', $options->imageMode());
            $builder->add('imageAnchor', 'image_anchor', $options->imageAnchor());
            $builder->add('imageAllowUpscale', 'image_allow_upscale', $options->imageAllowUpscale());
            $builder->add('imageBackground', 'image_background', $options->imageBackground());
            $builder->add('imageQuality', 'image_quality', $options->imageQuality());
            $builder->add('pdfPaperFormat', 'pdf_paper_format', $options->pdfPaperFormat());
            $builder->add('pdfLandscape', 'pdf_landscape', $options->pdfLandscape());
            $builder->add('pdfPrintBackground', 'pdf_print_background', $options->pdfPrintBackground());
            $builder->add('pdfMargin', 'pdf_margin', $options->pdfMargin());
            $builder->add('pdfMarginTop', 'pdf_margin_top', $options->pdfMarginTop());
            $builder->add('pdfMarginRight', 'pdf_margin_right', $options->pdfMarginRight());
            $builder->add('pdfMarginBottom', 'pdf_margin_bottom', $options->pdfMarginBottom());
            $builder->add('pdfMarginLeft', 'pdf_margin_left', $options->pdfMarginLeft());
            $builder->add('pdfScale', 'pdf_scale', $options->pdfScale());
            $builder->add('pdfPageRanges', 'pdf_page_ranges', $options->pdfPageRanges());
            $builder->add('cache', 'cache', $options->cache());
            $builder->add('cacheTtl', 'cache_ttl', $options->cacheTtl());
            $builder->add('cacheKey', 'cache_key', $options->cacheKey());
            $builder->add('storageMode', 'storage_mode', $options->storageMode());
            $builder->add('storageEndpoint', 'storage_endpoint', $options->storageEndpoint());
            $builder->add('storageBucket', 'storage_bucket', $options->storageBucket());
            $builder->add('storageRegion', 'storage_region', $options->storageRegion());
            $builder->add('storageObjectKey', 'storage_object_key', $options->storageObjectKey());

            return $builder->build();
        } catch (ScreenshotScoutException $exception) {
            throw $exception;
        } catch (\Throwable $cause) {
            throw new SerializationException('Capture options could not be serialized.', null, $cause);
        }
    }

    /** @param list<WirePair> $pairs */
    public static function buildCanonicalQuery(array $pairs, string $accessKey): string
    {
        $indexed = [];
        foreach ([...$pairs, new WirePair('access_key', $accessKey)] as $index => $pair) {
            $indexed[] = ['pair' => $pair, 'index' => $index];
        }
        usort(
            $indexed,
            static fn(array $left, array $right): int => $left['pair']->name === $right['pair']->name
                ? $left['index'] <=> $right['index']
                : $left['pair']->name <=> $right['pair']->name,
        );

        return self::encodeQuery(array_map(
            static fn(array $entry): WirePair => $entry['pair'],
            $indexed,
        ));
    }

    /** @param list<WirePair> $pairs */
    public static function encodeQuery(array $pairs): string
    {
        $encoded = [];
        foreach ($pairs as $pair) {
            $encoded[] = self::formEncode($pair->name) . '=' . self::formEncode($pair->value);
        }

        return implode('&', $encoded);
    }

    /** @param array<string, string|int|float|bool|list<string>> $body */
    public static function serializeJsonBody(array $body): string
    {
        try {
            return JsonCodec::encodeWireObject($body);
        } catch (ScreenshotScoutException $exception) {
            throw $exception;
        } catch (\Throwable $cause) {
            throw new SerializationException('The POST capture body could not be serialized as JSON.', null, $cause);
        }
    }

    /** Encodes one component using the same form rules as WHATWG URLSearchParams. */
    private static function formEncode(string $value): string
    {
        return str_replace('%2A', '*', urlencode($value));
    }

    private function __construct() {}
}
