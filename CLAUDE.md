# CLAUDE.md — Mouse28

## Project Overview
Disney blog + podcast site. Blog-first — primary content driver, podcast is secondary/complementary.
Covers: park tips, accessibility, autism awareness, Disney news, food reviews, resort reviews, Disney+, merchandise.

## Tech Stack
- PHP 8.4, Laravel 12, Filament 5
- Tailwind CSS via CDN, Alpine.js via CDN (no build step)
- Google Fonts CDN: Playfair Display (headings), Poppins (body)

## Git Workflow
- Simplified gitflow: `main` ← `develop`
- Commit format: `type: description`
- Git email: `jdavidsonwebdev@gmail.com` (NOT jeffrey.davidson@outlook.com)
- Remote: `git@github-mouse28:JeffreyDavidson/mouse28.git`

## Filament 5 Rules
Same as bakery project — see critical namespace changes:
- All actions: `Filament\Actions\*`
- Get/Set: `Filament\Schemas\Components\Utilities\Get` / `Set`
- `form(Schema $form): Schema` NOT `form(Form $form): Form`
- `$view` is NOT static on Pages
- `$navigationGroup` → `string|UnitEnum|null`
- `$navigationIcon` → `string|BackedEnum|null`

## Content Model
- **Posts**: Blog articles (primary content). Author field: jeffrey/cassie/both
- **Episodes**: Podcast episodes (secondary)
- **Guides**: Accessibility resource hub
- **CommunityStory**: User-submitted stories
- **Podcast**: Single-row metadata via `Podcast::info()` (firstOrCreate)

## Categories (10)
disney-tips, park-accessibility, episode-recap, family-life, autism-awareness, disney-news, food-reviews, resort-reviews, disney-plus, merchandise

## Navigation
4 main nav items: Blog, Guides, Podcast, About
Stories and Contact are footer-only.

## Color Palette
navy #1a1040, purple #5b3e9e, gold #d4a843, cream #fef9ef, cream-dark #f5efe0

## Key Decisions
- Blog-first: Homepage order: Hero → Featured Post → Latest Posts → Guides Teaser → Podcast → About → Community Stories → Newsletter
- Cassie co-authors 50/50
- No photography features at launch
