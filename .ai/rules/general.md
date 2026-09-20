---
paths:
  - '**'
---

# General

## Synchronize and clean merged PR branches
After GitHub verifies a PR merged, fetch with `--prune`, check out the PR's local base branch, and fast-forward it from its remote only when the worktree is clean, local state is not divergent, and no worktree blocks it. Then delete the local PR branch only after its local or remote tip exactly matches the PR's recorded merged head. Never delete `main`, `develop`, checked-out branches, branches with post-merge commits, worktrees, or uncommitted changes. If the base branch is already checked out in another worktree, leave the current PR branch in place and report the worktree constraint rather than forcing a checkout or deletion.
