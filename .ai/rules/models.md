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
Posts, guides, episodes and newsletter issues store `status` as `App\Enums\PublishStatus` (`draft`, `in_review`, `published`, `scheduled`), with The Laravel Architect's `#[PublishingStatus]`, `HasPublishingStatus` and `LocksSlugAfterPublication` copied byte-for-byte (do not reformat or edit them; differences belong in the PR text). Live means status Published or Scheduled and `published_at <= now()`; read it through `isPublished()`, `isScheduled()` and the `published()`, `scheduled()` and `unpublished()` scopes, and treat Draft and In Review as drafts. Change status only through `publish()` / `unpublish()` (the Publish / Unpublish actions); the form's `PublishStatusSelect` offers only pre-publication statuses. Each model keeps its own `publishingIssues()`, which overrides the trait's (TLA's `ContentReadiness` is not ported). Never read or write `is_published`: `SyncsLegacyPublishedFlag` mirrors the status into it on save until a guarded follow-up release drops the column and the trait together.

## Write post and guide text to content
Posts and guides store their Markdown in the nullable `content` column (TLA's name; TLA's is NOT NULL). Never read or write `body`: `SyncsLegacyBody` mirrors `content` into it on save (`content ?? ''`) until a guarded follow-up release drops the column and the trait together. Public archives write `content` and still import older archives that carry `body`.

## Relate posts to episodes through episode_post
A post relates to any number of episodes through the `episode_post` pivot (`Post::episodes()` / `Episode::posts()`, TLA's shape). Never read or write `posts.episode_id`: it was copied into the pivot and stays only until a guarded follow-up release drops it (no mirroring trait, owner decision). Public pages show published related episodes only. Public archives write `episode_slugs` and still import an older single `episode_slug`.

## Categorize posts through the categories table
A post belongs to at most one `Category` (`categories`: name, unique slug, description; TLA's table) through the nullable `posts.category_id` (`Post::category()`, null on delete). The retired `PostCategory` enum's values are the slugs of the first eleven rows, so `/blog?category=<slug>` URLs are unchanged; any existing category's slug is a valid filter. Never read or write the legacy `posts.category` string: `IgnoresLegacyCategoryColumn` removes it from loaded posts so `$post->category` is the relation, with no mirroring hook (owner decision), until a guarded follow-up release drops the column and the trait together. Eager-load `category` wherever post cards render (`category_label`, artwork style by slug, `general` artwork for unmapped slugs). Public archives write the category `category` slug and `category_name`, and imports find or create the category by slug. Guides keep the `GuideCategory` enum.

## Credit posts and guides to author users
Posts and guides credit one or more `User`s through the `post_user` / `guide_user` pivots (`HasAuthors::authors()`, ordered by the pivot `position`; write credits with `syncAuthors()` so the given order becomes the byline order). Only `is_author` users (the `User::authors()` scope) are offered or matched by name; never credit or match the admin login accounts, and never give an author user admin access, a real email or a usable password. Never read or write the legacy `posts.author` / `guides.author` strings: they were copied into the pivots with no mirroring hook (owner decision) and the `ContentAuthor` enum is retired, until a guarded follow-up release drops the columns. Eager-load `authors` wherever `author_name` renders. Public archives write `authors` (names) and still import an older `author` value. This many-to-many shape is mouse28-only; The Laravel Architect keeps a single `posts.user_id`.

## Store cover images as media paths
Posts, episodes and guides store their cover in `featured_image_path` and the podcast in `cover_image_path` (TLA's columns), read through `featured_image_url` (`HasFeaturedImage`) and kept tidy by `ManagesStoredMedia` and the `#[ObservedBy]` observers, which generate responsive variants on save and remove replaced originals and variants after commit. Never read or write the legacy `cover_image` columns: they were copied into the new columns with no mirroring hook (owner decision) until a guarded follow-up release drops them. `HasOgImage` keeps `og_image_url` until the SEO slice. Public archives write `featured_image_path` / `cover_image_path` and still import an older `cover_image` (the new key wins when both exist).
