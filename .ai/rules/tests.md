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

## Use Pest Laravel functions at application boundaries
In Feature tests, prefer Pest Laravel's function-style helpers for entering and arranging application workflows: use `actingAs()`, `get()`, `post()`, `put()`, `patch()`, `delete()`, `assertAuthenticated()`, `assertGuest()`, `livewire()`, and related helpers instead of calling the equivalent `$this->` methods. Keep response, session, database, console, and Livewire assertion methods on the objects returned by those helpers. Use `$this` only when a framework API genuinely requires the underlying test instance or when no Pest helper exists.

## Isolate outbound integrations before tests boot
Keep shared Http::preventStrayRequests() enabled; integration tests must explicitly fake outbound Laravel HTTP calls. Neutralize service credentials and monitoring in phpunit.xml using both server entries and forced env entries: forced env alone does not override inherited $_SERVER values read by Laravel. Tests may opt into fake integration settings using config()->set().

## Keep factory setup behavior-focused
When creating models in tests, define only factory attributes that affect the behavior under test or are asserted directly. Let factories provide unrelated defaults instead of restating unused properties.

## Use the smallest effective fixture
Create only the records and files needed to cross the behavior boundary under test. For pagination, use only enough records to reach the next page. Do not invoke unrelated commands or seed extra data merely to establish incidental state.

## Keep test names behavior-specific
Name each test for the complete behavior it verifies. When a test covers unrelated behaviors, split it into focused test cases instead of hiding multiple responsibilities behind a broad name.

## Refactor during test audits

Every test-suite audit should look for safe refactoring opportunities in addition to missing coverage. Check for duplicated setup and assertions, repeated helper logic, repeated datasets or input matrices, oversized test files, misleading names, and fixtures that can be reduced without weakening the scenario. Extract a helper, dataset, shared setup, or focused test file only when it makes the behavior easier to understand; do not add indirection merely to reduce line count. Preserve the existing assertions and execution boundaries while refactoring, then run the affected tests and the complete relevant suite.

## Use Pest plugins deliberately

Use the installed Pest plugins when they improve an existing test boundary: Laravel and Livewire helpers for application entry points, Arch for source contracts, Browser for real-browser behavior, PHPStan and Rector for test analysis, type coverage for declared application types, and mutation testing for focused logic with meaningful tests. Keep snapshots, test-time helpers, TIA, and agent-generation tools optional unless a concrete test need justifies them. Mutation testing runs through the dedicated `test:mutate` Composer script with PCOV in CI; keep its scope focused with `covers()` declarations or explicit class/path filters rather than mutating the entire application by default. Prefer named Composer scripts for recurring suite commands so local execution and CI use the same flags.
