# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`ucsc-gutenberg-blocks` is one of the two UCSC block plugins. It provides
**Course Catalog**, **Class Schedule**, and **Campus Directory** blocks (plus
**Accordion** / **Accordion Wrapper**), as well as a non-Gutenberg shortcode path
for the directory. It is checked out as its own git repo inside the
`wp-dev.ucsc` Docker environment at
`public/wp-content/plugins/ucsc-gutenberg-blocks` (see the parent repo's
`CLAUDE.md` for the Docker stack lifecycle — start/stop, WP-CLI, the in-container
build/test command form, and the `ucsc-wp-block-dev` Claude plugin / `driver.sh`).

**Namespaces matter for detection and fingerprinting:** blocks are registered in
PHP under `ucscblocks/*` (e.g. `ucscblocks/coursecatalog`), but rendered output
uses the `ucsc/*` namespace. The Course Schedule REST API namespace is `ucsc/v1`;
the older per-block REST routes use `ucscgutenbergblocks/v1`. Do not assume
`ucsc/*`-only tooling will find these blocks.

**Never run host Node / PHP / Composer.** Everything goes through containers (see
Commands below). Local toolchains will mislead.

## Anatomy of a block (the dominant pattern)

These are **dynamic blocks**: the editor registration is mostly a placeholder and
the real markup is produced server-side by a PHP `render_callback`. A block is
spread across four places, all named after the block:

- **`classes/<Name>.php`** — the controller. Its constructor wires WordPress
  hooks (`init`, `wp_enqueue_scripts`, `rest_api_init`, rewrite rules, etc.),
  calls `register_block_type('ucscblocks/<name>', [... 'render_callback' => ...])`,
  and enqueues front-end JS/CSS from `src/components/`. **Everything stems from
  here.**
- **`src/blocks/<Name>.js`** — editor-side block registration (name + icon +
  minimal edit/save). The registered name must match the PHP `register_block_type`
  name.
- **`src/components/<Name>/`** — front-end browser JS + CSS (e.g.
  `tablesorter.js`, `classschedule.js`) and editor CSS.
- **`templates/<Name>Template.php`** — PHP markup for full-page renders (course
  detail, directory profile) wired via `template_include`.

**Wiring a block in requires two edits** (mirroring each other): include +
instantiate the class in `index.php`, and `import` + call the registration fn in
`src/index.js`. Blocks currently commented out in both files (ContentSharer,
FeedbackForm, the TestDemo blocks) are intentionally disabled — they still have
classes/tests but are not loaded. See `CustomBlock.md` for the full walkthrough.

### Rendering / data flow specifics

- **Course Schedule API** (`src/API/Course_Schedule_API.php`, namespace `ucsc/v1`)
  proxies PeopleSoft REST data with transient caching (`CACHE_DURATION` 15 min).
  Some blocks render server-side by calling their own REST endpoints internally
  via `rest_do_request(new WP_REST_Request(...))` rather than fetching client-side.
- **Detail-page routing** (ClassSchedule, CampusDirectory): custom rewrite rules
  map pretty URLs (`/course/<term>/<id>/`, `/directory/<cruzid>/`) to query vars,
  and `template_include` swaps in the matching `templates/*.php`. Activation,
  deactivation, and a file-mtime check on `init` (`ucsc_gutenberg_blocks_maybe_flush_rewrites`)
  flush rewrite rules so deploys don't 404 these routes. If you add/change a
  route, expect to flush permalinks.
- **Caching is mandatory** for external API data (CampusPress code standard). Use
  transients. The Course Catalog cache can be cleared with WP-CLI:
  `wp ucsc course-catalog-cache clear [--target=all|prod|qa]`.
- **Campus Directory needs the PHP LDAP extension + UCSC VPN** to reach the LDAP
  server (the reason the parent env uses a custom Docker image). The LDAP API key
  is read from the `ldap_api_key` site/blog option.
- **`build/index.js`** is the compiled editor bundle (from `src/index.js` via
  `wp-scripts`), enqueued in `index.php` as the `ucscblocks` script. In dev
  environments it's cache-busted by `filemtime`; in prod by plugin version.

## Commands

Run npm scripts in-container against the parent stack's `plugin_npm_start`
service with a working-dir override (full form in the parent `CLAUDE.md`):

```bash
docker compose -f docker-compose.yml -f docker-compose-start.yml run --rm \
  -w /var/www/html/wp-content/plugins/ucsc-gutenberg-blocks \
  plugin_npm_start npm run build      # or: start (watch), test
```

npm scripts (`package.json`): `build`, `start` (watch), `test`
(`wp-scripts test-unit-js` — Jest), `test:e2e` (`wp-scripts test-e2e
--rootDir=tests/e2e`), `plugin-zip`, `release` / `dryrun` (versioning).

### Validation drivers (`bin/`) — no host toolchain needed

These are the canonical way to run the test suites; each spins up its own
container and writes a log to `$UCSC_LOG_DIR` (default `/tmp`):

```bash
bash bin/validate.sh             # php + jest + e2e, combined PASS/FAIL summary
bash bin/validate.sh php jest    # subset
bash bin/validate-php.sh         # standalone PHP tests + PHPUnit, in php:8.1-cli
bash bin/validate-jest.sh
bash bin/validate-e2e.sh         # needs the wp-dev.ucsc stack UP
bash bin/verify.sh               # live-behavior verification
```

`bin/` also has seed helpers: `seed_course_cache.{php,sh}`, `seed_demo_page.{php,sh}`.

## Testing — three independent layers

1. **Jest** (`src/blocks/__tests__/<Name>.test.js`): tests the editor block
   components via `@testing-library/react`. `@wordpress/*` packages are provided
   at runtime, **not** installed, so they must be `jest.mock(..., { virtual: true })`;
   child components (dropdowns, layouts) are mocked too. Config:
   `jest-unit.config.js` (+ `jest-setup.js` polyfills TextEncoder for Node 16,
   enzyme serializer removed).
2. **Standalone PHP** (`tests/php/<Name>Test.php`): dependency-free scripts that
   define their own WP-function stubs and `exit` non-zero on failure — each runs
   as its own `php <file>` process, no PHPUnit. Run directly:
   `docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli php tests/php/ClassScheduleTest.php`.
3. **PHPUnit** (`tests/phpunit/`): run via the bundled `phpunit.phar` with
   `tests/phpunit/bootstrap.php` (which stubs `esc_html`, `esc_attr`, etc.).
   Currently covers template rendering (`CampusDirectoryTemplateTest.php`).
4. **E2E** (`tests/e2e/*.spec.js`): Puppeteer against the live stack at
   `https://wp-dev.ucsc` (self-signed cert). `tests/e2e/run-e2e.sh` runs it in a
   Node+Chromium container; `jest-puppeteer.config.js` has the launch details.

Run a single Jest file by appending `--testPathPattern=<Name>` to the
`test-unit-js` command.

CI (`.github/workflows/test.yml`) runs `npm test` plus the single standalone PHP
test `tests/php/ClassScheduleTest.php` on PRs to `main`. `release.yml` builds and
releases on `v*.*.*` tags via the shared `ucsc/actions` workflow.

## Releasing / versioning

`npm run release` (commit-and-tag-version) bumps the version in lockstep across
`package.json`, `package-lock.json`, and the **plugin header `Version:` in
`index.php`** (via `wp-plugin-version-updater.js`), and updates `CHANGELOG.md`
from conventional commits. Use `npm run dryrun` to preview. Do not hand-edit the
version in one place only.
