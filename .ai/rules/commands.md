---
paths:
  - 'app/{Support,Console/Commands}/**/*PublicContent*.php'
---

# Commands

## Keep staging content sync public-only
Public content archives export only currently published posts, guides, episodes, and non-sensitive podcast display metadata. Imports are idempotent, preserve environment-specific podcast email, and must remain blocked in production.
