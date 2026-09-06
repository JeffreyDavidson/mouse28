---
paths:
  - 'tests/Feature/**'
---

# Feature

## Mirror Feature tests to their source
Feature test paths mirror the owning app class, including the class name plus Test.php. Split mixed controller, model, support, command, and Filament page coverage by owner. Blade-only and configuration tests use Views/ and Config/ with explicit source mappings in tests/Arch/TestOrganizationTest.php; do not invent application classes to house those tests. Preserve existing assertions and dataset cases when reorganizing.
