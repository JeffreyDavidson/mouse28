---
name: mouse28-blog-artwork
description: Create or replace editorial cover artwork for Mouse28 blog posts and integrate approved covers into the site's bundled artwork workflow. Use for Mouse28 blog or post cover images; do not activate for podcast artwork, guide covers, logos, or general site imagery.
---

# Mouse28 Blog Artwork

Create an original cover that feels native to Mouse28's established artwork collection while telling the specific post's story. Use the `imagegen` skill and built-in image generation tool for the artwork itself.

## Establish the Brief

- Read the actual post content, title, excerpt, and slug from the repository or supplied source. Do not invent editorial details when the source is available.
- Inspect at least three relevant covers in `resources/content-artwork/posts/` with an image-viewing tool. Treat the current collection as the living style reference.
- Choose one clear visual story or metaphor from the post. Vary subject matter and composition across covers instead of repeating a stock family-at-the-park scene.
- If the request is only for a concept or preview, stop before changing repository files.

## Visual Language

- Use a richly textured, polished painterly editorial illustration with gentle storybook warmth and cinematic depth.
- Favor Mouse28's deep navy, aubergine, purple, lavender, antique gold, cream, and restrained olive or teal palette.
- Compose a wide 1.90:1 landscape image that crops cleanly to 1731×909. Keep important subjects away from extreme edges.
- Build accessibility into the scene naturally when the article supports it. Avoid stereotypes, diagnostic symbolism, or details not grounded in the post.
- Keep people non-identifying unless the user supplies likeness references and asks for recognizable portraits. Prefer natural poses and believable anatomy.
- Do not add titles, readable text, numerals, watermarks, borders, or UI.

## Intellectual-Property Boundary

The article may discuss Disney, but the artwork must remain original. Use general theme-park atmosphere, travel objects, landscaping, lighting, or original architecture to convey context.

Do not reproduce recognizable Disney characters, Mickey silhouettes or ear shapes, Cinderella Castle, attraction vehicles or scenes, logos, wordmarks, branded clothing, signage, or other protected designs. Inspect generated details closely; regenerate when a landmark, mark, or silhouette is too recognizable.

## Generate and Review

1. Use the most relevant existing covers as image references and describe both the article-specific scene and the shared visual language in the prompt.
2. Generate one strong direction unless the user requests alternatives.
3. Inspect the full-resolution result for composition, anatomy, unintended text, accidental branding, recognizable protected designs, and consistency with the collection.
4. Regenerate or edit when any of those checks fail. Do not accept a result merely because it is technically complete.

## Prepare the Repository Asset

Preserve the generator's original output. Before writing, confirm that the destination does not already exist unless the user explicitly requested replacement.

Convert the approved result to the project's standard asset with ImageMagick:

```shell
magick GENERATED_IMAGE -resize '1731x909^' -gravity center -extent 1731x909 -quality 88 resources/content-artwork/posts/POST_SLUG.webp
```

Use the exact blog post slug for `POST_SLUG`. Inspect the converted WebP and verify it is 1731×909.

## Integrate Bundled Artwork

When the user wants a repository-ready cover rather than only a preview:

- Add the slug and `posts/<slug>.webp` path to `POST_ARTWORK` in `app/Console/Commands/AttachContentArtwork.php`.
- Extend `tests/Feature/AttachContentArtworkTest.php` so the file is copied and attached to the matching post.
- Preserve the command's existing rule that user-uploaded artwork is never replaced.
- Follow the project's Laravel and Pest skills for PHP and test changes.
- Do not upload the image, run the attachment command against production, deploy, commit, push, or merge unless the user separately authorizes that action.

Run the focused verification:

```shell
vendor/bin/pint --dirty --format agent
php artisan test --compact tests/Feature/AttachContentArtworkTest.php
git diff --check
file resources/content-artwork/posts/POST_SLUG.webp
```

Report the saved project path, final generation prompt, generation mode, checks run, and whether production remains unchanged.
