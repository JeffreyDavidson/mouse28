---
paths:
  - 'resources/content-artwork/**'
---

# Content Artwork

## Separate active post covers from concepts
Repository-ready post covers live directly in `resources/content-artwork/posts` as `<post-slug>.webp`; `content:attach-artwork` discovers and attaches them automatically. Keep drafts, alternatives, demo artwork, and unassigned concepts in `resources/content-artwork/concepts`, which the command must ignore.
