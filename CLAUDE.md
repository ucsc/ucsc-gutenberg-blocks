# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`ucsc-gutenberg-blocks` is a WordPress plugin providing UCSC's custom Gutenberg blocks
(class schedule, course catalog, campus directory, accordion, etc.). It is developed
inside the `wp-dev.ucsc` Docker Compose stack (a sibling checkout) — **see
`wp-dev.ucsc/CLAUDE.md`** for how that stack works, the `ucsc-wp-block-dev` Claude Code
plugin skills, and the run/validate/verify workflow. This file covers only what's
specific to this plugin's code.

Registered dev namespace is `ucscblocks/*`; rendered/production namespace is `ucsc/*`
(both dynamic blocks and the internal REST API use `ucsc/*` — see Architecture below).

## Commands

All commands run **inside the Docker containers** from the `wp-dev.ucsc` repo root, not
on the host (no local Node/PHP toolchain — see the parent CLAUDE.md's "Never run host
Node / PHP / Composer" rule). From this plugin directory, the equivalent in-container
form is:

```bash
# Jest unit tests (all)
docker compose -f docker-compose.yml -f docker-compose-start.yml run --rm \
  -w /var/www/html/wp-content/plugins/ucsc-gutenberg-blocks \
  plugin_npm_start npm test

# Jest unit tests (single file / pattern)
docker compose -f docker-compose.yml -f docker-compose-start.yml run --rm \
  -w /var/www/html/wp-content/plugins/ucsc-gutenberg-blocks \
  plugin_npm_start npx wp-scripts test-unit-js --testPathPattern=ClassSchedule

# Build production JS (src/ -> build/index.js)
docker compose -f docker-compose.yml -f docker-compose-start.yml run --rm \
  -w /var/www/html/wp-content/plugins/ucsc-gutenberg-blocks \
  plugin_npm_start npm run build

# PHP tests — dependency-free, no PHPUnit/Composer. Run directly with php-cli.
docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli php tests/php/ClassScheduleTest.php
docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli php tests/php/CampusDirectoryTest.php
docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli php tests/php/CourseCatalogTest.php

# e2e (Playwright/Puppeteer via wp-scripts, fully containerized — no host Chrome needed)
bash tests/e2e/run-e2e.sh
```

Prefer the `ucsc-wp-block-dev` Claude Code plugin's `validate` skill (`php`/`jest`/`e2e`
modes) over invoking these directly — it wraps the same commands with plugin
auto-detection.

CI (`.github/workflows/test.yml`) runs `npm test` (all Jest) and only
`tests/php/ClassScheduleTest.php` on PRs into `main` — `CampusDirectoryTest.php` and
`CourseCatalogTest.php` exist but are not yet wired into CI.

## Architecture

### Two-namespace dynamic block pattern

Every block here is a **dynamic block**: registration happens in PHP with a
`render_callback`, not in JS via `save()`. Each block has up to four files that must
stay in sync:

1. `classes/<Name>.php` — PHP controller. Constructor wires WordPress hooks
   (`init`, `wp_enqueue_scripts`, `rest_api_init`, etc.), calls
   `register_block_type('ucscblocks/<name>', ['render_callback' => ...])`, and
   contains the block's server-side logic (data fetching, caching, rewrite rules).
2. `templates/<Name>Template.php` — markup, `echo`'d/included by the render callback
   for full-page templates (course detail pages, directory profiles), or inlined
   directly in the class for simpler blocks.
3. `src/blocks/<Name>.js` — editor-side registration only (block name, icon,
   attributes for the block inspector). Must be imported and invoked in
   `src/index.js`, and the registered name here must match the PHP `register_block_type`
   call exactly.
4. `src/components/<Name>/` — front-end JS/CSS enqueued by the PHP class via
   `wp_register_script`/`wp_register_style` in a `register_plugin_styles()`-style
   method (not bundled through `@wordpress/scripts`).

New blocks: follow `CustomBlock.md` in this directory, and `index.php` must
`include_once` the new class file and instantiate it.

### Internal REST API as the data layer

`src/API/Course_Schedule_API.php` (`Course_Schedule_API` class) registers the
`ucsc/v1` REST namespace (`/terms`, `/courses/(?P<term>)`, course detail) that
proxies and caches PeopleSoft data (`PS_BASE_URL`, 15-minute transient cache via
`CACHE_DURATION`). `classes/ClassSchedule.php` does **not** call PeopleSoft directly —
it builds a `WP_REST_Request` and calls `rest_do_request()` against `ucsc/v1`
internally (see `getCachedCourses()` and `course_detail_title()`). This means testing
or auditing "Class Schedule" data flow requires reading `Course_Schedule_API.php`,
not just `ClassSchedule.php`. `index.php` instantiates the API's `init()` explicitly:
`(new Course_Schedule_API())->init();`.

`classes/CampusDirectory.php` + `classes/CampusDirectoryAPI.php` follow a similar
split (directory queries live in the `API` class), but talk to LDAP instead of REST,
and cache via `get_transient()`/`set_transient()` directly (not through a REST layer)
— list vs. profile views use separate cache keys because they request different
LDAP attribute sets (see the `md5_q` comment in `CampusDirectoryAPI.php`).

`classes/CourseCatalog.php` fetches and caches PeopleSoft XML feeds directly
(`course-catalog-<target>-...` transient keys, `WEEK_IN_SECONDS` TTL, only cached
once the body parses as valid XML — see the cache-poisoning-avoidance comment around
line 255). It supports a `prod`/`csqa` PeopleSoft target split
(`PEOPLESOFT_TARGETS`) and a cache-bypass query flag
(`ucsc_course_catalog_bypass_cache`). A WP-CLI command
`wp ucsc course-catalog-cache clear [--target=<prod|csqa|all>]` is registered in
`index.php` for clearing these transients.

### Rewrite-based detail pages

`ClassSchedule` and `CampusDirectory` both register custom rewrite rules
(`add_course_detail_rewrite()`, `add_directory_profile_rewrite()`) for pretty URLs
like `/course/<term>/<id>/` and `/directory/<cruzid>/`, resolved via
`template_include` to `templates/CourseDetailTemplate.php` /
`DirectoryProfileTemplate.php`. Because rewrite rules are cached, `index.php`
auto-flushes them once per deploy by comparing `filemtime(__FILE__)` against a stored
option (`ucsc_gutenberg_blocks_rwflush`), and also flushes on plugin
activation/deactivation — don't add new rewrite rules without confirming this flush
path still covers them.

### PHP test harness (no PHPUnit)

`tests/php/` tests are dependency-free PHP scripts, not PHPUnit — see
`tests/php/helpers/harness.php` for the shared `check()`/`finish_tests()` runner and
WordPress function stubs (each stub is `function_exists`-guarded so a test file can
override it before requiring the harness). Only `ClassScheduleTest.php` currently uses
the shared harness; `CampusDirectoryTest.php` and `CourseCatalogTest.php` predate it
and should migrate onto it when their branches merge (per the harness file's own
docblock). Tests run with plain `php-cli` against a WordPress-stubbed environment —
no database, no WordPress core.

### Jest environment

`@wordpress/scripts` provides Jest config; `jest-unit.config.js` layers on
`jest-setup.js` (TextEncoder/TextDecoder polyfill for Node 16/jsdom) and disables
enzyme's snapshot serializer (not used — this repo uses
`@testing-library/react`). WordPress packages (`@wordpress/components`, etc.) are
runtime-provided by WordPress core, not npm dependencies, so tests must mock them
with `{ virtual: true }`. Test files live in `src/blocks/__tests__/` as
`<BlockName>.test.js`.

### Versioning

Version is tracked in three places kept in sync by `commit-and-tag-version`
(`npm run release` / `npm run dryrun`): `package.json`, `package-lock.json`, and the
`Version:` header in `index.php` (via the custom updater
`wp-plugin-version-updater.js`). Don't hand-edit the `index.php` version header —
let the release tooling do it.
