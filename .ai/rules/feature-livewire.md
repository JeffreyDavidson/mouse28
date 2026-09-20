---
paths:
  - 'tests/Feature/Livewire/**'
---

# Feature Livewire

## Chain assertions within each Livewire state
In Livewire Feature tests, chain all assertions that describe the same component state. Start a new assertion chain after each state-changing action so Arrange, Act, and Assert boundaries remain clear.
