---
name: continue-work
description: Pick up work another session left on a branch or pull request. Use when the owner says "continue branch X", "continue PR #N", or "continue the work in docs/PROGRESS.md".
---

# Continue work left by another session

The earlier session may have stopped at its usage limit in the middle of a step. Only what it pushed exists. Background: `docs/cloud-sessions.md` → *Handing work from one session to the next*.

## Before changing anything

1. **Check the permission.** Push to the named branch only if the owner's message allows it ("you may push to that branch"). Without that sentence, ask once. Leave this session's own `claude/…` branch unused.
2. **Get the branch.** `git fetch origin <branch>`, then `git checkout -B <branch> origin/<branch>`. If its PR is already merged, start a new branch from `main` instead and say so.
3. **Read the state, nothing more:**
   - the PR (title, description, check runs) through the GitHub tools;
   - in `docs/PROGRESS.md`: the branch's row under *Active branches* and the section that row points at;
   - `git log --oneline origin/main..HEAD` and the last commit's file list.
4. **Compare.** A ticked or described item whose files are not in the branch was not pushed: treat it as open. If the last commit left tests un-run (the row says so), run them first.
5. **Start the full suite once in the background** if code changed since the last full run, and work on documents meanwhile. Never two full runs at once (`CLAUDE.md` → *Tests*).

## While working

- Take the open items in order. An item that says "build it when …" stays open until that condition holds.
- Commit and push after each finished step, with the `docs/PROGRESS.md` rows updated in the same push.
- Decisions go in the decision log as they are made.

## Before stopping

- Full suite green, or the row says exactly what was run.
- PR title and description cover every commit on the branch, with no attribution lines.
- `docs/PROGRESS.md`: open items that remain, and the *Active branches* row with what was verified and what was not.
- Tell the owner which branches they can delete (sessions cannot delete branches).
