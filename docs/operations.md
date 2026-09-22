# Production operations

Mouse28 is hosted on Laravel Forge. Use a separate staging site for deployment verification; never treat the production `mouse28.com` site as staging.

## Before deploying

1. Confirm the target commit or tag and review its migrations and storage changes.
2. Confirm the staging or production environment uses persistent database and public-media paths.
3. Create and independently verify a database backup and an uploaded-media backup.
4. Load the target environment and run `php artisan app:verify-deployment`.
5. Stop when the preflight command reports any failure.

Never copy live credentials into the repository, deployment logs, or local documentation.

Staging runs with `APP_ENV=production` so production safeguards stay active. Set
`APP_URL` and `MOUSE28_PRODUCTION_URL` to `https://staging.mouse28.com`, set
`MOUSE28_DEPLOYMENT_ENVIRONMENT=staging`, enable Telescope, and keep Nightwatch
disabled. Use an isolated Sentry environment before running
`php artisan app:verify-deployment`. Production enables Nightwatch instead of
Telescope. Telescope stores its staging entries in the application's database
and is restricted to administrator accounts. New Debug Bar is restricted to the
local environment and is not enabled on either Forge site.

Staging Telescope excludes contact/newsletter request batches (including SQL and
exception entries) and redacts submitted contact fields and flashed old input from
other recorded requests. `TELESCOPE_RETENTION_HOURS` defaults to 48. The application's
daily, non-overlapping `telescope:prune` schedule runs only when Telescope is enabled
and `MOUSE28_DEPLOYMENT_ENVIRONMENT=staging`. Verify Forge's scheduler invokes
`php artisan schedule:run` for the staging site's current release before relying on
this retention policy. This code change does not provision or change Forge cron.
Contact-message retention remains manual and is unaffected.

The audit-hardening release adds `slug_locked_at` to posts, guides, and episodes.
Run its forward migration before serving the new editor. Existing past-dated URLs
are conservatively locked, including unpublished and soft-deleted content. The
lock is internal, not mass assignable. Editors can correct publication dates or
unpublish without unlocking the permalink; intentional URL migrations need a
separately reviewed redirect plan. Public content imports remain explicit canonical
synchronization operations and do not export this local editor metadata.

Deployment preflight validates `MOUSE28_CONTACT_EMAIL` separately from the sender
and administrator recipients. It must not retain the example address.

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

Run `php artisan content:sync-from-production --isolated=1` from the local Mouse28 checkout to replace local published posts, guides, episodes, podcast display metadata, and their referenced public media with the current production versions. The command uses the `cold-moon` SSH alias and `/home/forge/mouse28.com/current` site path by default; override them with `MOUSE28_PRODUCTION_SSH_HOST` and `MOUSE28_PRODUCTION_SITE_PATH` when the Forge target changes.

The sync is one-way and refuses to run when the current application environment is production. It never exports private users, subscribers, contact submissions, credentials, or environment-specific podcast email. Local drafts and scheduled content are preserved; stale currently published local records are soft deleted.

## Application commands

Use these descriptive command names for new scripts. Existing names remain aliases so deployed scripts continue to work.

| Command | Purpose | Compatibility aliases |
| --- | --- | --- |
| `app:verify-deployment` | Validate production or staging configuration | `app:verify-production` |
| `content:attach-bundled-artwork` | Attach bundled WebP files to matching post and episode slugs | `content:attach-artwork` |
| `content:generate-responsive-artwork` | Generate missing responsive cover variants | `content:generate-artwork`, `content:generate-post-artwork` |
| `content:sync-from-production --isolated=1` | Synchronize public content and media locally | `content:sync-production` |
| `content:export-public` | Export published content to a JSON archive | — |
| `content:import-public` | Import a public archive into a permitted environment | — |

Use `--isolated=1` for sync so overlapping invocations stop with a nonzero exit code before remote processes or local writes. Both command names share the same isolation lock. The framework releases it on completion; interrupted locks expire after one hour. The existing Forge verification command remains supported through its alias; no deployment script changes are required.

## Contact mail queue deployment prerequisite

Before deploying queued contact delivery, configure a supervised Forge worker:
`php artisan queue:work database --queue=contact-mail --timeout=60 --tries=3`.
Use the site's current release directory, keep database `retry_after` greater than
60 seconds (the default is 90), and restart workers after each deployment. Verify
the jobs and failed_jobs migrations are present and that the worker can process a
new, authorized test submission. A default-queue worker does not consume this queue.

Do not backfill historical contacts. New jobs automatically retry only within
23 hours of submission. Inspect failed jobs and provider receipts before a manual
retry; Resend retains idempotency keys for 24 hours, not indefinitely. No production
worker or deployment is created by the application changes themselves.

## Off-site backups

Mouse28's off-site backup job runs as `forge` on `cold-moon`, independently of
Forge's paid database-backup feature and application deployments. The daily cron
schedule is **07:15 UTC** (03:15 New York during daylight-saving time). Its private
installation is `/home/forge/mouse28-offsite-backup`:

- `backup.py` orchestrates the backup; `export-database.php` reads the active
  release's Laravel database configuration without printing credentials.
- `credentials.json` is mode `0600` inside the mode `0700` installation directory.
  Never print, download, or commit this file. The encryption password is the
  existing Mouse28 backup password, retained separately in the original secret
  stores for recovery; this server file must not be its only surviving copy.
- `last-success.json` records the latest verified snapshot. Scheduled output goes
  to `backup.log`, with generic failure stages rather than credentials or data.
- `test_backup.py` provides isolated safety tests using synthetic data.

Each run exports a transactional MySQL dump and archives only persistent public
media from `/home/forge/mouse28.com/storage/app/public`. It preserves the existing
AES-256-CBC/PBKDF2-SHA256 format with 200,000 iterations, verifies local encryption
round trips, and uploads the two encrypted archives followed by `manifest.txt` to
the existing `jdavidson-mouse28-production-backups` B2 bucket. Uploads use
Content-MD5 and are verified against remote size, ETag, and SHA-256 metadata.

Plaintext working files are temporary. Successfully verified encrypted working
bundles are removed from the server; failed uploads retain their encrypted bundle
for retry before the next fresh backup. A process lock prevents overlapping runs,
commands have timeouts, and cron imposes a 15-minute overall limit. The job refuses
to start a new export with less than 1 GiB free disk space.

The migration was verified with snapshot `20260916T024602Z`. The former Mac launch
agent `com.jeffreydavidson.mouse28-backup` is disabled and unloaded. At Jeffrey's
request, its launch-agent file, backup and restore-check scripts, logs, and local
archive directory were removed to macOS Trash and subsequently permanently
deleted with explicit confirmation. No further Mac backups are scheduled.
Keychain credentials and the encryption password were preserved for recovery, as were all Backblaze
archives. Existing server-side local database jobs are unchanged.

The B2 lifecycle policy was verified read-only on September 18, 2026: enabled
rules expire current objects after 90 days, noncurrent versions after 30 days,
and remove expired delete markers. This replaces the former hidden-versions-only
policy; unique dated snapshots are no longer retained indefinitely. No objects
were manually deleted during verification. The existing seven-day Object Lock
is a separate protection, not an indefinite-retention policy.

Forge monitors the job through the **Mouse28 verified off-site backup** heartbeat
on the production site's Observe page. It expects the existing `15 7 * * *`
schedule and allows 30 minutes after the expected run before notifying. A failed,
stuck, or missed backup therefore produces a missing-check-in alert rather than
an immediate exception email. This monitor runs outside the backup server.

The job pings only after a fresh database and media backup has been uploaded and
verified; retrying an older pending upload alone cannot report success. The
endpoint is stored in the server-only `heartbeat-url` file with mode `0600`.
Never commit or log this capability URL. The request has bounded timeouts and
retries, does not follow redirects, and keeps its URL out of process arguments.
A heartbeat delivery failure leaves the verified backup intact, returns failure,
and logs only the generic notification stage. Inspect `backup.log` and
`last-success.json` to distinguish backup failures from monitoring failures.

Forge's account email is `jdavidsonwebdev@gmail.com`; email and in-app
**Heartbeat check-in missed** notifications are enabled, with no servers muted.
The built-in **Backup failed** preference concerns Forge-managed database backups,
not this custom B2 job. Storage, encryption, retention, and cron remain unchanged.

On September 18, 2026, all 13 synthetic safety tests passed locally and on the
server. Fresh snapshot `20260918T213158Z` was verified at 21:32 UTC and Forge
reported **Beating** after its success ping. An actual missed-run email has not
been deliberately triggered or confirmed in the inbox. Original scripts are
preserved as `backup.py.before-heartbeat` and `test_backup.py.before-heartbeat`
in the private server installation for recovery.

For recovery, first verify the manifest's ciphertext hashes, decrypt with the
existing backup password and recorded OpenSSL parameters, then verify plaintext
hashes and archive integrity. A local integrity check of the pre-migration backup
passed, and the new job checks encryption round trips.

An explicitly approved server-side restore rehearsal of B2 snapshot
`20260916T024602Z` passed on September 15, 2026 (New York time):

- All three objects were downloaded on the server and checked against remote
  size, ETag, and SHA-256 metadata; encrypted and decrypted archive hashes matched
  the manifest.
- A separate MySQL 8.0 instance imported all 17 expected tables and 266 rows;
  every restored table passed `CHECK TABLE`.
- All 42 uploaded-media files were extracted into a private test directory and
  verified by size and checksum (7,189,222 uncompressed bytes).
- The temporary instance was stopped and every rehearsal directory removed.
  The existing production MySQL process remained running; `/up` and `/` returned
  HTTP 200 afterward. No backup contents were copied to the Mac.

This verifies database import and media recovery, not a full application boot or
disaster-recovery cutover. Future rehearsals require approval for their isolated
target and cleanup; never import into the live database as a routine check.
Use a private `mkdtemp` directory under `/tmp`, which the existing server AppArmor
policy permits, without changing confinement. Start a separate instance with
`--no-defaults`, its own data directory, socket, PID file, and logs; disable TCP,
MySQL X, scheduled events, binary logging, and file import/export. Use bounded
commands and conservative memory settings. MySQL 8.0 clients do not support
`--no-login-paths`: use `MYSQL_TEST_LOGIN_FILE=/dev/null` with `--no-defaults`
and an explicit test socket to avoid inherited connection settings. Confirm the
connected server's data directory and disabled networking before importing.

The removed Mac automation is not an active fallback. Reinstating it requires
explicit approval and rebuilding its deleted files before enabling a launch agent;
avoid running duplicate schedules. A pre-migration server crontab is preserved at
`/home/forge/mouse28-offsite-backup/crontab.before-migration`; compare it rather
than overwriting a newer crontab, to avoid losing unrelated jobs.

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

Laravel's destructive database commands (`db:wipe`, `migrate:fresh`, `migrate:refresh`, `migrate:reset`, and `migrate:rollback`) are prohibited when `APP_ENV=production`, even with `--force`. This includes Forge staging configured with that environment. Use forward migrations; do not disable this safeguard as a deployment or rollback shortcut.

## After deploying

For responsive artwork, confirm GD WebP support and the persistent local public disk before running `php artisan content:generate-responsive-artwork --type=posts --force` or `php artisan content:generate-responsive-artwork --type=episodes --force` on the explicitly approved target. The legacy `content:generate-post-artwork` alias still defaults to posts only. These additive operations leave original covers and database records untouched; each must be separately approved under the production mutation rules. Run the appropriate type again after publishing/replacing covers. Without generated candidates the public site continues serving originals. Verify candidate URLs return 200 before claiming responsive-image savings on production. Post candidates use `/storage/posts/responsive/`; square episode candidates use `/storage/episodes/responsive/v1/`.

Without `--force`, the artwork command uses Laravel's native production confirmation prompt. Declining it, or running noninteractively without force, cancels generation. A console prompt or flag does not replace the operational approval above.

The Forge/Nginx edge uses a one-year `public, immutable` policy for content-fingerprinted `/build/assets/` and responsive files under `/storage/posts/responsive/` and `/storage/episodes/responsive/v1/`. Unversioned originals, fonts and site images need a shorter freshness policy or versioned URLs. Preserve private/no-store behavior for admin, previews and errors, and do not apply public HTML caching to CSRF tokens or form/session feedback. No edge configuration is changed by the artwork command or benchmark.

On the installed Nginx 1.26 series, a location-level `add_header` stops inheritance
of server-level headers. Cache locations must preserve the shared security
headers explicitly, including HSTS and `X-Content-Type-Options`. Verify live
responses for all three locations after any approved change; configuration
syntax success alone does not prove header behavior. The September 15 audit
found these inherited headers missing; the approved repair now explicitly
preserves them in all three asset locations and was verified against live
asset, public, admin, and error responses.

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
