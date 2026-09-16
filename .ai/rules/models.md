---
paths:
  - 'app/Models/**'
  - app/Models/ContactMessage.php
---

# Models

## Document attribute scopes for editor discovery
Keep Laravel 12+ scopes as protected `#[Scope]` methods. Add matching `@method static Builder<static> scopeName()` declarations to model PHPDoc when application code calls them statically so editor tooling can resolve the magic builder method.

## Retain contact messages until manual deletion
Jeffrey chose manual retention: do not schedule automatic contact-message pruning. Older messages have unknown email-delivery status; do not treat missing timestamps as proof an email failed or automatically resend historical messages.
