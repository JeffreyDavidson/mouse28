---
name: Mouse28
description: A warm, accessible editorial system for Disney parks stories and the workspace that publishes them.
colors:
  navy: '#1a1040'
  navy-light: '#2d1b69'
  purple: '#5b3e9e'
  purple-light: '#7b5eb5'
  purple-dark: '#3a2370'
  gold: '#d4a843'
  gold-light: '#f0c75e'
  gold-dark: '#b8922e'
  gold-ink: '#805e10'
  cream: '#fef9ef'
  cream-dark: '#f5efe0'
  white: '#ffffff'
  paper-highlight: '#fffdf8'
  paper: '#fffdf7'
  paper-edge: '#d8c9a8'
  folio-navy-light: '#241256'
  folio-navy-dark: '#110a2e'
  folio-shadow: '#050215'
typography:
  heading:
    fontFamily: 'Besley, Georgia, serif'
    fontSize: '1.875rem'
    fontWeight: 600
  panel-heading:
    fontFamily: 'Besley, Georgia, serif'
    fontSize: '1.25rem'
    fontWeight: 600
  page-heading:
    fontFamily: 'Besley, Georgia, serif'
    fontSize: '1.5rem'
    fontWeight: 600
  body:
    fontFamily: 'Poppins, ui-sans-serif, system-ui, sans-serif'
    fontSize: '1rem'
    fontWeight: 400
  label:
    fontFamily: 'Poppins, ui-sans-serif, system-ui, sans-serif'
    fontSize: '0.875rem'
    fontWeight: 500
  scale:
    public-caption: '0.625rem'
    public-micro: '0.65rem'
    public-small: '0.7rem'
    public-body-compact: '0.9375rem'
    public-body-tight: '0.95rem'
    public-body-reading: '1.0625rem'
    public-reading-lead: '1.1rem'
    public-lead: '1.2rem'
    public-section: '1.35rem'
    public-title: '1.65rem'
    public-display-sm: '1.75rem'
    public-display-md: '2.25rem'
    public-display-lg: '2.75rem'
    public-display-xl: '3.25rem'
    public-display-hero: '3.5rem'
    public-display-fluid-min: '4.75rem'
    public-display-fluid-max: '7rem'
rounded:
  hairline: '1px'
  micro: '3px'
  tight: '4px'
  control: '8px'
  soft: '0.35rem'
  field: '0.6rem'
  compact: '0.625rem'
  inset: '12px'
  panel: '16px'
  editorial: '0.875rem'
  pill: '9999px'
spacing:
  compact: '8px'
  related: '12px'
  field: '16px'
  panel: '20px'
  group: '24px'
  section: '32px'
components:
  admin-panel:
    backgroundColor: '{colors.white}'
    textColor: '{colors.navy}'
    rounded: '{rounded.panel}'
    padding: '{spacing.panel}'
  admin-navigation:
    backgroundColor: '{colors.navy}'
    textColor: '{colors.cream}'
  admin-navigation-active:
    backgroundColor: '{colors.cream}'
    textColor: '{colors.navy}'
---

# Mouse28 Design System

## Overview

Mouse28's established direction is **Park-Day Dispatch**: navy framing, cream editorial surfaces, gold details, purple actions, and real family stories. The public site feels like a carefully assembled trip-planning folio. The administration area is the editorial workspace behind that publication.

Use this document for public pages, Filament screens, and shared components. Product scope and content hierarchy live in `PRODUCT.md`; implementation boundaries live in `docs/architecture.md`. The public homepage's composition is recorded separately in `.impeccable/surfaces/resources-views-home-blade-php.md`.

For administrators writing, reviewing, and scheduling content during ordinary daily work, a light reading surface makes long forms easier to scan. Navy navigation provides the brand anchor. Brand expression belongs in typography, surfaces, and selected accents; familiar controls keep the work predictable.

## Colors

The frontmatter records the palette. Public tokens live in `resources/css/app.css`; the matching `mouse-` tokens live in `resources/css/filament/admin/theme.css` for the admin bundle. Keep corresponding values aligned.

- **Navy:** primary text, public framing, and admin sidebar. Light navy supports intentionally dark surfaces.
- **Purple:** interactive emphasis, links, focus, and primary admin actions. Filament generates a coherent shade scale from the brand purple through its native color API.
- **Gold:** brand detail and emphasis against navy. Gold-light works on dark navigation and headers. Use gold-ink for small gold-colored text on cream; the decorative gold is too light for that role.
- **Cream:** main reading and working canvas. Dark cream groups secondary information. White distinguishes editable form surfaces and dashboard panels.
- **Semantic colors:** retain Filament's success, warning, danger, and disabled treatments. Publication states must have text labels as well as color.

Paper-highlight, paper, paper-edge, folio-navy-light, folio-navy-dark, and folio-shadow describe existing public folio treatments, not additional admin action colors. Preserve those public treatments when maintaining shared assets; do not propagate their decorative shadows and layering into the workspace.

Body text needs at least 4.5:1 contrast; large text needs at least 3:1. Check translucent colors against the actual composited background. An unavailable metric displays an em dash and an explanatory label, not a misleading zero.

## Typography

Use **Besley** for the Mouse28 wordmark and editorial headings. Use **Poppins** for navigation, forms, tables, buttons, descriptions, and metrics. Do not introduce Playfair Display or a second admin-only font pairing.

Both entry points import `resources/css/fonts.css`. Font binaries are committed under `public/fonts/mouse28`, served from the same origin at stable URLs, and use `font-display: swap`. These URLs match public preloads and the Vite external asset list. Filament uses `LocalFontProvider`; do not add Google or Bunny font requests.

The public site may use expressive headline scales. Admin headings use fixed rem sizes: a 1.875rem welcome heading and 1.25rem panel headings. Labels and dense metadata use the smaller sans scale. Body copy defaults to 1rem on mobile; 0.875rem is suitable for compact desktop descriptions. Reserve smaller text for timestamps and secondary metadata. Use tabular numerals for counts and dates.

The public article and illustrated-folio styles retain their existing context-specific scales. These are not the admin type ramp: maintain their responsive and print rules in `resources/css/app.css` rather than normalizing the public pages during an admin change.

## Image & Artwork

Images are editorial evidence and atmosphere, not interchangeable decoration. Preserve a clear subject, a deliberate crop, and enough negative space for adjacent or overlaid text. Keep important subjects away from extreme edges because the same source may appear as a wide cover, a 4:3 card, a square tile, or a social preview.

- **Editorial covers:** use original, painterly Mouse28 artwork with storybook warmth and cinematic depth. Favor navy, aubergine, purple, lavender, antique gold, cream, and restrained olive or teal. Do not add readable titles, watermarks, borders, protected characters, logos, recognizable Disney landmarks, or other branded silhouettes.
- **Canonical roles:** bundled post and episode artwork is prepared at `1731×909` (approximately 1.9:1); Filament post covers and custom social images use `1200×630` (the same visual ratio). Episode and podcast covers are square. Guide, archive, and profile imagery may use contextual 4:3, 16:10, 5:4, or portrait crops when the surrounding composition calls for them.
- **Asset boundaries:** keep approved bundled artwork in `resources/content-artwork/posts` or `resources/content-artwork/episodes`; keep concepts in `resources/content-artwork/concepts`; keep public site photography and category artwork under `public/images`. Uploaded covers, generated responsive candidates, social images, and source artwork are distinct roles and should not be silently substituted for one another.
- **Responsive delivery:** use `srcset` and `sizes` for generated candidates, retain the original source, and use `object-cover` only when the component owns the crop. Give images explicit dimensions or a stable aspect-ratio container to prevent layout shift. Hero images may be eager and high priority; other content images should be lazy by default.
- **Accessibility:** artwork that repeats an adjacent story title is decorative and uses an empty `alt`; standalone podcast, profile, or informative images receive a concise description. Branded fallbacks and geometric placeholders remain `aria-hidden` and must never pretend to be the missing photograph.
- **Fallbacks:** missing post artwork uses the category-aware editorial fallback; missing guide artwork uses its category illustration; missing podcast artwork falls back to the canonical Mouse28 show cover. A fallback should communicate the content category without inventing a photograph or claim.
- **Editorial uploads:** accept JPEG, PNG, or WebP where the Filament field permits it, crop to the role's aspect ratio, and preserve the user's original cover. WebP is preferred for committed public artwork and responsive derivatives.

The operational details for generating, attaching, caching, and verifying responsive artwork belong in `docs/operations.md`; component behavior and source-selection rules live with the relevant Blade component and `ResponsiveArtwork` support class.

## Layout

Public pages preserve the blog-first hierarchy: hero, featured post, latest posts, optional guides, podcast, about, newsletter. Real photography and editorial artwork carry the public identity.

The admin keeps Filament's responsive sidebar, topbar, global search, navigation, and page grid. It uses a cream canvas and navy sidebar, with native light-mode forms, menus, notifications, and dialogs. Do not force the framework into dark mode while painting the content light.

The dashboard places the welcome and creation actions first, then the five metrics. Recent Activity and Quick Draft are the first working panels, followed by Upcoming Content and Writing Prompt. Blog Posts precede Episodes and Guides. Guides remain available to editors even when public discovery is disabled.

Dashboard internals use container queries so widgets adapt to the space they actually receive. Metrics reflow from two to five columns; all five occupy one row when sufficient width is available. Long titles wrap; metric labels remain on one line. Small screens stack actions and panels. Keep controls at least 48px on mobile, and allow wide data tables to scroll inside a labeled region without making the entire page scroll horizontally.

## Elevation & Depth

The public folio can use its established paper layering and photography. The admin is flatter: whitespace groups related values, low-opacity navy rules separate records, and a quiet border defines a panel. Preserve native Filament elevation for menus, dialogs, and notifications.

Avoid decorative glows, glass effects, animated sparkles, and lifting every dashboard card on hover. Interactive state transitions should be brief and respect reduced motion. A hover treatment must not imply that a non-interactive metric is clickable.

## Shapes

Keep the established rounded language: compact rounded controls, slightly softer inset surfaces, and 16px panel corners. Use one consistent icon family, Filament's Heroicons. Do not use emoji as interface icons. Decorative icons remain hidden from assistive technology; icon-only actions retain accessible names.

## Components

### Navigation and identity

Use the typographic Mouse28 wordmark in Besley. The illustrated podcast mark belongs to podcast content. Sidebar labels use cream on navy; the current destination uses a cream surface with navy text and icon. Hover and focus remain distinguishable from the active destination. Mobile navigation uses Filament's own drawer and toggle.

### Actions

Use native Filament buttons for admin actions so focus, disabled, loading, and submission states work consistently. Purple identifies the primary action. Neutral buttons carry secondary actions. “New Post” leads the dashboard's creation actions. External destinations identify when they open a new tab.

### Dashboard panels and metrics

Use `resources/views/components/filament/dashboard-panel.blade.php` for panels with a heading, optional explanation, and content. Keep metrics as a compact definition list rather than five decorative icon cards. Display actual data; do not invent trends, percentages, or activity.

### Forms and tables

Keep native Filament field structure, labels, helper text, validation, selects, uploads, rich editors, and action menus. Shared theme overrides may change their surface and typography, but must not flatten semantic error or disabled states. Use light form fields, visible boundaries, and a purple focus indicator. Preserve page headings and table headings for orientation.

### Empty and loading states

Empty states explain what is missing and the next useful action. A publishing calendar with no entries explains how to choose a publish date. Recent Activity explains how to create the first record. Keep Filament's lazy widget placeholders and button progress states. A provider failure is distinct from a successful empty result.

## Do's and Don'ts

- Do preserve keyboard access, readable contrast, meaningful headings, visible focus, mobile sizing, and reduced motion.
- Do use the existing Vite entry points and shared Blade components; keep third-party CSS overrides in the admin theme.
- Do verify desktop and mobile dashboard layouts, a resource table and editor, login, and custom subscriber screens after shared theme changes.
- Do keep public and admin fonts and core palette aligned while allowing different information density.
- Don't hide all Filament page headings globally or override every label with a brand color.
- Don't replace native controls, authorization, MFA, or editorial workflows to achieve a visual effect.
- Don't use decorative gold for small text on light backgrounds, external font CDNs, invented metrics, or production data as test fixtures.
