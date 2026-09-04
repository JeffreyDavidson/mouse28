# Mouse28 Architecture

## Public application

Public routes are defined in `routes/web.php` and rendered with Blade through the shared `layouts.app` layout. Tailwind, Alpine, and public JavaScript are delivered by the existing Vite entry points.

The homepage is assembled by `HomeController` in this order: hero, featured post, latest posts, guides, podcast, about, and newsletter. Community Stories are intentionally not part of the application.

## Content boundaries

- `Post` owns blog articles and optional episode relationships.
- `Guide` owns durable resources grouped by accessibility, park strategy, food and reviews, or family planning. Guides support source URLs and review dates because park policies can change.
- `Episode` owns podcast installments, hosted or external audio, distribution links, show notes, and transcripts. Hosted MP3s are stored on the public disk under `episodes/audio`; `audio_path` takes precedence over the legacy-compatible external `audio_url`.
- `Podcast` owns the single show-level metadata record.
- `ContactMessage` stores contact submissions for the admin inbox.

The `published` model scopes require both an enabled publication flag and a non-future publication date. Public detail controllers enforce the same rule so direct URLs cannot expose drafts or scheduled content.

Site search is handled by the invokable `SearchController`. It searches posts, guides, and episodes independently, limits each result group, and always begins with the models' `published` scopes so drafts and scheduled content remain private. Search result pages are marked `noindex,follow` and are not included in the sitemap.

`ContentContinuation` keeps detail pages connected without exposing unpublished records. Guide recommendations prioritize the current category and fill remaining spaces with recent guides, while episode pages link to the chronologically adjacent published episodes. Article and guide category labels link back to their filtered indexes.

Public detail pages emit Schema.org JSON-LD through `StructuredData`: `BlogPosting` for posts, `Article` for guides, `PodcastEpisode` for episodes, and `BreadcrumbList` navigation for each type. JSON output is hex-escaped before it is placed in the document head.

The public layout normalizes canonical and social-image URLs to absolute URLs. Landing pages provide page-specific search and social descriptions. Category archives and paginated archives use self-referencing canonicals, while text searches and alternate sort views are marked `noindex,follow` and canonicalized to the clean archive URL.

## Administration and authorization

Filament is mounted at `/admin`. `User::canAccessPanel()` requires the explicit `is_admin` flag. Model policies provide the same boundary for content resources and protected record actions. Custom settings and subscriber pages also enforce administrator access.

`EditorialReadiness` provides shared publication status and readiness rules for posts, guides, and episodes. Filament list tabs and filters separate drafts, scheduled and published content, incomplete records, and review-due material. Editors publish and unpublish from explicit edit-page actions; publication is blocked until required editorial content, artwork, and SEO metadata are complete, and a blank publish date is filled automatically. Episode audio and transcripts are tracked as availability information but intentionally do not block publication while the original recordings are unavailable.

Filament global search covers posts, guides, episodes, and contact messages. The dashboard surfaces review-due counts for both sourced posts and guides so time-sensitive information returns to the editorial queue.

Content and social artwork uses a 1200:630 aspect ratio. Filament restricts uploads to common web image formats, caps them at 5 MB, and crops and resizes them through its native uploader. The idempotent `content:attach-artwork` command connects bundled Mouse28 WebP artwork to the known post and episode slugs only when a record does not already have a cover.

Production is the source of truth for published editorial content. `PublicContentArchive` exports only published posts, guides, episodes, and public podcast display metadata; its sync mode reconciles those records by slug while preserving local drafts and scheduled content. The local-only `content:sync-production` command retrieves that archive over SSH, validates every referenced public-storage path, transfers only those cover, social, and hosted-audio files, and refuses to run in production. Users, subscribers, contact submissions, credentials, and environment-specific podcast email remain outside the archive.

Posts, guides, and episodes use Eloquent soft deletion. Deleted content disappears from public queries immediately, while Filament administrators can filter the trash, restore records, or explicitly confirm a permanent deletion. Resource route binding includes trashed records only inside the authorized panel recovery flow.

Each content edit page links to an administrator-authorized preview route. Preview pages reuse the public templates, carry a visible preview banner, emit `noindex,nofollow`, and omit structured data. Every preview is authorized through its model policy, so drafts are not exposed by knowing their URL.

The migration that introduces `is_admin` promotes existing accounts because every existing account had panel access under the previous behavior. New accounts default to non-administrators.

## External integrations

Newsletter subscriptions are sent to the configured Resend audience. The public endpoint is protected by a honeypot, Turnstile verification, validation, and an IP rate limit. A shared `ResendAudience` reader supplies both the admin subscriber page and dashboard from one five-minute cache. The page can explicitly refresh that cache, reports provider failures separately from an empty audience, and exports spreadsheet-safe CSV values.

Contact submissions use a separate Turnstile action and rate limiter, store the message, and send administrator and sender emails. Provider failures are logged without exposing submitted data or provider response bodies.

Contact and newsletter submissions use separate named validation bags, and their views only restore old input when feedback belongs to that form. Newsletter return URLs are restricted to the configured application origin, with the homepage newsletter section as the fallback.

Unhandled exceptions are registered with the Sentry Laravel integration in `bootstrap/app.php`. Monitoring is opt-in through a production-only DSN; the committed defaults disable tracing, profiling, and default personally identifiable information. Deployments may identify events with `SENTRY_ENVIRONMENT` and `SENTRY_RELEASE` without placing credentials in source control.

Podcast and blog RSS feeds plus the sitemap are generated by controllers. Podcast ownership values come from configuration or the podcast metadata record. The first-party `/rss/podcast` route is always advertised publicly and includes hosted file sizes in enclosure metadata. Reading a feed does not create database records.

Public pages render through anonymous `x-layouts.app` and `x-layouts.error` Blade components. Page views declare their document metadata as layout attributes, and the layout components pass those values to Laravel Head before rendering its `@head` output. This keeps titles, descriptions, canonical URLs, robots directives, Open Graph data, Twitter cards, and feed discovery out of Blade section inheritance while preserving server-rendered metadata.

`PodcastComposer` supplies show-level metadata to the shared public layout component, while the home and episode controllers provide it directly to content views. Apple Podcasts, Spotify, and YouTube links configured in Podcast Settings are the public source of truth and are omitted when unavailable. Episode-specific destinations override show-level links on episode pages. RSS always points to the generated first-party feed. These read paths use `Podcast::info()` and never create a settings row.

Guide freshness is controlled by `GUIDE_REVIEW_INTERVAL_DAYS`, with a 180-day default. Missing or older review dates are surfaced to editors and produce a public verification notice because accessibility and park-operation details can change.

## Frontend and accessibility

The public layout provides a semantic header, primary navigation, skip link, main landmark, footer, visible focus styles, responsive controls, and reduced-motion behavior. Mobile interactive targets should remain at least 48 pixels. User-authored Markdown is rendered with embedded HTML stripped and unsafe links disabled.

HTTP 404, 419, 500, and 503 responses use branded recovery pages. Their lightweight standalone layout avoids session, database, and shared-view dependencies so failures remain renderable. Error pages are `noindex,nofollow`, omit canonical URLs, and never render exception messages. Unknown routes and unpublished content receive the same 404 response so private titles are not disclosed.

Global response middleware adds `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, and a restrictive camera, geolocation, and microphone `Permissions-Policy` to public, admin, feed, and error responses. HSTS is left to the HTTPS deployment edge, and a content security policy is not declared until the public and Filament script requirements can be expressed without unsafe fallbacks.

The framework health endpoint probes the migrations table, so `/up` reports failure when the configured database is unavailable or has not been migrated. The `app:verify-production` command validates required production configuration without printing secret values. Demo content cleanup is restricted to known seed slugs, runs in one database transaction, and requires an explicit `--force` flag in production.

Admin, preview, and error responses also emit `X-Robots-Tag: noindex, nofollow` and `Cache-Control: no-store, private`, preventing crawlers and shared caches from retaining private or failure-specific content. The dynamic and static robots policies both disallow admin pages, authorized previews, and generated search results; the application still uses page-level robots metadata for filtered archives and other contextual indexing decisions.

## Verification

Pest is the test runner, with Jason McCreary's Double as the project-level test-double library. The base test case disables Laravel's Mockery-backed console-output interception, allowing database refreshes and Artisan setup to run without installing Mockery. Feature tests cover public visibility rules, guide pages, blog filtering, site search, editorial readiness and previews, feeds, sitemap output, contact protection, newsletter protection, Sentry's safe defaults, and Filament access. Pest architecture tests enforce application boundaries and test organization. A separate Pest Browser suite exercises critical public pages, mobile navigation, and the admin login in Chromium on a weekly, manual, and release-tag schedule. Pint formats both PHP and Blade through the always-on `Pint/laravel_blade` rule, backed by the project's locked Prettier dependencies. FilaCheck checks Filament code for deprecated APIs and common implementation issues. Larastan provides level-5 static analysis, and Rector checks Laravel 13 upgrades without modifying code in CI. Laravel Boost supplies project-aware Codex context and tools, while Pao condenses supported command output for agents. CI runs with read-only repository permissions, does not persist checkout credentials, and pins third-party actions to immutable commit SHAs. It validates Composer metadata, audits the locked PHP and Node dependencies, installs dependencies, checks PHP and Blade formatting, checks Filament code, runs Larastan and Rector, runs `composer test`, and builds the frontend. Dependabot vulnerability alerts remain enabled without automated update pull requests; direct dependencies are reviewed monthly and related upgrades are grouped into focused pull requests. Release verification also includes `git diff --check`.
