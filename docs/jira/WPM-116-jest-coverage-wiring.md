# Wire Jest coverage reporting so JS coverage is measurable

**Jira:** WPM-116 · **Parent:** WPM-115 · **Type:** Task

## Problem

`package.json` runs `wp-scripts test-unit-js`, and `--coverage` works today with
no new dependency. But `jest-unit.config.js` declares no `collectCoverageFrom`
and no machine-readable reporter, so a coverage run:

- reports only on modules that some test happened to `import`, and
- silently omits every file no test touches — precisely the gap set we want.

Result: there is no ingestible JS coverage artifact, and no honest JS number.

## Scope

In `jest-unit.config.js`:

- Add `collectCoverageFrom` covering `src/**/*.js`, excluding `src/**/__tests__/**`
  and build output.
- Add `coverageReporters: ['text', 'json-summary', 'lcov']` so
  `coverage/coverage-summary.json` and `coverage/lcov.info` are emitted and can
  be ingested by the coverage report.
- Add a `test:coverage` npm script.
- Add `coverage/` to `.gitignore` if not already ignored.

Runs in Docker like the rest of the Jest suite:

```
docker compose -f docker-compose.yml -f docker-compose-start.yml run --rm \
  -w /var/www/html/wp-content/plugins/ucsc-gutenberg-blocks \
  plugin_npm_start npm run test:coverage
```

## Acceptance criteria

- [x] `coverage/coverage-summary.json` and `coverage/lcov.info` are produced by a
      Docker-only run — no host Node.
- [x] The coverage report's Layer 1 shows a real `jest/summary` line instead of
      "No coverage artifact found".
- [x] Files with **no** test appear in the report at 0%, not absent.
- [x] The existing 4 Jest suites still pass.

## Implementation

Completed 2026-08-26 (prior to WPM-117).

**Files changed:**
- `jest-unit.config.js` — Added coverage configuration:
  - `collectCoverageFrom: ['src/**/*.js']` with exclusions for `__tests__` and test files
  - `coverageReporters: ['text-summary', 'json-summary', 'lcov']`
  - `coverageDirectory: 'coverage'`
- `package.json` — Added `"test:coverage": "wp-scripts test-unit-js --coverage"`
- `.gitignore` — Added `coverage/` to ignore coverage artifacts

**Usage:**
```bash
# Local run
npm run test:coverage

# Docker run (from wp-dev.ucsc stack root)
docker compose -f docker-compose.yml -f docker-compose-start.yml run --rm \
  -w /var/www/html/wp-content/plugins/ucsc-gutenberg-blocks \
  plugin_npm_start npm run test:coverage
```

**Result:**
Jest Coverage: **35.08% statements (254/724)**, 34.89% lines (239/685)

Coverage artifacts generated:
- `coverage/coverage-summary.json` — Machine-readable summary
- `coverage/lcov.info` — LCOV format for IDE/CI integration
- `coverage/lcov-report/` — HTML coverage report

Files with 0% coverage ARE included (e.g., `Accordion.js`, `ContentSharer.js`, 
`FeedbackForm.js`) — proving `collectCoverageFrom` is working correctly to surface 
untested files.

## Out of scope

Raising the number. This ticket makes JS coverage *measurable*; the per-block
tickets do the covering.

## Notes

No new npm dependency. Highest-return single change on the plugin, and a
prerequisite for the campus-directory and course-catalog component work.
