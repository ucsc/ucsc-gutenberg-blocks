## 1. Baseline Current Test Effectiveness

- [x] 1.1 Inventory existing `tests/php/*Test.php` and Jest tests, map each to the primary block
      behavior it claims to protect (LDAP query/escaping, cache key/TTL, REST proxy, rendered
      markup), and verify the inventory separates smoke-only tests from behavior-proof tests
- [x] 1.2 Run the current PHP harness and Jest suites via the `validate` skill (`php`, `jest`) and
      verify the baseline pass/fail result is recorded before changing any test
- [x] 1.3 Review existing tests for weak-proof patterns — no assertions, broad mocking of LDAP/
      REST calls, implementation-duplicate expected values, unrealistic fixtures — and verify
      findings are grouped by block (`campus-directory`, `class-schedule`, `course-catalog`)

## 2. Define Test Quality Standards

- [x] 2.1 Add project guidance for effective tests covering behavior mapping, realistic LDAP/REST
      fixtures, negative and boundary paths, and assertion quality, and verify the guidance
      answers "what defect would make this test fail?"
- [x] 2.2 Add explicit guidance for AI-generated tests requiring review for realistic scenarios,
      maintainability, and behavior-level assertions, and verify generated tests cannot be
      accepted based only on passing locally or raising the structural coverage number
- [x] 2.3 Document when a test is allowed to be classified as smoke coverage only, and verify
      smoke tests are not counted toward closing a WPM-115 child ticket's gap
- [x] 2.4 Document when a targeted mutation-probe script or one-time probe is appropriate, and
      verify the guidance states probes are opt-in and dependency-free (no PHPUnit/Xdebug) unless
      a probe becomes fast, deterministic, and valuable enough for CI

## 3. Run One Representative Mutation Probe

- [x] 3.1 Select the LDAP-escaping behavior in `buildUidFilter()` /
      `processDeptDivFilterString()` as the first probe target, given the existing audit finding
      referenced in `campus-directory-block`'s tasks.md, and verify the selection records the
      suspected false-positive risk
- [x] 3.2 Document the `prove-regression.sh` invocation for this plugin's dependency-free PHP
      harness (`--container ""`, `--check` running the target `tests/php/*Test.php` file in the
      one-shot `php:8.1-cli` container) in `docs/test-effectiveness.md`, and verify no new script
      is added to this repo for this purpose (superseded the original plan per design.md decision
      5 — `prove-regression.sh` already exists and covers this)
- [x] 3.3 Run the probe against the LDAP-escaping behavior and verify the targeted test fails for
      the intentional defect, or record the test gap that must be fixed before the probe can pass
- [x] 3.4 Decide whether the probe is worth re-running for future high-risk changes and record the
      decision in this change's notes

## 4. Final Verification

- [x] 4.1 Re-run the PHP harness and Jest suites via the `validate` skill and verify no regression
      from baseline — PHP: 28/28, 24/28 (same 4 documented failures), 66/66, 29/29, matching the
      §1.2 baseline exactly; `git status --porcelain classes/ tests/ src/` confirmed clean after
      the probe restored `classes/CampusDirectoryAPI.php`; Jest was unchanged this session (no
      `src/` edits) so the §1.2 60/60 result stands
- [x] 4.2 Review the guidance and probe script against the AI/test-quality checklist in section 2
      and verify each accepted artifact protects behavior with acceptable maintenance overhead —
      `docs/test-effectiveness.md` names observable-result assertions, negative/boundary cases,
      and the AI-assisted-test checklist; the probe run added zero new maintenance surface (reused
      `prove-regression.sh`, no new file committed to this repo)
- [x] 4.3 Run `openspec validate evaluate-test-effectiveness --strict` and verify the change passes
      validation
