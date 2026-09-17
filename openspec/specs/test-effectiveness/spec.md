# Test Effectiveness Specification

## Purpose

Defines how tests for this plugin's blocks demonstrate that they protect required behavior,
especially LDAP-backed directory data, cached REST-proxied course/schedule data, coverage
reporting, and rendered markup, so that a passing test is treated as proof rather than just
executed code.

## Requirements

### Requirement: Tests map to protected behavior

The project SHALL maintain tests that are traceable to the behavior they protect, prioritizing
LDAP query construction and escaping, REST proxy responses, transient cache behavior, attribute
persistence, and server-rendered markup for `campus-directory`, `class-schedule`, and
`course-catalog`.

#### Scenario: High-risk behavior has explicit proof

- **WHEN** a test covers a high-risk behavior such as an LDAP filter string, a cache key/TTL, or
  rendered field output
- **THEN** the test names or structures its setup, action, and assertions so the protected
  behavior and expected outcome are clear

#### Scenario: Coverage without behavioral assertion is insufficient

- **WHEN** a test executes production code without asserting the resulting state, rendered output,
  cached value, or REST response body
- **THEN** the test is not considered an effective proof for that behavior

### Requirement: Tests prove important negative and edge behavior

The project SHALL include negative-path and boundary tests for behaviors where invalid input, LDAP
filter metacharacters, an empty or malformed upstream response, a missing attribute key, or a cache
miss could produce an incorrect or unsafe result.

#### Scenario: Failure path is protected

- **WHEN** an upstream LDAP or REST dependency is unavailable, empty, or returns malformed data
- **THEN** the test suite verifies the block degrades, caches, or reports the condition according
  to the required behavior rather than fataling or rendering unescaped output

#### Scenario: Edge input is protected

- **WHEN** a behavior depends on boundary values such as LDAP filter metacharacters in
  user-controlled input, an attribute set missing an expected key, or a size/TTL ceiling
- **THEN** the test suite verifies the expected result for representative boundary values

### Requirement: Test assertions must detect behavioral regressions

The project SHALL evaluate important tests for whether they fail when the behavior they claim to
protect is intentionally broken.

#### Scenario: Built-in proof is enough

- **WHEN** a test uses realistic fixtures, exercises the real behavior through `tests/php/*Test.php`
  and `helpers/harness.php` or Jest, and makes assertions that would fail for plausible regressions
- **THEN** no separate evaluation script is required for that test

#### Scenario: Targeted evaluation script is justified

- **WHEN** a high-risk behavior has complex setup, broad mocking of LDAP/REST calls, or a history
  of tests that pass without exercising the change under review
- **THEN** the project may include a targeted, opt-in evaluation script or documented one-time
  probe that intentionally breaks the behavior and confirms the relevant tests fail

#### Scenario: Evaluation result must be actionable

- **WHEN** a mutation-probe script or one-time probe is run
- **THEN** it produces a clear pass/fail result identifying which behavior proof is effective or
  weak, and it restores the production file it modified before exiting

### Requirement: Coverage data guides but does not replace effectiveness

The project SHALL treat PHP and JS coverage output as a discovery signal rather than proof that
behavior is protected.

#### Scenario: Coverage report identifies gaps

- **WHEN** coverage is generated for a test run
- **THEN** uncovered or weakly covered high-risk files are used to prioritize behavior tests

#### Scenario: Covered code still needs assertions

- **WHEN** code appears as covered but the covering test lacks assertions for the required result
- **THEN** the behavior remains a test-effectiveness gap until assertions or another proof are
  added, independent of the structural or line-coverage number reported for it

### Requirement: Coverage artifacts are standardized and reproducible

The project SHALL emit instrumented PHP and JS coverage into the top-level `coverage/` directory
using machine-readable and human-readable formats that can be regenerated on demand and are not
committed.

#### Scenario: JavaScript coverage artifacts are produced

- **WHEN** `npm run test:coverage` or `npm run test:js:coverage` completes successfully
- **THEN** the run produces Jest coverage artifacts under `coverage/`, including
  `coverage/coverage-summary.json`, `coverage/lcov.info`, and an HTML report such as
  `coverage/lcov-report/index.html`

#### Scenario: PHP coverage artifacts are produced

- **WHEN** `composer run test:coverage` or `npm run test:php:coverage` completes successfully
- **THEN** the run produces PHP coverage artifacts under `coverage/`, including
  `coverage/clover.xml`, `coverage/html/index.html`, and the raw coverage data needed by the
  local harness

#### Scenario: Coverage output is regenerated rather than stored

- **WHEN** coverage artifacts are created
- **THEN** they remain generated local output under `coverage/` and are excluded from source
  control rather than reviewed as committed fixtures

### Requirement: Coverage reports are easy to produce and documented

The project SHALL provide documented command paths that produce PHP and JS coverage reports for all
three blocks (`campus-directory`, `class-schedule`, `course-catalog`) without bespoke setup.

#### Scenario: Coverage report command is documented

- **WHEN** a contributor wants current PHP or JS coverage for any of the three blocks
- **THEN** the README or testing documentation names a runnable command such as
  `npm run test:coverage`, `composer run test:coverage`, `npm run test:js:coverage`, or
  `npm run test:php:coverage`

#### Scenario: Documentation stays runnable

- **WHEN** the documented coverage commands are followed exactly as written on a prepared checkout
- **THEN** they produce working coverage reports for the plugin without undocumented flags or
  manual report assembly

### Requirement: End-to-end tests are not counted as coverage percentage

The project SHALL treat browser-level E2E tests as integration pass/fail checks rather than as
PHP or JS coverage instrumentation.

#### Scenario: E2E verifies integration behavior

- **WHEN** an E2E test exercises a published block through a browser
- **THEN** it verifies user-visible wiring, navigation, rendering, or interaction behavior and may
  produce traces, screenshots, or reports on failure

#### Scenario: E2E is excluded from coverage math

- **WHEN** PHP or JS coverage percentages are reported
- **THEN** E2E pass/fail results are not merged into those percentages or used as a substitute for
  PHPUnit-style harness tests or Jest assertions

### Requirement: Test effectiveness is reviewed with test changes

The project SHALL review new and changed tests for behavioral value before relying on them as
regression proof for any of the three blocks.

#### Scenario: Test change is accepted

- **WHEN** a test is added or changed
- **THEN** review confirms the test would fail for at least one plausible defect in the behavior
  it claims to protect

#### Scenario: Weak test is identified

- **WHEN** a test only asserts that code runs, mocks away the LDAP/REST call it claims to protect,
  or duplicates production logic to compute its own expected value
- **THEN** the test is revised or documented as smoke coverage only, and is not counted toward
  closing a test-coverage gap

### Requirement: Tests document why they exist

The project SHALL document, for each test class and each non-trivial test method, the behavior it
protects and any gap deliberately left unasserted, so a reader does not have to reverse-engineer
intent from the assertions alone.

#### Scenario: Test intent is documented

- **WHEN** a new test class or non-trivial test method is added to `tests/php/*Test.php` or a Jest
  suite
- **THEN** it carries a comment or docblock stating why the test exists and, if applicable, a
  ticket reference and any known gap the test does not cover

#### Scenario: Undocumented intent is a review finding

- **WHEN** a test's purpose cannot be determined from its name, comments, or docblock
- **THEN** review requests the missing intent documentation before treating the test as effective
  coverage

### Requirement: External dependencies are faked, not called

The project SHALL fake LDAP and REST/HTTP calls at the lowest available seam in
`tests/php/*Test.php`, `helpers/harness.php`, and Jest suites, so no test reaches a real external
service.

#### Scenario: Test suite makes no real network calls

- **WHEN** the PHP harness or Jest suite runs for `campus-directory`, `class-schedule`, or
  `course-catalog`
- **THEN** every LDAP or REST/HTTP call the code under test would make is faked or stubbed, and no
  test depends on reaching a live LDAP server or upstream API

#### Scenario: Fake preserves real matching logic

- **WHEN** a fake or stub replaces an LDAP or REST/HTTP call
- **THEN** it overrides only the network primitive and lets real business/matching logic run
  against in-memory data, rather than hardcoding the expected result

### Requirement: External WordPress testing-skill resources are documented

The project SHALL document where to discover external, community-maintained testing-skill resources
for WordPress block development, so contributors are not left guessing at unverified package names.

#### Scenario: Discoverable skill sources are named

- **WHEN** a contributor looks for external skill-based guidance on WordPress block unit,
  dynamic-block, or accessibility testing
- **THEN** `docs/test-effectiveness.md` names at least one confirmed, installable source and states
  plainly when a source is a bundled, multi-topic skill collection rather than a single-purpose
  unit/dynamic-block/accessibility-testing package

#### Scenario: Unverified package names are not asserted

- **WHEN** documentation references an external skill or tool
- **THEN** it names only sources that have been confirmed to exist and does not assert an exact
  package name that was searched for but not found

### Requirement: AI-generated tests require quality scrutiny

The project SHALL scrutinize AI-generated or AI-assisted tests for realistic scenarios,
maintainability, and behavioral proof before treating them as trusted coverage.

#### Scenario: Generated test models real usage

- **WHEN** an AI-generated test is proposed for block behavior
- **THEN** review verifies its fixtures - directory records, course/schedule payloads, block
  attributes - and assertions resemble realistic data this plugin actually receives

#### Scenario: Generated test avoids unnecessary maintenance cost

- **WHEN** an AI-generated test depends on brittle implementation details, excessive mocking of
  LDAP/REST calls, duplicated setup, or assertions unrelated to rendered output or cached state
- **THEN** the test is simplified, reclassified as smoke coverage, or rejected

#### Scenario: Generated test is trusted only after behavior proof

- **WHEN** an AI-generated test passes
- **THEN** it is not considered effective until review confirms it would fail for a plausible real
  defect in the protected behavior
