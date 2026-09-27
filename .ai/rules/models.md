---
paths:
  - 'app/Models/**'
  - 'app/Enums/**'
  - 'app/Filament/Resources/Episodes/**'
  - 'resources/views/pages/episodes/**'
  - 'config/podcast.php'
---

# Models

## Document attribute scopes for editor discovery
Keep Laravel 12+ scopes as protected `#[Scope]` methods. Add matching `@method static Builder<static> scopeName()` declarations to model PHPDoc when application code calls them statically so editor tooling can resolve the magic builder method.

## Retain contact messages until manual deletion
Jeffrey chose manual retention: do not schedule automatic contact-message pruning. Older messages have unknown email-delivery status; do not treat missing timestamps as proof an email failed or automatically resend historical messages.

## Keep enum storage values compatible
ContentAuthor, PostCategory, and GuideCategory use existing database strings with Eloquent enum casts. The current persisted values are `jeffrey`, `cassie`, `both`; `disney-tips`, `park-accessibility`, `episode-recap`, `family-life`, `autism-awareness`, `disney-news`, `food-reviews`, `resort-reviews`, `disney-plus`, `merchandise`, `general`; and `accessibility`, `park-strategy`, `food-reviews`, `family-planning`, respectively. PublicationStatus is derived from publication fields, not persisted. ContactTopic owns recognized contact choices and labels, but ContactMessage.subject remains a string so existing free-text subjects retain their fallback labels. Implement only the Filament presentation interfaces each enum uses.

## Keep Filament enum contract method names
Use natural domain names for application-owned enum methods. Retain `getLabel()` and `getColor()` when implementing Filament's `HasLabel` and `HasColor` contracts; those method names are required for native enum integration.

## Use Transistor as the podcast host
Transistor owns podcast MP3 hosting, the canonical RSS feed, and embedded episode players. Store each episode's https://share.transistor.fm/s/... URL and derive only allowlisted Transistor embed URLs; do not restore site-hosted MP3 upload controls or a generated first-party podcast feed.
