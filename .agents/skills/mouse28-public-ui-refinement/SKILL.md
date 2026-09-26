---
name: mouse28-public-ui-refinement
description: Diagnose and refine existing Mouse28 public pages through browser observations and measured loading performance. Use for responsive layout, visual inconsistencies, image delivery, and public-page performance without an approved design comp. Use mouse28-visual-fidelity for approved comps; excludes Filament administration and editorial artwork creation.
---

# Mouse28 Public UI Refinement

Improve an existing public Mouse28 page using evidence from its rendered interface and resource loading. Match the work to the request: an audit produces findings; an implementation request authorizes scoped fixes.

## Establish the target

1. Read the project `AGENTS.md`, `DESIGN.md`, and the applicable files mapped by `.ai/rules/index.md` before changing interface code.
2. Identify the visitor's task and reproduce the reported issue. Record the URL, viewport, pixel density, relevant content/state, and observed result. If reproduction is blocked, continue useful code or asset inspection and distinguish confirmed findings from hypotheses.
3. Determine which version is being viewed. Compare staging's deployed revision with the intended branch before treating a staging-only problem as a code defect.
4. Define observable acceptance criteria before editing. Examples: the listening action is reachable without an oversized hero; the page fits at the affected breakpoint; or the cover downloads once at a resolution appropriate for its rendered width and pixel density. Treat a particular viewport's above-the-fold target as a scoped requirement, not a universal layout rule.

## Diagnose before changing

Separate the likely cause before choosing a fix:

- **Interface/code:** layout, responsive behavior, hierarchy, interaction, or styling differs from the agreed design system.
- **Asset/build/cache:** an image, font, or compiled asset is missing, stale, incorrectly generated, or not served by the current build.
- **Content/operations:** required content, configuration, deployment, or environment state is absent or incorrect.

Fix the cause at the layer that owns it. Do not mask missing content or a deployment/configuration problem with placeholder UI or unrelated CSS. Use established asset and content workflows; preserve source artwork and factual editorial content.

## Make the smallest coherent refinement

- Preserve Mouse28's page hierarchy, brand tokens, typography, semantic HTML, keyboard access, focus visibility, contrast, reduced-motion behavior, and responsive conventions.
- Follow `.ai/rules/views.md`: prefer Tailwind utilities and existing shared Blade components/tokens. Add custom CSS only for concerns that do not fit the utility system, such as design tokens, fonts, keyframes, third-party integration, or necessary behavior.
- Reuse existing components. Extract repeated UI or a substantial semantic section when a named component improves readability, ownership, or encapsulation, even with one caller. Avoid trivial wrappers and speculative abstractions.
- Do not invent or alter editorial facts, episode details, testimonials, metrics, or site behavior beyond the requested refinement. Ask when the intended behavior or content is ambiguous.
- Keep public-site work separate from Filament admin work. For admin pages, use the appropriate Filament guidance and existing admin design system.

## Couple Layout and Image Delivery

When changing image geometry or delivery, inspect the rendered width, pixel density, `sizes`, available `srcset` candidates, and the browser's selected `currentSrc` together. Recheck sizing hints after changing max-widths, grid columns, or breakpoints. Update associated preload sizing and formats when applicable.

Confirm the selected resource loads and decodes, has the intended crop/aspect ratio, and avoids unnecessary or duplicate downloads. A passing assertion for an old filename is not evidence that the candidate still suits the layout. Use fresh browser contexts for candidate comparisons so cached larger images do not distort the result.

Keep bundled site images and generated upload derivatives distinct. Read the responsive-artwork sections of [architecture](../../../docs/architecture.md#responsive-post-artwork) and [operations](../../../docs/operations.md) when those workflows are involved; do not generate derivatives during page rendering.

## Measure Performance When It Is in Scope

Use the existing runner and measurement rules in [Public-page performance benchmark](../../../docs/architecture.md#public-page-performance-benchmark). Narrow runs to affected routes and retain before/after evidence with the actual revision, origin, content, viewport, pixel density, browser, and network profile.

Compare three samples under matching conditions, reviewing medians, outliers, and failures. Report cold and warm results separately; do not run benchmarks alongside builds or tests. Treat local and staging measurements as separate evidence. A smaller asset or correct `currentSrc` proves a delivery change, not a measured LCP improvement. Do not impose timing thresholds on deterministic browser tests.

## Verify and Finish

1. Add or update a focused Pest browser test when the refinement changes important visible behavior or guards against a reproduced regression. Assert user-visible outcomes rather than implementation classes, and use minimal synthetic fixtures as required by `.ai/rules/browser.md`.
2. Revisit the original viewport/state, both sides of an affected breakpoint, and a representative desktop size. Check only the relevant behavior and regressions. Capture and inspect comparable before/after screenshots for material visual changes after fonts and relevant images settle; screenshots are optional for delivery-only changes with unchanged geometry.
3. Run focused tests and the asset build when applicable, plus `git diff --check`. Use the project's canonical commands in `AGENTS.md`.
4. Finish when the agreed criteria pass and relevant checks show no unresolved regression. Use one review pass and a focused confirmation after corrections; continue only for a remaining failure or unresolved requirement. List worthwhile adjacent work separately, ordered by visitor impact and evidence, rather than starting another polishing cycle.
5. Summarize the cause, changes, and verified outcomes with the environment and evidence. Distinguish local verification from deployed verification and report any remaining blocker without implying completion.

## Boundaries and related skills

- For an approved screenshot or design comp, use `mouse28-visual-fidelity`; that skill owns comp-driven measurements and screenshot comparisons.
- Invoke `tailwindcss-development` when changing Tailwind markup or styles, `pest-testing` when changing Pest tests, and Laravel guidance when the refinement requires backend code.
- Do not push, deploy, or mutate staging/production content, credentials, storage, or environment configuration as part of visual refinement without explicit authorization. Confirm the exact environment and action before any production mutation.
- Leave the user's browser tab and viewport as found when using browser tools, unless the user asks to keep the refined page open.
