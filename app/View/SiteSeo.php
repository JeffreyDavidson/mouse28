<?php

declare(strict_types=1);

namespace App\View;

use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\SEOData;

/**
 * Builds the SEOData Mouse28 pages publish: absolute canonical and share-image URLs, the site
 * name, no title suffix (page titles carry their own "| Mouse28") and no og:locale. The site
 * layouts use it for pages that still pass their metadata as layout props.
 */
final readonly class SiteSeo
{
    public function page(
        string $title,
        string $description,
        string $robots = 'index,follow',
        ?string $ogTitle = null,
        string $ogType = 'website',
        ?string $ogImage = null,
        ?string $canonical = null,
    ): SEOData {
        $canonicalUrl = $canonical ?: url()->current();

        // The home page's canonical keeps its trailing slash ("https://host/"), as it has always been published.
        if (parse_url($canonicalUrl, PHP_URL_PATH) === null) {
            $canonicalUrl .= '/';
        }

        return new SEOData(
            title: $title,
            description: $description,
            image: $this->absoluteImageUrl($ogImage ?: url('/images/logo.jpg')),
            url: $canonicalUrl,
            enableTitleSuffix: false,
            type: $ogType,
            site_name: 'Mouse28',
            locale: '',
            robots: $robots,
            canonical_url: $canonicalUrl,
            openGraphTitle: $ogTitle ?: $title,
        );
    }

    /** An error page has no URL of its own, so it is never indexed and the error layout drops its canonical tag. */
    public function errorPage(string $title, string $description, string $ogTitle): SEOData
    {
        return new SEOData(
            title: $title,
            description: $description,
            image: url('/images/logo.jpg'),
            url: url()->current(),
            enableTitleSuffix: false,
            site_name: 'Mouse28',
            locale: '',
            robots: 'noindex, nofollow',
            openGraphTitle: $ogTitle,
        );
    }

    private function absoluteImageUrl(string $image): string
    {
        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        return url('/'.ltrim($image, '/'));
    }
}
