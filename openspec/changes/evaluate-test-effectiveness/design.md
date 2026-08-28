## Context

See `proposal.md` for motivation. This plugin's PHP suite is deliberately dependency-free
(`tests/php/*Test.php` + `helpers/harness.php`, no PHPUnit) and currently has no coverage emitter;
Jest coverage runs but omits any file no test happens to import, since no `collectCoverageFrom`
is declared. WPM-116 and WPM-117 (children of the WPM-115 epic) are closing those two enablement
gaps independently of this change. Structural coverage today is 18/40 units (45%) across the
plugin per `docs/jira/WPM-115-epic-test-coverage.md`, and that epic already states the core
principle this change formalizes: "a test is not coverage until it has been seen to fail."

This change does not touch WPM-115/116/117/118/119/120 scope or ticket structure. It adds review
criteria and an opt-in probe pattern that those tickets' work can be checked against once written,
without requiring PHPUnit, Xdebug, or any new dependency.

## Goals / Non-Goals

**Goals:**

- Define a repeatable way to judge whether a PHP-harness or Jest test proves behavior for
  `campus-directory`, `class-schedule`, or `course-catalog`.
- Add a lightweight mutation-probe pattern that works against the existing dependency-free PHP
  harness and Jest, without requiring PHPUnit or Xdebug.
- Make AI-generated tests subject to explicit review for realistic LDAP/REST fixtures, behavior
  value, and maintenance cost.
- Treat future coverage output (once WPM-116/WPM-117 land) as a discovery signal, not a
  substitute for behavior review.

**Non-Goals:**

- Do not require a probe for every test or every pull request.
- Do not add a permanent second test suite whose only job is to test the tests.
- Do not require PHPUnit, Xdebug, or any other new test dependency — this change must work with
  the harness as it exists today.
- Do not change WPM-115/116/117/118/119/120 scope, sequencing, or ticket content.
- Do not change block registration, rendered markup, REST endpoints, or any production behavior.

## Decisions

1. Treat behavior mapping as the primary proof, not coverage percentage.

   Tests should make their protected behavior obvious through names, fixtures, action, and
   assertions — an LDAP filter string, a cache key/TTL, a rendered field, a REST response shape —
   rather than only a method name. This matches WPM-115's existing "baselines, not thresholds"
   ground rule: this change adds a quality lens, not a new percentage gate.

   Alternative considered: rely on the structural/instrumented coverage numbers WPM-115 is
   building. Coverage finds untested code; it cannot prove a test's assertions are meaningful.

2. Use built-in proof patterns before separate evaluation scripts.

   Effective tests should prove themselves within `tests/php/*Test.php` or a Jest test by
   asserting rendered output, cached transient values, REST response bodies, or LDAP filter
   strings — the same pattern WPM-115's ground rule already implies ("revert only the behavior
   under test, confirm the new test fails, restore").

   Alternative considered: introduce a broad "test the tests" harness running in CI. Rejected as
   maintenance overhead disproportionate to a plugin this size; DivData's own retrospective
   (`~/_code/_laravel/divdata/.hermes/comparison-recharge-vs-divdata-test-effectiveness.md`) found
   a planned-but-unbuilt mutation suite scored lower in practice than one working probe script.

3. Allow targeted, opt-in mutation-style probes for high-risk or suspicious tests.

   A small script is appropriate when a reviewer cannot easily tell whether a test would fail for
   a plausible defect — for example, an LDAP-escaping test, a cache-key derivation test, or a
   field-mapping test with broad mocking. The probe temporarily edits the production file,
   confirms the target test fails, and restores the file before exiting; it must run outside
   Docker where possible so it stays fast enough to use routinely.

   Alternative considered: no evaluation scripts at all. That leaves no practical answer for
   tests with broad LDAP/REST mocking or historically weak assertions, which is exactly the risk
   WPM-118 through WPM-120 will encounter as they close per-block gaps.

4. Review AI-generated tests with a maintenance-cost gate.

   A generated test is not accepted merely because it passes or raises the structural coverage
   number. It needs realistic directory/course/schedule fixtures and assertions that would fail
   for a plausible real defect. Tests requiring large bespoke setup, duplicating production logic
   as their expected value, or asserting incidental structure should be simplified or rejected.

   Alternative considered: accept generated tests now and improve them later. Rejected because it
   raises the risk that WPM-118/119/120 report gaps as "closed" on tests that only raise the
   structural number without protecting anything, which WPM-115 explicitly warns against.

5. Reuse the existing `prove-regression.sh` tool instead of adding a new in-repo probe script.

   **Revised during implementation.** The `ucsc-wp-block-dev` Claude Code skill already ships
   `skills/validate/scripts/prove-regression.sh` (ADR-113-VALIDATE-REGRESSION-PROOF): it swaps in
   a broken version of a file, verifies the change actually landed, runs a `--check` command,
   confirms it fails, and restores the file unconditionally. This is exactly the pattern this
   decision originally proposed building from scratch. It supports `--container ""` to skip its
   container-hash verification step, which fits this plugin's dependency-free PHP harness (a
   one-shot `php:8.1-cli` container, not the persistent `wp` service the tool was written against).

   Adding a duplicate script under `tests/effectiveness/` or `scripts/test-effectiveness/` would
   itself be the "permanent second test suite" this change's non-goals reject. Instead, this
   change documents how to invoke the existing tool against this plugin's harness in
   `docs/test-effectiveness.md`. No new script lives in this repo.

   Alternative considered (original decision): write a new in-repo script under
   `tests/effectiveness/` or `scripts/test-effectiveness/`, matching Recharge's
   `evaluate-split-recharge-proof.sh` in
   `~/_code/_laravel/recharge/openspec/changes/evaluate-test-effectiveness`. Superseded once
   `prove-regression.sh` was found to already cover the same need without a new maintenance
   surface.

   Alternative considered: run every probe in CI immediately. Deferred — start with one or two
   documented probes for genuinely high-risk behavior before considering CI integration.

## Risks / Trade-offs

- [Risk] Reviewers treat the checklist as paperwork instead of technical scrutiny. → Mitigation:
  keep review criteria concrete: "what defect would make this test fail?"
- [Risk] A probe script becomes stale or more expensive to maintain than the test it evaluates. →
  Mitigation: only add probes for high-risk LDAP/REST/cache behavior, and require each probe to
  document the behavior it protects.
- [Risk] This change is read as expanding or gating WPM-115/116/117's scope. → Mitigation: design
  and tasks explicitly scope this as complementary review guidance, not a change to that epic's
  tickets or sequencing.
- [Risk] AI-generated tests overfit current implementation details (a named field, a specific
  cache key format) rather than the behavior contract. → Mitigation: require realistic fixtures
  and behavior-level assertions before accepting a generated test.
- [Risk] A probe that edits a production file mid-run is interrupted, leaving the file modified. →
  Mitigation: probe scripts must restore the file in a cleanup path (trap/finally) and check for a
  clean git tree before starting.

## Migration Plan

1. Baseline the plugin's current PHP-harness and Jest tests against the review standard and
   record obvious weak-proof areas per block.
2. Add the test-effectiveness review guidance near existing test documentation.
3. Run `prove-regression.sh` against one concrete high-risk behavior (LDAP filter escaping is the
   strongest candidate given the existing audit finding referenced in `campus-directory-block`'s
   tasks.md) and confirm it catches an intentional defect.
4. Apply the review standard to WPM-118/119/120 test work as those tickets land, without altering
   their scope.

Rollback is straightforward because this change is documentation- and tooling-only: remove the
added guidance and any probe script if they create unacceptable friction.
