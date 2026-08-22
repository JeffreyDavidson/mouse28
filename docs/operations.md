# Production operations

Mouse28 is hosted on Laravel Forge. Use a separate staging site for deployment verification; never treat the production `mouse28.com` site as staging.

## Before deploying

1. Confirm the target commit or tag and review its migrations and storage changes.
2. Confirm the staging or production environment uses persistent database and public-media paths.
3. Create and independently verify a database backup and an uploaded-media backup.
4. Load the target environment and run `php artisan app:verify-production`.
5. Stop when the preflight command reports any failure.

Never copy live credentials into the repository, deployment logs, or local documentation.

## Deploying

The Forge deployment should install locked Composer dependencies, install locked Node dependencies, build assets, run forward-only migrations, link public storage, refresh optimized caches, and restart workers only when queued jobs are introduced.

Do not clean demo content as part of an unattended deployment. After verified backups and real-content review, `php artisan content:clean-seeded --force` removes only the documented demo slugs in one transaction.

## After deploying

Verify all of the following against the deployed commit:

- `php artisan migrate:status` has no pending migrations.
- `/up` returns HTTP 200 and reports the database as available.
- `/`, `/blog`, `/guides`, `/episodes`, `/about`, `/contact`, `/search`, and `/admin/login` render successfully.
- `/sitemap.xml`, `/rss/blog`, and `/rss/podcast` return valid XML.
- Public uploaded-media URLs return successful responses.
- Turnstile appears once per protected page without console errors.
- Contact mail and newsletter subscriptions succeed with production providers.

## Rollback

1. Stop the deployment if health or smoke checks fail.
2. Redeploy the last known-good tag.
3. Restore only from independently verified database and media backups when data or schema restoration is required.
4. Repeat migration, health, media, public-page, and admin-boundary checks.

Never delete the only verified rollback artifacts during an incident.
