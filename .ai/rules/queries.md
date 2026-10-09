---
paths:
  - 'app/Queries/**'
---

# Queries

## Keep reusable read composition in Query objects
Use app/Queries for reusable or non-trivial read composition. ViewModels inject and call Queries; controllers and Livewire components never do. ViewModels may perform simple page-local reads, but shared filtering, ordering, pagination, and relationship loading belong in Query objects. This mirrors The Laravel Architect's `.ai/rules/queries.md`.

## Name Query classes after their read responsibility
Name Query classes after the data they return or the read use case they own, followed by Query. Prefer descriptive names such as RssFeedQuery over route-action names such as IndexQuery. Do not use Query objects for writes or unrelated orchestration.

## Keep queries free of HTTP, URLs and display formatting
Queries only read. Return models, collections or paginators, and apply every filter, order and limit inside the query rather than in the caller. Do not abort, read the request, build URLs or set paginator links, use App\Http or App\Filament, or format values for display; the ViewModel adds URLs and the page or presenter formats. Keep queries portable: production runs MySQL and tests run SQLite.
