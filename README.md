# Mouse28

Mouse28 is a blog-first Disney parks and podcast site from Jeffrey and Cassie Davidson. It focuses on accessibility, autism awareness, practical park planning, family experience, and the Mouse28 podcast.

## Stack

- PHP 8.4 and Laravel 13
- Filament 5 administration panel
- Blade, Tailwind CSS 4, Alpine.js, and Vite 7
- SQLite locally by default
- PHPUnit with Laravel's test runner

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
- `GUIDE_REVIEW_INTERVAL_DAYS` controls when durable guides are flagged for editorial review; it defaults to 180 days.

Never commit live credentials. Keep them in the deployment environment.

## Content workflow

The Filament panel is available at `/admin` to users with `is_admin = true`.

- Posts contain news, trip reports, recaps, and family writing.
- Guides contain durable park resources. Policy-sensitive guides should include an official source and a current `last_reviewed_at` date.
- Episodes contain podcast metadata, audio links, show notes, and transcripts.
- Podcast Settings owns show-level distribution metadata.

Content is publicly visible only when it is marked published and its publication date is not in the future. Community Stories and reader-submitted story publishing are intentionally outside the product scope.

Published post, guide, and episode pages emit Schema.org content and breadcrumb data. Guides older than the configured review interval display a reader notice and are flagged in Filament.

## Development commands

```bash
composer test
npm run build
vendor/bin/pint
git diff --check
```

Use `composer dev` only when a long-running local server, queue listener, Vite server, and log viewer are all needed.

## Deployment checklist

- Configure the application URL, database, mail, Resend, Turnstile, podcast ownership, storage, cache, sessions, and queues.
- Run `php artisan migrate --force`.
- Run `npm run build` before publishing the release artifact.
- Ensure `public/storage` is linked when uploaded media is used.
- Run `php artisan optimize` after environment configuration is final.
- Confirm the scheduler and queue worker are supervised if production uses queued work.
- Verify `/`, `/guides`, `/sitemap.xml`, both RSS feeds, contact submission, and newsletter signup.
- Back up the database and uploaded files before each deployment.

See [docs/architecture.md](docs/architecture.md) for application boundaries and [docs/content-model.md](docs/content-model.md) for editorial language.
