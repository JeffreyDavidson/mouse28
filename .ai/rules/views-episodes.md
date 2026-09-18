---
paths:
  - '{app/Support/ResponsiveArtwork.php,app/Console/Commands/GenerateResponsiveArtwork.php,resources/views/components/post-artwork.blade.php,resources/views/pages/episodes/show.blade.php}'
---

# Views Episodes

## Keep responsive artwork generation outside page requests
Generate derivatives ahead of time; rendering stays read-only with original fallback when candidates are absent or source bytes change. Preserve originals. Post derivatives retain posts/responsive; episode derivatives use centered square crops under episodes/responsive/v1 so the existing square frame does not magnify a downsized wide image. Change URL format when changing transformations. Production generation requires separate approval. The legacy post command defaults to posts; episodes require --type=episodes.
