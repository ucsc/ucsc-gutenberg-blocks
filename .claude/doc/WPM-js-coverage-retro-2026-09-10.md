# WPM JS Coverage Push — Retrospective (2026-09-10)

## Maintainer skill location (important)
- The `ucsc-wp-block-dev` maintainer skill lives at:
  `/Users/henryh/_code/_opensource/ucsc-wp-block-dev/skills/maintainer/SKILL.md`
  (symlinked from `~/.claude/skills/ucsc-wp-block-dev`).
- `skill_view('ucsc-wp-block-dev:maintainer')` does NOT resolve — read `SKILL.md` from disk.
- Retro mode = `skills/maintainer/retrospective/SKILL.md`. It appends lessons to
  `skills/validate/references/{run,create}.md` (or `develop` refs), grep-before-write,
  then runs `scripts/check-skill-references.sh`.

## Session summary
Goal: reduce the JS coverage gap on ucsc-gutenberg-blocks, working only tickets
that were mine or unassigned.

Shipped (all Docker-verified, CI-green):
- PR #187 — WPM-133 shared dropdowns (Department/Subject/Division) — merged
- PR #189 — WPM-118 CampusDirectory sub-components (PageLayout, AutomatedFeeds, CampusDirectoryDepartmentDropdown)
- PR #190 — WPM-145 InformationToDisplay.js
- PR #188 — WPM-153 save-lock FIX (source change; flagged for user vetting)
- PR #184 (WPM-144) and PR #183 (WPM-132) — rebased/verified for merge
- PR (WPM-128 tablesorter) — user-created

Jira hygiene: closed WPM-124/125 as duplicate orphan subtasks with comments.

Coverage arc: JS statements ~43.56% -> 61.92%. Every targeted file hit 100%.

Also: rewrote README test-running section (Docker-from-project-root, copy-paste-proof);
explained the PHP "100%" illusion (denominator = files that ran, not all files).

## What worked
- Docker-only verification matched CI every time — zero CI surprises across PRs.
- RED-proof discipline: run new tests against origin/main in a throwaway git
  worktree to prove they fail without the change. Caught real regressions (WPM-132, WPM-128).
- Per-suite mock of `@wordpress/components` with `{ virtual: true }` is the reliable
  pattern for editor-control component tests.
- Filtering the backlog by assignee up front kept off other people's tickets
  (Tom WPM-146, Rodney WPM-119, Jim WPM-120, Mohit WPM-134).

## What was tricky
- Stale branches: `dev/henryh/WPM-145_WPM-146_information_display_tests` was cut from an
  old main, so a naive diff looked like it reverted merged work. Fix: cherry-pick just
  the target test file(s) onto a fresh branch from origin/main.
- Branch upstream mis-set: creating a branch tracking origin/main breaks plain `git push`.
  Use `git push -u origin HEAD` so the user's plain `git push` works.
- gh PAT for ucsc/ucsc-gutenberg-blocks lacks PR-create scope (403) — open PRs via browser.
- `git rebase --continue` opens vim and hangs the terminal; set `GIT_EDITOR=true`.
- Scope drift: WPM-153 was a fix not a test — user redirected to tests-only.

## PHP "100%" clarification
- JS coverage denominator = every file (`collectCoverageFrom: ['src/**/*.js']`), so
  untouched files show 0% and drag the number down — honest.
- PHP (Xdebug) denominator = only files that were loaded during a test run; a PHP file
  no test loads is invisible, not counted as 0%. So "835/835 = 100%" means "100% of what
  ran," not 100% of the plugin's PHP. Repo's own note calls the structural PHP number
  "a floor, not a measurement."
- Potential future ticket: force every `.php` under `classes/` + `templates/` into the
  PHP coverage denominator (mirror `collectCoverageFrom`) for a trustworthy work queue.

## Memory facts to persist later (memory was full this session)
- ucsc-wp-block-dev maintainer skill path + retro workflow (see top of this doc).
- In ucsc-gutenberg-blocks: create feature branches from origin/main and push with
  `git push -u origin HEAD` (do not leave origin/main as upstream).
- gh PAT for ucsc/ucsc-gutenberg-blocks cannot create PRs (403) — use the browser.
