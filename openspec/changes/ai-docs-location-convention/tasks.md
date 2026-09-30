## 1. Prepare

- [ ] 1.1 Confirm PR #212 has merged, then branch from `main` (e.g. `dev/henryh/<JIRA>_ai_docs_location`). Get a Jira key first, or file one under the right epic.
- [ ] 1.2 Re-list root Markdown (`ls *.md`) and classify each file as human-maintained (stays) or AI-generated (moves). Expect `README.md`, `CHANGELOG.md` and `CustomBlock.md` to stay.

## 2. Remove and move

- [x] 2.1 `git rm WPM-GUTENBERG-ISSUES-PLAYBOOK.md` (done on the PR #212 branch; copy archived outside the repo)
- [ ] 2.2 `git mv audit-campus-directory.md audit-class-schedule.md docs/audits/`
- [ ] 2.3 `git mv test-coverage-js.md test-coverage-php.md docs/coverage/`

## 3. Fix links

- [ ] 3.1 Update links in `docs/exploration/README.md` and `docs/exploration/02-normative-vs-descriptive-baseline.md`
- [ ] 3.2 Update links in `openspec/changes/campus-directory-block/tasks.md` and `openspec/changes/campus-directory-block/design.md`
- [ ] 3.3 Re-grep the repo for each old root filename. There should be no hits outside git history and this change.

## 4. Record the convention for agents

- [ ] 4.1 Add a short rule to the repo's agent instructions: AI-generated prompts, playbooks, handoffs, todo lists, audits, coverage/gap reports and session notes go under `docs/` (`docs/audits/`, `docs/coverage/`, `docs/exploration/`, or a new topic folder), never the repo root.
- [ ] 4.2 Make sure that instruction file is one agents actually load for this repo. Choose between a new root `AGENTS.md` and the existing `.agents/` or `.claude/` directories.

## 5. Verify

- [ ] 5.1 `ls *.md` at the root shows only human-maintained files.
- [ ] 5.2 `bash tests/php/run-php-tests.sh` and the Jest suite still pass. Nothing should depend on these files; this confirms it.
- [ ] 5.3 `openspec validate ai-docs-location-convention` passes, then archive the change.
