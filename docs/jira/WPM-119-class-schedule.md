# class-schedule — the template has no executing coverage

**Jira:** WPM-119 · **Parent:** WPM-115 · **Type:** Task

## Current state (measured 2026-08-25)

Structurally this block looks **complete** — 4 of 4 units named by a test, with
the deepest suites in the plugin (463-line PHP, 340-line Jest component test,
194-line block test, 105-line e2e spec).

That reading is misleading, and it is the clearest example in the codebase of why
structural coverage is a floor rather than a measurement.

**`templates/ClassScheduleTemplate.php` (189 loc) is credited to a comment.** Its
only match is line 4 of `src/components/ClassSchedule/__tests__/classschedule.test.js`:

```
 * templates/ClassScheduleTemplate.php.
```

A Jest test cannot execute a PHP template, and no PHP test loads this file. So a
189-line template that renders the front-end schedule table has **zero executing
coverage** while reporting as covered.

## Scope

**PHP (`validate php create`)**

- Add a PHP test that actually requires and renders `ClassScheduleTemplate.php`
  against fixture data — not one that asserts on its path.
- Assert escaping of every interpolated value and the table structure the
  front-end depends on.

**Baseline** — depends on Enablement B

- Once the harness emits clover, record class-schedule's real line coverage and
  write it as the baseline. This block has the most mature suites, so it is the
  right place to learn what the instrumented number actually looks like before
  applying the approach to the other two.

## Acceptance criteria

- [ ] A PHP test exercises `ClassScheduleTemplate.php` by rendering it.
- [ ] The test has been **seen to fail** without the behaviour it guards.
- [ ] The misleading comment match no longer accounts for this unit's coverage —
      it is credited to a test that runs it.
- [ ] Real line coverage for the block is recorded as a baseline.
- [ ] Docker only — no host PHP.

## Out of scope

- Raising the existing PHP/Jest suites' depth beyond the template gap.
- Migrating the e2e spec off puppeteer.
- Accessibility testing — covered separately by WPM-44.

## Note for the reporting tool

This is a true positive for the "structural coverage can be fooled" caveat in
ADR-114. Worth considering whether the matcher should ignore comment blocks in
test files, tracked separately against the plugin rather than here.
