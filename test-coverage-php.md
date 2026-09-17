# PHP Test Coverage

Date: 2026-09-16

Scope: dependency-free PHP harness suites under `tests/php/*Test.php`.

Runner: plain `php` CLI — no PHPUnit, no framework. Each file is a standalone
script that defines its own WordPress stubs and calls `check($label, $condition)`.

## Running Coverage

From the plugin directory:

```bash
composer run test:coverage
```

This writes:

```text
coverage/clover.xml
coverage/html/index.html
coverage/coverage-raw.json
```

> **Current baseline:** PHP coverage reports 100.00% statement coverage
> (1048/1048) across the files instrumented by the local harness. All 11 PHP
> suites pass — the four intentionally failing XSS assertions previously noted in
> `CampusDirectoryShortcodeTest.php` were resolved by the WPM-132 escaping fix.
> Statement coverage still measures only which lines execute, not whether their
> behaviour is asserted, so it should not be read as a quality ceiling —
> WPM-171 is the worked example: every affiliation-narrowing branch in
> `CampusDirectoryAPI.php` already counted as covered because WPM-113's test
> *ran* them, yet none of their output was asserted until WPM-171.
>
> **WPM-119 baseline (class-schedule block).** With the harness coverage
> instrumentation (WPM-117) emitting clover, the class-schedule block's real
> line coverage is now recorded as the baseline for the block:
> `templates/ClassScheduleTemplate.php` 75/75 lines (100%),
> `classes/ClassSchedule.php` 98/126 lines (77.8%). Before
> `ClassScheduleTemplateTest.php`, the template file was reached only as a
> side effect of `ClassSchedule::theHTML()` in `ClassScheduleTest.php` and had
> no test asserting the escaping of its interpolated values; the structural
> coverage matcher credited its 189 source lines to a comment match in a Jest
> test (`src/components/ClassSchedule/__tests__/classschedule.test.js`) that
> cannot execute PHP.

The structural gap report groups classes, templates, blocks, and components that
are named by no test (read-only, no mutation):

```bash
python3 /path/to/ucsc-wp-block-dev/skills/validate/scripts/coverage-report.py . --gaps
```

On systems where the script requires Python 3.10+, use that interpreter; Python 3.9
cannot parse its union type syntax.

Run command (from repo root):
```
docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
  php tests/php/<TestFile>.php
```

Shared harness: `tests/php/helpers/harness.php` — provides `check()`, `finish_tests()`,
and common WordPress stubs (`add_action`, `add_filter`, `sanitize_text_field`,
`esc_attr`, `esc_html`, `esc_url`, `selected`, `home_url`, `is_wp_error`, `WP_Error`).
Currently used only by `ClassScheduleTest.php`; `CampusDirectoryTest.php` and
`CourseCatalogTest.php` still define their own inline versions.

---

## CampusDirectory — 76 tests

File: `tests/php/CampusDirectoryTest.php`

Loads: `classes/CampusDirectory.php` (which loads `classes/CampusDirectoryAPI.php`)

Stubs: `add_action`/`add_filter`, `register_rest_route`, `add_rewrite_rule`,
WordPress query/template functions, `ldap_*` family, `get_transient`/`set_transient`

### template_include hook (3 tests)
- Returns original template when `directoryprofilecruzid` query var is missing
- Returns `DirectoryProfileTemplate.php` on singular pages (WPM-114 regression)
- Returns `DirectoryProfileTemplate.php` on non-singular pages

### the_content hook / renderDirectoryProfile (6 tests)
- Returns original content when cruzid missing
- Returns original content in admin context
- Returns original content when not singular
- Returns original content when not the main query
- Returns original content when post ID mismatches queried object (loop guard)
- Appends profile output to original content; does not emit `<main>` wrapper inline

### LDAP query behaviour (6 tests)
- List views request a limited attribute list (no `jpegphoto`)
- List views apply the configured-feed size ceiling (1000)
- Profile views request all attributes (`*`)
- List and profile queries use separate transient keys
- Automated feed list queries also use limited attribute list
- LDAP time limit kept under 30 s edge proxy timeout

### LDAP filter security / hardening (WPM-103) (7 tests)
- Wildcard cruzids escaped in manual list filters (`*` → `\2a`)
- Wildcard add cruzids escaped in automated feed filters
- Empty cruzid list issues no LDAP search
- Blank entries in a cruzid list are skipped (no double-`(uid=)` clauses)
- Failed LDAP search returns empty result without fatal
- Empty results cached with short negative-cache expiration (60 s)
- Exclude-only automated feeds produce no filter (avoid full-directory query)
- Exclude retained when a feed filter exists to subtract from

### Profile-route LDAP injection (WPM-152) (10 tests)

The profile route takes its cruzid from the URL (`directoryprofilecruzid` via the
`/directory/<cruzid>/` rewrite), so it is the one LDAP input an anonymous visitor
controls directly. WPM-152 found **no escaping gap** — all three profile entry
points (`directory_profile_title`, `renderDirectoryProfile`, and
`DirectoryProfileTemplate.php`) call `getCampusDirData($cruzid, true)`, which takes
the same `buildUidFilter()` branch as the list view. These tests pin that down so a
future refactor cannot split the profile route onto an unescaped path.

- Wildcard `*` escaped to `\2a`
- Parentheses `(` `)` escaped to `\28` `\29`
- Backslash `\` escaped to `\5c`
- NUL byte escaped to `\00`
- Injection payload `*)(uid=*` cannot add an LDAP clause (parenthesis balance held)
- Injection payload fully neutralized to the exact expected filter
- Profile and list routes build an identical filter for identical input
- Profile route filter is byte-identical to `buildUidFilter()` output
- Empty profile-route cruzid issues no LDAP search
- Whitespace-only profile-route cruzid issues no LDAP search

Regression value: 6 of these 10 fail when `ldap_escape()` is removed from
`buildUidFilter()`. The other 4 guard filter-construction shape and empty input,
which that mutation does not affect.

### Affiliation narrowing (WPM-171) (20 tests)

`buildFilterString()` composes the LDAP filter from three affiliation groups —
faculty types, staff types, and graduate students. WPM-171 found **no defect**:
every branch already matches the normative behaviour in
`openspec/specs/campus-directory/audience-selection/spec.md`. These tests pin it
down. The branches were reached by WPM-113's staff-type test, which asserts only
that `ucscpersonpubaffiliation=Staff` appears and never inspects the narrowing
clause — which is how they read as covered while staying unasserted.

`processStaffFilterString()` — NOT-clause exclusion for partial selections:
- Regular Staff alone excludes the unselected specialized types with a NOT clause
- Regular Staff alone does not silently include postdoctoral scholars
- Two excluded staff types are OR-combined inside the NOT clause
- A single excluded staff type is negated without an OR wrapper
- A selected specialized staff type is not negated
- Specialized types without Regular Staff select positively, not by exclusion
- `Postdoctoral Scholar` maps to `ucscPersonIsPostDoc`, not `ucscpersonpubstafftype`
- Selecting all three staff types collapses to every staff affiliation

`processFacultyFilterString()` — specific-multi-type branch:
- Two specific faculty types are OR-combined under the Faculty affiliation
- A specific faculty selection lists exactly the selected types
- Unselected faculty types stay out of the filter
- A single specific faculty type is not OR-wrapped
- `All` takes precedence over individually selected faculty types

No affiliation selected:
- An automated feed with no affiliation builds no filter
- ...and does not fall back to the department alone
- ...and issues no LDAP search

Union of groups:
- Faculty, staff and graduate students OR-combine under the department scope
- Each selected group contributes one affiliation clause to the union
- Two selected groups OR-combine and exclude the unselected one
- A single selected group is ANDed to the department without an OR wrapper

Regression value: 15 of these 20 fail under at least one of seven targeted
mutations (removing the staff NOT wrapper, the faculty OR wrapper, the
`count > 0` guard, the union OR wrapper, the postdoc attribute mapping, the
`All` precedence check, and the all-three-staff collapse). The other 5 guard
filter shape and negative invariants — that unselected types stay absent and
that single selections are not OR-wrapped — which no single mutation flips.

Harness note: `campus_directory_api_fixture()` previously declared faculty types
`Senate` and `Emeritus`, which no UI control sets. WPM-171 replaced them with the
ten types `src/components/CampusDirectory/AutomatedFeeds.js` actually offers; the
mismatch was invisible while tests only exercised the `All` branch.

### getDirDropdowns (2 tests)
- Can run twice in one request without error
- Requests only the grouping attribute from LDAP (not `*`)

---

## ClassSchedule — 16 tests

File: `tests/php/ClassScheduleTest.php`

Loads: `classes/ClassSchedule.php`

Stubs: `harness.php` shared stubs + `rest_do_request`, `wp_enqueue_script`,
`wp_enqueue_style`, `get_query_var`, `add_rewrite_rule`, `get_option`,
`wp_redirect` (throws `Test_Redirect_Called`); stub `WP_REST_Request`,
`WP_REST_Response`

### Error and empty states (4 tests)
- Returns terms error message when the terms REST request returns `WP_Error`
- Returns no-terms message for an empty terms response
- Prompts for block settings when no department/subject selected; issues only 1 REST request
- Returns no-courses message when courses request fails

### REST requests and rendered template (12 tests)
- Requests default term (`/ucsc/v1/courses/2262`)
- Uppercases the `dept` query parameter
- Renders `id="classSchedule"` and `id="classScheduleTable"` mount nodes
- Selects the default term in the quarter dropdown
- Sorts courses numerically by catalog number
- Renders course detail links
- Renders directory profile links for instructors
- Enqueues `classschedule-js` script and `classschedule` stylesheet after successful render
- Does not render legacy WCSI mount node or host

### Default visible columns (5 tests)
- Emits `data-default-columns="seats,days"` with no editor config
- Seats header visible; Time and Class # headers hidden
- Seats toggle checked; Time toggle unchecked

### Editor-configured default columns (5 tests)
- `defaultColumns: ['class-num', 'time']` emits correct data attribute
- Class # and Time headers visible; Seats header hidden
- Class # toggle checked; Seats toggle unchecked

### Empty default columns (2 tests)
- `defaultColumns: []` emits empty data attribute
- Seats header hidden when no defaults set

### Subject queries (2 tests)
- Falls back to first term when no term marked default
- Uppercases the `subject` query parameter

### Status, cancellation, and seats rendering (5 tests)
- Maps Open / Closed / Closed with Wait List enrollment statuses to row classes
- Labels wait list icon for screen readers
- Flags cancelled courses on title link
- Shows `Cancelled` in days column
- Clamps open seats at zero when over-enrolled

### Instructor rendering (4 tests)
- Does not link `Staff` instructors even when cruzid present
- Joins multiple instructors with commas
- Renders instructors without cruzid as plain text
- Skips instructors with empty name

### Course detail routing (3 tests)
- Registers `course_term`, `course_id`, and `legacy_redirect` query vars
- Uses course detail template when both term and id set
- Keeps original template without a course id

### Rewrite rules (6 tests)
- Registers two rewrite rules at `top` priority
- Canonical rule matches `/course/<term>/<id>/` and captures both
- Canonical rule ignores legacy hyphenated URLs
- Legacy rule matches prefixed hyphenated URLs
- Legacy rule ignores canonical URLs
- Legacy rule sets `legacy_redirect` query flag

### Legacy redirect (3 tests)
- Redirects legacy course URLs with 301 to canonical form
- Does not redirect without `legacy_redirect` flag
- Does not redirect when course id is missing

### Course detail title (4 tests)
- Requests `/ucsc/v1/course/<term>/<id>` REST route
- Builds document title from primary section (`CSE 101 - Introduction to Algorithms`)
- Keeps existing title when course request fails
- Keeps existing title without a primary section in the response

### Department endpoint (1 test)
- Returns configured department from `classscheduledept()` REST handler

---

## ClassScheduleTemplate — 49 tests

File: `tests/php/ClassScheduleTemplateTest.php`

Renders: `templates/ClassScheduleTemplate.php` directly via `include` (not through
`ClassSchedule::theHTML()`), with the exact locals the controller exposes
(`$courses`, `$current_term`, `$terms_data`, `$attributes`).

Stubs: `harness.php` shared stubs + a local `esc_url` (strips `javascript:` like
production) and `checked()` (not provided by the harness).

WPM-119: the template file has the deepest structural coverage in the plugin but
had zero test asserting the escaping of its interpolated values — its 189 lines
were credited to a comment match in a Jest test. This suite requires and renders
the template so its lines get real executing coverage (75/75 lines, 100%) and pins
the escaping and table structure the front-end depends on.

### Table structure (10 tests)
- Renders the block wrapper, `role="table"` mount node, and header/body rowgroups
- Renders one `course-row` per course and the Course ID / Title column headers
- Renders the filter modal dialog and the displayed-class count from the array

### Term dropdown (3 tests)
- Renders an option per term and the description text
- Marks the current term selected

### Default columns (9 tests)
- Emits `data-default-columns="seats,days"` and shows/hides the matching columns
- Checks default-column toggles; leaves hidden-column toggles unchecked
- Honors editor-configured `defaultColumns`
- Falls back to seats+days when `defaultColumns` is not an array

### Cell content and status (11 tests)
- Renders course id, title link, seats, and instructor directory links
- Maps Open / Closed / Closed with Wait List to row status and screen-reader label
- Flags cancelled courses; shows `Cancelled` in the days column
- Clamps open seats at zero when over-enrolled

### Instructor edge cases (4 tests)
- Joins multiple instructors with commas; renders no-cruzid instructors as text
- Does not link `Staff`; skips empty-name instructors

### Empty course list (2 tests)
- Renders the table shell with no rows; reports a zero class count

### Escaping every interpolated value (10 tests)
- No raw `<script>` / `<img onerror>` from any course field (title, days, time,
  location, class #, instructor name, subject/catalog into the course id)
- Escapes attribute-breaking values and the instructor cruzid inside the `href`
- Escapes the term option value/description and `current_term` in the course href
- Never emits an attribute-breaking `data-default-columns` value

Regression value: the escaping assertions fail if any `esc_html`/`esc_attr`/`esc_url`
guard is removed — verified by dropping the title escape (the raw-script-tag
assertion then fails).

---

## CourseCatalog — ~20 tests

File: `tests/php/CourseCatalogTest.php`

Loads: `classes/CourseCatalog.php`

Stubs: `add_action`, enqueue functions, `plugins_url`, `plugin_dir_path`,
`is_user_logged_in`, `current_user_can`, `wp_unslash`, `get_transient`/`set_transient`,
`wp_remote_post`, `wp_remote_retrieve_response_code`/`body`, `is_wp_error`, `WP_Error`,
stub `Test_WPDB` for cache-clear queries

### PeopleSoft target selection (3 tests)
- Defaults to production target (`prod`, host `my.prd.ais.aws.ucsc.edu`)
- `UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET=qa` maps to `csqa` target
- Unknown env alias falls back to production

### Cache controls (4 tests)
- Cache bypass defaults off
- `UCSC_COURSE_CATALOG_BYPASS_CACHE=true` enables bypass
- Successful response cached under target-aware transient key
- Cache bypass does not store response but still calls remote feed

### Feed error handling (6 tests)
- Transport `WP_Error` returned to caller; not cached
- Non-2xx response returns `course_catalog_feed_error`; reports upstream status; not cached
- Invalid XML in 200 response returns `course_catalog_feed_xml_error`; not cached

### Subject queries (2 tests)
- Cached under subject-aware key
- Lowercased subject sent in request body

### Rendered HTML (4 tests)
- Feed failure renders unavailable fallback; block wrapper retained
- Course rows render title
- Graduate level maps to sort value 3
- Unknown level maps to sort value 0 (no bleed from previous row)

### Cache clearing (4 tests)
- `clearCachedCourses('qa')` returns deleted row count; uses `csqa` transient prefix
- `clearCachedCourses()` targets all `course-catalog-` transients

---

## SiteSettings (cddepartmentcode) — 11 tests

File: `tests/php/SiteSettingsTest.php`

Loads: `classes/SiteSettings.php`

Stubs: `wp_remote_get`/`wp_remote_retrieve_body`/`wp_remote_retrieve_response_code`,
`get_transient`/`set_transient`, `is_wp_error`, `WP_Error`, `WP_REST_Response`,
faked `ABSPATH` + `WP_Filesystem_Base`/`WP_Filesystem_Direct` stub files (needed
because `SiteSettings.php` loads `CampusDirectoryAPI.php`, which requires them)

WPM-134: `cddepartmentcode()` previously fetched the department dropdown source
via `file_get_contents()` with no error handling, so a failed fetch or missing
DOM element caused an unguarded null-method-call fatal, and non-2xx responses
were parsed as if they were valid HTML. Converted the fetch to `wp_remote_get()`
(matching the `Course_Schedule_API` convention) with explicit guards. Confirmed
regression coverage: 7 of these 11 tests fail against the pre-fix code.

### Remote fetch failure paths (5 tests)
- `WP_Error` from the remote fetch returns a controlled `WP_Error`, not a fatal
- `WP_Error` result is not cached
- Non-2xx response returns a controlled `WP_Error`
- Non-2xx error does not leak the raw upstream response body
- Non-2xx response is not cached

### Parse failure (2 tests)
- Unparseable HTML (missing `#ucscpersonpubdepartmentnumber`) does not fatal
- Unparseable HTML returns only the placeholder option (empty list)

### Happy path (2 tests)
- Returns a `WP_REST_Response`
- Parses the department list into the expected `label`/`value` shape

### Caching (2 tests)
- Successful fetch is cached under `ucsc_cddepartmentcode` for `WEEK_IN_SECONDS`
- Cache hit makes no remote call

---

## Coverage gaps

### Entire class not tested
- `src/API/Course_Schedule_API.php` — zero PHP tests; the public PeopleSoft REST proxy
  with unbounded `subject`/`dept` params (high-priority audit finding); needs tests for:
  - Invalid/oversized `subject` and `dept` reject with 400
  - Successful response cached; cache hit skips remote call
  - Non-2xx upstream returns a controlled error
  - `validate_remote_response` helper paths

### Security / escaping not tested
- `templates/CampusDirectoryTemplate.php` — LDAP data echoed into HTML without escaping
  (high-priority audit finding); no rendering tests exist
- `classes/CampusDirectoryShortcode.php` — LDAP data in link text, `href`, `mailto:`,
  and image/style output without escaping (high-priority audit finding)

### Other untested paths
- `templates/DirectoryProfileTemplate.php` — no rendering tests
- `templates/CourseDetailTemplate.php` — single-object primary instructor shape
  (medium audit finding); missing `start_date`/`end_date` keys (low audit finding)
- `classes/CampusDirectoryAPI.php` — LDAP injection via `cruzidList`, `excludeCruzids`,
  `addCruzids` in the profile-route query var path not tested for metacharacter escaping

### Harness migration pending
- `tests/php/helpers/harness.php` note says to migrate `CampusDirectoryTest.php`
  and `CourseCatalogTest.php` to use the shared harness once their branches merge
