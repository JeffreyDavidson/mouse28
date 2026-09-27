---
paths:
  - 'app/Console/Commands/**'
  - 'app/Support/**/*PublicContent*.php'
  - 'resources/content-artwork/**'
  - 'app/Support/ResponsiveArtwork.php'
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

## Keep responsive artwork generation outside page requests
Generate derivatives ahead of time; rendering stays read-only with original fallback when candidates are absent or source bytes change. Preserve originals. Post derivatives retain posts/responsive; episode derivatives use centered square crops under episodes/responsive/v1 so the existing square frame does not magnify a downsized wide image. Change URL format when changing transformations. Production generation requires separate approval. The legacy post command defaults to posts; episodes require --type=episodes.
