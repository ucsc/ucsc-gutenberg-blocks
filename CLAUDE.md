# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`ucsc-gutenberg-blocks` is a WordPress plugin of **dynamic** (server-rendered) Gutenberg
blocks for UCSC department sites: Class Schedule, Course Catalog, Campus Directory, and
Accordion. It is its own git repo, cloned by `setup.sh` into the
[wp-dev.ucsc](https://github.com/ucsc/wp-dev.ucsc) Docker environment at
`public/wp-content/plugins/ucsc-gutenberg-blocks`. That parent repo's `CLAUDE.md` governs
the Docker lifecycle and the `ucsc-wp-block-dev` Claude Code plugin (skills: `hub`,
`develop`, `run`, `validate`, `verify`, `review`) — prefer those skills over rediscovering
commands.

**Never run host Node/PHP/Composer.** All builds, tests, and PHP execution go through
containers. Host Node is not the toolchain and will mislead.

## Commands

All commands run from the plugin directory unless noted. `WP_DEV_ROOT` below is the
`wp-dev.ucsc` repo root (four levels up).

```bash
# Build / watch (run from WP_DEV_ROOT)
docker compose -f docker-compose.yml -f docker-compose-start.yml run --rm \
  -w /var/www/html/wp-content/plugins/ucsc-gutenberg-blocks plugin_npm_start npm run build

# Jest unit tests (same wrapper; swap `npm test` for build)
docker compose -f docker-compose.yml -f docker-compose-start.yml run --rm \
  -w /var/www/html/wp-content/plugins/ucsc-gutenberg-blocks plugin_npm_start npm test

# Single Jest file
... plugin_npm_start npx wp-scripts test-unit-js --testPathPattern=ClassSchedule

# PHP tests — plain PHP scripts, no PHPUnit. One file per block; run each.
docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli php tests/php/ClassScheduleTest.php
docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli php tests/php/CourseCatalogTest.php
docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli php tests/php/CampusDirectoryTest.php

# Puppeteer e2e (self-contained: builds its own Node+Chromium image, seeds the page)
bash tests/e2e/run-e2e.sh

# Clear Course Catalog feed cache
docker compose exec wpcli wp ucsc course-catalog-cache clear [--target=prod|csqa|all]
```

CI (`.github/workflows/test.yml`) runs `npm ci`, `npm test`, and **only**
`tests/php/ClassScheduleTest.php` on PRs to `main` — the other PHP suites are not in CI, so
run them locally when touching those blocks.

## Architecture

### Four-file pattern per block

Every block is spread across four locations; changing one usually means touching several:

| Layer | Path | Role |
|---|---|---|
| PHP controller | `classes/<Name>.php` | constructor wires all hooks; `theHTML($attributes)` is the `render_callback` |
| Markup | `templates/<Name>Template.php` | `include`d inside an `ob_start()` in `theHTML()`, so it reads the controller's local vars (`$courses`, `$attributes`, …) |
| Editor block | `src/blocks/<Name>.js` | `wp.blocks.registerBlockType` + the `edit` sidebar; `save` is a placeholder because rendering is server-side |
| Frontend assets | `src/components/<Name>/` | vanilla JS + CSS enqueued by the controller, versioned with `filemtime()` |

Registering a new block means editing **both** entry points: `include_once` + `new Foo()`
in `index.php`, and `import`/call in `src/index.js`. See `CustomBlock.md`.

Only `src/*.js` is bundled (`build/index.js`, enqueued as the `ucscblocks` **admin-only**
handle). Frontend `src/components/**/*.js` files are served raw, unbundled — do not use
ESM/JSX there; `classschedule.js` is an IIFE.

### Namespaces (two distinct ones — don't mix them up)

- **Blocks:** `ucscblocks/*` — `classschedule`, `coursecatalog`, `campusdirectory`,
  `accordion`, `accordion-wrapper`. The PHP `register_block_type` name and the JS
  `registerBlockType` name must match exactly.
- **REST:** `ucscgutenbergblocks/v1` = small editor-support endpoints on the block
  controllers and `SiteSettings` (dropdown option lists, saved dept/subject codes).
  `ucsc/v1` = `src/API/Course_Schedule_API.php`, the PeopleSoft-backed data API
  (`/terms`, `/courses/{term}`, `/course/{term}/{course}`).

### Data flow and caching

- **Class Schedule** does not call PeopleSoft directly. `ClassSchedule::theHTML()` renders
  server-side by issuing **internal** `rest_do_request()` calls to `ucsc/v1`, which owns
  the HTTP call and a 15-minute transient cache. Grepping for outbound HTTP in
  `classes/ClassSchedule.php` finds nothing — look in `src/API/Course_Schedule_API.php`.
- **Course Catalog** POSTs XML to a PeopleSoft `HttpListeningConnector` target
  (`PEOPLESOFT_TARGETS` selects prod vs. csqa), validates the XML before caching, and
  stores it in `course-catalog-*` transients for a week. Any API data **must** be cached —
  CampusPress code standards require it.
- **Campus Directory** queries LDAP directly (`classes/CampusDirectoryAPI.php`), which is
  why the dev image ships the PHP LDAP extension and why a UCSC VPN connection is needed.
  Credentials come from `ldap_api_key` / `ldap_cn` / `ldap_url`, read via `get_site_option`
  first (network settings) then `get_option` (per-site), managed by `classes/SiteSettings.php`.
  Also exposed without Gutenberg via the `[ucsc_profiles]` shortcode
  (`classes/CampusDirectoryShortcode.php`).

### Rewrites and detail pages

`ClassSchedule` and `CampusDirectory` add rewrite rules for `/course/<term>/<id>/` and
`/directory/<cruzid>/`, plus a 301 from legacy hyphenated `/course/<term>-<id>/…` URLs.
`index.php` re-flushes rewrite rules on activation **and** automatically whenever its own
`filemtime` changes (tracked in the `ucsc_gutenberg_blocks_rwflush` option) — that is the
guard against post-deploy 404s; keep it working if you touch activation code.
Course detail renders via `template_include`; directory profiles instead append to
`the_content` so block themes keep header/nav/footer.

## Testing conventions

- **PHP tests are dependency-free scripts**, not PHPUnit. `tests/php/helpers/harness.php`
  supplies `check($label, $cond)`, `finish_tests()`, and `function_exists`-guarded
  WordPress stubs; a test may define its own stub **before** requiring the harness to win.
  `ClassScheduleTest.php` uses the harness; the other two still inline their own stubs.
- **Jest** tests live in `src/blocks/__tests__/*.test.js` and
  `src/components/<Name>/__tests__/`. WordPress packages (`@wordpress/components`,
  `@wordpress/element`) are runtime-provided, not installed — mock them with
  `{ virtual: true }`. Child components are mocked too, so tests cover the block's own
  logic. `jest-unit.config.js` drops the default enzyme snapshot serializer (cheerio needs
  Node 18+); `jest-setup.js` polyfills `TextEncoder`/`TextDecoder`.
- **e2e** (`tests/e2e/class-schedule.spec.js`) drives the live `https://wp-dev.ucsc`
  frontend with Puppeteer against the self-signed cert. `run-e2e.sh` seeds a page via
  `wp eval-file seed-e2e-page.php` and runs in a container reaching the host through
  `--add-host=wp-dev.ucsc:host-gateway`. Requires the stack to be up.

## Releases

`commit-and-tag-version` drives versioning from Conventional Commits (`npm run dryrun`
first, then `npm run release`). Commit subjects follow
`type(scope): WPM-xxx description` and feed `CHANGELOG.md`. Bumping updates
`package.json`, `package-lock.json`, the `Version:` header in `index.php`
(`wp-plugin-version-updater.js`), and the editor-sidebar `>version X.Y.Z<` indicator in
`src/blocks/ClassSchedule.js` (`js-version-updater.js`) — that updater **throws** if the
JSX text node no longer matches, so update the regex if you reformat that markup.
Pushing a `v*.*.*` tag triggers `ucsc/actions` to build and publish the plugin zip.
Branches: `feature/WPM-xxx_description` or `dev/<user>/<topic>`, PRs into `main`.

## Xdebug

Listens on port 9003. Map `/var/www/html/wp-content/plugins/ucsc-gutenberg-blocks` →
`${workspaceRoot}`, hostname `wp-dev.ucsc` (full `launch.json` in `README.md`).
