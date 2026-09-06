---
paths:
  - 'tests/Feature/**'
---

# Feature

## Mirror Feature tests to their source
Feature test paths mirror the owning app class, including the class name plus Test.php. Keep HTTP, Artisan, and Livewire workflow coverage here and split files by owner. Move direct component/persistence tests to Integration and isolated logic to Unit. Blade-only Feature coverage uses Views/ with explicit source mappings in tests/Arch/TestOrganizationTest.php; configuration checks belong in Integration/Config. Preserve existing assertions and dataset cases when reorganizing.
