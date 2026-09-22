---
paths:
  - 'tests/Feature/Livewire/**'
---

# Feature Livewire

## Chain assertions within each Livewire state
In Livewire Feature tests, chain all assertions that describe the same component state. Start a new assertion chain after each state-changing action so Arrange, Act, and Assert boundaries remain clear.

## Keep Livewire tests focused and efficient
Name each test for one component workflow, use the minimum records needed for that state transition, and chain assertions for the same rendered state. Start a new chain after each set, call, or other state-changing action.
