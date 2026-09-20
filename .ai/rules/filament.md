---
paths:
  - 'tests/Feature/Filament/**'
---

# Filament

## Keep Filament tests focused and synthetic
Name each test for one page or resource workflow, use neutral dummy fixture data unless branding or validation requires a specific value, and create only the records needed for the assertion. Keep page rendering, authorization, validation, actions, and persistence assertions in separate focused tests.
