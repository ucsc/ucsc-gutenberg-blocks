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

## 5. Document and Verify Coverage Reporting

- [x] 5.1 Verify `coverage-report.py`, `run-php-coverage.sh`, and `npm run test:coverage` together
      produce a PHP and JS coverage report for all three blocks with no bespoke setup, and record
      any gap — `npm run test:coverage -- --runInBand` passed 11 suites / 160 tests and emitted
      `coverage/coverage-summary.json`, `coverage/lcov.info`, and `coverage/lcov-report/`;
      `composer run test:coverage` passed 10 PHP suites / 344 checks and emitted
      `coverage/clover.xml`, `coverage/html/index.html`, and `coverage/coverage-raw.json`;
      Python 3.11 `coverage-report.py .` read all three machine-readable artifacts. Gap recorded:
      the report's readiness layer still prints stale WPM-117 blocked guidance even while Layer 1
      consumes the generated Clover artifact.
- [x] 5.2 Document the easy-to-use invocation syntax for both reports in
      `docs/test-effectiveness.md`, and verify a new contributor could run either report from the
      documented command alone — added a "Coverage Reporting" section with `npm run
      test:coverage`, `composer run test:coverage`, and Python 3.11 `coverage-report.py`
      invocations plus expected artifact paths.
- [x] 5.3 Re-run `openspec validate evaluate-test-effectiveness --strict` and verify the change
      still passes — validation passed on 2026-09-17 after the coverage-reporting updates.

## 6. Adapt Baseapp-Inspired Standards

- [x] 6.1 Review existing `tests/php/*Test.php` and Jest tests for missing intent documentation,
      and verify a representative sample of new/changed test classes carries a comment or
      docblock stating why the test exists — reviewed the PHP harness files and Jest suites by
      grep for test headers, comments, and behavior labels. Representative current examples carry
      intent via docblocks/group labels such as WPM-103/WPM-152 LDAP hardening,
      WPM-169 profile rendering, and WPM-117 coverage harness comments; the review standard now
      requires a comment/docblock when names/group labels are not enough.
- [x] 6.2 Verify every LDAP and REST/HTTP call reachable from `tests/php/*Test.php`,
      `helpers/harness.php`, and Jest suites is faked or stubbed rather than real, and record any
      gap found — LDAP functions are shadowed in the Campus Directory PHP tests, `wp_remote_get()`
      and `wp_remote_post()` are shadowed in REST/CourseCatalog/SiteSettings PHP tests, and Jest
      tests mock browser/WordPress seams. No live LDAP, PeopleSoft, or REST dependency was found
      in the searched test paths.
- [x] 6.3 Add both requirements to `docs/test-effectiveness.md` review guidance, and verify the
      guidance names the baseapp standard as the source of the adapted ideas — added intent
      documentation and lowest-seam fake requirements, explicitly scoped from the UCSC
      Laravel/Vue baseapp standard to this dependency-free PHP/Jest plugin.
- [x] 6.4 Re-run `openspec validate evaluate-test-effectiveness --strict` and verify the change
      still passes — validation passed on 2026-09-17 after the baseapp-inspired guidance updates.

## 7. Document External Testing-Skill Sources

- [x] 7.1 Pin the exact confirmed package name(s) for external WordPress block testing-skill
      resources (candidates so far: `WordPress/agent-skills` via
      `npx openskills install WordPress/agent-skills`, `jorgerosal/wordpress-skills`) and verify
      each named source actually exists and installs before citing it — confirmed both public Git
      repositories with `git ls-remote`; WordPress.org documents `npx openskills install
      WordPress/agent-skills` / `npx openskills sync`, and `jorgerosal/wordpress-skills` documents
      Codex install by copying `codex-skills/*` plus shared references.
- [x] 7.2 Add the confirmed source(s) to `docs/test-effectiveness.md`, and verify the doc does not
      assert any package name that was searched for but not found — added only the two confirmed
      multi-topic skill collections and explicitly warned against unverified single-purpose package
      names.
- [x] 7.3 Re-run `openspec validate evaluate-test-effectiveness --strict` and verify the change
      still passes — validation passed on 2026-09-17 after external-skill-source documentation.
