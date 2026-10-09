<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\PageMeta;

/**
 * The ViewModel of a public HTML page. Its data(), and previewData() when the page has
 * previews, return the view data with the page's PageMeta under the PAGE_META key, and the
 * page passes it to the site layout, which reads the SEO tags and JSON-LD from that value.
 * ViewModels stay stateless, so each method documents the key in its array shape
 * (`pageMeta: PageMeta`) and PHPStan checks the shape.
 *
 * @see PageMeta
 */
interface PageViewModel
{
    /** The view-data key that holds the page's PageMeta. */
    public const string PAGE_META = 'pageMeta';
}
