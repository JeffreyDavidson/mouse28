---
name: mouse28-visual-fidelity
description: Rebuild or refine Mouse28 public Blade and Tailwind interfaces against an approved visual comp with measured screenshot comparisons. Use when implementing a Mouse28 mockup, correcting visual drift, or judging whether a rendered page faithfully matches its reference; do not activate for backend-only work, Filament administration, or editorial artwork generation.
---

# Mouse28 Visual Fidelity

Turn an approved Mouse28 visual reference into a faithful, production-safe interface. Treat the reference as a measurable composition target, not loose inspiration, while preserving real content, accessibility, and Laravel behavior.

## Coordinate the Available Skills

- Use `impeccable` as the visual-design authority and load its relevant workflow.
- Use `tailwindcss-development` for Blade and Tailwind CSS implementation.
- Use `browser:control-in-app-browser` to inspect the rendered application and capture comparison screenshots.
- Use `pest-testing` when adding or changing tests, including browser coverage.
- Use `laravel-best-practices` only when the work changes PHP or Laravel data flow.
- Use `design-taste-frontend` for the final anti-template craft review.
- Use `ui-sh` only when the user requests it or a specific implementation pattern is useful. It must not replace the approved reference as visual authority.
- Use `imagegen` or `mouse28-blog-artwork` only when the task explicitly requires a missing raster asset or post cover.

## Establish the Fidelity Contract

Before editing:

1. Read the repository guidance, `.ai/rules/index.md`, matching rules, `PRODUCT.md`, and the relevant Impeccable surface brief.
2. Inspect the approved reference at original resolution and record its pixel dimensions.
3. Inspect the existing route, Blade view, shared layout, CSS, real content flow, and available image assets.
4. Identify the viewport represented by the reference. Use that exact CSS viewport for the primary desktop comparison.
5. Separate intentional differences from defects. Real titles, dates, routes, and accessibility requirements outrank fictional comp copy; composition, scale, hierarchy, overlap, crop, and material treatment remain fidelity targets.

Read [references/review-checklist.md](references/review-checklist.md) before implementation or critique.

## Implement by Visual Slice

Work from the top of the page downward in coherent slices. A slice usually contains one composition such as header and hero, featured and latest stories, guides, podcast, about, newsletter, or footer.

For each slice:

1. Match the large geometry first: container width, columns, section height, alignment, overlap, and negative space.
2. Match typography and image crop next.
3. Add physical depth, borders, texture, decorative routes, tabs, and small details last.
4. Render the real page before continuing. Do not polish details on top of an incorrect structure.

Prefer rebuilding an incorrect composition over accumulating offsets that only work at one viewport. Reuse existing components and design tokens when they support the reference; do not preserve an incumbent layout merely because it already exists.

## Render and Compare

Capture a fresh full-page screenshot after every material slice at the exact reference viewport. Wait for fonts, layout, and above-the-fold images to settle; scroll through the page before the final capture so lazy-loaded images are present.

Create objective comparison artifacts with:

```shell
bash .agents/skills/mouse28-visual-fidelity/scripts/compare-visuals.sh REFERENCE.png CURRENT.png OUTPUT_DIRECTORY
```

Inspect all three outputs:

- `side-by-side.png` for composition and rhythm.
- `overlay.png` for alignment, scale, and vertical drift.
- `difference.png` for areas that changed most.

Classify visible deltas as structural, refinement, or intentional. Fix structural differences before advancing to another slice. Never claim pixel-perfect fidelity when dynamic content or responsive adaptation makes that untrue.

## Responsive and Accessible Adaptation

The desktop comp does not authorize shrinking the page mechanically on mobile. Preserve its editorial folio character while giving content a deliberate single-column reading order.

- Keep semantic landmarks, heading order, keyboard access, visible focus, contrast, reduced-motion behavior, and 48-pixel mobile controls.
- Avoid excessive mobile page length when a compact horizontal or indexed treatment preserves clarity.
- Verify long real titles, missing optional records, validation states, and image fallbacks.
- Keep the blog-first order: Hero, Featured post, Latest posts, Guides teaser, Podcast, About, Newsletter.

## Completion Gate

Do not call the interface complete until all of the following are true:

- A fresh rendered screenshot exists at the reference viewport and at a representative mobile viewport.
- Side-by-side and overlay comparisons show no unexplained structural drift.
- Real dynamic content and empty states both remain coherent.
- Relevant Pest tests pass, the frontend build passes, and `git diff --check` passes.
- The Impeccable detector has been run once over the finished changed targets.
- The user is shown the current render and the reference before approval.

Preserve unrelated working-tree changes. Do not commit, push, merge, deploy, or mutate production unless separately authorized.

## Mouse28 Blade Formatting Safeguard

Format changed Blade files one at a time with `env -u FORCE_COLOR vendor/bin/pint --blade PATH`. Do not pass multiple Blade files or use `--dirty` for Blade formatting in this repository; the current formatter workflow has previously overwritten one changed Blade file with another.
