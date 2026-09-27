---
paths:
  - '**'
---

# General

## Synchronize and clean merged PR branches
After GitHub verifies a PR merged, first confirm `gh pr view` reports `MERGED` and record the PR head, base, and merge commit. Fetch with `--prune`, inspect `git worktree list`, and fast-forward the local base branch from its remote only when clean and non-divergent. GitHub's `gh pr merge --delete-branch` can merge successfully while failing local cleanup because the base branch is attached to another worktree; verify the PR state independently. If a clean, temporary worktree blocks the base checkout, remove that temporary worktree after inspection; never remove a worktree with changes or an active branch. Check out the base branch in the active worktree, then delete the local PR branch after confirming its tip matches the recorded merged head. Squash merges may require `git branch -D` because the squash commit is not an ancestor of the PR tip; use it only after the merge and clean-state checks pass. Never delete `main`, `develop`, checked-out branches, branches with post-merge commits, or active worktrees.

## Pair sourced posts with review dates
Official source and last-reviewed fields are optional for evergreen posts. Policy or planning posts opt into tracking by setting both fields; sourced posts are flagged for review after 180 days. Public pages should link the official source and expose the citation and review date in structured data.

## Create dates through Laravel's Date facade
Use Illuminate\Support\Facades\Date for application date creation (Date::now(), Date::today(), Date::parse()) rather than global date helpers or direct Carbon static creation. Keep accurate Carbon/CarbonInterface object type declarations; Date is a facade, not an object type. Preserve current mutable-date behavior; do not configure immutability or convert objects merely to satisfy analysis.

## Name configurable limits
Do not hard-code behavior-affecting numeric limits or thresholds in application code. Define them as named configuration values with an environment-backed value and a safe default, then read them through the typed Config API.

## Let Forge Nginx resolve trusted client IPs
The current Forge topology resolves trusted Cloudflare proxies in Nginx and passes the result as REMOTE_ADDR. Do not enable blanket Laravel trustProxies('*'): client-supplied forwarded headers can undermine IP-based contact/newsletter throttles. Reassess trusted proxies explicitly if the hosting topology changes.
