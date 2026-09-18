---
paths:
  - '**'
---

# General

## Synchronize and clean merged PR branches
After GitHub verifies a PR merged, fast-forward local `develop` from `origin/develop` only when the worktree is clean, local state is not divergent, and no worktree blocks it. Delete a merged feature branch only after its local or remote tip exactly matches the PR’s recorded merged head; never delete `main`, `develop`, checked-out branches, branches with post-merge commits, worktrees, or uncommitted changes. Fetch with `--prune` before broader cleanup, but do not treat pruning as proof of a safe merge.
