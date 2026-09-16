# Production operations

Mouse28 is hosted on Laravel Forge. Use a separate staging site for deployment verification; never treat the production `mouse28.com` site as staging.

## Before deploying

1. Confirm the target commit or tag and review its migrations and storage changes.
2. Confirm the staging or production environment uses persistent database and public-media paths.
3. Create and independently verify a database backup and an uploaded-media backup.
4. Load the target environment and run `php artisan app:verify-production`.
5. Stop when the preflight command reports any failure.

Never copy live credentials into the repository, deployment logs, or local documentation.

Staging runs with `APP_ENV=production` so production safeguards stay active. Set
`APP_URL` and `MOUSE28_PRODUCTION_URL` to `https://staging.mouse28.com`, set
`MOUSE28_DEPLOYMENT_ENVIRONMENT=staging`, and use matching isolated Nightwatch
and Sentry environments before running `php artisan app:verify-production`.

## Branch and release workflow

Keep the Forge sites configured to these source branches:

- `staging.mouse28.com` tracks `develop` for normal integration work.
- `mouse28.com` tracks `main` for production releases.

Before a release, create `release/YYYY.MM.DD` from the up-to-date `develop`
branch and open a release pull request into `main`. Confirm its head, base,
checks, and Conventional Commit subject. Approve and merge it with a regular
merge commit; Forge's configured deployment starts from that merge. Do not
manually trigger a second deployment or repoint staging for routine releases.
When checking staging, record its deployed revision: a newer `develop` is not
proof that an older release candidate passed verification.

After production verification, synchronize `main` into `develop` when Jeffrey
explicitly requests it: fetch the remote branches, update the local `develop`,
merge `origin/main` with a Conventional Commit synchronization subject, and
push `develop`. This synchronization does not add feature work to `develop`.
If branch protection rejects the push, stop and ask for the approved resolution;
do not silently disable protection or create a synchronization PR instead.
Feature work still goes through squash-merged pull requests into `develop`.

## Syncing public content locally

Run `php artisan content:sync-production` from the local Mouse28 checkout to replace local published posts, guides, episodes, podcast display metadata, and their referenced public media with the current production versions. The command uses the `cold-moon` SSH alias and `/home/forge/mouse28.com/current` site path by default; override them with `MOUSE28_PRODUCTION_SSH_HOST` and `MOUSE28_PRODUCTION_SITE_PATH` when the Forge target changes.

The sync is one-way and refuses to run when the current application environment is production. It never exports private users, subscribers, contact submissions, credentials, or environment-specific podcast email. Local drafts and scheduled content are preserved; stale currently published local records are soft deleted.

## Deploying

The Forge deployment should install locked Composer dependencies, install locked Node dependencies, build assets, run forward-only migrations, link public storage, refresh optimized caches, and restart workers only when queued jobs are introduced.

The production Forge script runs `php artisan app:verify-production --no-interaction`
after optimization, before migrations, and stops on failure. Immediately after
release activation, bounded HTTP checks require `/up` and `/` to return exactly
200 before release cleanup proceeds. These checks are part of the normal
merge-triggered deployment; changing the script does not itself deploy the site.
A failed post-activation check marks the deployment failed and preserves recovery
artifacts, but does not automatically roll back the active release or database.
Future changes to the live Forge script require separate approval.

The audit-remediation release adds nullable MFA and contact-email tracking
columns. Run its forward migrations before activating its application code.
Every administrator must enroll an authenticator app and save their recovery
codes; existing unenrolled administrators are routed to enrollment after signing
in. Do not seed or manually fill MFA secrets on their behalf.

Do not clean demo content as part of an unattended deployment. After verified backups and real-content review, `php artisan content:clean-seeded --force` removes only the documented demo slugs in one transaction.

Laravel's destructive database commands (`db:wipe`, `migrate:fresh`, `migrate:refresh`, `migrate:reset`, and `migrate:rollback`) are prohibited when `APP_ENV=production`, even with `--force`. This includes Forge staging configured with that environment. Use forward migrations; do not disable this safeguard as a deployment or rollback shortcut.

## After deploying

For responsive artwork, confirm GD WebP support and the persistent local public disk before running `php artisan content:generate-artwork --type=posts --force` or `php artisan content:generate-artwork --type=episodes --force` on the explicitly approved target. The legacy `content:generate-post-artwork` alias still defaults to posts only. These additive operations leave original covers and database records untouched; each must be separately approved under the production mutation rules. Run the appropriate type again after publishing/replacing covers. Without generated candidates the public site continues serving originals. Verify candidate URLs return 200 before claiming responsive-image savings on production. Post candidates use `/storage/posts/responsive/`; square episode candidates use `/storage/episodes/responsive/v1/`.

Without `--force`, the artwork command uses Laravel's native production confirmation prompt. Declining it, or running noninteractively without force, cancels generation. A console prompt or flag does not replace the operational approval above.

The Forge/Nginx edge uses a one-year `public, immutable` policy for content-fingerprinted `/build/assets/` and responsive files under `/storage/posts/responsive/` and `/storage/episodes/responsive/v1/`. Unversioned originals, fonts and site images need a shorter freshness policy or versioned URLs. Preserve private/no-store behavior for admin, previews and errors, and do not apply public HTML caching to CSRF tokens or form/session feedback. No edge configuration is changed by the artwork command or benchmark.

On the installed Nginx 1.26 series, a location-level `add_header` stops inheritance
of server-level headers. Cache locations must preserve the shared security
headers explicitly, including HSTS and `X-Content-Type-Options`. Verify live
responses for all three locations after any approved change; configuration
syntax success alone does not prove header behavior. The September 15 audit
found these inherited headers missing; the repair remains a separately approved
production change.

Nginx owns client-IP resolution through its trusted Cloudflare ranges and passes
the resolved address to PHP as `REMOTE_ADDR`. Laravel must not trust arbitrary
forwarded headers again. Review this boundary before introducing another proxy.

Verify all of the following against the deployed commit:

- `php artisan migrate:status` has no pending migrations.
- `/up` returns HTTP 200, confirming that the application boots successfully.
- `/`, `/blog`, `/episodes`, `/about`, `/contact`, `/search`, and `/admin/login` render successfully. When `GUIDES_ENABLED=true`, `/guides` also renders successfully; otherwise it returns 404.
- `/sitemap.xml` and `/rss/blog` return valid XML, and `/rss/podcast` permanently redirects to the configured Transistor feed.
- Public uploaded-media URLs return successful responses.
- Turnstile appears once per protected page without console errors.
- Contact mail and newsletter subscriptions succeed with production providers.

## Rollback

1. Stop the deployment if health or smoke checks fail.
2. Redeploy the last known-good tag.
3. Restore only from independently verified database and media backups when data or schema restoration is required.
4. Repeat migration, health, media, public-page, and admin-boundary checks.

Never delete the only verified rollback artifacts during an incident.
