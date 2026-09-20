---
paths:
  - 'tests/Integration/ViewModels/**'
---

# View Models

## Test ViewModel payloads at the Integration boundary
ViewModel Integration tests should cover payload assembly, filtering, pagination, relationships, configuration branches, selected fields, and preview data. Do not mock ViewModels merely to prove a controller called data().
