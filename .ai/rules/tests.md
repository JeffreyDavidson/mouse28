---
paths:
  - 'tests/**'
---

# Tests

## Separate tests by execution boundary
Unit tests exercise isolated logic without booting Laravel, using factories, facades, persistence, or external I/O. Integration tests boot Laravel to verify collaborating components, model persistence, configuration, or service adapters directly. Feature tests exercise HTTP, Artisan, and Livewire entry points; a database alone does not determine the suite. Browser tests cover real-browser behavior; Arch covers source contracts. Mirror app class paths within Unit, Integration, and Feature, with explicit non-class source mappings in TestOrganizationTest. Preserve assertions and dataset cases when moving coverage.

## Name tests for the behavior they assert
Match the class that owns the asserted behavior, not a model used as a fixture or an internal collaborator reached through a request. Page-specific response assertions belong with the corresponding controller, route, HTTP error boundary, or Filament page. Direct composer tests must inspect composer binding. Split mail assertions from model persistence tests. Built-in Filament dashboard access belongs with the application AdminPanelProvider that registers it. Browser scenarios may span pages when testing a shared interaction or rendering constraint.

## Choose model effort for test refactoring scope
Jeffrey prefers GPT-5.6 Terra with Medium reasoning for focused test moves, assertion improvements, and boundary coverage. Recommend GPT-6 Astra with High reasoning for repository-wide test-value audits or changes spanning application architecture; Terra with Low reasoning is sufficient for routine Git publishing steps. Before substantive work, flag a known model mismatch and explain the recommended switch. These are model-selection preferences, not automatic routing: never claim a model switch occurred unless the runtime confirms it, and do not spawn agents solely to switch models.

## Prefer Pest expectations for values
Use Pest's expect() API for value and object assertions. Keep Laravel HTTP response, session, database/model, console, and Livewire assertion APIs when they express framework behavior directly; do not replace those fluent domain assertions with raw content or state checks.

## Isolate outbound integrations before tests boot
Keep shared Http::preventStrayRequests() enabled; integration tests must explicitly fake outbound Laravel HTTP calls. Neutralize service credentials and monitoring in phpunit.xml using both server entries and forced env entries: forced env alone does not override inherited $_SERVER values read by Laravel. Tests may opt into fake integration settings using config()->set().
