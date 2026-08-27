# Exploration notes

Open questions surfaced on 2026-08-26 while writing the `campus-directory-block`
OpenSpec baseline. None of these are decided. Each file states a tension, the
options, a recommendation, and what acting on it would actually cost.

These are **not** ADRs — nothing here has been ratified. Promote one to a
decision (or an OpenSpec change) when it is acted on, and delete it from here.

| # | Note | Question | Blocks |
|---|---|---|---|
| 01 | [Coverage coordinate systems](01-coverage-coordinate-systems.md) | Should spec scenarios become the work queue for WPM-115, replacing the per-file structural count? | WPM-115 prosecution |
| 02 | [Normative vs. descriptive baseline](02-normative-vs-descriptive-baseline.md) | Should the archived baseline describe what ships, or the contract the code is held to? | `openspec archive` of campus-directory-block |
| 03 | [Shared component contracts](03-shared-component-contracts.md) | Where does a contract live when a component is shared across blocks — or across capabilities within one block? | Any cross-block change |
| 04 | [Spec baseline asymmetry](04-spec-baseline-asymmetry.md) | Do class-schedule and course-catalog get spec files before campus-directory archives? | Archive ordering |

## Context these came out of

- `openspec/changes/campus-directory-block/` — 4 artifacts, 37 requirements,
  97 scenarios across 6 capabilities. Complete, unarchived.
- `openspec/changes/class-schedule-block/`, `.../course-catalog-block/` —
  proposal only, no specs.
- `openspec/specs/` — empty. Nothing archived yet, so every structural decision
  below is still cheap.
- `docs/jira/WPM-115-epic-test-coverage.md` and children — the live work.
- `audit-campus-directory.md` (2026-07-17) — two findings still open, one now
  closed.

## Priority

01 and 02 are substantive. 01 could change how the whole coverage epic is
prosecuted; 02 is a decision that was made implicitly while writing the specs
and should be owned deliberately before archive. 03 and 04 are real but can wait
— though 04 gets more expensive the longer it waits.
