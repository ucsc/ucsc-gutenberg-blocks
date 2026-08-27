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

- [ ] `coverage/coverage-summary.json` and `coverage/lcov.info` are produced by a
      Docker-only run — no host Node.
- [ ] The coverage report's Layer 1 shows a real `jest/summary` line instead of
      "No coverage artifact found".
- [ ] Files with **no** test appear in the report at 0%, not absent.
- [ ] The existing 4 Jest suites still pass.

## Out of scope

Raising the number. This ticket makes JS coverage *measurable*; the per-block
tickets do the covering.

## Notes

No new npm dependency. Highest-return single change on the plugin, and a
prerequisite for the campus-directory and course-catalog component work.
