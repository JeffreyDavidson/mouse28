---
paths:
  - '**'
---

# General

## Synchronize and clean merged PR branches
After GitHub verifies a PR merged, first confirm `gh pr view` reports `MERGED` and record the PR head, base, and merge commit. Fetch with `--prune`, inspect `git worktree list`, and fast-forward the local base branch from its remote only when clean and non-divergent. GitHub's `gh pr merge --delete-branch` can merge successfully while failing local cleanup because the base branch is attached to another worktree; verify the PR state independently. If a clean, temporary worktree blocks the base checkout, remove that temporary worktree after inspection; never remove a worktree with changes or an active branch. Check out the base branch in the active worktree, then delete the local PR branch after confirming its tip matches the recorded merged head. Squash merges may require `git branch -D` because the squash commit is not an ancestor of the PR tip; use it only after the merge and clean-state checks pass. Never delete `main`, `develop`, checked-out branches, branches with post-merge commits, or active worktrees.
