# Mouse28

Mouse28 is a blog-first Disney parks and podcast site from Jeffrey and Cassie Davidson. It focuses on accessibility, autism awareness, practical park planning, family experience, and the Mouse28 podcast.

## Stack

- PHP 8.4 and Laravel 13
- Filament 5 administration panel
- Blade, Tailwind CSS 4, Alpine.js, and Vite 7
- SQLite locally by default
- Pest 5 with Jason McCreary's Double library

## Local setup with Herd

1. Clone the repository under the Herd directory and link or park it as `mouse28.test`.
2. Install dependencies and initialize the application:

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   npm install
   npm run build
   ```

3. Configure the application URL in `.env`:

   ```dotenv
   APP_NAME=Mouse28
   APP_URL=https://mouse28.test
   ```

4. To create a local administrator while seeding sample content, set `SEED_ADMIN_EMAIL` and `SEED_ADMIN_PASSWORD`, then run `php artisan db:seed`. No administrator is created when either value is absent.

## External services

The application can run locally without live third-party calls, but these features require production configuration:

- `RESEND_API_KEY` and `RESEND_AUDIENCE_ID` power newsletter signup and the subscriber dashboard.
- `TURNSTILE_SITE_KEY` and `TURNSTILE_SECRET_KEY` protect contact and newsletter forms. `TURNSTILE_ALLOWED_HOSTNAMES` must contain the exact production and local hostnames.
- `MAIL_*` and `MAIL_ADMIN_ADDRESS` deliver contact notifications and confirmations.
- `PODCAST_OWNER_NAME` and `PODCAST_OWNER_EMAIL` populate podcast-feed ownership metadata.
- `FATHOM_SITE_ID` enables the optional analytics script.
- `SENTRY_LARAVEL_DSN` enables production error reporting. Keep `SENTRY_SEND_DEFAULT_PII=false`; tracing and profiling remain disabled until their sample rates are deliberately raised above `0.0`.
- `GUIDES_ENABLED` controls public guide routes and discovery. It defaults to `false` while the guide library is being prepared.
- `GUIDE_REVIEW_INTERVAL_DAYS` controls when durable guides are flagged for editorial review; it defaults to 180 days.

Never commit live credentials. Keep them in the deployment environment.

## Content workflow

The Filament panel is available at `/admin` to users with `is_admin = true`.

- Posts contain news, trip reports, recaps, and family writing.
- Guides contain durable park resources. Policy-sensitive guides should include an official source and a current `last_reviewed_at` date.
- Episodes contain podcast metadata, audio links, show notes, and transcripts.
- Podcast Settings owns show-level distribution metadata.
- Show-level podcast destinations appear throughout the public site and act as fallbacks when an episode does not have a platform-specific URL. The generated podcast feed is always available as the RSS fallback.
- Content lists show publication status and readiness reminders. Edit pages provide administrator-only draft previews.

Content is publicly visible only when it is marked published and its publication date is not in the future. Community Stories and reader-submitted story publishing are intentionally outside the product scope.

Published post, guide, and episode pages emit Schema.org content and breadcrumb data. Guides older than the configured review interval display a reader notice and are flagged in Filament.

## Development commands

```bash
composer validate --strict --no-check-publish
composer audit --locked --format=plain
npm audit --audit-level=high
vendor/bin/pint --test
vendor/bin/filacheck
composer analyse
composer test:rector
composer test
composer test:browser
npm run build
git diff --check
```

Run `vendor/bin/pint` to apply PHP and Blade formatting fixes. Blade formatting is enabled by default through `pint.json`.
Run `vendor/bin/filacheck` to check Filament code for deprecated APIs and common implementation issues.
Run `npx playwright install chromium` once before the local browser suite. Browser smoke tests also run weekly, on demand, and for release tags in GitHub Actions.

Laravel Boost provides project-aware documentation and inspection tools through the committed Codex MCP configuration. Its generated Laravel, Pest, and Tailwind skills are tracked under `.agents/skills`. Laravel Pao automatically condenses supported test and analysis output when an agent runs the commands.

Use `composer dev` only when a long-running local server, queue listener, Vite server, and log viewer are all needed.

## Dependency maintenance

Routine dependency version pull requests are intentionally disabled. Review direct dependencies monthly with `composer outdated --direct` and `npm outdated`, then group related upgrades that need to move together into focused pull requests.

Dependabot vulnerability alerts remain enabled. CI audits the locked Composer and npm dependency trees, and security findings should be addressed through the normal branch and pull request workflow.

## Deployment checklist

- Configure the application URL, database, mail, Resend, Turnstile, podcast ownership, storage, cache, sessions, and queues.
- Set `SENTRY_LARAVEL_DSN`, `SENTRY_ENVIRONMENT=production`, and a deploy-specific `SENTRY_RELEASE` to opt into error reporting. Leave PII disabled and choose non-zero tracing or profiling sample rates only after reviewing volume and privacy.
- Run `php artisan app:verify-production` after loading production configuration and stop if it reports a failure.
- Run `php artisan migrate --force`.
- Run `npm run build` before publishing the release artifact.
- Ensure `public/storage` is linked when uploaded media is used.
- Run `php artisan optimize` after environment configuration is final.
- Confirm the scheduler and queue worker are supervised if production uses queued work.
- Verify `/`, `/search?q=accessibility`, `/sitemap.xml`, both RSS feeds, contact submission, and newsletter signup. When `GUIDES_ENABLED=true`, also verify `/guides`.
- Back up the database and uploaded files before each deployment.
- Use `php artisan content:clean-seeded --force` only after backups are verified and real content is ready; it removes only the known demo slugs.

See [docs/architecture.md](docs/architecture.md) for application boundaries, [docs/content-model.md](docs/content-model.md) for editorial language, and [docs/operations.md](docs/operations.md) for the Forge deployment and rollback runbook.
