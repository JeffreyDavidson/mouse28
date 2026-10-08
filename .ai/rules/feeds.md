---
paths:
  - 'app/Support/Feeds/**'
---

# Feeds

## Render feeds from plain payloads
Classes in app/Support/Feeds turn a plain payload (strings, dates and arrays) into a feed document such as RSS, a sitemap or robots.txt. They know nothing about models, routes or config: a Query reads the records, a ViewModel builds the URLs and payload, and the controller passes `$viewModel->data()` to the renderer. Escape every text value for XML with `ENT_XML1`. The renderers mirror The Laravel Architect's `app/Support/Feeds`; list any difference in the pull request.
