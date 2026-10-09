---
paths:
  - 'app/Console/Commands/**'
  - 'app/Services/ContentArchive/**'
  - 'resources/content-artwork/**'
  - 'app/Services/*ResponsiveImage*.php'
  - 'resources/views/components/post-artwork.blade.php'
  - 'resources/views/pages/episodes/show.blade.php'
---

# Commands

## Keep staging content sync public-only
Public content archives export only currently published posts, guides, episodes, and non-sensitive podcast display metadata. Imports are idempotent, preserve environment-specific podcast email, and must remain blocked in production.

## Require the staging hostname override for imports
Forge runs staging with APP_ENV=production. `PublicContentImportGuard` decides: imports and syncs are refused in any environment whose config('app.url') host is mouse28.com or www.mouse28.com, and in production they run only when the command receives --staging and the host is exactly staging.mouse28.com. Keep the archive classes in `app/Services/ContentArchive/` (TLA's layout) with mouse28's own format until the shared package converges them.

## Separate active post covers from concepts
Repository-ready post covers live directly in `resources/content-artwork/posts` as `<post-slug>.webp`; `content:attach-artwork` discovers and attaches them automatically. Keep drafts, alternatives, demo artwork, and unassigned concepts in `resources/content-artwork/concepts`, which the command must ignore.

## Keep responsive image generation outside page requests
Responsive variants are generated on save by the model observers (`ResponsiveImageLifecycle`) and by the `*:generate-image-variants` and `media:repair-responsive-images` commands; rendering only checks which variants exist and falls back to the original. Preserve originals. Variants are `{dir}/responsive/{filename}-{width}.webp` for `config('media.responsive_widths')`; posts, guides and the podcast use TLA's `ResponsiveImageVariants` unchanged, episodes use the mouse28-only `SquareResponsiveImageVariants` (centered square crops) so the square frame never shows a stretched wide image. Variant names are not content fingerprints and post variants are served with a one-year immutable edge policy, so never overwrite a stored image or variant in place with different content: store a new file and point the record at it. Production generation and repair runs require separate approval. The legacy content-addressed variants (`posts/responsive/<sha256>-*.webp`, `episodes/responsive/v1/`) are unreferenced and only a guarded, dry-run-first prune may remove them.

## Move to SQLite only through db:copy-to-sqlite
The one-off MySQL → SQLite move uses `db:copy-to-sqlite` (a fresh migrated file, ids kept, every table's count and checksum verified, a failed copy deletes the file). Keep its refusals: an existing target, a relative path, production without `--force`, pending source migrations, and leftover tables that still hold rows. Steps and rollback are in docs/operations.md "Moving the database from MySQL to SQLite". Remove the command once staging and production both run SQLite.

