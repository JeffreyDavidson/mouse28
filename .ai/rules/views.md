---
paths:
  - 'resources/views/**'
---

# Views

## Homepage reflects published records only
Homepage story and guide cards must represent actual currently published records. When content or artwork is absent, render a truthful branded empty state or neutral fallback; never substitute demo titles or unrelated post artwork.

## Use Tailwind utilities in Blade
Use Tailwind utilities directly in Blade for layout, typography, spacing, colors, responsive behavior, and interaction states. Reserve custom CSS for theme tokens, fonts, keyframes, third-party integrations, and behavior utilities cannot express clearly.

## Reuse Blade components for repeated markup
Extract repeated utility-heavy Blade markup into reusable Blade components. Keep custom CSS for theme tokens, fonts, keyframes, third-party integrations, and behavior utilities cannot express clearly.

## Organize public page views under pages
Place public route views under `resources/views/pages`. Keep single pages flat there and group related multi-page features beneath it; retain reusable components, mail, errors, Filament, and Livewire views in their dedicated directories.

## Use one full public footer and newsletter signup
All public pages share the full footer, including Home, Blog and Podcast indexes and detail pages. Keep exactly one newsletter form in that footer and preserve its newsletter anchor, bot protection, named validation bag, old input and feedback. Do not switch footer structure based on content or filtering state or add duplicate page-body signup cards.
