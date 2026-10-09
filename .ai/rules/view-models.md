---
paths:
  - 'app/ViewModels/**'
  - 'app/View/SiteSeo.php'
  - 'app/Data/PageMeta.php'
  - 'app/Contracts/PageViewModel.php'
  - 'app/Support/Seo/**'
  - 'resources/views/components/layouts/**'
---

# View Models

## Let page ViewModels own payloads and SEO
Page ViewModels assemble the view payload and own the page's SEO and JSON-LD. Controllers inject them and translate the HTTP request and response. This mirrors The Laravel Architect's `.ai/rules/view-models.md`. The feed ViewModels (RSS, newsletter RSS, sitemap, robots.txt) are not page ViewModels.

## Return the page's PageMeta from every page ViewModel
A page ViewModel implements `App\Contracts\PageViewModel` and returns an `App\Data\PageMeta` (the page's `SEOData` plus its own JSON-LD nodes) under the `pageMeta` key from `data()` and `previewData()`, documented as `pageMeta: PageMeta` in the array shape. The page passes it to its layout (`<x-layouts.app :page-meta="$pageMeta">`), which renders the SEO tags and wraps the nodes in one schema.org `@graph`; never add SEO under other view-data keys or build it in Blade. Build the `SEOData` with `App\View\SiteSeo` (`page()` or `errorPage()`) so canonical and share-image URLs, the site name, the title suffix and `og:locale` match every other page. A preview returns `noindex,nofollow` and no JSON-LD nodes. Put generic schema.org shapes, such as breadcrumbs and ISO 8601 durations, in `App\Support\Seo\JsonLd`.

## Move pages to PageMeta without changing their tags
Pages move to PageMeta one at a time. Until a page moves, it passes `title`, `description`, `robots`, `og-*` and `canonical` props, and the layouts build the same `SEOData` from them through `SiteSeo`; remove those props only when no page passes them. `tests/Feature/PublicPageSeoTagsTest.php` snapshots the title, meta and canonical tags of the main public pages. A move keeps those snapshots unchanged; update one only for an intended change, and list it in the PR.
