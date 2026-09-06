---
paths:
  - 'app/Enums/**'
---

# Enums

## Keep enum storage values compatible
ContentAuthor, PostCategory, and GuideCategory use existing database strings with Eloquent enum casts. PublicationStatus is derived from publication fields, not persisted. ContactTopic owns recognized contact choices and labels, but ContactMessage.subject remains a string so existing free-text subjects retain their fallback labels. Implement only the Filament presentation interfaces each enum uses.

## Keep Filament enum contract method names
Use natural domain names for application-owned enum methods. Retain `getLabel()` and `getColor()` when implementing Filament's `HasLabel` and `HasColor` contracts; those method names are required for native enum integration.
