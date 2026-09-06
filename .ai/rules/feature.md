---
paths:
  - 'tests/Feature/**'
---

# Feature

## Mirror Feature tests to their source
Feature test paths mirror the owning application entry point, including the class name plus Test.php. Keep HTTP, Artisan, and Livewire workflow coverage here and split files by owner. Move direct component/persistence tests to Integration and isolated logic to Unit. Configuration checks belong in Integration/Config. Preserve existing assertions and dataset cases when reorganizing.

## Organize Feature tests by entry point
Feature tests must exercise an application entry point. Put public HTTP response coverage under the controller that owns the route and Artisan coverage under the owning command; do not organize public Feature tests by Blade view source. Keep direct model, support, composer, and rendering-unit coverage in Integration or Unit.

## Keep framework routes at the Feature root
When an HTTP entry point has no application controller, such as a static Route::view page or Laravel's built-in health route, use a clear top-level Feature test such as AboutTest.php or HealthTest.php and add an explicit source mapping. Do not create tests/Feature/Http/Routes.
