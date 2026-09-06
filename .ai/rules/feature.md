---
paths:
  - 'tests/Feature/**'
---

# Feature

## Mirror Feature tests to their source
Feature test paths mirror the owning application entry point, including the class name plus Test.php. Keep HTTP, Artisan, and Livewire workflow coverage here and split files by owner. Move direct component/persistence tests to Integration and isolated logic to Unit. Configuration checks belong in Integration/Config. Preserve existing assertions and dataset cases when reorganizing.

## Organize Feature tests by entry point
Feature tests must exercise an application entry point. Put public HTTP response coverage under the controller that owns the route and Artisan coverage under the owning command; do not organize public Feature tests by Blade view source. Keep direct model, support, composer, and rendering-unit coverage in Integration or Unit.
