# Test Effectiveness

Companion to `openspec/changes/evaluate-test-effectiveness/` and the WPM-115 test-coverage epic
(`docs/jira/WPM-115-epic-test-coverage.md`). WPM-115 is closing structural gaps; this document is
about whether the tests that close those gaps actually prove behavior. A covered line is not a
behavior proof unless a test asserts the resulting rendered output, cached value, LDAP filter
string, or REST response.

## Current Baseline

Recorded 2026-08-28 by running the suites directly (no coverage instrumentation needed to record
pass/fail):

```bash
docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli php tests/php/<File>Test.php
docker compose -f docker-compose.yml -f docker-compose-start.yml run --rm \
  -w /var/www/html/wp-content/plugins/ucsc-gutenberg-blocks plugin_npm_start npm run test
```

| Suite | Result | Notes |
| --- | --- | --- |
| `tests/php/CampusDirectoryTest.php` | 28/28 passed | LDAP query shape, filter escaping, transient caching, template routing |
| `tests/php/CampusDirectoryShortcodeTest.php` | **24/28 passed** | 4 failures are intentional — see "A documented failing baseline" below |
| `tests/php/ClassScheduleTest.php` | 66/66 passed | REST-backed rendering, routing, rewrite rules, redirects |
| `tests/php/CourseCatalogTest.php` | 29/29 passed | PeopleSoft target selection, cache, feed error handling |
| Jest (4 suites) | 60/60 passed | Block registration, editor behavior, frontend `classschedule.js` interactions |

### A documented failing baseline

`CampusDirectoryShortcodeTest.php` does not pass clean. Four checks fail on purpose and are
labeled `KNOWN VULNERABILITY` / `this test should FAIL` in the test source: LDAP-sourced `cn`,
`title`, and phone-number fields render into HTML unescaped, and the `cruzids` shortcode attribute
is not escaped in the rendered `id` attribute. This is an **audit-pin pattern** — a test written to
assert the *secure* behavior, deliberately left failing so the suite documents a live gap instead
of hiding it behind a passing assertion. Treat a suite with this pattern as passing "except for
N documented findings," not as red; check the failure list against this table before assuming a
change introduced a new failure. The escaping fix itself is out of scope here — see
`openspec/changes/campus-directory-block/tasks.md` §3.1 for the tracked remediation.

## Review Standard

For each new or changed test, ask: **what plausible defect would make this test fail?**

This follows the same reasoning WPM-115's ground rules already state ("a test is not coverage
until it has been seen to fail") plus two established testing ideas:

- Mutation testing measures whether tests detect small intentional defects — see "Proving a Test"
  below for how to run this by hand against this plugin's tests.
- Brittle tests are a maintenance smell: tests should fail for important behavior changes, not
  incidental implementation details.

An effective test should:

- Name or structure the behavior it protects (an LDAP filter string, a cache key/TTL, a rendered
  field, a REST response shape) rather than only a method name.
- Use realistic fixtures — directory records, course/schedule payloads, block attributes — that
  resemble what this plugin actually receives from LDAP or the `ucsc/v1` REST proxy.
- Exercise the real behavior rather than replacing it with mocks that assert nothing about the
  mocked call's inputs.
- Assert the observable result: rendered HTML, a stored transient value, a REST response body, an
  LDAP filter string, a redirect, an enqueued asset.
- Include negative or boundary cases when the behavior can fail unsafely: LDAP filter
  metacharacters, an empty or malformed upstream response, a missing attribute key.

A weak test should be revised, rejected, or explicitly labeled smoke coverage when it:

- Only asserts that code runs (`renders without crashing` — present in all three blocks' Jest
  suites; acceptable as a cheap smoke check, but it does not count as behavior proof for anything
  more specific).
- Asserts implementation details no one downstream relies on (`registers with the correct block
  name`, `has the correct icon` — fine as config-pinning, not behavior proof).
- Recomputes the expected value by duplicating production logic.
- Mocks the behavior it claims to prove without asserting what the mock received.
- Contains a tautological or short-circuited assertion. **Found during this baseline review:**
  `CampusDirectoryShortcodeTest.php` line 234 —
  `check( 'Default cruzid is "cosmo"', strpos( $result, 'cosmo' ) === false || true );` — the
  `|| true` makes this check pass regardless of `$result`. It is not currently exercising anything.
  Left as-is pending a decision on the fixture; the label suggests the test writer changed the
  fixture (to `jgarcia`) without updating the assertion. Flagged here as the review standard's own
  worked example rather than fixed silently.

## AI-Assisted Tests

AI-generated or AI-assisted tests need the same scrutiny as production code. Do not accept one
because it passes or raises the structural coverage number reported by
`skills/validate/scripts/coverage-report.py`.

Before accepting one, verify:

- The scenario resembles a real block workflow: a realistic LDAP record, `ucsc/v1` response shape,
  or block attribute set — not synthetic data shaped to make the assertion trivially true.
- The assertions would fail for a plausible real defect, not just for the exact code currently
  written.
- The test does not duplicate implementation logic (e.g., recomputing an LDAP filter string in the
  test to compare against itself).
- The maintenance cost — bespoke fixtures, broad LDAP/REST mocking — is justified by the behavior
  risk being protected.

## Proving a Test

Do not build a permanent second suite just to test the tests. Built-in proof — clear fixtures,
real behavior, assertions that fail for real defects — is enough for most tests. Reach for a
targeted probe only when a reviewer genuinely cannot tell whether a test would catch a plausible
regression.

The `ucsc-wp-block-dev` skill already ships a general mutation-probe tool
(`skills/validate/scripts/prove-regression.sh`, ADR-113) that does exactly this: swap in a broken
version of a file, confirm the change actually landed, run a check command, confirm it fails, then
restore the file unconditionally — regardless of whether the check passed or crashed. This plugin's
dependency-free PHP harness (`tests/php/*Test.php` run via a one-shot `php:8.1-cli` container) has
no persistent container to verify against, so skip the container-hash check with `--container ""`:

```bash
bash "<ucsc-wp-block-dev skill root>/skills/validate/scripts/prove-regression.sh" \
  --file classes/CampusDirectoryAPI.php \
  --broken-rev <a-revision-before-ldap_escape-was-added> \
  --container "" \
  --check "docker run --rm -v \"\$PWD:/plugin\" -w /plugin php:8.1-cli php tests/php/CampusDirectoryTest.php"
```

(the skill root is wherever the `ucsc-wp-block-dev` Claude Code skill/plugin is installed —
typically under `~/.claude/skills/ucsc-wp-block-dev` — not a path inside this repo)

A successful probe means the targeted test failed while the defect was present and passed once
restored — i.e., the test is trustworthy proof for that behavior, not just code that happens to run.
No new script needs to live in this repo for this; `prove-regression.sh` is a shared tool intended
for exactly this use across the UCSC block plugins.

### Probe result: `buildUidFilter()` LDAP escaping (2026-08-28)

Ran the above against `CampusDirectoryAPI::buildUidFilter()`, temporarily replacing
`ldap_escape($uid, "", LDAP_ESCAPE_FILTER)` with the raw `$uid` (defect: unescaped LDAP filter
metacharacters reach the query) and checking `tests/php/CampusDirectoryTest.php`:

- Clean tree: 28/28 passed.
- Defect present: 26/28 passed — `wildcard cruzids are escaped in manual list filters` and
  `wildcard add cruzids are escaped in feed filters` both failed, exactly the two WPM-103
  hardening tests that name this behavior.
- File restored; working tree confirmed clean (`git status --porcelain` empty, md5 matched).

**PROVEN** — these two tests are trustworthy proof for `buildUidFilter()`'s escaping, not just
tests that happen to pass. No other test in the suite incidentally caught the regression, which
confirms escaping coverage here rests on exactly those two assertions and nothing broader.

**Decision:** keep this as a documented one-time probe, not a recurring CI check. The behavior is
simple enough (one `ldap_escape()` call) that re-running the probe is only worth doing if
`buildUidFilter()` or its two hardening tests change; it is not fast or automatic enough today to
justify wiring into CI per this change's non-goals.

## References

- WPM-115 epic ground rules: `docs/jira/WPM-115-epic-test-coverage.md`
- `openspec/changes/evaluate-test-effectiveness/` — the OpenSpec change this document implements
- ADR-113-VALIDATE-REGRESSION-PROOF (`ucsc-wp-block-dev` skill) — the `prove-regression.sh` tool
- ADR-114-VALIDATE-COVERAGE-MODE (`ucsc-wp-block-dev` skill) — the three-layer coverage report this
  document's "structural coverage number" refers to
