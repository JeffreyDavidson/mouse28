---
paths:
  - 'resources/{js,views/livewire}/**'
---

# Livewire

## Animate blog filter reflow
Blog filtering must preserve the filter controls' viewport position while retained story cards visibly animate into their new grid positions. Use bounded transform-based motion without a new dependency, and skip spatial card animation when prefers-reduced-motion is active. Browser coverage must prove both paths.
