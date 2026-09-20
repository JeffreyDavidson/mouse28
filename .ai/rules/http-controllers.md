---
paths:
  - 'tests/Feature/Http/Controllers/**'
---

# Http Controllers

## Keep controller tests focused on orchestration
Controller Feature tests should verify entry-point behavior: route access, authorization and publication guards, redirects, the returned view, and required payload keys. Test detailed payload assembly in the owning ViewModel's Integration tests instead of duplicating those assertions here.

## Use synthetic controller fixtures
Use neutral dummy titles, slugs, bodies, emails, and URLs in controller tests. Retain branded or canonical production values only when the assertion specifically verifies public copy, configured domains, feed URLs, or security behavior.
