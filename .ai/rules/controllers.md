---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Use standalone controllers
Controllers are standalone classes and do not extend an empty application base controller. Introduce shared inheritance only when concrete reusable controller behavior earns it.

## Keep controllers as orchestrators
Controllers authorize or reject requests, pass normalized inputs to application/page collaborators, and choose the response. Move page payload assembly to ViewModels and reusable reads to Query or Support classes when controller logic grows beyond simple orchestration.
