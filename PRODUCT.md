# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Mouse28 primarily serves parents and caregivers planning Disney Parks visits for autistic family members and people with accessibility needs. They need practical preparation, realistic expectations, and trustworthy firsthand context that can make a park visit calmer and more manageable.

## Product Purpose

Mouse28 shares accessibility guidance, sensory-aware planning advice, family experiences, park tips, and podcast conversations. It exists to help families make informed Disney Parks decisions through useful articles, guides, and honest stories from Jeffrey and Cassie's own family experience.

Success means readers can find relevant guidance, understand what a Disney experience may involve, and plan with greater confidence without feeling reduced to a diagnosis or generic checklist.

The homepage's primary action is reading the blog. Guides and podcast episodes support that editorial path rather than competing with it for first priority.

## Positioning

Mouse28 offers first-person, accessibility-centered Disney planning from a real family's continuing park experience. Its value is lived context and honest specificity rather than generic Disney news, exhaustive destination coverage, or secondhand accessibility summaries.

## Operating Context

Readers use Mouse28 before and during trip planning. They browse recent stories, search for a specific concern, read focused accessibility guides, and listen to podcast episodes. The public experience is blog-first, with guides and podcast content supporting the editorial core.

Jeffrey and Cassie manage posts, guides, episodes, podcast information, subscribers, and contact messages through the existing Laravel and Filament application.

## Capabilities and Constraints

- The public content hierarchy remains Hero, Featured post, Latest posts, Guides teaser, Podcast, About, and Newsletter.
- Posts are the primary editorial content. Guides provide focused accessibility resources, and episodes contain podcast content.
- Posts may credit Jeffrey, Cassie, or both.
- Search, contact, newsletter, RSS feeds, sitemap, previews, and the protected administration area remain supported.
- Community Stories and reader-submitted story workflows are outside the product scope.
- Existing routes, content records, structured data, forms, Turnstile protection, rate limits, and editorial workflows must remain stable through visual work.
- Mouse28 is an independent family publication and must not imply official Disney affiliation or unsupported expertise.

## Brand Commitments

The Mouse28 name, Besley wordmark, Jeffrey and Cassie's identities, and the site's family photography are established assets. The illustrated Mouse28 mark remains the podcast identity and may appear with podcast content, while the public website uses the typographic wordmark. The voice is warm, direct, practical, encouraging, and honest about lived experience. Accessibility is part of the product's substance, not a decorative theme or marketing claim.

The redesign may establish a more creative visual language while preserving factual copy, the blog-first information hierarchy, and the recognizable Mouse28 identity unless Jeffrey explicitly approves a change.

## Evidence on Hand

- Real family and author photography in `public/images`.
- Existing Mouse28 logo and podcast cover artwork in `public/images`.
- Published and draft posts, guides, and podcast episodes managed by the application.
- Bundled editorial artwork in `resources/content-artwork`.
- Existing public copy, author attribution, source metadata, and editorial readiness workflows.

Future design work must not fabricate testimonials, audience metrics, Disney endorsements, medical claims, or performance statistics.

## Product Principles

1. Lead with firsthand usefulness rather than generic Disney coverage.
2. Make accessibility guidance clear, respectful, and easy to act on.
3. Let real family experience and photography carry trust.
4. Keep the publication's editorial content more prominent than promotional material.
5. Add personality without adding sensory or cognitive friction.

## Accessibility & Inclusion

Mouse28 must preserve semantic HTML, keyboard access, visible focus states, readable contrast, text resizing, responsive layouts, 48-pixel mobile controls, and reduced-motion support. The experience should be calm and predictable enough for accessibility-focused readers while still feeling distinctive and creative.
