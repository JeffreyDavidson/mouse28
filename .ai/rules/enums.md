---
paths:
  - 'app/Enums/**'
---

# Enums

## Keep enum storage values compatible
ContentAuthor, PostCategory, and GuideCategory use existing database strings with Eloquent enum casts. PublicationStatus is derived from publication fields, not persisted. ContactTopic owns recognized contact choices and labels, but ContactMessage.subject remains a string so existing free-text subjects retain their fallback labels. Implement only the Filament presentation interfaces each enum uses.
