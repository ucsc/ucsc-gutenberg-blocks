## Why

The WPM-115 test-coverage epic (`docs/jira/WPM-115-epic-test-coverage.md`) is closing structural
gaps in the PHP harness (`tests/php/*Test.php`) and Jest suites for the three LDAP-backed blocks,
but the plugin has no shared standard for judging whether a passing test actually proves the
behavior it claims to protect. WPM-115 already states the underlying principle as a ground rule —
"a test is not coverage until it has been seen to fail" — but that rule is not backed by review
criteria, a repeatable probe pattern, or guidance for scrutinizing AI-assisted tests, so it is easy
to satisfy in spirit only. As coverage work expands across `campus-directory`, `class-schedule`,
and `course-catalog`, the suite should measure behavior protection, not just structural presence or
happy-path execution.

## What Changes

- Establish test effectiveness as a first-class requirement alongside the structural/instrumented
  coverage layers WPM-115 is already building, without replacing that epic's scope.
- Add a review standard for judging whether a PHP-harness or Jest test proves behavior: assertion
  on observable state/output, negative and boundary cases for LDAP/REST-dependent paths, and no
  implementation-detail or mocked-away assertions.
- Add a lightweight mutation-probe pattern — intentionally break production logic, confirm the
  targeted test fails, restore — usable manually against both `tests/php/*Test.php` (via
  `helpers/harness.php`) and Jest, scoped as an opt-in targeted check rather than a permanent
  second test suite.
- Add explicit review guidance for AI-generated or AI-assisted tests: they are not trusted coverage
  until reviewed for realistic LDAP/REST fixtures and behavior-level assertions that would fail for
  a plausible defect.
- Document when coverage output (once WPM-116/WPM-117 land) is a discovery signal versus when it is
  mistaken for proof, so this change stays complementary to, not duplicative of, WPM-115.

## Capabilities

### New Capabilities

- `test-effectiveness`: Defines how tests in this plugin must demonstrate they protect behavior,
  and how optional mutation-probe tooling and AI-generated-test review should be used.

### Modified Capabilities

- None.

## Impact

- Affects the PHP harness under `tests/php/` and `helpers/harness.php`, and Jest suites under each
  block's `src/`.
- May add a small, clearly named, opt-in evaluation script location (e.g.
  `tests/effectiveness/` or `scripts/test-effectiveness/`) for targeted mutation probes; does not
  require CI wiring.
- Adds review expectations to future test PRs, especially AI-assisted ones, for all three blocks
  (`campus-directory`, `class-schedule`, `course-catalog`).
- Complements rather than duplicates the WPM-115 epic and its children (WPM-116 Jest coverage
  wiring, WPM-117 PHP harness instrumentation, WPM-118/119/120 per-block gap closure); this change
  does not alter that epic's scope or ticket structure.
- Does not change block registration, rendered markup, REST endpoints, or any production behavior.
