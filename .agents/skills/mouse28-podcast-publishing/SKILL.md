---
name: mouse28-podcast-publishing
description: Prepare, publish, update, or audit Mouse28 podcast episodes across Transistor, the Mouse28 Filament editor, public episode pages, and long-term source archives. Use for episode metadata, show notes, transcripts, distribution, launch checks, and podcast file organization; do not activate for blog posts or general site releases.
---

# Mouse28 Podcast Publishing

Treat Transistor as the podcast host and Mouse28 as the editorial companion. Transistor owns uploaded audio, streaming, the canonical RSS feed, and distribution. Mouse28 stores the Transistor share URL, episode presentation, show notes, transcript, artwork, SEO metadata, and related editorial content. Never add a second website-hosted MP3 or podcast feed.

## Establish Current State

- Read `.ai/rules/episodes.md`, `README.md`, and the episode model and Filament resource before changing repository code or content behavior.
- Inspect the actual Transistor episode and corresponding Mouse28 record when access is available. Do not infer whether an episode is a draft, scheduled, published, or already distributed.
- Preserve original audio and transcript exports. Do not overwrite a source recording with an edited or compressed delivery file.
- Ask for missing editorial choices only when they materially affect publication, such as the final title, publish time, explicit-content status, or approval of a transcript.

## Prepare the Episode Package

Collect or draft the following before publication:

- final title, episode type, episode number when applicable, and publish date;
- short listener-facing summary and complete show notes;
- final audio file and duration;
- square podcast or episode artwork with meaningful alternative text;
- Transistor-generated or externally generated transcript with Jeffrey and Cassie labeled consistently;
- corrected names, Disney terminology, links, and obvious transcription errors;
- Mouse28 slug, description, SEO title, SEO description, and any related post.

Use the editor's native rich-text controls for Transistor show notes. Do not paste raw Markdown syntax unless the current editor explicitly previews it correctly. Keep notes scannable, lead with what listeners will learn or hear, and include only links actually discussed or useful to the episode.

Do not replace a Transistor transcript merely because another tool produced a version. Compare accuracy first and replace it only when the reviewed alternative is meaningfully better. Never present a generated transcript as fully accurate until a person has reviewed speaker labels, names, and sensitive family details.

## Publish Through Transistor

1. Upload the delivery audio to the matching Transistor episode.
2. Set the correct episode type and number. Leave season blank when Transistor does not offer or require it for the show.
3. Add the summary and formatted show notes.
4. Add the reviewed transcript using Transistor's supported transcript workflow.
5. Set the alternate episode URL to the canonical public Mouse28 episode URL only when that URL is ready to resolve.
6. Review artwork, explicit-content status, publish date, and destinations.
7. Publish or schedule only when the user has authorized that external action.

Record the final `https://share.transistor.fm/s/...` URL. Do not derive integration data from an MP3 CDN URL.

## Complete the Mouse28 Record

- Use the existing Filament episode workflow; do not write directly to production storage or the database.
- Match title, episode number, description, publication state, and publish time to Transistor.
- Store the Transistor share URL so the site can derive its allowlisted embedded player.
- Add show notes and the reviewed transcript through their rich-text fields, preserving headings, lists, paragraphs, and speaker labels.
- Add cover/social artwork and SEO metadata required by the existing editorial-readiness checks.
- Keep production as the source of truth for published editorial records. Use the established production-content sync when a local copy is needed.

## Archive Source Material

When the NAS is mounted and file organization is in scope, use this canonical structure:

```text
/Volumes/home/Projects/mouse28/podcast/episodes/<episode-slug>/
├── audio-original/
├── audio-delivery/
├── transcripts/
├── artwork/
└── notes/
```

Keep filenames descriptive and stable, including the episode number or `trailer`, slug, and revision when useful. Copy rather than move the only known original. Verify copied files by size and checksum before treating the archive as complete. Do not commit audio, transcript exports containing private material, or secrets to the website repository.

## Verify the Launch

After publication, verify observable results rather than assuming propagation:

- the Transistor player loads and plays the intended episode;
- the Mouse28 podcast index and episode page render the correct title, artwork, player, notes, and transcript;
- transcript disclosure controls work by keyboard and on mobile;
- canonical URL, metadata, structured data, and search presentation describe the episode accurately;
- the Transistor RSS feed contains the correct enclosure and episode metadata;
- Apple Podcasts, Spotify, and other configured destinations receive the episode after their normal propagation delay;
- Sentry and Nightwatch remain healthy after launch.

Use read-only checks freely. Treat publication, scheduling, production edits, distribution changes, and file replacement as external mutations that require the user's authorization when it has not already been given.

## Track the Work

Use the Mouse28 Hermes Kanban board for a real episode launch when multiple steps, human review, or propagation waits remain. Prefer one episode card with a concrete checklist over several tiny cards. Create separate dependent cards only for genuinely independent work such as transcript review, archive recovery, or delayed distribution verification. A card is complete only when its acceptance checks are observable; schedule propagation checks instead of marking them blocked.

Report what changed in Transistor and Mouse28, the archived source location, URLs checked, verification results, pending propagation, and any action that still needs Jeffrey or Cassie.
