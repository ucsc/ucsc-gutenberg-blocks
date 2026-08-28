## Purpose

Defines how tests for this plugin's blocks demonstrate that they protect required behavior —
especially LDAP-backed directory data, cached REST-proxied course/schedule data, and rendered
markup — so that a passing test is treated as proof, not just as executed code.

## ADDED Requirements

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
- **WHEN** a test executes production code without asserting the resulting state, rendered
  output, cached value, or REST response body
- **THEN** the test is not considered an effective proof for that behavior

### Requirement: Tests prove important negative and edge behavior
The project SHALL include negative-path and boundary tests for behaviors where invalid input,
LDAP filter metacharacters, an empty or malformed upstream response, a missing attribute key, or a
cache miss could produce an incorrect or unsafe result.

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
- **WHEN** a test uses realistic fixtures, exercises the real behavior through
  `tests/php/*Test.php` and `helpers/harness.php` or Jest, and makes assertions that would fail
  for plausible regressions
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
The project SHALL treat PHP and JS coverage output produced by the WPM-115 epic's instrumentation
work (WPM-116, WPM-117) as a discovery signal rather than proof that behavior is protected.

#### Scenario: Coverage report identifies gaps
- **WHEN** coverage is generated for a test run
- **THEN** uncovered or weakly covered high-risk files are used to prioritize behavior tests

#### Scenario: Covered code still needs assertions
- **WHEN** code appears as covered but the covering test lacks assertions for the required result
- **THEN** the behavior remains a test-effectiveness gap until assertions or another proof are
  added, independent of the structural or line-coverage number reported for it

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
  closing a WPM-115 child ticket's gap

### Requirement: AI-generated tests require quality scrutiny
The project SHALL scrutinize AI-generated or AI-assisted tests for realistic scenarios,
maintainability, and behavioral proof before treating them as trusted coverage.

#### Scenario: Generated test models real usage
- **WHEN** an AI-generated test is proposed for block behavior
- **THEN** review verifies its fixtures — directory records, course/schedule payloads, block
  attributes — and assertions resemble realistic data this plugin actually receives

#### Scenario: Generated test avoids unnecessary maintenance cost
- **WHEN** an AI-generated test depends on brittle implementation details, excessive mocking of
  LDAP/REST calls, duplicated setup, or assertions unrelated to rendered output or cached state
- **THEN** the test is simplified, reclassified as smoke coverage, or rejected

#### Scenario: Generated test is trusted only after behavior proof
- **WHEN** an AI-generated test passes
- **THEN** it is not considered effective until review confirms it would fail for a plausible real
  defect in the protected behavior
