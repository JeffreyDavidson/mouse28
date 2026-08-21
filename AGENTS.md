# Mouse28 Agent Guidance

## Project

Mouse28 is a blog-first Disney parks and podcast site focused on accessibility,
autism awareness, family experiences, park tips, and related Disney content.

The homepage content order is:

1. Hero
2. Featured post
3. Latest posts
4. Guides teaser
5. Podcast
6. About
7. Community stories
8. Newsletter

## Stack

- PHP 8.4
- Laravel 13
- Filament 5
- Blade views
- Tailwind CSS 4 and Vite 7
- Pest-style tests run through Laravel's test command

The Vite entry points are `resources/css/app.css`,
`resources/css/filament/admin/theme.css`, and `resources/js/app.js`. The public
layout loads Tailwind and Alpine through Vite. The Filament entry point owns
admin design tokens and global panel overrides; custom admin Blade views use
the shared components in `resources/views/components/filament`. Keep frontend
work in the existing Vite pipeline and do not introduce a second toolchain.

## Git Workflow

- Use `develop` as the integration branch and `main` as the release branch.
- Never commit feature work directly to `develop` or `main`.
- Create focused feature branches from an up-to-date `develop` branch.
- Merge feature branches into `develop` through pull requests.
- Promote releases from `develop` to `main` through pull requests.
- Use commit messages in `type: description` format.
- Do not commit `.worktrees/` or changes belonging to another worktree.

## Application Structure

- `Post` is the primary blog content model.
- `Episode` contains podcast episodes.
- `Guide` contains accessibility resources.
- `CommunityStory` contains reader-submitted stories.
- `Podcast::info()` provides the single-row podcast metadata record.
- Jeffrey, Cassie, or both may be credited as post authors.

The main navigation contains Home, Blog, Podcast, About, and Contact. Preserve
the site's blog-first content hierarchy when changing public pages.

## Design System

- Navy: `#1a1040`
- Light navy: `#2d1b69`
- Purple: `#5b3e9e`
- Gold: `#d4a843`
- Cream: `#fef9ef`
- Dark cream: `#f5efe0`
- Heading font: Playfair Display
- Body font: Poppins

Preserve semantic HTML, keyboard access, visible focus states, readable color
contrast, responsive layouts, 48-pixel mobile controls, and reduced-motion
support. Prefer Tailwind utilities and shared CSS tokens over new inline styles.
Keep dynamic Blade-derived colors in CSS custom properties when utilities cannot
represent them safely.

## Development and Verification

Inspect `composer.json` and `package.json` before running project commands. The
standard checks are:

- `composer test`
- `npm run build`
- `git diff --check`

Use Pest syntax for new tests. Keep public routes renderable, preserve contact
form Turnstile and rate-limit protections, and never expose service secrets in
Blade, JavaScript, logs, fixtures, or documentation.

## Filament

Use APIs supported by the installed Filament 5 version. Keep custom admin styles
in Filament's supported theme pipeline, authorize protected actions server-side,
and add smoke coverage when introducing a Filament resource or registered page.
