---
paths:
  - 'tests/Arch/**'
---

# Architecture Tests

## Enforce durable source contracts

Architecture tests protect conventions that should remain true across implementations: dependency boundaries, class shape, test organization, source locations, and committed asset or stylesheet contracts. Use Arch tests for these repository-wide invariants, not for controller behavior, persistence, validation, or rendered browser behavior already covered by the appropriate execution suite.

## Keep contracts deterministic

Prefer Pest architecture expectations, reflection, and bounded filesystem inspection. Architecture tests must not depend on external services, production data, browser sessions, or mutable runtime state. Give each contract a behavior-specific name and split unrelated contracts into separate tests.

## Audit and refactor contracts

During every Arch audit, check whether each contract still protects a meaningful project decision, whether names describe the enforced rule, and whether duplicated implementation tests belong in another suite. Look for missing contracts around newly established boundaries, but do not encode incidental implementation details. Run the complete `Architecture` suite after changes.
