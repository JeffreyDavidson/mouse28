# Mouse28

Mouse28 is a blog-first Disney parks and podcast site from Jeffrey and Cassie Davidson. It focuses on accessibility, autism awareness, practical park planning, family experience, and the Mouse28 podcast.

## Stack

- PHP 8.5 and Laravel 13
- Filament 5 administration panel
- Blade, Livewire 4, Tailwind CSS 4, Alpine.js, and Vite 7
- SQLite locally by default
- Pest 5

## Local setup with Herd

1. Clone the repository under the Herd directory and link or park it as `mouse28.test`.
2. Install dependencies and initialize the application:

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   npm ci
   npm run build
   ```

3. Configure the application URL in `.env`:

   ```dotenv
   APP_NAME=Mouse28
   APP_URL=https://mouse28.test
   ```

4. To create a local administrator while seeding sample content, set `SEED_ADMIN_EMAIL` and `SEED_ADMIN_PASSWORD`, then run `php artisan db:seed`. No administrator is created when either value is absent. Outside production, each seed run adds factory-generated posts, episodes, and guides in published, draft, and scheduled states. Production seeding creates only the configured administrator and baseline podcast settings.

## External services

The application can run locally without live third-party calls, but these features require production configuration:

- `RESEND_API_KEY` and `RESEND_AUDIENCE_ID` power newsletter signup and the subscriber dashboard.
- `TURNSTILE_SITE_KEY` and `TURNSTILE_SECRET_KEY` protect contact and newsletter forms. `TURNSTILE_ALLOWED_HOSTNAMES` must contain the exact production and local hostnames.
- `MAIL_*` and `MAIL_ADMIN_ADDRESS` deliver contact notifications and confirmations.
- `PODCAST_RSS_URL` identifies the canonical Transistor feed. It defaults to the Mouse28 feed.
- `FATHOM_SITE_ID` enables the optional analytics script.
- `NIGHTWATCH_ENABLED=true` and `NIGHTWATCH_TOKEN` enable production application monitoring. Request payload capture stays disabled, authenticated users are identified only by their internal ID, and the default request sample rate is 10%.
- `SENTRY_LARAVEL_DSN` enables production error reporting. Keep `SENTRY_SEND_DEFAULT_PII=false`; tracing and profiling remain disabled until their sample rates are deliberately raised above `0.0`.
- `GUIDES_ENABLED` controls public guide routes and discovery. It defaults to `false` while the guide library is being prepared.
- `GUIDE_REVIEW_INTERVAL_DAYS` controls when durable guides are flagged for editorial review; it defaults to 180 days.

Never commit live credentials. Keep them in the deployment environment.

## Content workflow

The Filament panel is available at `/admin` to users with `is_admin = true`.

- Posts contain news, trip reports, recaps, and family writing.
- Guides contain durable park resources. Policy-sensitive guides should include an official source and a current `last_reviewed_at` date.
- Episodes contain podcast metadata, a Transistor share URL, show notes, and transcripts. Upload the episode audio to Transistor rather than the website.
- Podcast Settings owns show-level distribution metadata.
- Show-level podcast destinations appear throughout the public site and act as fallbacks when an episode does not have a platform-specific URL. Transistor owns the canonical RSS feed and embedded episode players; the legacy `/rss/podcast` URL permanently redirects there.
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

Run `vendor/bin/pint` to apply PHP and Blade formatting fixes. Blade formatting is enabled by default through `pint.json` and requires the locked Prettier, Blade, and Tailwind formatting packages.
Run `vendor/bin/filacheck` to check Filament code for deprecated APIs and common implementation issues.
Run `npx playwright install chromium` once before the local browser suite. A focused `browser-smoke` group runs in pull-request and main-branch CI. The full Chromium browser suite runs weekly, on demand, and for release tags; the `browser-compatibility` group checks reading, print presentation, and key interactions in Firefox and WebKit.

`composer test` runs the unit, integration, feature, and architecture suites; browser tests use the separate `composer test:browser` command. Rector uses its default parallel processing. Agent sandboxes must allow the local sockets used by Rector and Pest.

### Pest static analysis and refactoring

Test-only analysis uses separate configurations so the existing application checks remain unchanged:

```bash
composer analyse:pest
composer test:rector:pest
```

`phpstan-pest.neon` scans all PHP tests at level 5 with a separate result cache. Composer's PHPStan extension installer already registers the Pest plugin; do not include it a second time. `rector-pest.php` applies Pest's coding-style rules only to `tests/`.

`composer analyse:pest` sets `APP_ENV=testing` for the analysis process so Livewire registers its test-only response assertions. Use the Composer command, or set the same environment variable when invoking PHPStan directly. This does not change `.env` or the application analysis command.

`tests/pest-livewire.stub` supplies the component-specific return type missing from Pest Livewire 5.0's `livewire()` helper, retaining the generic `Component` fallback for named components. It is loaded only by Pest PHPStan analysis, never at runtime. Revisit the stub when the plugin supplies equivalent typing upstream.

`composer analyse:pest` is a required CI step after Laravel preparation, using an in-memory SQLite connection and test service drivers. It remains separate from `composer test`. A nonzero exit code fails CI; no baseline or blanket suppression hides findings.

`composer test:rector:pest` is read-only and remains opt-in. Its reviewed configuration excludes rewrites from strict empty-array comparisons to broad emptiness checks, and from `is_file()` to an existence-only assertion. Run `composer rector:pest` only to deliberately apply the proposed test changes, then inspect the diff and rerun the affected tests. Preserve Arrange / Act / Assert boundaries and framework-specific assertions when reviewing rewrites.

Choose the suite by what the test exercises:

- `Unit`: isolated logic without booting Laravel or using persistence, facades, factories, or external I/O.
- `Integration`: components working with Laravel, the database, storage, configuration, or service adapters directly.
- `Feature`: behavior through HTTP requests, Artisan commands, or Livewire interactions.
- `Browser`: real-browser interactions and rendering.
- `Arch`: source structure and architectural contracts.

Unit, Integration, and Feature paths mirror their owning `app/` classes. A class can have tests in more than one suite when they exercise different boundaries. For example, database casts belong in `tests/Integration/Models/PostTest.php`, while public post behavior belongs in `tests/Feature/Http/Controllers/PostControllerTest.php`. Split mixed files by boundary and owner. Choose that owner from the behavior being asserted, not a fixture model or internal collaborator. Page-specific response checks belong with their controller or Filament page; shared navigation and metadata checks belong with the layout. Blade and configuration tests without an application class use explicit source mappings in `tests/Arch/TestOrganizationTest.php`.

Run a suite independently with `php artisan test --compact --testsuite=Unit` (or `Integration`, `Feature`, or `Architecture`).

Laravel Boost provides project-aware documentation and inspection tools. `boost.json` tracks four managed skills: `infer-conventions`, `laravel-best-practices`, `pest-testing`, and `tailwindcss-development`; their project copies live under `.agents/skills`. Filament is selected for package guidance. Run `php artisan boost:update` to regenerate guidelines and skills, and review the resulting diff.

The `"nightwatch": true` preference in `boost.json` selects Nightwatch MCP setup through `php artisan boost:install`. It is separate from application monitoring and requires MCP client setup and OAuth authorization. Changing the preference alone does not establish an authenticated connection. Laravel Pao automatically condenses supported test and analysis output when an agent runs the commands.

Herd already serves the application locally. Use `npm run dev` for Vite's development server when needed. The optional `composer dev` script also starts an HTTP server, queue listener, and log viewer, so it is not needed for the normal Herd workflow.

## Dependency maintenance

Routine dependency version pull requests are intentionally disabled. Review direct dependencies monthly with `composer outdated --direct` and `npm outdated`, then group related upgrades that need to move together into focused pull requests.

Dependabot vulnerability alerts remain enabled. CI audits the locked Composer and npm dependency trees, and security findings should be addressed through the normal branch and pull request workflow.

## Deployment checklist

- Review the target release and verify database and uploaded-file backups before production mutations. Follow the Forge staging and production runbook linked below.
- Configure the application URL, database, mail, Resend, Turnstile, Transistor podcast feed, storage, cache, sessions, and queues.
- Set `NIGHTWATCH_ENABLED=true` and `NIGHTWATCH_TOKEN` to enable Nightwatch. Keep request payload capture disabled and request sampling at or below `0.1`.
- Set `SENTRY_LARAVEL_DSN`, `SENTRY_ENVIRONMENT=production`, and a deploy-specific `SENTRY_RELEASE` to enable error reporting. Leave PII disabled and tracing and profiling set to `0.0` until they are deliberately reviewed.
- Run `php artisan app:verify-production` after loading production configuration and stop if it reports a failure.
- Run `php artisan migrate --force`.
- Run `npm run build` before publishing the release artifact.
- Ensure `public/storage` is linked when uploaded media is used.
- Run `php artisan optimize` after environment configuration is final.
- Confirm the scheduler and queue worker are supervised if production uses queued work.
- Verify `/up`, `/`, `/blog`, `/episodes`, `/search?q=accessibility`, `/sitemap.xml`, `/rss/blog`, contact submission, and newsletter signup. Confirm `/rss/podcast` permanently redirects to the configured Transistor feed. When `GUIDES_ENABLED=true`, also verify `/guides`.
- Use `php artisan content:clean-seeded --force` only after backups are verified and real content is ready; it removes only the known demo slugs.

See [docs/architecture.md](docs/architecture.md) for application boundaries, [docs/content-model.md](docs/content-model.md) for editorial language, and [docs/operations.md](docs/operations.md) for the Forge deployment and rollback runbook.
