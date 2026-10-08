---
paths:
  - 'app/Models/**'
  - 'app/Enums/**'
  - 'app/Filament/Resources/Episodes/**'
  - 'resources/views/pages/episodes/**'
  - 'config/podcast.php'
---

# Models

## Document attribute scopes for editor discovery
Keep Laravel 12+ scopes as protected `#[Scope]` methods. Add matching `@method static Builder<static> scopeName()` declarations to model PHPDoc when application code calls them statically so editor tooling can resolve the magic builder method.

## Retain contact inquiries until manual deletion
Jeffrey chose manual retention: do not make ContactInquiry prunable or schedule automatic contact pruning, even though The Laravel Architect prunes its inquiries. Older messages have unknown email-delivery status; do not treat missing timestamps as proof an email failed or automatically resend historical messages.

## Keep enum storage values compatible
GuideCategory uses existing database strings with an Eloquent enum cast; the persisted values are `accessibility`, `park-strategy`, `food-reviews`, `family-planning`. ContactType (formerly ContactTopic) keeps the stored values `general`, `accessibility`, `collaboration`, `guest`, `other` and is cast on `ContactInquiry.type`; free-text subjects were folded into `other` (subject prepended to the message) when contact messages became inquiries. ContactInquiryStatus stores `new`, `in_progress`, `resolved`, matching The Laravel Architect. Implement only the Filament presentation interfaces each enum uses.

## Keep Filament enum contract method names
Use natural domain names for application-owned enum methods. Retain `getLabel()` and `getColor()` when implementing Filament's `HasLabel` and `HasColor` contracts; those method names are required for native enum integration.

## Use Transistor as the podcast host
Transistor owns podcast MP3 hosting, the canonical RSS feed, and embedded episode players. Store each episode's https://share.transistor.fm/s/... URL and derive only allowlisted Transistor embed URLs; do not restore site-hosted MP3 upload controls or a generated first-party podcast feed.

## Persist the publish status
Posts, guides, episodes and newsletter issues store `status` as `App\Enums\PublishStatus` (`draft`, `in_review`, `published`, `scheduled`), with The Laravel Architect's `#[PublishingStatus]`, `HasPublishingStatus` and `LocksSlugAfterPublication` copied byte-for-byte (do not reformat or edit them; differences belong in the PR text). Live means status Published or Scheduled and `published_at <= now()`; read it through `isPublished()`, `isScheduled()` and the `published()`, `scheduled()` and `unpublished()` scopes, and treat Draft and In Review as drafts. Change status only through `publish()` / `unpublish()` (the Publish / Unpublish actions); the form's `PublishStatusSelect` offers only pre-publication statuses. Each model keeps its own `publishingIssues()`, which overrides the trait's (TLA's `ContentReadiness` is not ported). The legacy `is_published` flag is gone (a guarded migration dropped it with its sync trait); the status is the only source of truth, so do not add a flag back.

## Write post and guide text to content
Posts and guides store their Markdown in the nullable `content` column (TLA's name; TLA's is NOT NULL). The legacy `body` column is gone (a guarded migration dropped it with its sync trait); do not add it back. Public archives write `content` and still import older archives that carry `body`.

## Relate posts to episodes through episode_post
A post relates to any number of episodes through the `episode_post` pivot (`Post::episodes()` / `Episode::posts()`, TLA's shape). `posts.episode_id` is gone (copied into the pivot, then dropped with its foreign key by a guarded migration); do not add it back. Public pages show published related episodes only. Public archives write `episode_slugs` and still import an older single `episode_slug`.

## Categorize posts through the categories table
A post belongs to at most one `Category` (`categories`: name, unique slug, description; TLA's table) through the nullable `posts.category_id` (`Post::category()`, null on delete). The retired `PostCategory` enum's values are the slugs of the first eleven rows, so `/blog?category=<slug>` URLs are unchanged; any existing category's slug is a valid filter. The legacy `posts.category` string is gone (a guarded migration dropped it with the `IgnoresLegacyCategoryColumn` trait), so `$post->category` is the relation; do not add the column back. Eager-load `category` wherever post cards render (`category_label`, artwork style by slug from `config('mouse28.post_artwork_styles')` through `PostPresenter`, `general` artwork for unmapped slugs; Home's planning categories are `config('mouse28.home_planning_category_slugs')`). Public archives write the category `category` slug and `category_name`, and imports find or create the category by slug. Guides keep the `GuideCategory` enum; `GuideCategory::artworkUrl()` names each category's bundled image.

## Credit posts and guides to author users
Posts and guides credit one or more `User`s through the `post_user` / `guide_user` pivots (`HasAuthors::authors()`, ordered by the pivot `position`; write credits with `syncAuthors()` so the given order becomes the byline order). Only `is_author` users (the `User::authors()` scope) are offered or matched by name; never credit or match the admin login accounts, and never give an author user admin access, a real email or a usable password. The legacy `posts.author` / `guides.author` strings are gone (copied into the pivots, then dropped by a guarded migration) and the `ContentAuthor` enum is retired; do not add them back. Eager-load `authors` wherever `author_name` renders. Public archives write `authors` (names) and still import an older `author` value. This many-to-many shape is mouse28-only; The Laravel Architect keeps a single `posts.user_id`.

## Store cover images as media paths
Posts, episodes and guides use the SEO package's `HasSEO` trait (`ralphjsmit/laravel-seo`): the title, description and robots live in the model's `seo` row, edited through `SEO::make()` in the Filament form and read as `$model->seo?->title` and so on. Do not define `getDynamicSEOData()` (it overrides saved values) and do not add the legacy `meta_title`, `meta_description` or `og_image` columns back (a guarded backfill copied them into the SEO row and a guarded migration dropped them). Use `ScopesMissingSeo::missingSeo()` to find records without a saved title or description, and eager-load `seo` in list queries that show readiness. `HasOgImage` is gone; the share image is the cover.

Posts, episodes and guides store their cover in `featured_image_path` and the podcast in `cover_image_path` (TLA's columns), read through `featured_image_url` (`HasFeaturedImage`) and kept tidy by `ManagesStoredMedia` and the `#[ObservedBy]` observers, which generate responsive variants on save and remove replaced originals and variants after commit. The legacy `cover_image` columns were copied into the new columns with no mirroring hook (owner decision) and have since been dropped by a guarded migration; do not add them back. `HasOgImage` keeps `og_image_url` until the SEO slice. Public archives write `featured_image_path` / `cover_image_path` and still import an older `cover_image` (the new key wins when both exist).

## Generate slugs with the sluggable package
Posts, guides, episodes and newsletter issues use `#[Sluggable(from: 'title', maxLength: 255)]`, categories and podcasts `from: 'name'` (`nunomaduro/laravel-sluggable`, TLA's package). Leave the slug out when creating a record in code (factories, widgets, seeders that don't need a fixed address) and let the package name it; it never rewrites an existing slug, so `LocksSlugAfterPublication` stays the only rule for editing. Keep `maxLength: 255` so generated slugs fit the column. Admin forms use the shared `SlugSourceInput` and `SlugInput` (copied byte-for-byte from TLA) rather than their own title-to-slug hooks or slug rules.

## Keep post review notes private
Posts have TLA's `review_notes`, `reviewed_by` (`Post::reviewer()`, NULL when the user is deleted) and `reviewed_at`. They are private editorial data: never export them in public archives, render them on public pages or overwrite them on import. Nothing sets `reviewed_by` / `reviewed_at` yet (as in TLA); change that in both sites together. Guides have no review fields.

## Use the shared model concerns
Editorial models log through `LogsEditorialActivity`, which logs the whole `$fillable` list; keep anything that must stay out of the activity feed out of `$fillable` (Post's private review fields are logged on purpose). Use `drafts()` for the Draft and In Review list and `newestFirst()` for newest-first ordering instead of writing `whereIn('status', [...])` or `latest('published_at')->latest('id')` again. Post and Guide source review lives in `HasSourceReview`; a model sets its interval and, like Post, narrows which records are tracked.
