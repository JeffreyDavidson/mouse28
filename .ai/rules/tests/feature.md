---
paths:
  - 'tests/Feature/**'
---

# Feature Tests

## Mirror Feature tests to their source
Feature test paths mirror the owning application entry point, including the class name plus Test.php. Keep HTTP, Artisan, and Livewire workflow coverage here and split files by owner. Move direct component/persistence tests to Integration and isolated logic to Unit. Configuration checks belong in Integration/Config. Preserve existing assertions and dataset cases when reorganizing.

## Organize Feature tests by entry point
Feature tests must exercise an application entry point. Put public HTTP response coverage under the controller that owns the route and Artisan coverage under the owning command; do not organize public Feature tests by Blade view source. Keep direct model, support, composer, and rendering-unit coverage in Integration or Unit.

## Treat user-facing component workflows as Feature tests
Livewire components and Filament pages are application entry points because users enter their workflows through a mounted component or panel request. Keep their lifecycle, authorization, validation, state changes, redirects, events, and rendered output in Feature tests. Test the underlying models, actions, jobs, and support classes at their narrower Integration or Unit boundaries instead of repeating their implementation details in component tests.

## Keep framework routes at the Feature root
When an HTTP entry point has no application controller, such as a static Route::view page or Laravel's built-in health route, use a clear top-level Feature test such as AboutTest.php or HealthTest.php and add an explicit source mapping. Do not create tests/Feature/Http/Routes.

## Test application-owned health behavior only
Keep Laravel's stock `/up` route as an untested liveness endpoint. Add Feature coverage only when the application attaches custom health behavior; do not test framework defaults.

## Keep controller tests focused on orchestration
Controller Feature tests should verify entry-point behavior: route access, authorization and publication guards, redirects, the returned view, and required payload keys. Test detailed payload assembly in the owning ViewModel's Integration tests instead of duplicating those assertions here.

## Use synthetic controller fixtures
Use neutral dummy titles, slugs, bodies, emails, and URLs in controller tests. Retain branded or canonical production values only when the assertion specifically verifies public copy, configured domains, feed URLs, or security behavior.

## Keep Filament tests focused and synthetic
Name each test for one page or resource workflow, use neutral dummy fixture data unless branding or validation requires a specific value, and create only the records needed for the assertion. Keep page rendering, authorization, validation, actions, and persistence assertions in separate focused tests.

## Chain assertions within each Livewire state
In Livewire Feature tests, chain all assertions that describe the same component state. Start a new assertion chain after each state-changing action so Arrange, Act, and Assert boundaries remain clear.

## Keep Livewire tests focused and efficient
Name each test for one component workflow, use the minimum records needed for that state transition, and chain assertions for the same rendered state. Start a new chain after each set, call, or other state-changing action.

## Invoke Artisan commands through the typed Pest helper
Use the shared pendingCommand() helper for command entry points so command tests retain typed PendingCommand support and fluent console assertions.
