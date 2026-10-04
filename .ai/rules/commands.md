---
paths:
  - 'app/Console/Commands/**'
  - 'app/Support/**/*PublicContent*.php'
  - 'resources/content-artwork/**'
  - 'app/Services/*ResponsiveImage*.php'
  - 'resources/views/components/post-artwork.blade.php'
  - 'resources/views/pages/episodes/show.blade.php'
---

# Commands

## Keep staging content sync public-only
Public content archives export only currently published posts, guides, episodes, and non-sensitive podcast display metadata. Imports are idempotent, preserve environment-specific podcast email, and must remain blocked in production.

## Require the staging hostname override for imports
Forge runs staging with APP_ENV=production. Public-content imports may bypass that environment label only when the command receives --staging and config('app.url') has the exact host staging.mouse28.com; the production mouse28.com host must remain blocked even with the option.

## Separate active post covers from concepts
Repository-ready post covers live directly in `resources/content-artwork/posts` as `<post-slug>.webp`; `content:attach-artwork` discovers and attaches them automatically. Keep drafts, alternatives, demo artwork, and unassigned concepts in `resources/content-artwork/concepts`, which the command must ignore.

## Keep responsive image generation outside page requests
Responsive variants are generated on save by the model observers (`ResponsiveImageLifecycle`) and by the `*:generate-image-variants` and `media:repair-responsive-images` commands; rendering only checks which variants exist and falls back to the original. Preserve originals. Variants are `{dir}/responsive/{filename}-{width}.webp` for `config('media.responsive_widths')`; posts, guides and the podcast use TLA's `ResponsiveImageVariants` unchanged, episodes use the mouse28-only `SquareResponsiveImageVariants` (centered square crops) so the square frame never shows a stretched wide image. Variant names are not content fingerprints and post variants are served with a one-year immutable edge policy, so never overwrite a stored image or variant in place with different content: store a new file and point the record at it. Production generation and repair runs require separate approval. The legacy content-addressed variants (`posts/responsive/<sha256>-*.webp`, `episodes/responsive/v1/`) are unreferenced and only a guarded, dry-run-first prune may remove them.
