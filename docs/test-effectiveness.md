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

## Coverage Reporting

Coverage is a discovery signal, not proof that behavior is protected. Generate it when you need to
find weakly covered surfaces, then use the review standard below to decide whether those lines are
covered by useful assertions.

Run JavaScript coverage from the plugin directory:

```bash
npm run test:coverage
```

Expected output:

```text
coverage/coverage-summary.json
coverage/lcov.info
coverage/lcov-report/index.html
```

Run PHP coverage from the plugin directory:

```bash
composer run test:coverage
```

Expected output:

```text
coverage/clover.xml
coverage/html/index.html
coverage/coverage-raw.json
```

Then run the unified coverage report with Python 3.11 or newer:

```bash
/Users/henryh/.local/bin/python3.11 \
  /Users/henryh/_code/_opensource/ucsc-wp-block-dev/skills/validate/scripts/coverage-report.py .
```

Use `--gaps` when you want a prompt-ready list of untested units:

```bash
/Users/henryh/.local/bin/python3.11 \
  /Users/henryh/_code/_opensource/ucsc-wp-block-dev/skills/validate/scripts/coverage-report.py . --gaps
```

Verification on 2026-09-17:

- `npm run test:coverage -- --runInBand` passed: 11 suites, 160 tests, 81.01% statements,
  80.64% lines.
- `composer run test:coverage` passed: 10 PHP suites, 344 PHP checks, 100.00% harness-covered
  statements, with Clover and HTML reports emitted.
- `coverage-report.py .` passed with Python 3.11 and read `coverage/clover.xml`,
  `coverage/coverage-summary.json`, and `coverage/lcov.info`.
- `coverage-report.py . --gaps` passed and reported 15 structurally untested units.

Known reporting caveat: the unified report's readiness layer still prints historical WPM-117
"BLOCKED" advice about missing PHP coverage drivers even when Layer 1 successfully reads the newly
generated `coverage/clover.xml`. Treat the existing Clover/HTML artifacts as the authoritative
coverage-run result and the readiness warning as stale reporting guidance until
`coverage-report.py` is updated.

## Review Standard

For each new or changed test, ask: **what plausible defect would make this test fail?**

This follows the same reasoning WPM-115's ground rules already state ("a test is not coverage
until it has been seen to fail") plus two established testing ideas:

- Mutation testing measures whether tests detect small intentional defects — see "Proving a Test"
  below for how to run this by hand against this plugin's tests.
- Brittle tests are a maintenance smell: tests should fail for important behavior changes, not
  incidental implementation details.
- The UCSC Laravel/Vue baseapp testing standard requires tests to document their intent and fake
  external services at the lowest available seam. Those two ideas transfer to this plugin even
  though its PHP harness is dependency-free and does not use Laravel, PHPUnit, or Vue.

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
- Explain why a non-trivial test exists, either in the test name plus surrounding group label or in
  a short comment/docblock when the behavior, ticket context, or deliberate gap is not obvious.
- Fake LDAP and REST/HTTP at the lowest available seam. The PHP harness should shadow functions
  such as `ldap_search()`, `wp_remote_get()`, or `wp_remote_post()` with deterministic in-memory
  fixtures; Jest tests should mock browser fetch/WordPress package seams. No test should require a
  live LDAP server, PeopleSoft endpoint, or campus directory service.

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

## External Skill Sources

Use external WordPress testing-skill resources as optional guidance, not as authoritative project
policy. Only cite sources that have been confirmed to exist.

Confirmed sources:

- `WordPress/agent-skills`: public repository confirmed with `git ls-remote`; WordPress.org's
  announcement documents project-local installation via `npx openskills install
  WordPress/agent-skills` followed by `npx openskills sync`. This is a multi-topic WordPress skill
  collection, not a single-purpose testing package.
- `jorgerosal/wordpress-skills`: public repository confirmed with `git ls-remote`; its README
  documents Codex installation by copying `codex-skills/*` into `~/.codex/skills/` and shared
  references into `~/.codex/claude-skills`. This is also a multi-topic WordPress skill collection.

Do not document unverified package names such as `wordpress-block-unit-testing`,
`wordpress-dynamic-block-testing`, or `wordpress-block-accessibility-testing` unless a real
repository or install target has been confirmed.

## References

- WPM-115 epic ground rules: `docs/jira/WPM-115-epic-test-coverage.md`
- `openspec/changes/evaluate-test-effectiveness/` — the OpenSpec change this document implements
- UCSC Laravel/Vue baseapp testing standard:
  `/Users/henryh/_code/_laravel/baseapp/doc/TESTING-STANDARDS.md`
- ADR-113-VALIDATE-REGRESSION-PROOF (`ucsc-wp-block-dev` skill) — the `prove-regression.sh` tool
- ADR-114-VALIDATE-COVERAGE-MODE (`ucsc-wp-block-dev` skill) — the three-layer coverage report this
  document's "structural coverage number" refers to
