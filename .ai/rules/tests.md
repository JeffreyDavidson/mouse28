---
paths:
  - 'tests/**'
---

# Tests

## Separate tests by execution boundary
Unit tests exercise isolated logic without booting Laravel, using factories, facades, persistence, or external I/O. Integration tests boot Laravel to verify collaborating components, model persistence, configuration, or service adapters directly. Feature tests exercise HTTP, Artisan, and Livewire entry points; a database alone does not determine the suite. Browser tests cover real-browser behavior; Arch covers source contracts. Mirror app class paths within Unit, Integration, and Feature, with explicit non-class source mappings in TestOrganizationTest. Preserve assertions and dataset cases when moving coverage.

## Name tests for the behavior they assert
Match the class that owns the asserted behavior, not a model used as a fixture or an internal collaborator reached through a request. Page-specific response assertions belong with the corresponding controller or Filament page. Shared layout output belongs with its Blade source mapping; direct composer tests must inspect composer binding. Split mail assertions from model persistence tests. Built-in Filament dashboard access belongs with the application AdminPanelProvider that registers it. Browser scenarios may span pages when testing a shared interaction or rendering constraint.
