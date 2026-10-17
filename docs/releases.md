# Release process

`develop` is the integration branch for routine work. Start feature branches
from an up-to-date `develop` and squash-merge reviewed feature PRs into it after
CI passes. `main` contains approved releases; it identifies releasable code, not
necessarily the revision currently deployed to production.

```text
feature branch → reviewed PR → develop
                                  ↓ (pause develop merges)
                         release/YYYY.MM.N → CI → staging → review
                                                       ↓ approved regular merge
main → CI → staging verification → explicit production approval
  ↓ (after successful main staging)
fast-forward develop to main
```

This process matches The Laravel Architect's and has been in force since
2026-09-29, when the repository variable `STAGED_RELEASES_ENABLED` was set to
`true`. The Forge and Cloudflare setup behind it is recorded in "Staged release
setup" in `operations.md`.

## Naming

Releases use a monthly counter that starts at zero: branch `release/YYYY.MM.N`
and annotated tag `vYYYY.MM.N` (for example, `release/2026.10.0` and
`v2026.10.0`). Tags up to September 2026 (`v2026.09.1` to `v2026.09.30`) are
dates, not counters; `v2026.09.30` is the last date-based tag and `v2026.10.0`
is the first counter-numbered release.

## Cut and validate a release

1. Choose the next calendar release number and cut `release/YYYY.MM.N` from
   `develop`. Do not use release branches for routine feature, fix,
   performance, documentation, or dependency work. Before cutting, check
   `operations.md` for rollout steps owed by the included changes (for
   example, "Rollout of contact inquiries": default-queue worker and backup
   confirmed before release, `contact-mail` worker retired after).
2. Pause merges into `develop` while the release is under validation. Keep the
   branch frozen until its release PR is merged or the release is cancelled.
   If validation finds a release-specific defect, prepare a focused fix branch
   from the release branch and merge it back through a reviewed PR. Every
   release branch update creates a new candidate SHA and requires fresh staging
   verification; do not merge the fix into `develop` during the freeze. The
   post-release fast-forward carries the fix back into `develop`.
3. Push the release branch. CI runs on that exact branch and, after successful
   push CI, `Deploy staging` deploys the tested full SHA. The staging workflow
   rejects a stale candidate if its source branch has advanced. Review public
   pages, affected behavior, and the authenticated admin boundary on staging.
   Once the release PR is open (step 4), run the **Staging Verifier** agent on
   the candidate (release skill, step 6). It reads push CI and the
   `Deploy staging` run for the exact SHA, including that run's own smoke step
   between its marker checks, maps each PR in the release to a check that
   covers it, and ends with "Ready to ship: yes/no". Put its report in the
   release PR description. A "no" blocks the release until a new candidate
   passes.
4. Open a release PR from the release branch into `main` with a Conventional
   Commit title (`chore(release): release YYYY.MM.N`). After review and
   approval, merge it with a regular merge commit to preserve ancestry. Do not
   squash or rebase the release PR.
5. The merge commit on `main` receives push CI. After CI succeeds, staging
   deploys and verifies that exact `main` revision. Production promotion must
   use this successful `main` staging run; the earlier release-branch staging
   run is only for pre-merge validation.

## Validate staging

`Deploy staging` runs after successful **push CI** on `release/*` and `main`,
not PR CI. It checks out the tested full commit SHA and confirms the source
branch still points to it before deployment. Deployment and production
promotion share a concurrency lock, so staging cannot be replaced while
promotion is checking it. Running deployments are never auto-cancelled.

If Forge accepts a staging trigger but the deploy verifier reaches its deadline,
the workflow does not trigger another deployment. It checks the currently
served full SHA and health endpoint, runs the smoke suite, and checks the SHA
again. The staging run succeeds only when all these read-only recovery checks
pass. Otherwise, inspect Forge before retrying because the original trigger may
have completed late.

Read-only requests (the Access diagnostic, `/deployment.json` and `/up`) are
made three times in total when they time out, cannot connect, or return HTTP
502, 503 or 504, waiting 5 and then 15 seconds between attempts, so a slow or
briefly overloaded server does not fail the run. While waiting for a triggered
deployment, a failed read only means "not ready yet": polling continues until
the 11-minute deadline. The Forge trigger itself is never retried. A failure
before the trigger step (for example in **Diagnose staging Access
credentials**, or production's staging recheck) means nothing was deployed, so
re-running the failed job is safe. Only a failure that says to inspect Forge
before retrying a trigger leaves the deployment uncertain.

Forge checks out the requested revision before installing dependencies. Only
after activation and the deployment checks succeed does it publish
`/deployment.json`, containing the full revision and Forge deployment ID. This
file must not be cached by Cloudflare or Nginx. The workflow requires this marker
and `/up`, runs the HTTP smoke suite against the deployed revision, then checks
the marker again. A redirect to Cloudflare Access login is a failure, not a
successful application response. The smoke suite's HTTP client retries once,
after 2 seconds, when a request cannot connect; an HTTP error status is never
retried.

Staging never delivers email: its `MAIL_MAILER` is `array`, which
`app:verify-deployment` requires, so confirmation, test and
newsletter emails are discarded there even though the queue and worker run them.
Verify the pipeline on staging (sign-up stored, jobs consumed, no failed jobs,
pages and admin) and verify real delivery in production in a safe order: sign up
with an address you control and confirm it, send an issue's "Send test email"
(admin addresses only), and only then send to readers. A staging admin login that
fails with a server error ("The MAC is invalid") means a stored two-factor secret
was encrypted with a different `APP_KEY`; the preflight check described in
`operations.md` reports it.

For production approval, choose the successful staging run created for the
merged `main` SHA. A newer staging deployment invalidates a pending manual
review; select and review the new revision before promotion.

### Wait for a CI retry

Before requesting a retry, verify the parent CI run's `head_sha` equals the full
candidate SHA and capture `run_attempt + 1`. Request the retry once, then use the
shared bounded waiter with that recorded attempt. A SHA-only green result can
still describe older checks while the new attempt is registering.

Replace the quoted placeholders with the selected CI run ID and full candidate
SHA. The commands stop if the run belongs to a different SHA or the retry request
fails:

```bash
repo=JeffreyDavidson/mouse28
run_id='<CI-run-id>'
sha='<full-candidate-SHA>'
run=$(gh api "repos/$repo/actions/runs/$run_id") &&
jq -e --arg sha "$sha" '.head_sha == $sha' <<<"$run" &&
next_attempt=$(jq -er '.run_attempt + 1' <<<"$run") &&
gh run rerun "$run_id" --failed --repo "$repo" &&
~/.agents/skills/merge-pr/scripts/wait-for-checks.sh "$sha" auto 2400 "$repo" --require-run "$run_id:$next_attempt"
```

For a cancelled whole run, omit `--failed`; for a single job, replace it with
`--job <job-database-id>`. Capture the parent run's next attempt first in either
case. Never calculate it after rerunning. Repeat `--require-run RUN_ID:ATTEMPT`
when several CI runs must pass. The guard adds to the commit checks and waits
through delayed registration; a missing or pending attempt cannot count as green.
After success, freshly verify the PR head SHA, merge state and checks before merge.

### Recover from stuck or cancelled runs

- **Release branch push CI cancelled** (for example by a GitHub runner
  incident): staging only follows successful push CI, so re-run that CI run
  using the guard above, omitting `--failed` for a whole cancelled run. Success
  of the requested attempt triggers a new `Deploy staging` run for the same SHA.
  Wait for that new staging run; an earlier success on the SHA is not retry evidence.
- **Approved promotion has no runner after about five minutes:** nothing has
  been deployed yet. Cancel the run and dispatch `Promote production` again
  from `main` with the same revision, staging run ID and reviewed attempt, then
  approve it.
- **A PR's CI never starts:** close and reopen the PR. The `reopened` event
  starts CI on the same head SHA without a new commit.

## Promote production

1. Choose a successful `Deploy staging` run for the full SHA on `main` that was
   created after the release PR merged. Record its run ID and the exact
   `run_attempt` reviewed; both identify the staging evidence for approval.
   Before requesting a staging retry, record the next attempt. Wait for that
   exact attempt to register and complete successfully, then review its staging
   deployment. An earlier success on the same run ID cannot clear the retry.
2. Confirm rollback artifacts, migration safety, media requirements, queue
   worker prerequisites and any one-time commands from `operations.md`. Code
   rollback does not undo database migrations. Prefer forward-compatible
   migrations and forward fixes.
3. **`cold-moon` is shared.** GitHub's concurrency lock covers only this
   repository. Check Forge for a running deployment on any `cold-moon` site and
   never promote Mouse28 and The Laravel Architect at the same time.
4. Dispatch `Promote production` **from main**, providing that staging run ID,
   reviewed `staging_run_attempt` and the full revision. This request is not a substitute for environment
   approval.
5. Review the waiting `production` environment job and approve the exact
   revision. The workflow validates the staging run's workflow, source
   repository, event, branch, exact attempt, conclusion and SHA, and requires the
   successful staging job from that attempt. A newer attempt invalidates the
   previous review; review it before dispatching again. The workflow verifies
   staging still serves it before triggering production. Old approvals cannot authorize a
   different SHA.
6. Require production deployment verification and the HTTP smoke suite to pass.
   A timed-out trigger is an uncertain deployment: inspect Forge before retrying.
7. Record the successful production deployment with the next annotated calendar
   tag, `vYYYY.MM.N`, on the full deployed SHA. Verify no existing tag uses that
   version, push the tag without force, and record the Forge deployment and
   workflow run. Never move or reuse a tag.
8. Once the tag is on the remote, create a GitHub Release for it with the
   release notes (the release's meta-repo cycle notes):
   `gh release create <tag> --verify-tag --title "<tag> — <short name>" --notes-file <notes>`.

The staging attempt is required; do not infer it from the current run after a
retry. Use the values captured from the staging execution you reviewed:

```bash
gh workflow run promote-production.yml --repo JeffreyDavidson/mouse28 --ref main \
  -f revision='<full-reviewed-SHA>' \
  -f staging_run_id='<reviewed-staging-run-ID>' \
  -f staging_run_attempt='<reviewed-staging-attempt>'
```

## Synchronize develop after release

After the release has merged into `main` and its resulting revision has passed
CI and staging verification, fetch the latest refs and verify `develop` is an
ancestor of `origin/main`:

```sh
git fetch origin main develop
git merge-base --is-ancestor origin/develop origin/main
git switch develop
git merge --ff-only origin/main
git push origin develop
```

The push is a normal fast-forward update. Do not create a sync merge commit or
force-push. If the ancestry check or fast-forward fails, stop and resolve the
branch history before updating `develop`.

## Hotfixes and rollback

An urgent production fix that cannot wait for the next release uses a hotfix:
branch `hotfix/<description>` from `main`, open a PR into `main`, and squash
merge it after review and CI (as `AGENTS.md` says). The `main` merge commit then
follows the normal path: push CI, `Deploy staging` of that exact revision,
approved `Promote production`, and a patch tag. Leave `develop` alone; the fix
reaches it through the next release's fast-forward sync. If `main` holds a
release that has merged but not been promoted yet, promote that release first.

Rollback is an explicit operational decision, not automatic branch rewriting.
Verify the previous release remains compatible with the current database and
stored media before reactivation. Restore data only through a separately
approved recovery procedure. Record failed releases and recovery; do not move
tags to conceal them.

## Release profile

Read by the `release` agent skill and the Staging Verifier. Keep it in step with the process above.

```yaml
version_scheme: calendar
tag_format: vYYYY.MM.N          # N starts at 0 each month
integration_branch: develop
release_base: develop
release_branch: release/YYYY.MM.N
release_pr_merge: merge          # regular merge commit; never squash or rebase
release_commit: false            # the release branch is exactly the tip of develop
staging: workflow "Deploy staging" (workflow_run after push CI on main and release/**)
staging_smoke: the "Run application smoke checks" step inside the Deploy staging run, between its two marker checks of the candidate SHA
staging_verify: https://staging.mouse28.com/deployment.json (revision), https://staging.mouse28.com/up (200); behind Cloudflare Access, so read inside the Deploy staging run
verifier: staging-verifier       # its "Ready to ship" report goes in the release PR description; "no" blocks the release
promote: workflow promote-production.yml, inputs revision (40-char SHA) + staging_run_id + staging_run_attempt, protected `production` environment; never promote while another cold-moon site is deploying
verify: https://mouse28.com/deployment.json (revision), https://mouse28.com/up (200)
github_release: required         # a GitHub Release with the notes for every tag
sync_integration: fast-forward
backup_gate: the production deploy script takes and integrity-checks a database backup (sqlite3 .backup + PRAGMA integrity_check) automatically when the release has pending migrations and stops before migrating if either fails; the media backup stays a manual, independently verified step (see operations.md)
```
