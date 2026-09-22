---
paths:
  - 'tests/Browser/**'
---

# Browser Tests

## Test the real browser boundary

Browser tests cover behavior that only exists in a rendered browser: user interaction, JavaScript, keyboard and focus behavior, responsive layout, accessibility, browser APIs, asset loading, and supported-browser compatibility. Keep HTTP response shape, persistence, validation, authorization, and query behavior in Feature or Integration tests.

## Keep scenarios focused

Name each test for the complete browser behavior it verifies. Split unrelated interactions into separate tests, even when they use the same page. A page matrix is appropriate when every page receives the same rendering constraint; assert the shared constraint once per page rather than duplicating page-specific application assertions.

Use synthetic, minimal fixtures. Create only the records needed to render or interact with the scenario, and set only attributes that affect the browser behavior being asserted.

## Use groups intentionally

`browser-smoke` is reserved for fast, deterministic Chromium coverage of critical public and admin paths. Keep expensive resilience, performance, print, and broad compatibility scenarios outside smoke unless they protect a critical release path. Use `browser-compatibility` only for behavior that must be verified across the supported browser matrix.

## Prefer observable outcomes

Assert what a user can observe: visible text, focus, keyboard interaction, URL state, layout boundaries, accessibility findings, JavaScript errors, and loaded assets. Avoid asserting implementation details unless the browser contract depends on them, such as a responsive image candidate or a specific ARIA relationship.

Keep browser JavaScript helpers small and named after the browser behavior they measure. Do not use browser tests to duplicate unit-level checks for helper functions or server-side tests for controllers and view models.

Test audits follow the shared refactoring guidance in `.ai/rules/tests.md`. For Browser tests, apply it specifically to duplicated page assertions, browser scripts, route matrices, oversized files, and execution groups.
