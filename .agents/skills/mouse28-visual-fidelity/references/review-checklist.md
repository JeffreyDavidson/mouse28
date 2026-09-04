# Visual Fidelity Review Checklist

Use this checklist for each implemented slice and for the final full-page review.

## Structure

- Page canvas, maximum width, outer gutters, and header height match the reference.
- Major columns have the correct proportional widths.
- Section starts and ends occur at comparable vertical landmarks.
- Intended overlaps, rotations, layering, and clipping are preserved.
- The primary action remains dominant and the blog-first content order is unchanged.

## Typography

- Heading and body families, weights, sizes, line heights, and tracking match the design system.
- Important headings wrap at comparable points without hard-coded line breaks that break real content.
- Long titles and metadata do not change card geometry unpredictably.
- Text remains readable at 200% zoom and on narrow screens.

## Imagery

- The correct source asset is used for the content it represents.
- Crop, focal point, aspect ratio, and rendered scale match the reference.
- Fallback artwork is semantically compatible with the accompanying title.
- Lazy-loaded images appear in the final full-page capture.

## Material and Detail

- Paper, cloth, envelopes, tabs, borders, shadows, and route marks create the same depth hierarchy.
- Decorative effects support hierarchy without obscuring content or adding sensory clutter.
- Border radii and shadows vary only where the reference implies different materials.
- Hover and focus treatments feel consistent with the physical metaphor.

## Responsive Behavior

- Mobile uses a deliberate reading composition rather than a uniformly stacked desktop layout.
- Tap targets are at least 48 pixels and navigation remains keyboard accessible.
- Cards do not create avoidable scrolling or repeated empty space.
- No horizontal overflow, clipped text, or off-screen controls appear at common widths.

## Content and Behavior

- Routes, forms, structured data, search, newsletter protection, and dynamic records remain functional.
- Real content, empty collections, missing images, and validation errors remain visually coherent.
- The implementation does not introduce fictional claims, records, dates, or affiliations from the comp.

## Delta Classification

- **Structural:** Wrong geometry, ordering, scale, overlap, asset, or responsive composition. Fix before continuing.
- **Refinement:** Small spacing, crop, typography, shadow, border, or decorative discrepancy. Fix after structure is correct.
- **Intentional:** Real-content, accessibility, or responsive adaptation that should differ from the static reference. Document it in the handoff.
