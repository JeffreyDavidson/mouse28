---
paths:
  - 'tests/Unit/**'
---

# Unit Tests

## Keep Unit tests isolated

Unit tests exercise deterministic logic in one class without booting Laravel
or touching factories, databases, facades, HTTP clients, queues, storage,
temporary files, or other external I/O. Move tests that verify persistence,
framework wiring, configuration, or filesystem behavior to Integration.

## Name the exact behavior under test

Test names should identify the class behavior and the complete assertion being
made. Split independent presentation methods or mappings into separate cases;
keep datasets when they describe one behavior across meaningful inputs.

## Prefer direct construction and values

Instantiate the class under test directly, pass synthetic inputs, and assert
its returned value or thrown exception. Avoid testing framework internals or
duplicating Feature and Integration coverage in Unit tests.
