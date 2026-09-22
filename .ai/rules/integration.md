---
paths:
  - 'tests/Integration/**'
---

# Integration Tests

## Test collaborating application services directly

Integration tests boot Laravel and exercise a concrete application class with
its real collaborators, persistence, configuration, or service adapters. Keep
HTTP, Artisan, Livewire, and Filament entry-point behavior in Feature tests;
do not repeat their response or rendering assertions here.

## Keep each case at one behavior boundary

Name tests after the complete behavior they verify. Use the smallest fixture
that crosses that boundary, assert only values that prove the behavior, and
split unrelated configuration branches or payload responsibilities into
separate cases.

## Prefer deterministic, synthetic fixtures

Use factories and test-only values. Override only attributes that affect the
behavior or are asserted directly. Fake outbound HTTP before resolving the
service under test, and never depend on production content or credentials.

## Protect useful integration guarantees

When a class intentionally selects a reduced column set, caches within a
request, or branches on configuration, keep a focused assertion for that
guarantee. Do not add query-count assertions to ordinary cases unless query
count is the behavior being protected.
