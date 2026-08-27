# WPM-115 — WPM Test Coverage (Epic)

Parent epic for closing test-coverage gaps in `ucsc-gutenberg-blocks`, scoped to
the three flagship LDAP-backed blocks: **campus-directory**, **class-schedule**,
**course-catalog**.

## Measured starting point

Produced 2026-08-25 by `ucsc-wp-block-dev:validate coverage`:

```
python3 skills/validate/scripts/coverage-report.py <plugin-path>
```

| Layer | State |
|---|---|
| Instrumented (real line coverage) | **None.** No artifact can be produced today |
| Structural (unit named by any test) | 18/40 units = 45% across the plugin |

Structural coverage is a **floor, not a measurement** — it answers "is this unit
tested at all", never "how much of it runs". Two blockers stop us producing a
real number, and both are enablement tasks below rather than per-block work.

## Why no coverage number exists yet

1. **PHP.** The suite is deliberately dependency-free (`tests/php/*Test.php` +
   `helpers/harness.php`) and runs in a throwaway `php:8.1-cli` that carries
   neither Xdebug nor PCOV. The harness prints `N/M passed` and exits — there is
   no clover emitter. Separately, the stack's `wp` image installs Xdebug but sets
   `xdebug.mode=debug`; Xdebug 3 only records coverage when the mode includes
   `coverage`.
2. **Jest.** `wp-scripts test-unit-js --coverage` works today, but no
   `collectCoverageFrom` is declared, so a coverage run reports only on modules
   some test happened to import and silently omits every untouched file — exactly
   the set we are trying to find.

## Children

| Ticket | Work | Depends on |
|---|---|---|
| WPM-116 | Wire Jest coverage reporting | — |
| WPM-117 | Instrument the PHP harness so the suite can emit coverage | — |
| WPM-118 | campus-directory — 5 untested units, 889 loc | WPM-116 |
| WPM-119 | class-schedule — template has no executing coverage | WPM-117 |
| WPM-120 | course-catalog — untested tablesorter, template tested by path only | WPM-116 |

WPM-116 is the cheapest first move: no new dependency, and it produces a
real layer-1 number for JS immediately.

## Ground rules for every child ticket

- **A test is not coverage until it has been seen to fail.** Revert only the
  behaviour under test, confirm the new test fails for the expected reason,
  restore. A test that names a unit without exercising it raises the structural
  number and guards nothing.
- **Do not batch.** Close gaps one unit at a time and re-run the report to
  confirm movement.
- **Baselines, not thresholds.** Record where each block actually is; do not set
  a percentage gate on a plugin this early.
- **Gate on changed-line coverage,** not absolute, once artifacts exist.

## References

- ADR-114 — coverage as a read-only `validate` mode (ucsc-wp-block-dev)
- `skills/validate/references/coverage-strategy.md` — framework direction and the
  harness instrumentation recipe
- WPM-97 (Done) — precedent: PHP tests for ClassSchedule
- WPM-104 (Done) — Course Catalog suite fatal, fixed
