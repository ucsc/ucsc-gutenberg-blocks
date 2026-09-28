# Playbook: work through unassigned WPM Gutenberg issues

## Request for Claude

Find all open, unassigned WPM Jira issues concerning this Gutenberg plugin,
including Class Schedule, Course Catalog, and Campus Directory. Read their
requirements, identify work already implemented, and work through the remaining
actionable issues using the `ucsc-wp-block-dev` plugin skills and scripts.
Continue through the queue; do not stop after producing an inventory or plan.
Keep this file updated with issue status, implementation notes, and validation.

The user requested this handoff on September 28, 2026. This playbook is the
handoff artifact. Discovery ran 2026-09-28 via Claude's Atlassian connection
(29 unassigned open WPM issues, single page); implementation has not started.

## Repository and starting state

- Product repository: `ucsc/ucsc-gutenberg-blocks`.
- Local root: `/Users/henryh/_code/_campuspress/wp-dev.ucsc/public/wp-content/plugins/ucsc-gutenberg-blocks`.
- WordPress environment root: four directories above this repository (`../../../..`).
- Canonical development skills: `../../../../.claude/plugins/ucsc-wp-block-dev/skills/`.
- Branch at handoff: `dev/henryh/WPM-115_test_buildout`. Recheck before editing.
- Existing changes were committed before this handoff:
  - `ceed1fc`: `fix(class-schedule): isolate multiple blocks on the same page`
    (WPM-180).
  - `7aba913`: `fix(tests): report PHP coverage across all plugin sources`.
- The working tree was clean before adding this playbook.

Read applicable `AGENTS.md` instructions, including the parent environment's
`AGENTS.md`, and the complete skill files for each workflow used. The session's
instructions referenced `BOOST.md`, but that file was not found during handoff.
Check whether it is available in your environment.

## Jira connection and discovery

Use Claude's configured Atlassian/Jira connection.

- Site: `https://ucsc-its.atlassian.net`.
- Cloud ID: `24d95033-cc26-4d31-87b4-ce1c3d5b419d`.
- Project: `WPM`.

Codex successfully listed this site through its Atlassian connector, but the
following issue search returned:

> You don't have permission to connect from this IP address. Please ask your organization admin for access.

This is the observed response from that connector. The cause was not diagnosed;
it does not establish that Claude's connection will fail. Retry discovery through
Claude's normal connection. If access also fails there, report the exact error
and request an authorized connection or ticket export. Do not bypass an access
restriction.

Start with the broad query so inconsistent labels and summaries do not hide work:

```jql
project = WPM AND assignee IS EMPTY AND statusCategory != Done
ORDER BY priority DESC, key ASC
```

Follow every page of results. Inspect summaries, descriptions, components,
labels, parent/epic context, and linked issues to identify this plugin's work.
Include shared Gutenberg components, REST/PHP behavior, accessibility, tests,
and build tooling when the issue belongs to this repository. Include relevant
blocks beyond the three named above. Record issues belonging to another
repository as such before deciding whether they can be handled in this workspace.

For each candidate, fetch the full issue description, acceptance criteria,
comments, status, priority, assignee, and issue links. Fetch linked requirements
when needed to understand the work. Local `docs/jira/` notes and audit files are
supporting context; use live Jira for current assignment and status.

Recheck assignment and status before starting each issue. If someone has taken
it or it is already complete, record that change and move on. Here, “grab” means
collect the work queue and implement it. Assignment, comments, and transitions
in Jira require explicit user direction; keep progress in this file meanwhile.

## Build the queue

Record every relevant issue, including duplicates, existing implementations,
and blocked work. Order by priority, then dependencies. Group related work where
it avoids conflicting changes, while preserving each issue's acceptance criteria.

| Issue | Block / area | Priority | Acceptance criteria / source | Local status | Changes and evidence | Blocker / next step |
| --- | --- | --- | --- | --- | --- | --- |
| WPM-180 | class-schedule | Medium | Fix global DOM ID conflict with two blocks on a page | already implemented (ceed1fc) | Commit ceed1fc; Jira still To Do | Compare to ticket AC; add regression test if missing |
| WPM-7 | class-schedule | Highest | Remove styling | queued | — | Fetch AC |
| WPM-10 | class-schedule | Highest | CSV ordering | queued | — | Fetch AC |
| WPM-68 | class-schedule | Highest | a11y: Seats color contrast (AA) | queued | — | Fetch AC |
| WPM-73 | class-schedule | Highest | a11y: Search Schedule visible text label (A) | queued | — | Likely dup of WPM-95 |
| WPM-95 | class-schedule | Highest | a11y: search field placeholder-only label (AA) | queued | — | Group with WPM-73 |
| WPM-91 | class-schedule | Highest | a11y: aria-sort on sortable headers (A) | queued | — | Fetch AC |
| WPM-93 | class-schedule | Highest | a11y: inconsistent tabindex on optional headers (A) | queued | — | Group with WPM-91/94 |
| WPM-94 | class-schedule | Highest | a11y: div+ARIA instead of native table; cell/header association (A) | queued | — | May be already implemented by self-rendered table; verify |
| WPM-110 | class-schedule | Highest | a11y: cancelled status only via strikethrough | queued | — | Fetch AC |
| WPM-111 | class-schedule | Highest | a11y: quarter dropdown auto-submits (3.2.2) | queued | — | Check WPM-159 term dropdown tests |
| WPM-92 | cross-block | Highest | a11y: heading hierarchy H1->H3 (CS/CD/CC) | queued | — | Fetch AC; may involve theme |
| WPM-106 | campus-directory | Highest | a11y: data labels hidden from SR (1.3.1) | queued | — | Fetch AC |
| WPM-107 | campus-directory | Highest | a11y: table missing row headers (1.3.1) | queued | — | Fetch AC |
| WPM-13 | campus-directory | Highest | Long text alignment feature request | queued | — | Fetch AC |
| WPM-19 | campus-directory | Highest | GRLN update | queued | — | Fetch AC |
| WPM-148 | campus-directory | Medium | List view CSS issue (GH #103) | queued | — | Fetch AC + GH issue |
| WPM-150 | campus-directory | Medium | Photos cropped at top (GH #141) | queued | — | Fetch AC + GH issue |
| WPM-149 | campus-directory | Low | Enhancements for Prof. Carson (GH #140) | queued | — | Fetch AC; may need product decision |
| WPM-108 | course-catalog | Highest | a11y: sortable headers keyboard/SR (2.1.1/4.1.2) | queued | — | Fetch AC |
| WPM-109 | course-catalog | Highest | a11y: expand/collapse descriptions keyboard (2.1.1/4.1.2) | queued | — | Fetch AC |
| WPM-63..67 | KB articles | Highest | Refresh ServiceNow KB articles | out of scope | Docs outside repo | Human task |
| WPM-20 | — | Highest | Backlog separator row | out of scope | — | — |
| WPM-121 | reporting (epic) | Highest | Block usage/settings reporting | out of scope | Epic; reporting tooling, not this plugin | — |
| WPM-182 | a11y (epic) | Highest | WPM A11Y epic container | out of scope | Epic; children listed above | — |

### Test tickets under epic WPM-115 "WPM Test Coverage" (2026-09-28)

The unassigned-only JQL misses test work; query `parent = WPM-115` instead.
User direction: test-ticket titles should contain the string `test`, and link to
epic WPM-115 when it fits.

| Issue | Block / area | Assignee | Local status | Changes and evidence | Blocker / next step |
| --- | --- | --- | --- | --- | --- |
| WPM-164 | course-catalog | Jim Snook | verified | `tablesorter.test.js`: search term `introductory` exists only in a description; asserts that course plus its description row stay visible. Fails when the description-match branch is disabled. | Reassigned to Henry, @Jim comment posted 2026-09-28; status In Progress |
| WPM-120 | course-catalog | Jim Snook | already implemented | Item 1 was done by WPM-128 (`bcfc277`). Item 2 was moved to class-schedule (WPM-163/181) per the 2026-09-11 comment. Item 3 (e2e) was done by WPM-168 (`93afee9`, `tests/e2e/course-catalog.spec.js`). | Reassigned to Henry; e2e decision + @Jim comment posted 2026-09-28; status In Progress |
| WPM-177 | campus-directory | Tom Gardner | verified | `CampusDirectoryTest.php`: name-found, unresolved-fallback, and non-profile cases for `directory_profile_title()`. Two mutations each caught (assignment removed; empty-guard removed). | Side finding: `directory_profile_title()` does `new CampusDirectoryAPI([])` and emits 2x "Undefined array key automatedFeeds" warnings per profile page. Backlogged as WPM-183 (unassigned, Medium). Reassigned to Henry, @Tom comment posted 2026-09-28; status In Progress |
| WPM-165 | course-catalog | Tom Gardner | verified | New `src/blocks/__tests__/CourseCatalogDropdowns.test.js` renders the CourseCatalog editor with the real Department/Subject dropdowns (only `@wordpress/components` and `fetch` mocked). Catches a wrong endpoint URL (3 fail) and wrong editor wiring (1 fail). The component fetch was already tested by WPM-133; the endpoint by WPM-154. | Reassigned to Henry, @Tom comment posted 2026-09-28; status In Progress |
| WPM-181 | class-schedule | Tom Gardner -> Henry | verified | New `tests/php/CourseDetailTemplateTest.php` (46 checks, renders each scenario in a child process because the template exits). Six template mutations caught. Template coverage 0 -> 208/214; PHP total 60.59% -> 71.65%. | Reassigned, In Progress, @Tom comment posted 2026-09-28 |
| WPM-178 | campus-directory | Henry | already implemented (unmerged) | Commit `cd788a2` on branch `WPM-178-campus-directory-ldap-50-ceiling`, PR #201; not on main or this branch | Merge PR #201 (gh not authenticated, so PR state is unknown) |
| WPM-180 | class-schedule | unassigned | already implemented | `ceed1fc` includes PHP + Jest regression tests | Ticket update only |
| WPM-155, WPM-156 | course-catalog | Jim Snook | out of scope (not test tasks) | Epic WPM-115 was set by mistake (user, 2026-09-28) | Dead-code removal / product decision |

### Coverage-gap tickets filed and implemented 2026-09-28 (all Henry, In Progress, parent WPM-115)

Scope (user, 2026-09-28): only course-catalog, campus-directory and class-schedule are tracked.

| Issue | Area | Local status | Tests |
| --- | --- | --- | --- |
| WPM-184 | campus-directory shortcode | verified | `CampusDirectoryShortcodeTest.php` now drives the REAL `ucsc_cdp_profile_render_shortcode()` (copied override removed; fixture served through CampusDirectoryAPI's transient lookup). New checks: lookup filter/escaping, `cosmo` default, `'false'` coercion, list/grid dispatch, list-layout field rows, block classes, helpers, style registration |
| WPM-185 | SiteSettings divisioncode() | verified | `SiteSettingsTest.php` divisioncode section |
| WPM-186 | SiteSettings LDAP admin | verified | New `SiteSettingsAdminTest.php` (nonce-before-write, failed nonce writes nothing, redirect, notice, escaping on both settings pages, register_setting sanitize) |
| WPM-187 | campus-directory wiring | verified | `PluginWiringTest.php`: requirements endpoint (never leaks the password), block/style registration, query var, rewrite |
| WPM-188 | CampusDirectoryAPI branches | verified | `CampusDirectoryTest.php`: ldaps vs dev ldap scheme, Researcher staff filter, affiliated-department filter, binary-safe jpegphoto |
| WPM-189 | DirectoryProfileTemplate | verified | `DirectoryProfileTemplateTest.php`: linkify website link/escaping (branch unreachable from template, tested directly), duplicate-title removal, header-plugin/footer-plugin, standalone not-found |
| WPM-190 | class-schedule | verified | `ClassScheduleTest.php`: subject-mode guard; requested term via a real `?class_schedule_term=` served by `php -S` (file doubles as router); registration in `PluginWiringTest.php` |
| WPM-191 | REST routes (shared) | verified | `PluginWiringTest.php`: all 6 ucscgutenbergblocks/v1 routes, methods, callbacks, public permission callbacks, no extras |
| WPM-192 | index.php lifecycle + course-catalog assets | verified | `PluginWiringTest.php`: activation order, deactivation, deploy auto-flush, editor script versioning, tablesorter assets/block; plus Upper Division rank in `CourseCatalogTest.php` |

Every new check group was mutation-tested (source broken, test seen to fail, source restored). Result comments posted on WPM-184..192.

Bugs found and backlogged (unassigned, no epic): WPM-183 (profile title warnings), WPM-193 (shortcode fatal TypeError on unlabeled website, High), WPM-194 (shortcode `$options` warning + empty card for unknown cruzid), WPM-195 (shortcode office hours never render).

Coverage after: PHP 89.20% overall (1677/1880), **97.46% for the three tracked blocks** (1648/1691), up from 60.21% at handoff. Remaining in-scope lines: WPM-155/156 code in CourseCatalog, `exit`/`require_once` lines, WPM-178 (unmerged PR #201), shortcode lines blocked by WPM-193/195, and a few template branches.

Validation, 2026-09-28 (all Docker): full Jest 12 suites / 177 tests passed;
all 14 PHP suites passed; PHP coverage 89.20% overall, 97.46% in scope. No live browser
verification was needed; these are test-only changes.

Use local statuses such as `queued`, `in progress`, `verified`, `already
implemented`, `blocked`, and `out of scope`. These are progress notes, not Jira
transitions. Replace the placeholder row with the actual inventory.

WPM-180 is a known implementation to compare with its ticket if it appears in
the queue. Its current Jira status and assignee are unknown. Do not assume it
is unassigned, resolved, or fully accepted from its commit message alone.

## Workflow for each issue

1. **Read and restate the requirement.** Record the expected behavior, current
   behavior, acceptance criteria, and any dependency. Resolve routine technical
   choices from the code and ticket. Ask only when a missing requirement blocks
   a meaningful decision; continue independent issues while awaiting an answer.
2. **Select the skill.** Use `develop fix` for defects and `develop feature` for
   new behavior. Read `develop/references/issue-context.md`, the selected block
   reference from `develop/references/targets.md`, and the target session contract.
3. **Check the target and branch.** Use the plugin's `block-target-check.sh`,
   `session-target.sh`, and `stack-check.sh` according to their instructions.
   Confirm PHP and JavaScript target the same block. Preserve unrelated changes.
   Follow the skill's branch rules before creating or switching branches.
4. **Compare with existing work.** Check current code, tests, and relevant Git
   history before patching. Consult applicable `openspec/specs/` and existing
   change artifacts where they describe the behavior. Record evidence for work
   already implemented and validate any remaining acceptance gaps.
5. **Reproduce and implement.** Establish a focused reproduction for defects.
   Read the relevant files completely, then make the smallest coherent change.
   Keep PHP and JavaScript attribute schemas aligned. Preserve escaping,
   accessibility, cache behavior, and existing integration contracts.
6. **Validate and verify.** Use the `validate` skill for automated tests, `run`
   for Docker build/startup, and `verify` for live editor/frontend acceptance.
   Run relevant checks before moving on. Broaden tests when shared behavior is
   affected. Record failures honestly and separate setup failures from defects.
7. **Review and record.** Use `review` to check the final diff. Update the queue
   with files changed, commands/results, acceptance evidence, and outstanding
   limitations. Keep changes traceable to individual Jira keys.

Work through every actionable issue. If one is blocked by credentials, external
services, another repository, or a product decision, record the specific blocker
and continue the rest. Finish with completed work, remaining issues, and the
exact input needed to unblock each one.

## Validation details from this workspace

Build, PHP, Jest, and browser tests run through Docker. Do not use host PHP,
Composer, npm, or wp-scripts as the project runtime. Prefer the skill's scripts
and current repository configuration over old examples.

- `bin/validate*.sh` was absent at handoff; do not assume those runners exist.
- Existing PHP runners: `tests/php/run-php-tests.sh` and
  `tests/php/run-php-coverage.sh`. Both orchestrate Docker execution.
- JavaScript tests use the `test` script in `package.json`. Older skill text
  claiming no test script exists is stale.
- E2E runner: `tests/e2e/run-e2e.sh`; inspect its current prerequisites and
  fixture setup before execution.
- Use session-specific test logs. Run test suites sequentially, as required by
  the validation skill. Use container dependencies rather than host node_modules.
- The WordPress stack was not running at the last Docker inspection. Recheck
  and use the `run` skill to prepare live verification.

PHP coverage command, from this repository root:

```bash
bash tests/php/run-php-coverage.sh
```

Example focused Jest command, from the WordPress environment root:

```bash
docker compose -f docker-compose.yml -f docker-compose-start.yml run --rm \
  -e CI=true \
  -w /var/www/html/wp-content/plugins/ucsc-gutenberg-blocks \
  plugin_npm_start npm test -- --runInBand --testPathPattern=ClassSchedule
```

Read the `run` skill before using its Docker build driver:

```bash
bash ../../../../.claude/plugins/ucsc-wp-block-dev/skills/run/driver.sh build
```

Last verified baseline, September 28, 2026:

- All 11 PHP suites passed.
- PHP statement coverage: **60.21% (1,132 / 1,880)**, including unloaded sources.
- The frontend `src/components/ClassSchedule/__tests__/classschedule.test.js`
  suite passed all **37 tests**. This was a focused run, not the full Jest suite.
- `git diff --check` and shell syntax checks passed for the prior changes.
- No live browser verification or full build was performed during the commit task.

The README's 100% PHP coverage claim is stale. See `test-coverage-php.md` and
the actual generated report. Coverage percentages alone do not prove acceptance.

## Completion and delivery

An issue is locally verified when its acceptance criteria are implemented,
relevant automated checks pass, and required live verification has evidence.
Do not claim verification for checks that could not run.

Keep code changes and this queue reviewable. The earlier request to commit
applied to the preexisting files and has been fulfilled; it is not a standing
instruction to commit every new issue. Follow current user instructions and the
plugin's commit policy. Use Conventional Commit messages with Jira references
when commits are requested. Repository instructions prohibit pushing; provide
the user with the appropriate command or PR link instead.

Final report:

- Issues implemented or confirmed already implemented, with Jira links.
- Behavior changed and validation evidence for each issue.
- Files and any requested commits produced.
- Remaining issues, blockers, and next steps.
