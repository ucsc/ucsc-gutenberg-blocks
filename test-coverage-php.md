# PHP Test Coverage

Date: 2026-08-26

Scope: `tests/php/CampusDirectoryTest.php`, `tests/php/ClassScheduleTest.php`,
`tests/php/CourseCatalogTest.php`, `tests/php/helpers/harness.php`

Runner: plain `php` CLI — no PHPUnit, no framework. Each file is a standalone
script that defines its own WordPress stubs and calls `check($label, $condition)`.

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

## CampusDirectory — 10 tests

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
- `classes/SiteSettings.php` — department dropdown endpoint failure path when remote
  fetch fails or DOM parse fails (medium audit finding)
- `templates/DirectoryProfileTemplate.php` — no rendering tests
- `templates/CourseDetailTemplate.php` — single-object primary instructor shape
  (medium audit finding); missing `start_date`/`end_date` keys (low audit finding)
- `classes/CampusDirectoryAPI.php` — LDAP injection via `cruzidList`, `excludeCruzids`,
  `addCruzids` in the profile-route query var path not tested for metacharacter escaping

### Harness migration pending
- `tests/php/helpers/harness.php` note says to migrate `CampusDirectoryTest.php`
  and `CourseCatalogTest.php` to use the shared harness once their branches merge
