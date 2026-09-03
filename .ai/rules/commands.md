---
paths:
  - 'app/{Support,Console/Commands}/**/*PublicContent*.php'
---

# Commands

## Keep staging content sync public-only
Public content archives export only currently published posts, guides, episodes, and non-sensitive podcast display metadata. Imports are idempotent, preserve environment-specific podcast email, and must remain blocked in production.

## Require the staging hostname override for imports
Forge runs staging with APP_ENV=production. Public-content imports may bypass that environment label only when the command receives --staging and config('app.url') has the exact host staging.mouse28.com; the production mouse28.com host must remain blocked even with the option.
