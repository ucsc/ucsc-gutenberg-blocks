# Course Catalog Block Audit

Date: 2026-07-17

Scope: `classes/CourseCatalog.php`, `src/blocks/CourseCatalog.js`, `src/components/CourseCatalog/tablesorter.js`, `src/components/CourseCatalog/*.css`, and related tests.

Tools used:

- `node /Users/henryh/_code/_tools/ucsc-node-review/bin/ucsc-node-review.mjs . --json --out .claude/ucsc-node-review-gutenberg.json`
- `docker run --rm -v /Users/henryh/_code/_tools/ucsc-php-review:/app -v /Users/henryh/_code/_campuspress/wp-dev.ucsc/public/wp-content/plugins/ucsc-gutenberg-blocks:/target -w /app composer:2 bin/ucsc-php-review /target --json --out /target/.claude/ucsc-php-review-gutenberg.json`
- `docker run --rm -v /Users/henryh/_code/_campuspress/wp-dev.ucsc/public/wp-content/plugins/ucsc-gutenberg-blocks:/plugin -w /plugin php:8.1-cli php tests/php/CourseCatalogTest.php`
- `npm test -- --runInBand`

Tool notes:

- `ucsc-node-review`: security output around `tablesorter.js` was mostly generic object-indexing noise; manual review found more important server-rendered escaping and validation issues.
- `ucsc-php-review`: PHPCS/PHPStan/PHPMD output was dominated by default-rule and missing-WordPress-stub messages.
- `CourseCatalogTest.php` failed before exercising product code because it redeclares PHP's built-in `filemtime()` at `tests/php/CourseCatalogTest.php:36`.
- Full JS unit suite passed 44/44.

## Architecture And Attack Surface

Course Catalog is a dynamic block registered at `classes/CourseCatalog.php:51-55`. It receives editor-saved `department`, `subject`, and `subjectOrDept` block attributes, builds an XML request to PeopleSoft, caches successful XML responses in transients for one week, parses XML with `simplexml_load_string()`, and renders a public HTML table directly from XML fields.

Generated/dependency directories excluded from manual review: `node_modules/`, `build/`, and raw runner JSON except for summary signals.

## Findings

### High: unescaped PeopleSoft XML fields render into public HTML

Files: `classes/CourseCatalog.php:306`, `classes/CourseCatalog.php:307`

Evidence and path: `$course->subject`, `$course->catalog_nbr`, `$course->title`, `$course->level`, `$course->units`, and `$course->description` are concatenated directly into HTML after parsing remote XML at `classes/CourseCatalog.php:242-256`.

Impact: if the upstream feed, cache, or test/staging target returns markup or script, it can execute for public visitors.

Remediation: cast feed values to strings and use `esc_html()` for table text. Use `wp_kses_post()` only for fields intentionally allowed to contain safe HTML.

Confidence: high.

Suggested validation: mock a feed course title and description containing `<script>alert(1)</script>` and assert rendered output contains escaped text, not markup.

### Medium: block attributes are inserted into outbound XML without validation or XML escaping

File: `classes/CourseCatalog.php:189`

Evidence and path: `department` and `subject` attributes are lowercased and inserted directly into `<acad_org>` or `<subject>` tags at `classes/CourseCatalog.php:191-205`.

Impact: an editor or compromised post can create malformed XML or inject extra XML nodes into the PeopleSoft request body, causing incorrect catalog queries and polluted cache entries.

Remediation: validate `subjectOrDept` as `dept|subject`, reject empty and `---`, constrain subject/dept to expected code characters or known options, and XML-escape values with `htmlspecialchars($value, ENT_XML1)` or build the request with `XMLWriter`.

Confidence: high.

Suggested validation: render with `department => 'lit</acad_org><subject>cse</subject>'` and assert the request is rejected or escaped as text.

### Medium: unknown course levels reuse a stale sort value

File: `classes/CourseCatalog.php:293`

Evidence and path: `$lvlval` is assigned only for `Lower Division`, `Upper Division`, and `Graduate` switch cases, then emitted at `classes/CourseCatalog.php:306`. It is not initialized for each course and has no `default`.

Impact: a course with a missing or new level can inherit the previous course's hidden sort value, producing incorrect sorting and PHP notices.

Remediation: initialize `$lvlval` at the start of every loop iteration and add a default case.

Confidence: high.

Suggested validation: feed two courses where the second has `level => 'Professional'`; assert it receives the default hidden sort value and no notice occurs.

### Medium: unconfigured or invalid block settings still trigger PeopleSoft requests

File: `classes/CourseCatalog.php:189`

Evidence and path: `getCachedCourses()` assumes `subjectOrDept`, `department`, and `subject` exist and does not reject empty or `---` values before building the query and cache key.

Impact: an unconfigured block can generate useless remote calls, cache bad responses, and emit PHP notices on public page render.

Remediation: mirror `ClassSchedule::theHTML()` behavior: default `subjectOrDept`, validate the active selected value, and return a user-facing prompt before calling PeopleSoft.

Confidence: high.

Suggested validation: render with empty attributes and assert no remote request is made.

### Low: Course Catalog JS/CSS are enqueued on every frontend page

File: `classes/CourseCatalog.php:31`

Evidence and path: `register_plugin_styles()` both registers and enqueues `tablesorterjs` and `tablesorter` on `wp_enqueue_scripts`, regardless of whether the block is present. `tablesorter.js` binds all `.table-sortable` tables globally.

Impact: unnecessary asset cost on pages without the block and potential behavior leakage into unrelated sortable tables.

Remediation: register assets globally if needed, but enqueue them inside `theHTML()` only when this block renders; scope selectors to the `#courseCatalog` wrapper.

Confidence: high.

Suggested validation: assert a page without the block does not enqueue `tablesorterjs`/`tablesorter`, while a page with the block does.

### Low: Course Catalog PHP test harness is not runnable on PHP 8

File: `tests/php/CourseCatalogTest.php:36`

Evidence and path: the test file declares `function filemtime()`, but `filemtime()` is a PHP built-in. Running the documented Docker command exits with `Fatal error: Cannot redeclare filemtime()`.

Impact: Course Catalog PHP behavior is not currently covered by the documented dependency-free test despite the test file existing.

Remediation: avoid mocking built-ins directly. Refactor the product code behind a wrapper if needed, or remove the `filemtime()` mock and make the test avoid paths that require file version values.

Confidence: high.

Suggested validation: the documented `docker run ... php tests/php/CourseCatalogTest.php` command exits 0.

## Remediation Order

1. Escape all rendered PeopleSoft fields.
2. Validate and XML-escape block attributes before building the PeopleSoft request.
3. Guard unconfigured block settings before remote calls.
4. Fix `$lvlval` initialization/default handling.
5. Move asset enqueueing to block render and fix the PHP test harness.

## Test Gaps

- Course Catalog PHP tests currently fail before running.
- No tests cover malicious XML field rendering, malformed block attributes, unknown course levels, empty attributes, or asset enqueue scoping.
