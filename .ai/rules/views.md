---
paths:
  - resources/views/home.blade.php
  - 'resources/views/**'
---

# Views

## Homepage reflects published records only
Homepage story and guide cards must represent actual currently published records. When content or artwork is absent, render a truthful branded empty state or neutral fallback; never substitute demo titles or unrelated post artwork.

## Use Tailwind utilities in Blade
Use Tailwind utilities directly in Blade for layout, typography, spacing, colors, responsive behavior, and interaction states. Reserve custom CSS for theme tokens, fonts, keyframes, third-party integrations, and behavior utilities cannot express clearly.

## Reuse Blade components for repeated markup
Extract repeated utility-heavy Blade markup into reusable Blade components. Keep public page views and reusable components in their established Mouse28 locations; do not introduce a `pages/` directory solely to match another application.
