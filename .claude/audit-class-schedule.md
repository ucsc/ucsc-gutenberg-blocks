# Class Schedule Block Audit

Date: 2026-07-17

Scope: `classes/ClassSchedule.php`, `src/API/Course_Schedule_API.php`, `templates/ClassScheduleTemplate.php`, `templates/CourseDetailTemplate.php`, `src/blocks/ClassSchedule.js`, `src/components/ClassSchedule/classschedule.js`, and related tests.

Tools used:

- `node /Users/henryh/_code/_tools/ucsc-node-review/bin/ucsc-node-review.mjs . --json --out .claude/ucsc-node-review-gutenberg.json`
- `docker run --rm -v /Users/henryh/_code/_tools/ucsc-php-review:/app -v /Users/henryh/_code/_campuspress/wp-dev.ucsc/public/wp-content/plugins/ucsc-gutenberg-blocks:/target -w /app composer:2 bin/ucsc-php-review /target --json --out /target/.claude/ucsc-php-review-gutenberg.json`
- `docker run --rm -v /Users/henryh/_code/_campuspress/wp-dev.ucsc/public/wp-content/plugins/ucsc-gutenberg-blocks:/plugin -w /plugin php:8.1-cli php tests/php/ClassScheduleTest.php`
- `npm test -- --runInBand`

Tool notes:

- `ucsc-node-review`: ESLint/security output included default-config issues and a low-risk `innerHTML` warning in `classschedule.js:30`; the value assigned there is internally generated count text, not user input.
- `ucsc-php-review`: PHPStan output mostly reflected missing WordPress stubs and default-rule mismatches. Manual verification found the concrete issues below.
- Class Schedule PHP tests passed 16/16. Full JS unit suite passed 44/44.

## Architecture And Attack Surface

Class Schedule is a dynamic block registered at `classes/ClassSchedule.php:113-118`. The frontend calls internal public REST routes implemented by `src/API/Course_Schedule_API.php`, which proxy PeopleSoft class schedule APIs and cache successful results for 15 minutes. Public course detail pages are exposed via rewrite rules in `classes/ClassSchedule.php:23-38` and rendered through `templates/CourseDetailTemplate.php`.

Generated/dependency directories excluded from manual review: `node_modules/`, `build/`, and raw runner JSON except for summary signals.

## Findings

### Medium: public API accepts unbounded `subject` and `dept` values

File: `src/API/Course_Schedule_API.php:151`

Evidence and path: `get_courses()` accepts public `subject` and `dept` query params, applies `sanitize_text_field()`, forwards arbitrary values to PeopleSoft at `src/API/Course_Schedule_API.php:176-183`, and creates distinct transient keys at `src/API/Course_Schedule_API.php:166-172`.

Impact: unauthenticated callers can force remote PeopleSoft requests and transient growth with junk values, especially on cold cache.

Remediation: validate `subject` and `dept` against expected code format or known code lists, impose a short max length, and reject requests with neither filter or unsupported combinations.

Confidence: high.

Suggested validation: REST request with a 1,000-character `dept` returns `400` and does not call `wp_remote_get()`.

### Medium: multiple Class Schedule blocks on one page conflict

Files: `templates/ClassScheduleTemplate.php:22`, `templates/ClassScheduleTemplate.php:31`, `templates/ClassScheduleTemplate.php:48`, `templates/ClassScheduleTemplate.php:96`, `src/components/ClassSchedule/classschedule.js:24`

Evidence and path: the template emits fixed IDs such as `quarterDropdown`, `courseSearch`, `filterModal`, and `classScheduleTable`. The script uses global `document.getElementById()` and `querySelectorAll()` selectors.

Impact: search, filters, CSV export, modal behavior, and term switching can target the wrong block or combine state across blocks when two schedules are embedded on one page.

Remediation: generate a per-instance wrapper ID in PHP and scope all selectors/events to that wrapper; avoid fixed global IDs for controls repeated per block.

Confidence: high.

Suggested validation: render two schedules with different rows, type into the second search box, and assert only the second table changes.

### Medium: course detail template can fail on single-object primary instructor shapes

File: `templates/CourseDetailTemplate.php:119`

Evidence and path: primary meeting instructors are iterated directly and `$inst['name']` is read at `templates/CourseDetailTemplate.php:121`. The secondary-section path normalizes single instructor objects at `templates/CourseDetailTemplate.php:195-198`, showing that this shape is expected elsewhere.

Impact: if PeopleSoft returns a single instructor object for a primary meeting, PHP 8 can warn or error rather than rendering the detail page.

Remediation: normalize primary meeting `instructors` the same way secondary sections do before iterating.

Confidence: medium.

Suggested validation: course detail fixture with `instructors => ['name' => 'Ada', 'cruzid' => 'ada']` renders one instructor link without warnings.

### Low: course detail assumes date keys exist

File: `templates/CourseDetailTemplate.php:134`

Evidence and path: `$primary['start_date']` and `$primary['end_date']` are accessed directly.

Impact: missing API fields produce PHP notices; with display errors enabled this can leak paths and degrade rendering.

Remediation: use `$primary['start_date'] ?? ''` and `$primary['end_date'] ?? ''`.

Confidence: high.

Suggested validation: detail fixture without `start_date` or `end_date` renders without warnings and omits meeting dates.

### Low: course detail may perform duplicate remote work on failures

Files: `classes/ClassSchedule.php:71`, `templates/CourseDetailTemplate.php:22`, `src/API/Course_Schedule_API.php:247`

Evidence and path: `course_detail_title()` calls `/ucsc/v1/course/...` for the document title, and `CourseDetailTemplate.php` calls the same endpoint again. `Course_Schedule_API` caches only successful responses.

Impact: on PeopleSoft failure, one page view can wait on two 30-second upstream requests.

Remediation: share the REST response between title/template rendering or briefly cache upstream errors.

Confidence: medium.

Suggested validation: mocked failing course detail endpoint records only one upstream fetch per page render.

## Remediation Order

1. Validate and length-limit public REST query params before remote calls.
2. Scope frontend IDs/selectors for multiple-block support.
3. Normalize primary instructor data shape in the detail template.
4. Guard optional date fields and reduce duplicate failure-path remote calls.

## Test Gaps

- No REST tests for invalid `subject`/`dept` request rejection.
- No frontend test for two Class Schedule blocks on the same page.
- No course detail tests for single-object instructor shapes, missing dates, or upstream failure duplication.
