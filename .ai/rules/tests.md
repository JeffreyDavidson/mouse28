---
paths:
  - 'tests/**'
---

# Tests

## Separate tests by execution boundary
Unit tests exercise isolated logic without booting Laravel, using factories, facades, persistence, or external I/O. Integration tests boot Laravel to verify collaborating components, model persistence, configuration, or service adapters directly. Feature tests exercise HTTP, Artisan, and Livewire entry points; a database alone does not determine the suite. Browser tests cover real-browser behavior; Arch covers source contracts. Mirror app class paths within Unit, Integration, and Feature, with explicit non-class source mappings in TestOrganizationTest. Preserve assertions and dataset cases when moving coverage.
