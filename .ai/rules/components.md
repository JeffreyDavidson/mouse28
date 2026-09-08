---
paths:
  - '{app/Support/ResponsivePostArtwork.php,app/Console/Commands/GeneratePostArtwork.php,resources/views/components/post-artwork.blade.php}'
---

# Components

## Keep responsive artwork generation outside page requests
Generate post derivatives ahead of time on the public disk; rendering must remain read-only and fall back to originals when candidates are absent or source bytes change. Never overwrite originals. Derivative URLs are immutable: change their path format when changing the transformation algorithm. Production generation is a separately approved operation, not an implicit side effect of deployment or benchmarking.
