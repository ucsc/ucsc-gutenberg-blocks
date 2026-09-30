## Why

AI-assisted sessions keep leaving generated Markdown at the repository root:
audits, coverage reports, and working prompts or playbooks. They crowd the root
next to `README.md` and `CHANGELOG.md`, look like maintained project docs, and get
committed by accident. `WPM-GUTENBERG-ISSUES-PLAYBOOK.md`, a session handoff
prompt, was committed to the PR #212 branch this way. The team convention is that
these files live under `docs/`, never at the root. It has not been written down
anywhere an agent reads, so it keeps being broken.

## What Changes

- Record the convention: AI-generated Markdown (prompts, playbooks, handoffs,
  todo or work queues, audits, coverage and gap reports, session notes) goes
  under `docs/`, never the repository root. The root keeps only
  human-maintained project files: `README.md`, `CHANGELOG.md`, `CustomBlock.md`,
  and tool config.
- Remove `WPM-GUTENBERG-ISSUES-PLAYBOOK.md`. It was a one-off session handoff
  and is no longer needed. Its outcomes are recorded on the WPM Jira tickets and
  in PR #212. (Done 2026-09-28 with `git rm` on the PR #212 branch; a copy is
  archived outside the repo.)
- Move the existing AI-generated reports from the root into `docs/`, keeping
  their filenames so history and search stay easy:
  - `audit-campus-directory.md`, `audit-class-schedule.md` → `docs/audits/`
  - `test-coverage-js.md`, `test-coverage-php.md` → `docs/coverage/`
- Update every in-repo link to the moved files.
- Add the rule to the repo's agent instructions so future sessions follow it
  without being told.

## Capabilities

### New Capabilities

None. This is a repository-layout and contributor convention. It changes no
block, REST, or editor behavior, so the change sets `skip_specs: true`.

### Modified Capabilities

None.

## Impact

- **Files moved:** four root Markdown reports go to `docs/audits/` and
  `docs/coverage/`.
- **File removed:** `WPM-GUTENBERG-ISSUES-PLAYBOOK.md`.
- **Links to update:** `docs/exploration/README.md`,
  `docs/exploration/02-normative-vs-descriptive-baseline.md`,
  `openspec/changes/campus-directory-block/tasks.md`, and
  `openspec/changes/campus-directory-block/design.md`. No references exist in
  `.github/`, `tests/`, `package.json`, `README.md`, or the `ucsc-wp-block-dev`
  skills.
- **Agent guidance:** add a rule to repo agent instructions. The plugin repo
  has `.agents/` and `.claude/` directories but no `AGENTS.md` or `CLAUDE.md` at
  its root.
- **Timing:** apply the moves on a fresh branch after PR #212 merges.
