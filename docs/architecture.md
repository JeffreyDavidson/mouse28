# Mouse28 Architecture

## Public application

Public routes are defined in `routes/web.php` and rendered with Blade through the shared `layouts.app` layout. Tailwind, Alpine, and public JavaScript are delivered by the existing Vite entry points.

The homepage is assembled by `HomeController` in this order: hero, featured post, latest posts, optional guides teaser, podcast, and about, followed by the shared footer newsletter. Community Stories are intentionally not part of the application.

## Content boundaries

- `Post` owns blog articles and optional episode relationships.
- `Guide` owns durable resources grouped by accessibility, park strategy, food and reviews, or family planning. Guides support source URLs and review dates because park policies can change.
- `Episode` owns podcast installments, Transistor share URLs, distribution links, show notes, and transcripts. A validated Transistor share URL is converted to the provider's single-episode embed URL. Legacy hosted and external audio fields remain readable for compatibility but are no longer part of the editing or listening workflow.
- `Podcast` owns the single show-level metadata record.
- `ContactMessage` stores contact submissions for the admin inbox.

`ContentAuthor`, `PostCategory`, and `GuideCategory` are backed enums cast by Eloquent; their stored strings and archive values remain unchanged. `PublicationStatus` represents derived editorial state without another database column. `ContactTopic` supplies recognized dropdown choices and labels, while contact subjects remain strings to preserve free-text messages.

The `published` model scopes require both an enabled publication flag and a non-future publication date. Public detail controllers enforce the same rule so direct URLs cannot expose drafts or scheduled content.

Site search is handled by the invokable `SearchController`. It searches posts and episodes independently, limits each result group, and always begins with the models' `published` scopes so drafts and scheduled content remain private. Search result pages are marked `noindex,follow` and are not included in the sitemap.

Guides are retained as a dormant feature. `GUIDES_ENABLED` defaults to `false`; while disabled, guide archives and detail routes return 404 responses and guides are omitted from the homepage, shared navigation, search, and sitemap. Filament management and authorized preview routes remain available so the editorial library can be prepared before the public launch.

`ContentContinuation` keeps detail pages connected without exposing unpublished records. Guide recommendations prioritize the current category and fill remaining spaces with recent guides, while episode pages link to the chronologically adjacent published episodes. Article and guide category labels link back to their filtered indexes.

Public detail pages emit Schema.org JSON-LD through `StructuredData`: `BlogPosting` for posts, `Article` for guides, `PodcastEpisode` for episodes, and `BreadcrumbList` navigation for each type. JSON output is hex-escaped before it is placed in the document head.

The public layout normalizes canonical and social-image URLs to absolute URLs. Landing pages provide page-specific search and social descriptions. Category archives and paginated archives use self-referencing canonicals, while text searches and alternate sort views are marked `noindex,follow` and canonicalized to the clean archive URL.

## Administration and authorization

Filament is mounted at `/admin`. `User::canAccessPanel()` requires the explicit `is_admin` flag. Model policies provide the same boundary for content resources and protected record actions. Custom settings and subscriber pages also enforce administrator access.

`EditorialReadiness` provides shared publication status and readiness rules for posts, guides, and episodes. Filament list tabs and filters separate drafts, scheduled and published content, incomplete records, and review-due material. Editors publish and unpublish from explicit edit-page actions; publication is blocked until required editorial content, artwork, and SEO metadata are complete, and a blank publish date is filled automatically. Episode audio and transcripts are tracked as availability information but intentionally do not block publication while the original recordings are unavailable.

Filament global search covers posts, guides, episodes, and contact messages. The dashboard surfaces review-due counts for both sourced posts and guides so time-sensitive information returns to the editorial queue.

Content and social artwork uses a 1200:630 aspect ratio. Filament restricts uploads to common web image formats, caps them at 5 MB, and crops and resizes them through its native uploader. The idempotent `content:attach-artwork` command discovers direct WebP files under `resources/content-artwork/posts`, matches each filename to a post slug, and connects bundled episode artwork to its known slug only when a record does not already have a cover. Unpublished visual concepts are preserved separately in `resources/content-artwork/concepts` and are never copied or attached by that command.

Production is the source of truth for published editorial content. `PublicContentArchive` exports only published posts, guides, episodes, and public podcast display metadata; its sync mode reconciles posts and guides by slug and episodes by their unique episode number while preserving local drafts and scheduled content. The local-only `content:sync-production` command retrieves that archive over SSH, validates every referenced public-storage path, transfers referenced cover, social, and legacy hosted-audio files, and refuses to run in production. Transistor-hosted MP3 files stay outside this sync because the episode share URL is the website's integration point. Users, subscribers, contact submissions, credentials, and environment-specific podcast email remain outside the archive.

Posts, guides, and episodes use Eloquent soft deletion. Deleted content disappears from public queries immediately, while Filament administrators can filter the trash, restore records, or explicitly confirm a permanent deletion. Resource route binding includes trashed records only inside the authorized panel recovery flow.

Each content edit page links to an administrator-authorized preview route. Preview pages reuse the public templates, carry a visible preview banner, emit `noindex,nofollow`, and omit structured data. Every preview is authorized through its model policy, so drafts are not exposed by knowing their URL.

The migration that introduces `is_admin` promotes existing accounts because every existing account had panel access under the previous behavior. New accounts default to non-administrators.

## External integrations

Newsletter subscriptions are sent to the configured Resend audience. The public endpoint is protected by a honeypot, Turnstile verification, validation, and an IP rate limit. A shared `ResendAudience` reader supplies both the admin subscriber page and dashboard from one five-minute cache. The page can explicitly refresh that cache, reports provider failures separately from an empty audience, and exports spreadsheet-safe CSV values.

Contact submissions use a separate Turnstile action and rate limiter, store the message, and send administrator and sender emails. Provider failures are logged without exposing submitted data or provider response bodies.

Contact and newsletter submissions use separate named validation bags, and their views only restore old input when feedback belongs to that form. Newsletter return URLs are restricted to the configured application origin, with the homepage footer's `#newsletter` anchor as the fallback.

Unhandled exceptions are registered with the Sentry Laravel integration in `bootstrap/app.php`. Monitoring is opt-in through a production-only DSN; the committed defaults disable tracing, profiling, and default personally identifiable information. Deployments may identify events with `SENTRY_ENVIRONMENT` and `SENTRY_RELEASE` without placing credentials in source control.

The blog RSS feed and sitemap are generated by controllers. Transistor owns the canonical podcast feed configured by `PODCAST_RSS_URL`; public discovery points directly to it, while the legacy `/rss/podcast` route returns a permanent redirect. Reading a feed does not create database records.

Public pages render through anonymous `x-layouts.app` and `x-layouts.error` Blade components. Page views declare their document metadata as layout attributes, and the layout components pass those values to Laravel Head before rendering its `@head` output. This keeps titles, descriptions, canonical URLs, robots directives, Open Graph data, Twitter cards, and feed discovery out of Blade section inheritance while preserving server-rendered metadata.

The blog archive is a class-based Livewire component. Search, category, sorting, and pagination update the archive in place, preserve the reader's scroll position, and remain synchronized with browser-history query parameters so filtered views can be linked and revisited. The server-rendered links and forms remain usable without JavaScript. The existing Vite entry point loads Livewire and its bundled Alpine runtime only for the blog archive; other public pages retain the smaller standalone Alpine bundle.

`PodcastComposer` supplies show-level metadata to the shared public layout component, while episode ViewModels supply it to episode content views. The homepage receives its shared podcast links from the layout composer. Apple Podcasts, Spotify, and YouTube links configured in Podcast Settings are the public source of truth and are omitted when unavailable. Episode-specific destinations override show-level links on episode pages. RSS always points to the configured Transistor feed. These read paths share a request-scoped `Podcast::info()` result and never create a settings row.

Guide freshness is controlled by `GUIDE_REVIEW_INTERVAL_DAYS`, with a 180-day default. Missing or older review dates are surfaced to editors and produce a public verification notice because accessibility and park-operation details can change.

## Frontend and accessibility

The public layout provides a semantic header, primary navigation, skip link, main landmark, footer, visible focus styles, responsive controls, and reduced-motion behavior. Mobile interactive targets should remain at least 48 pixels. User-authored Markdown is rendered with embedded HTML stripped and unsafe links disabled.

All main public pages share the full footer previously used on About and Contact, including Home, the blog archive and articles, and the podcast archive and episodes. Its single newsletter form owns the `#newsletter` anchor; page bodies do not repeat the signup form. Existing bot protections, validation, and submission feedback remain shared.

The About opening groups the headline and introduction on the left and the existing uncropped 4:3 safari photograph on the right at desktop widths. Mobile stacks the text before the photograph. The page retains its copy, search metadata, and lower editorial content.

HTTP 404, 419, 500, and 503 responses use branded recovery pages. Their lightweight standalone layout avoids session, database, and shared-view dependencies so failures remain renderable. Error pages are `noindex,nofollow`, omit canonical URLs, and never render exception messages. Unknown routes and unpublished content receive the same 404 response so private titles are not disclosed.

Global response middleware adds `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, and a restrictive camera, geolocation, and microphone `Permissions-Policy` to public, admin, feed, and error responses. HSTS is left to the HTTPS deployment edge, and a content security policy is not declared until the public and Filament script requirements can be expressed without unsafe fallbacks.

The framework health endpoint provides a lightweight application liveness check at `/up`. Database readiness is verified separately through migration status and smoke checks of database-backed pages. The `app:verify-production` command validates required production configuration without printing secret values. Demo content cleanup is restricted to known seed slugs, runs in one database transaction, and requires an explicit `--force` flag in production.

`AppServiceProvider` enables Laravel's `DB::prohibitDestructiveCommands()` in production. This blocks `db:wipe`, `migrate:fresh`, `migrate:refresh`, `migrate:reset`, and `migrate:rollback`, including forced invocations. Normal forward migrations remain available. The guard follows `APP_ENV`, so Forge staging with `APP_ENV=production` is protected too; it is not a blanket prohibition on custom content commands.

Admin, preview, and error responses also emit `X-Robots-Tag: noindex, nofollow` and `Cache-Control: no-store, private`, preventing crawlers and shared caches from retaining private or failure-specific content. The dynamic and static robots policies both disallow admin pages, authorized previews, and generated search results; the application still uses page-level robots metadata for filtered archives and other contextual indexing decisions.

## Verification

### Public-page performance benchmark

Run `npm run benchmark:pages -- --base https://mouse28.test --profile slow-mobile --runs 3 --output /tmp/mouse28-mobile.json` against a built, content-populated local site. Run the same command with `--profile desktop` separately. Select a staging or production origin explicitly; the command never deploys, authenticates, or submits forms. It visits the public landing pages, searches for “disney”, checks the dormant guides route, and discovers one published detail page per enabled content type. `--paths /blog,/episodes` narrows a repeat run; never silently compare runs that discovered different content.

Each repeat uses a new browser context for a cold load, then revisits the same URL with its browser cache intact. Cold means **browser** cold, not a flushed server/CDN cache. Slow-mobile conditions are 390×844, DPR 2, 4× CPU slowdown, 150 ms added latency, 200,000 bytes/sec down and 93,750 up. Desktop is 1440×1000/DPR 1 without throttling. Measurements cover a fixed ten-second window after DOMContentLoaded rather than waiting indefinitely for third-party network idleness. The runner reports LCP, FCP, TTFB, session-window CLS, long-task blocking time, observed request/transfer totals, first-party cache headers, incomplete visible images, and error origins. Its blocking-time sum is not Lighthouse TBT, and it does not measure field INP. CDP transfer totals can exclude out-of-process third-party iframe traffic. A null load time or incomplete visible image means the sample did not settle within the window.

Keep browser version, hardware, content, build, viewport, network conditions and origin constant for before/after comparisons. Use three runs and review medians and outliers; do not run browser benchmarks concurrently with builds/tests or other benchmarks. Local Herd and production differ in latency, compression, monitoring and third-party configuration: their timings are not a controlled before/after result. Reports omit cookies, response bodies, console text and third-party URL query strings. Store raw reports as review artifacts, not committed logs.

Initial production reconnaissance (2026-09-07 America/New_York, Chromium, one slow-mobile sample per path; **not** a field percentile or a release gate):

| Page | Cold LCP | Warm LCP |
| --- | ---: | ---: |
| Home | 4.68 s | 0.58 s |
| Blog archive | 8.87 s | 0.51 s |
| Podcast archive | 6.76 s | 0.56 s |
| About | 2.89 s | 0.54 s |
| Contact | 1.31 s | 0.53 s |
| Search | 1.32 s | 0.59 s |
| Park bag article | 1.36 s | 0.62 s |
| Trailer episode | 4.01 s | 0.55 s |

All enabled pages returned 200 with no uncaught application JavaScript errors and negligible CLS. `/guides` correctly returned 404. Cloudflare challenge console messages are tracked separately. Five archive covers accounted for about 1.3 MB; the podcast archive requested a 977 KB JPEG despite an existing 103 KB WebP. Warm reuse was effective, but sampled first-party images had no explicit Cache-Control. Local request diagnostics found roughly 1–3 ms total SQL per page with the current content; this does not establish production database timing or performance at larger scale.

Working performance targets: median cold LCP ≤2.5 s, CLS ≤0.1, median warm LCP ≤1 s, no uncaught application errors or broken visible media. These are improvement targets under the fixed profile, not claims that every page already meets them. Flag a repeatable >10% timing or transfer regression for review. Enforce deterministic query budgets in CI now; use timing targets as review evidence until controlled staging runs are stable. Field INP ≤200 ms remains a future real-user measurement, not a synthetic typing-to-response timer.

Final local verification on Chromium 151.0.7922.34: the blog's three-run slow-mobile medians were 1.22 s cold LCP, 0.31 s warm LCP and 535 KB observed cold transfer, with zero CLS. A preceding three-run podcast archive check was 1.42 s cold / 0.22 s warm. Final one-run desktop checks measured 0.68 s blog LCP (CLS 0.017) and 0.32 s podcast LCP (CLS <0.001). No uncaught JavaScript errors occurred. These local results are not production improvement percentages. Sanitized production reconnaissance, local reconnaissance and final repeat reports are attached to Hermes task `t_3dac2a20`. Keep an explicit `--revision` label with subsequent benchmark runs; do not substitute the local checkout SHA for an unknown deployed revision.

Public Feature tests named `query budget` seed larger content sets and assert SQL counts with array-backed sessions/cache: home 3 (guides disabled), blog archive 4, article with linked episode 4 (same-category recommendations), podcast archive 3, episode 5, guide archive/detail 3 (same-category recommendations), search 4 (guides enabled), about/contact 1. Recommendation fallback can add a query. These counts include the layout's single request-memoized podcast read, but not production database-session overhead. Run `php artisan test --compact --filter='query budget'` after changing page payloads. Homepage featured/latest cards share one limited query; publication scopes stay live instead of serving stale cached content.

### Responsive post artwork

`content:generate-artwork --type=posts` generates 480/640/768/1280-pixel WebP candidates from currently published post covers on the local public disk; `--type=episodes` explicitly selects published episode covers. The default remains posts, and `content:generate-post-artwork` remains a compatible alias. Both use the same generator. The 640-pixel candidate serves cards around 320 CSS pixels wide on 2× displays without requiring a 768-pixel download. Rerun the generator to add it; existing derivatives and URLs remain unchanged. Post variants retain `posts/responsive/`; episode variants use `episodes/responsive/v1/` with a centered square crop matching the existing hero frame. The crop happens before resizing and never upscales the shorter source edge. Square candidate lists exclude the differently shaped original; the original remains the fallback `src` when candidates are absent. It never changes original files or model records, never upscales, and uses SHA-256 source-content fingerprints in derivative filenames. WebP generation requires GD, is bounded to 5 MB/12-megapixel inputs, and runs outside HTTP requests. Existing derivatives are reused and completed files are atomically replaced into place. A change to the transformation algorithm must also change the derivative path format to preserve immutable URLs.

The shared post-artwork component and episode detail hero use `ResponsiveArtwork` to emit `srcset` only for candidates that exist. Post candidates include the original for larger displays; square episode candidates exclude the differently shaped original while retaining it as the fallback `src`. Both fall back to the original when variants are unavailable or source bytes change. Request rendering hashes source bytes to avoid stale variants after an in-place replacement; it performs no database queries or image transformations. The archive's featured cover is eager/high priority; below-fold cards remain lazy. Article heroes use the same responsive component. Episode heroes retain high priority and explicit sizing for their existing square frame. Podcast fallback artwork reuses the existing WebP; uploaded show-level fallback artwork and social-image URLs remain unchanged.

Lazy cards use native `sizes="auto"` with a responsive fallback list so the browser can select a candidate using the actual rendered card width; eager heroes use explicit sizes. This follows the [HTML responsive-image sizing rules](https://html.spec.whatwg.org/multipage/images.html#sizes-attributes). The full PHP suite has an explicit test-only 512 MB process budget; it does not change application or production PHP limits.

Generate variants after local public-content sync and after publishing/replacing covers. Production generation uses Laravel's `ConfirmableTrait`: it requires affirmative interactive confirmation or an explicit `--force`, and noninteractive execution without force cancels. Operational approval is still required before running it on production; until then, the new markup safely serves originals. Derived files are additive and are not automatically pruned. Uploaded show-level artwork, explicit edge asset-cache headers, scheduled staging benchmarks and privacy-reviewed field metrics remain follow-up opportunities; do not introduce whole-page shared caching without separating CSRF/session/form-feedback state and publication invalidation.

Pest is the test runner; tests use Laravel facade fakes where needed. The base test case disables Laravel's Mockery-backed console-output interception, allowing database refreshes and Artisan setup to run without installing Mockery. Unit tests exercise isolated logic without booting Laravel. Integration tests exercise model persistence, editorial readiness, archive imports, service caching, and application configuration directly. Feature tests exercise HTTP, Artisan, and Livewire workflows, including public visibility, validation, previews, feeds, and Filament access. Each of these suites mirrors its owning application classes; mixed coverage is split by execution boundary. `composer test` runs all three plus Architecture. Pest architecture tests enforce application boundaries and test organization. A focused Pest Browser group exercises critical interactions and validation focus in pull-request and main-branch CI. The full Chromium suite runs weekly, manually, and for release tags; Firefox and WebKit run the shared compatibility group, including live filtering, mobile navigation, keyboard controls, and native print-media checks. Pint formats both PHP and Blade through the always-on `Pint/laravel_blade` rule, backed by the project's locked Prettier dependencies. FilaCheck checks Filament code for deprecated APIs and common implementation issues. Larastan provides level-5 static analysis, and Rector checks Laravel 13 upgrades without modifying code in CI. Laravel Boost supplies project-aware Codex context and tools, while Pao condenses supported command output for agents. CI runs with read-only repository permissions, does not persist checkout credentials, and pins third-party actions to immutable commit SHAs. It validates Composer metadata, audits the locked PHP and Node dependencies, installs dependencies, checks PHP and Blade formatting, checks Filament code, runs Larastan and Rector, runs `composer test`, and builds the frontend. Dependabot vulnerability alerts remain enabled without automated update pull requests; direct dependencies are reviewed monthly and related upgrades are grouped into focused pull requests. Release verification also includes `git diff --check`.
