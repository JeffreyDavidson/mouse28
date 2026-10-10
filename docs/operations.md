# Production operations

Mouse28 is hosted on Laravel Forge. Use a separate staging site for deployment verification; never treat the production `mouse28.com` site as staging.

## Before deploying

1. Confirm the target commit or tag and review its migrations and storage changes.
2. Confirm the staging or production environment uses persistent database and public-media paths.
3. Create and independently verify a database backup and an uploaded-media backup.
4. Load the target environment and run `php artisan app:verify-deployment`.
5. Stop when the preflight command reports any failure.

Never copy live credentials into the repository, deployment logs, or local documentation.

The preflight also tries to decrypt every stored two-factor secret and recovery code with the site's `APP_KEY`. Values encrypted under a different key make the admin login fail with a server error ("The MAC is invalid"), so the preflight fails and reports how many values are unreadable, without printing them. Clear those columns for the affected users, or restore the key they were encrypted with, then re-enrol two-factor authentication.

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
Its overlap lock, like the daily `model:prune` task's, expires after 60 minutes
instead of the 24-hour default, so a run killed mid-way (out of memory, a hung
server) cannot leave a lock that skips the next day's run.
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

The staged release pipeline described in [`releases.md`](releases.md) has been in
force since 2026-09-29. Both Forge sites track `main` with push-to-deploy **off**;
merging to `main` does not deploy anything. Staging deploys only after successful
push CI on `main` or a `release/**` branch, and production deploys only through
the approved `Promote production` workflow. Do not use the Forge Deploy button: the
installed scripts fail closed without a pinned revision.

After production verification, synchronize `main` into `develop` when Jeffrey
explicitly requests it: fetch the remote branches, update the local `develop`,
fast-forward it to `origin/main` (`git merge --ff-only origin/main`, no
synchronization commit), and push `develop`. This synchronization does not add
feature work to `develop`.
If branch protection rejects the push, stop and ask for the approved resolution;
do not silently disable protection or create a synchronization PR instead.
Feature work still goes through squash-merged pull requests into `develop`.

## Staged release setup

The staged pipeline (`deploy-staging.yml`, `promote-production.yml`,
`staging-smoke.yml`, `production-smoke.yml` and `scripts/forge-deployment.mjs`)
matches The Laravel Architect's. It is enabled by the repository variable
`STAGED_RELEASES_ENABLED=true` (set 2026-09-29); deleting the variable switches it
off. Any change to the settings below needs Jeffrey's explicit approval.

Targets in organization `jeffrey-davidson`, server `cold-moon` (753072), shared
with The Laravel Architect: staging `staging.mouse28.com` (site 3396232) and
production `mouse28.com` (site 3064716).

The pipeline has deployed both sites. The first staging deployment succeeded on
2026-09-29 (run 36623049256, revision `e82d29f`, Forge deployment 78939264). The
first production promotion (release `v2026.09.30`, run 36633297973, Forge
deployment 78945158) succeeded on its second attempt: the first failed safely at
the staging recheck because the production environment's Access token was missing
from the Cloudflare policy, before any deployment was triggered. Release
`v2026.10.0` (Forge deployment 78957195) promoted first time.

### GitHub

- Environments: `staging` and `production`. `production` requires Jeffrey as
  reviewer, allows self-approval, and has no administrator bypass.
- Secrets in **both** environments: `FORGE_DEPLOY_HOOK` (each site's own hook;
  never use the production hook in staging), `CF_ACCESS_CLIENT_ID` and
  `CF_ACCESS_CLIENT_SECRET`. Production promotion rechecks staging, so the token in
  the `production` environment must also be allowed into the staging application.
  Each environment may hold its own token (today "Mouse28 GitHub staging
  deployments" in `staging` and "Mouse28 Github production deployments" in
  `production`) as long as the Cloudflare policy below includes both. A token that
  the policy omits makes `Promote production` fail at its staging recheck with
  "Release marker returned HTTP 302" before it triggers any deployment (this
  happened on 2026-09-29). `mouse28.com` itself is not behind Cloudflare Access.
- Repository secret `COMPOSER_AUTH`: Composer auth JSON with read access to the
  private `jeffreydavidson/creator-kit` package. Every `composer install` in CI and
  the `deployment-runtime` action receives it; a guard test in
  `scripts/forge-deployment.test.mjs` fails if one doesn't. On cold-moon the `forge`
  user's global Composer auth holds the GitHub token the deploys use. Locally,
  Composer needs a GitHub token (or `gh` login) that can read the package.
- Never paste tokens or hook URLs into chat, logs, repository files or workflow
  inputs. Forge's Deployments page shows the deploy-hook URL, token included, in its
  page text: read that page with element inspection, not page-text extraction.

### Cloudflare

- **Access:** the staging application ("Mouse28 Staging") allows the GitHub
  deployment token through its own **Service Auth** policy, "Mouse28 GitHub Staging
  Deployments", which includes the "Mouse28 GitHub staging deployments" and "Mouse28 Github
  production deployments" tokens.
  Do not edit the shared "GitHub Staging Deployment Checks" policy: other
  applications use it, and adding a token there widens their access. A token that
  no attached policy includes gets the Access login redirect (HTTP 302) on every
  route.
- **Cache:** a Cache Rule bypasses the cache for
  `(http.request.uri.path eq "/deployment.json") or (http.request.uri.path eq "/up")`
  on the whole zone, so both hosts are covered. The deployment client also rejects
  a cached marker.

### Nginx (both sites, Forge → site → Edit Nginx configuration)

Validate before each reload; Forge rejects an invalid file and keeps the old one.
Forge trims trailing blank lines when it saves.

- Under the favicon locations, add the revision-marker location as one line:

  ```nginx
  location = /deployment.json { try_files $uri =404; add_header Cache-Control "no-store" always; }
  ```

  A location-level `add_header` replaces the server-level headers for that response
  (see the Nginx 1.26 note under "After deploying"). The marker carries only a
  revision and a deployment ID, so it does not repeat them.
- Remove Forge's default `location = /robots.txt { access_log off; log_not_found off; }`
  line. With it, Nginx looks for a static file, falls back to
  `error_page 404 /index.php`, and serves the application's robots document with
  HTTP **404**, which crawlers treat as "no rules". Production returned 404 for
  `/robots.txt` until 2026-09-29. `/robots.txt` must now return 200 on both hosts.

### Forge site settings

- Git branch `main` on both sites (staging previously tracked `develop`). Changing
  the branch asks for the Forge account password.
- Push-to-deploy **off**. On the Deployments page the toggle is part of the same
  form as the deploy script and only persists after **Save**.
- The deploy script on each site is the one below. Both fail before creating a
  release when `FORGE_VAR_REVISION`, `FORGE_VAR_SOURCE_BRANCH` or the site
  identity does not match. Forge's `forge_deploy_commit` parameter is metadata,
  not checkout pinning; the separate `revision` and `source_branch` hook
  parameters become `FORGE_VAR_REVISION` and `FORGE_VAR_SOURCE_BRANCH`. Staging
  accepts `main` or a numbered `release/YYYY.MM.N`; production accepts only `main`.
- Sites are pinned by `FORGE_SITE_ID` alone. Whether `FORGE_SITE_ROOT` is the site
  folder or its `current` directory is not documented for this layout, so the
  scripts do not rely on it.
- The deployment marker is written only after activation and the health checks.
  In production it must be written **before** the cleanup step, which changes
  directory.
- Runtime heartbeats are not part of deployment verification until they are enabled
  (see "Runtime health monitoring").

### Installed staging deploy script (site 3396232)

```bash
set -e

test "$FORGE_SITE_BRANCH" = main
test "$FORGE_SITE_ID" = 3396232
[[ "${FORGE_VAR_SOURCE_BRANCH:-}" = main || "${FORGE_VAR_SOURCE_BRANCH:-}" =~ ^release/[0-9]{4}\.(0[1-9]|1[0-2])\.[0-9]+$ ]]
[[ "${FORGE_VAR_REVISION:-}" =~ ^[a-f0-9]{40}$ ]]
test "$FORGE_DEPLOY_COMMIT" = "$FORGE_VAR_REVISION"

$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY

if test "$(git rev-parse --is-shallow-repository)" = true; then
git fetch --unshallow origin
fi
git fetch --no-tags origin "$FORGE_VAR_SOURCE_BRANCH:refs/remotes/origin/$FORGE_VAR_SOURCE_BRANCH"
test "$(git rev-parse "origin/$FORGE_VAR_SOURCE_BRANCH")" = "$FORGE_VAR_REVISION"
git checkout --detach "$FORGE_VAR_REVISION"
test "$(git rev-parse HEAD)" = "$FORGE_VAR_REVISION"
test ! -e public/deployment.json

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

npm ci
npm run build
$FORGE_PHP artisan optimize
$FORGE_PHP artisan app:verify-deployment --no-interaction
$FORGE_PHP artisan storage:link
$FORGE_PHP artisan migrate --force

$ACTIVATE_RELEASE()

$RESTART_QUEUES()

mouse28_verified=false
for mouse28_attempt in $(seq 1 12); do
if curl --silent --fail --insecure --max-time 10 --output /dev/null --resolve "staging.mouse28.com:443:127.0.0.1" "https://staging.mouse28.com/up"; then
mouse28_verified=true
break
fi
sleep 5
done
test "$mouse28_verified" = true
test "$(readlink -f "$FORGE_SITE_PATH")" = "$(pwd -P)"
[[ "$FORGE_DEPLOYMENT_ID" =~ ^[1-9][0-9]*$ ]]
printf '{"revision":"%s","deployment_id":"%s"}\n' "$FORGE_VAR_REVISION" "$FORGE_DEPLOYMENT_ID" > public/deployment.json.tmp
mv public/deployment.json.tmp public/deployment.json
```

The local health check calls the site's own name on 127.0.0.1, bypassing Cloudflare
and Access; `--insecure` is acceptable there because the request never leaves the
server.

### Installed production deploy script (site 3064716)

Production's script predates the pipeline. The pins, the checkout block and the
marker block were added to it, and the asset build was moved ahead of the
migration; every other line is the original.

```bash
set -e

test "$FORGE_SITE_BRANCH" = main
test "$FORGE_SITE_ID" = 3064716
test "${FORGE_VAR_SOURCE_BRANCH:-}" = main
[[ "${FORGE_VAR_REVISION:-}" =~ ^[a-f0-9]{40}$ ]]
test "$FORGE_DEPLOY_COMMIT" = "$FORGE_VAR_REVISION"

$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY

if test "$(git rev-parse --is-shallow-repository)" = true; then
git fetch --unshallow origin
fi
git fetch --no-tags origin "$FORGE_VAR_SOURCE_BRANCH:refs/remotes/origin/$FORGE_VAR_SOURCE_BRANCH"
test "$(git rev-parse "origin/$FORGE_VAR_SOURCE_BRANCH")" = "$FORGE_VAR_REVISION"
git checkout --detach "$FORGE_VAR_REVISION"
test "$(git rev-parse HEAD)" = "$FORGE_VAR_REVISION"
test ! -e public/deployment.json

SENTRY_RELEASE="${FORGE_DEPLOY_COMMIT:-$(git rev-parse HEAD)}"
if grep -q '^SENTRY_RELEASE=' .env; then
    sed -i "s/^SENTRY_RELEASE=.*/SENTRY_RELEASE=${SENTRY_RELEASE}/" .env
else
    echo "SENTRY_RELEASE=${SENTRY_RELEASE}" >> .env
fi

# PHP
$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# JS — with memory cap for Next.js builds
export NODE_OPTIONS="--max-old-space-size=1024"
npm ci --production=false
npm run build

$FORGE_PHP artisan optimize
$FORGE_PHP artisan app:verify-production --no-interaction
$FORGE_PHP artisan storage:link --force
# Migrate last: once the schema has changed, nothing may stop the release from activating.
$FORGE_PHP artisan migrate --force

# Activate only if everything succeeded
$ACTIVATE_RELEASE()

# Verify the activated application before removing recovery releases.
for MOUSE28_HEALTH_PATH in /up /; do
    MOUSE28_HEALTH_STATUS=$(curl --silent --show-error --fail \
        --connect-timeout 5 --max-time 15 --retry 2 --retry-delay 2 --retry-max-time 45 \
        --header "Cache-Control: no-cache" --output /dev/null --write-out "%{http_code}" \
        "https://mouse28.com${MOUSE28_HEALTH_PATH}") || exit 1
    if [ "$MOUSE28_HEALTH_STATUS" != "200" ]; then
        echo "Deployment health check failed: ${MOUSE28_HEALTH_PATH} returned HTTP ${MOUSE28_HEALTH_STATUS}" >&2
        exit 1
    fi
done

# Publish the deployment marker only after activation and the health checks pass.
test "$(readlink -f "$FORGE_SITE_PATH")" = "$(pwd -P)"
[[ "$FORGE_DEPLOYMENT_ID" =~ ^[1-9][0-9]*$ ]]
printf '{"revision":"%s","deployment_id":"%s"}\n' "$FORGE_VAR_REVISION" "$FORGE_DEPLOYMENT_ID" > public/deployment.json.tmp
mv public/deployment.json.tmp public/deployment.json

# Cleanup old releases (keep last 5)
cd $FORGE_SITE_PATH
ls -dt releases/* | tail -n +6 | xargs rm -rf 2>/dev/null || true

$RESTART_QUEUES()

echo "Deploy complete: $(date)"
```

In both scripts `migrate --force` is the last step that can stop the deploy before
`$ACTIVATE_RELEASE()`. The migration changes the shared live database, so once it
has run the new release has to go live: a failure after it would leave the previous
release serving a schema it was not written for. The asset install and build,
caching, configuration check and `storage:link` therefore run first, and a failed
`npm ci` or `npm run build` stops the deploy with the database untouched.
Production built its assets after migrating until this order was adopted in
October 2026.

To roll a script back, paste the previous version into Forge, keep push-to-deploy
off unless the earlier merge-triggered deployment is deliberately restored, and confirm the
site's branch setting.

## Syncing public content locally

Run `php artisan content:sync-production --isolated=1` from the local Mouse28 checkout to replace local published posts, guides, episodes, podcast display metadata, and their referenced public media with the current production versions. The command uses the `cold-moon` SSH alias and `/home/forge/mouse28.com/current` site path by default; override them with `MOUSE28_PRODUCTION_SSH_HOST` and `MOUSE28_PRODUCTION_SITE_PATH` when the Forge target changes.

The sync is one-way and refuses to run when the current application environment is production, or when the local `APP_URL` is the live site's address (mouse28.com or www.mouse28.com). It never exports private users, subscribers, contact submissions, credentials, or environment-specific podcast email. Local drafts and scheduled content are preserved; stale currently published local records are soft deleted.

## Application commands

Use these descriptive command names for new scripts. Existing names remain aliases so deployed scripts continue to work.

| Command | Purpose | Compatibility aliases |
| --- | --- | --- |
| `app:verify-deployment` | Validate production or staging configuration | `app:verify-production` |
| `content:attach-bundled-artwork` | Attach bundled WebP files to matching post and episode slugs and generate their responsive variants | `content:attach-artwork` |
| `posts:generate-image-variants` | Generate missing responsive variants for post covers (`--force` regenerates verified ones) | — |
| `episodes:generate-image-variants` | Generate missing square responsive variants for episode covers (`--force`) | — |
| `guides:generate-image-variants` | Generate missing responsive variants for guide covers (`--force`) | — |
| `podcasts:generate-image-variants` | Generate missing responsive variants for the podcast cover (`--force`) | — |
| `media:repair-responsive-images` | Generate missing variants for every media type, then verify them all (`--force`) | — |
| `media:verify-responsive-images` | Report, without writing, whether every stored image has its required variants | — |
| `content:sync-production --isolated=1` | Synchronize public content and media locally | `content:sync-from-production` |
| `content:export-public` | Export published content to a JSON archive (absolute path) | — |
| `content:import-public` | Import a public archive into a permitted environment | — |

The generate and repair commands are isolatable: a second run while one is in progress stops with a nonzero exit code. They report counts only, never media paths, and exit nonzero when any source image is missing or cannot be decoded. `content:generate-responsive-artwork` and its `content:generate-artwork` and `content:generate-post-artwork` aliases were retired with the stored media release; use the commands above.

Use `--isolated=1` for sync so overlapping invocations stop with a nonzero exit code before remote processes or local writes. Both command names share the same isolation lock. The framework releases it on completion; interrupted locks expire after one hour. The existing Forge verification command remains supported through its alias; no deployment script changes are required.

## Database (SQLite)

Staging and production run SQLite, like The Laravel Architect (owner decision 2026-10-09). Staging switched from MySQL on 2026-10-09 and production on 2026-10-10.

- **File:** `/home/forge/<site>/shared/database.sqlite`, outside the releases, with its `-wal` and `-shm` files beside it. `DB_CONNECTION=sqlite` and `DB_DATABASE` (that absolute path) are set in Forge's environment.
- **Settings:** `config/database.php` uses `busy_timeout` 5000, WAL, `synchronous` NORMAL and `transaction_mode` IMMEDIATE.
- **Backups:** the hourly `backup-all.py` writes `*.sqlite.gz` with `sqlite3 .backup`. The off-site job is described under "Off-site backups".
- **Copy by hand:** `sqlite3 database.sqlite ".backup 'copy.sqlite'"` is consistent while the site runs. Check the copy with `PRAGMA integrity_check`.

**Environment changes without a deploy.** Forge saves only the site's master `.env`; each release has its own copy, made at deploy time. To apply a change to the running release: `cp /home/forge/<site>/.env /home/forge/<site>/current/.env`, then `php8.5 artisan optimize` in `current`. The next normal deploy copies the same file.

**Queue workers after a cache-store change.** `queue:restart` writes its signal to the database cache. When the database behind that cache changes, running workers never see the signal, so restart them in Forge (Processes).

**Maintenance mode and deploys.** A deploy can't run in maintenance mode: production's deploy script and both workflows' smoke checks load `/`, which returns 503 then. `/up` stays available.

**The old MySQL databases** stay untouched until 2026-11-09 as a fallback, then the owner drops them. To roll back before then, set `DB_CONNECTION=mysql` and `DB_DATABASE=mouse28` in Forge (the MySQL login lines are commented out there), apply the change without a deploy as above, and restart the workers. Anything written to SQLite since the switch would be lost, so a rollback is only sensible in the first hours after it.

**How the move was done.** A one-off command built a new SQLite file from the migrations, copied every table and compared row counts and checksums. It was removed once both sites had switched; see PRs 565 and 568. The CI MySQL lane goes one release after that.

## Resend bounce and complaint webhook

Resend reports bounces and spam complaints to `POST https://mouse28.com/webhooks/resend`; the application then suppresses those addresses so they are never mailed again (see `docs/architecture.md`). To set it up on production:

1. In the Resend dashboard, open Webhooks, add `https://mouse28.com/webhooks/resend` and select `email.bounced`, `email.complained` and `email.suppressed`. Resend webhooks are account-wide, so events for the other projects' mail also arrive; they are ignored unless the address is a Mouse28 subscriber.
2. Copy the signing secret (`whsec_...`) into the production environment as `RESEND_WEBHOOK_SECRET` in Forge and save it, spelled exactly like that. Forge writes only the master `.env`; the running release keeps the copy it took at deploy time, so rebuilding the config cache does not load the value. Re-run **Promote production** for the revision that is already live (same `revision` and `staging_run_id`) and approve it; Forge's own **Deploy Now** button fails because the deploy script requires the revision the workflow passes. The deploy restarts the queue workers.
3. The Resend webhook page has no test button. Send one email to Resend's test address `bounced@resend.dev` (for example `Mail::raw(...)` through tinker on production) to produce an `email.bounced` event, then confirm a 2xx in the webhook's event log (the controller answers `204`). A 503 means the secret is not loaded; a 403 means the secret does not match this webhook.

`app:verify-deployment` does not require the secret, so a release can ship before the webhook exists. Staging never sends email and needs no webhook.

## Legacy Resend newsletter audience

Before double opt-in, sign-ups were stored only in a Resend audience. The one-time import command (`newsletter:import-resend-subscribers`, available in tag `v2026.10.1`) was dry-run on production on 2026-10-01 and would have imported 10 contacts, but most were spam sign-ups from the old unverified form, so the import was skipped: real readers sign up again through the confirmed form. The command and the code that read the audience were removed. The audience itself and its contacts live in the Resend dashboard and are owner-managed: delete the spam contacts, or the whole Mouse28 audience (not Ringside's), so Resend holds no subscriber data.

The application only uses Resend to send email. Its `RESEND_API_KEY` is the only Resend setting; the former `RESEND_ENABLED` and `RESEND_AUDIENCE_ID` variables are unused and can be removed from the Forge environment files. Prefer a sending-only key restricted to the `mouse28.com` domain; a full-access key is only needed for contact or audience management.

## Newsletter queue workers

Newsletter sign-up confirmations and issue deliveries are queued on the **default** queue (database connection). Contact emails use the same queue. Production and staging each run two supervised workers in Forge: the original contact-mail worker (`--queue=contact-mail`, retiring after the contact-inquiries rollout below) and a "Default queue worker" (`queue:work database --queue=default --sleep=3 --timeout=60 --tries=3`, added 2026-10-01). Keep `retry_after` above 60 seconds and restart workers after each deployment. Issue emails are rate limited to `MOUSE28_NEWSLETTER_DELIVERY_RATE_LIMIT` per second (default 5) and retried for up to a day, so a large send simply takes a few minutes. To send an issue: publish it, use "Send test email" to check the email, then "Send to subscribers" (it cannot be undone), and watch the delivery count on the issue's edit page. Staging never delivers email, so verify real delivery in production.

## Contact mail queue

Contact emails (`SendContactInquiryEmails`) run on the **default** queue of the
database connection, served by the "Default queue worker" described above. Keep
database `retry_after` greater than 60 seconds (the default is 90) and restart
workers after each deployment. Queued contact payloads are encrypted and carry only
the inquiry ID, so they depend on the site's `APP_KEY`.

Do not backfill historical contacts. New jobs automatically retry only within
23 hours of submission. Inspect failed jobs and provider receipts before a manual
retry; Resend retains idempotency keys for 24 hours, not indefinitely. No production
worker or deployment is created by the application changes themselves.

### Rollout of contact inquiries (`refactor/contact-inquiries`)

Before this release, contact emails ran on a dedicated `contact-mail` queue
(`queue:work database --queue=contact-mail --timeout=60 --tries=3`). The release
copies `contact_messages` into the encrypted `contact_inquiries` table (same IDs and
timestamps; `contact_messages` is kept) and moves new contact jobs to the default
queue. A legacy `SendContactMessageEmails` job still on `contact-mail` forwards its
ID to the new job. Follow these steps in order; Forge changes need Jeffrey's approval.

1. **Before the release (owner, Forge):** confirm production and staging each still
   run the "Default queue worker" (`--queue=default`). If either is missing, add it
   (or change the contact-mail worker to `--queue=default,contact-mail`) before
   releasing. Without a default-queue worker, contact emails silently stop.
2. Keep the `contact-mail` worker running through the release so it drains old jobs.
3. **Backup (owner):** confirm a fresh, independently verified database backup newer
   than the latest `contact_messages.created_at` (the off-site job's
   `last-success.json`), or take one. Record `SELECT COUNT(*), MAX(id) FROM contact_messages;`,
   the `contact-mail` rows in `jobs`, and any contact jobs in `failed_jobs`.
4. **Staging:** after the staged deployment, check that `contact_inquiries` has the
   same count as `contact_messages`, open an older message in the admin, then submit
   a test contact and confirm both emails are stamped on its page.
5. **Production (owner approval to promote):** promote at a quiet time. Migrations run
   before activation, so a submission between `migrate` and activation is stored only
   in `contact_messages` (the old worker still emails it). Afterwards run
   `SELECT id FROM contact_messages WHERE id > <MAX(id) from step 3>;` and record any
   stragglers for the follow-up. Confirm the counts, then a test contact with both stamps.
6. **After the release (owner, Forge):** once `jobs` has no `contact-mail` rows and no
   contact job in `failed_jobs` needs a retry, remove the `contact-mail` worker on both
   sites (or simplify a combined worker to `--queue=default`).
7. **Follow-up PR:** delete the `SendContactMessageEmails` compatibility class, copy any
   stragglers, and drop `contact_messages` once Jeffrey approves.

Rolling back to the previous tag keeps working because `contact_messages` still
exists, but inquiries submitted after the release are only in `contact_inquiries`.

## Runtime health monitoring

`/up` always checks the database. Runtime heartbeats are off by default (`RUNTIME_HEALTH_ENABLED=false`). Before enabling them on a site:

1. Confirm the site's Forge scheduler is running. Since 2026-09-28 each site has one Forge scheduled job, **Mouse28 Scheduler** and **Mouse28 Staging Scheduler**, running `php8.5 /home/forge/<site>/current/artisan schedule:run` every minute as `forge`. Forge installs these in `/etc/crontab`, not in the `forge` user's crontab, and logs to `/home/forge/.forge/scheduled-<id>.log`. A former hand-added production entry in the `forge` crontab was removed to avoid running the scheduler twice; the prior crontab is saved as `/home/forge/backups/crontab.before-scheduler-cleanup-20260928T152816Z`. Keep exactly one scheduler per site.
2. Confirm the supervised default-queue worker is running (see "Newsletter queue workers"). The legacy `contact-mail` worker does not consume the default queue.
3. Set `RUNTIME_HEALTH_ENABLED=true` (and optionally `RUNTIME_HEALTH_MAX_AGE`, default 300 seconds), deploy, wait a few minutes, and confirm `/up` returns `{"status":"up"}`.

If either heartbeat goes stale, `/up` returns `500` with `{"status":"down"}`.

## Off-site backups

Mouse28's off-site backup job runs as `forge` on `cold-moon`, independently of
Forge's paid database-backup feature and application deployments. The daily cron
schedule is **07:15 UTC** (03:15 New York during daylight-saving time). Its private
installation is `/home/forge/backups/.control/mouse28-offsite` (it was `/home/forge/mouse28-offsite-backup` until the 2026-09-23 centralization under `/home/forge/backups`):

- `backup.py` orchestrates the backup; `export-database.php` reads the active
  release's Laravel database configuration without printing credentials.
- `credentials.json` is mode `0600` inside the mode `0700` installation directory.
  Never print, download, or commit this file. The encryption password is the
  existing Mouse28 backup password, retained separately in the original secret
  stores for recovery; this server file must not be its only surviving copy.
- `last-success.json` records the latest verified snapshot. Scheduled output goes
  to `/home/forge/backups/logs/mouse28-offsite.log`, with generic failure stages rather than credentials or data.
- `test_backup.py` provides isolated safety tests using synthetic data.

Each run exports an SQLite dump (`sqlite3 -readonly .dump`, read in one transaction),
restores it into a scratch file and requires `PRAGMA integrity_check` and
`PRAGMA foreign_key_check` to pass, and records `database_format=sqlite` in the manifest.
Restore a dump with `sqlite3 new.sqlite ".read database.sql"`. Backups made before
2026-10-10 hold a transactional MySQL dump. Each run also archives only persistent public
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
and logs only the generic notification stage. Inspect `/home/forge/backups/logs/mouse28-offsite.log` and
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

The upload step shells out to the AWS CLI. It now uses **AWS CLI v2, installed for
the `forge` user** at `/home/forge/.local/bin/aws` (a self-contained bundle, so a
system Python upgrade cannot break it); `backup.py` calls that path. The `forge` user
has no passwordless `sudo`, so a system-wide install is not possible from Forge
commands. The previous script is kept as `backup.py.before-aws-v2`.

Incident, 2026-09-25 to 2026-10-01: the Ubuntu/Python upgrade on 2026-09-24
(15:36 UTC) left the old pip-installed `/usr/local/bin/aws` (CLI v1, shebang
`/usr/bin/python3`) without its `awscli` module, so every run failed at "upload and
verify encrypted backup" and then "retry pending encrypted uploads" (a stuck pending
bundle is retried first and blocks new ones). The Forge heartbeat showed *Missing*
for a week. It was diagnosed with `/usr/local/bin/aws --version`
(`ModuleNotFoundError: No module named 'awscli'`), repaired by the user-level v2
install and the one-line path change, and a manual run uploaded the stuck
`20260925T071502Z` bundle and a fresh `20261001T185413Z` snapshot. If the heartbeat
goes missing again, first run `/home/forge/.local/bin/aws --version`, then read the
log's last lines, then list `/home/forge/backups/production/mouse28.com/offsite/pending`.

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
`/home/forge/backups/.control/mouse28-offsite/crontab.before-migration`; compare it rather
than overwriting a newer crontab, to avoid losing unrelated jobs.

## Deploying

The Forge deployment should install locked Composer dependencies, install locked Node dependencies, build assets, run forward-only migrations, link public storage, refresh optimized caches, and restart queue workers after the release is activated.

The production Forge script runs `php artisan app:verify-production --no-interaction`
after optimization, before migrations, and stops on failure. Immediately after
release activation, bounded HTTP checks require `/up` and `/` to return exactly
200 before the deployment marker is published and release cleanup proceeds. These
checks are part of every pipeline deployment; changing the script does not itself
deploy the site.
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

Responsive image variants are generated automatically when an editor saves a post, episode, guide or the podcast settings with a cover (the model observers call `ResponsiveImageLifecycle`), and replacing or permanently deleting a cover removes the previous original and its variants after the database commit. Commands are only needed for images stored before the stored media release, or to repair a failed generation (the site logs a warning naming the command to run). Confirm GD WebP support and the persistent local public disk first. After the stored media release reaches production, the owner runs `php artisan media:repair-responsive-images` once (an approved production mutation): it is additive, writes the new variants beside the originals (at the time of writing 24 post and 6 episode files), and leaves originals and database records untouched; then `php artisan media:verify-responsive-images` should report every image verified. Until the variants exist the public site serves the originals. Verify candidate URLs return 200 before claiming responsive-image savings on production. Variants live beside each original as `{directory}/responsive/{filename}-{width}.webp` for the widths in `config/media.php` (480, 640, 768 and 1280 pixels, never wider than the original): post candidates under `/storage/posts/responsive/`, square episode candidates (centered crops) under `/storage/episodes/responsive/`, guide candidates under `/storage/guides/responsive/` and podcast candidates under `/storage/podcast/responsive/`. The content-addressed variants from the previous scheme (`posts/responsive/<sha256>-<width>.webp` and `episodes/responsive/v1/`) are no longer referenced; they stay on disk until a later, dry-run-first prune removes them.

The Forge/Nginx edge uses a one-year `public, immutable` policy for content-fingerprinted `/build/assets/` and responsive files under `/storage/posts/responsive/` and `/storage/episodes/responsive/v1/`. Variant names now follow the original's file name rather than a content fingerprint, so this is only safe because a stored image is never overwritten in place: an admin upload always gets a new random file name (so new variant URLs), bundled artwork is only copied when its file is missing, and a regenerated variant has the same bytes. Never overwrite a stored image or variant in place with different content; store a new file and point the record at it. New post variants (`/storage/posts/responsive/`) sit under the existing immutable rule; new episode variants (`/storage/episodes/responsive/`, outside `/v1/`), guide and podcast variants do not, and get the default freshness. Unversioned originals, fonts and site images need a shorter freshness policy or versioned URLs. Preserve private/no-store behavior for admin, previews and errors, and do not apply public HTML caching to CSRF tokens or form/session feedback. No edge configuration is changed by the media commands or benchmark.

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
