# Instrument the PHP test harness so the suite can emit coverage

**Jira:** WPM-117 · **Parent:** WPM-115 · **Type:** Task

## Problem

The PHP suite is deliberately dependency-free: `tests/php/*Test.php` stub every
WordPress function, share `tests/php/helpers/harness.php`, and run in a throwaway
`php:8.1-cli`. That is the property that makes the suite cheap — no composer, no
`vendor/`, no host runtime — and it should be preserved.

But it means there is **no coverage driver and no clover emitter**: the harness
prints `N/M passed` and exits. Separately, the stack's `wp` image installs Xdebug
and then sets `xdebug.mode=debug`; Xdebug 3 records coverage only when the mode
list includes `coverage`, so even there a coverage run reports "no code coverage
driver available" while step debugging works fine.

## Decision

Instrument the harness rather than migrating the suite to PHPUnit. Coverage
capture is roughly twenty lines; a PHPUnit migration is a rewrite that buys the
same clover output at the cost of the dependency-free property.

## Scope

- Add env-gated coverage capture to `harness.php`: start collection when
  `UCSC_COVERAGE` is set and the driver is present; emit at `finish_tests()`.
- Merge into a raw JSON accumulator before writing clover — each test file runs
  in its own PHP process and would otherwise overwrite the previous report.
- Exclude `/tests/` paths and skip dead-code lines.
- Add a driver-carrying test image (`FROM php:8.1-cli` + `pecl install pcov` or
  xdebug) and a runner that clears the accumulator before each batch.

The full recipe, including the clover emitter, is in
`skills/validate/references/coverage-strategy.md` under "Instrumenting the
harness" in the `ucsc-wp-block-dev` plugin.

## Acceptance criteria

- [ ] With `UCSC_COVERAGE` unset, the default run is byte-identical — same image,
      no driver, no slowdown.
- [ ] With it set, a full suite run produces a valid `clover.xml` covering all
      three existing test files, not just the last one to run.
- [ ] The coverage report's Layer 1 shows a real `php/clover` line.
- [ ] Everything runs in Docker — no host PHP.

## Out of scope

- Migrating to PHPUnit.
- Rebuilding the `wp` image. `XDEBUG_MODE=coverage` overrides the ini per run.
- Raising the number.
