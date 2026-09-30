## Verification record — class-schedule-block

Verified 2026-09-30 against `origin/main` at `defbf56`. Evidence is code
reading (file:line) unless marked **[live]** (run in the wp-dev.ucsc Docker
stack via `wp eval`) or **[test]** (asserted by an existing suite).

### Drift since the baseline commit (b6a9aa0)

- **WPM-180 multi-instance isolation** (`a213d2c`) — `ClassSchedule::theHTML()`
  numbers each render (`classes/ClassSchedule.php:219-221`); the template
  suffixes every element ID with `-N` after the first block and adds
  `class="class-schedule" data-cs-instance="N"`
  (`templates/ClassScheduleTemplate.php:14-17,40`); `classschedule.js` scopes
  every lookup to the block root (`rootFor()`), keeps per-block sort/snapshot
  state in a `WeakMap`, and keys persisted columns `cs_columns` /
  `cs_columns_N`. Folded into `frontend-render` as a new requirement.
- **WPM-154 dropdown feed hardening** (`f33118a`) — see 3.1 below.
- `window.classScheduleShowCopyToast` and `window.classScheduleChangeTerm` are
  now exported for tests; no behavior change.

### Group 1 — block-registration and rest-api

- 1.1 Confirmed. Four attributes, `save()` → `null`
  (`src/blocks/ClassSchedule.js:16-29,131-133`); `register_block_type` with
  `render_callback` (`classes/ClassSchedule.php:113-119`).
- 1.2 Confirmed **[test]** — `src/blocks/__tests__/ClassSchedule.test.js:249-272`
  runs `deprecated[0].migrate()` and asserts `useNewServer` is gone.
- 1.3 Confirmed. `columnOptions` (`ClassSchedule.js:39-47`) and
  `$toggle_columns` (`ClassScheduleTemplate.php:22`) list the same seven keys;
  fallback `['seats','days']` in both. Spec extended: non-array and unknown
  keys are filtered (`ClassScheduleTemplate.php:23-27`), and `[]` hides all.
- 1.4 Confirmed with a correction. Routes and 900s cache as specced
  (`src/API/Course_Schedule_API.php:37-100,25`). Non-numeric values never reach
  `validate_callback`: the `[0-9]+` route regex fails first (404
  `rest_no_route`). Spec scenario reworded.
- 1.5 Confirmed. Key is `md5($term . http_build_query(...))`
  (`Course_Schedule_API.php:164-167`); `dept=CSE` and `subject=CMPM` yield
  different query strings, so different keys.
- 1.6 Confirmed at the route level **[live]**: a simulated transport failure
  returns `api_error` (status 500) and `get_transient('ucsc_ps_terms')` stays
  `false`. JSON-decode failure returns `json_error` before `set_transient`
  in all three handlers (`:131-137,193-199,239-245`). But see D1 for how
  internal callers see the error.

### Group 2 — frontend-render and course-detail-pages

- 2.1 Confirmed (`ClassSchedule.php:168-192`). The param is page-wide, so all
  blocks on a page share one term — noted in the spec.
- 2.2 **Deviation D1.** Unconfigured vs. empty messaging is distinct and
  correct (`:197-205`). The dedicated upstream-error message is unreachable:
  **[live]** with PeopleSoft failing, `theHTML()` returned
  `<p>No terms available.</p>` (terms failure) and
  `<p>No courses found for the selected criteria.</p>` (courses failure).
- 2.3 Confirmed — `(int)` catalog-number comparator (`ClassSchedule.php:211-213`).
- 2.4 Confirmed — `rowMatchesSearch()` checks title, location, instructor,
  class #, course ID, and `data-status`; `applyStatusFilters()` re-applies the
  search (`classschedule.js` Search section). Hidden columns are still
  searched (their text stays in the DOM).
- 2.5 Confirmed with a precision fix — comparison is `parseFloat` on the
  leading text, so Time sorts by leading hour, ignoring AM/PM (**D2**). Spec
  now states the actual rule.
- 2.6 Confirmed — snapshot on open, restore on Cancel/backdrop/Escape, Apply
  re-snapshots and saves; Reset uses `getDefaultColumns(root)` reading
  `data-default-columns`, re-checks statuses, and clears search (WPM-112).
  Status choices are not persisted (only `saveColumnState`).
- 2.7 Confirmed — header index 0 skipped; hidden headers skipped;
  `display:none` rows skipped (`classScheduleDownloadCSV()`).
- 2.8 Confirmed — copies `window.location.href` verbatim, toast removed after
  3s; fallback path via `execCommand('copy')` **[test]** (WPM-160).
- 2.9 Confirmed — `updateClassCount(root)` is called from search and
  `applyStatusFilters()`, which Apply and Reset both call.
- 2.10 Confirmed **[live]** — `WP::parse_request()` resolves
  `/courses/course/2262-50222/history-of-the-present` and
  `/course/2262-50222/class` to the legacy rule with `legacy_redirect=1`, and
  `/course/2262/50222/` to the canonical rule; `/course/abc/50222/` falls
  through to a page lookup. The 301 is `wp_redirect(..., 301)` in
  `maybe_redirect_legacy_course()` (`ClassSchedule.php:47-56`); the HTTP
  status itself was not observed (host TLS from the sandbox failed).
- 2.11 **Deviation D1** — `is_wp_error($response)` at
  `templates/CourseDetailTemplate.php:25` is never true; an upstream failure
  falls through to "Course information unavailable." (`:44-47`).
  "Course not found." (`:33-37`) is reachable only for an empty 2xx body.
- 2.12 Confirmed — `max(0, capacity - enrl_total)` in the detail template
  (`:75`) and in the list template (`ClassScheduleTemplate.php:143`).
- 2.13 Confirmed — `; `-joined day/times, TBA passthrough
  (`CourseDetailTemplate.php:106-130`), `array_unique` locations, instructors
  deduped by name; "Staff" or CruzID-less names not linked (`:340-344`).
  Minor: a meeting with no location contributes an empty entry to the
  `, `-joined Room list.
- 2.14 Confirmed (`CourseDetailTemplate.php:155`).
- 2.15 Confirmed (`:85-89`) — referer must pass `wp_validate_redirect()` and
  start with `home_url()`.

### Group 3 — deviations

- 3.1 **Resolved by WPM-154** (`f33118a`). `SiteSettings::fetchClassDepts()`
  now uses `wp_remote_get()` with a 30s timeout, returns a `WP_Error` on
  transport failure or non-2xx, and returns `[]` for an unparseable body;
  `departmentcode()` / `subjectcode()` return that error without caching it
  (`classes/SiteSettings.php:200-300`). Remaining gap: no stale-if-error
  fallback, and an unparseable 2xx body caches a list containing only `---`
  for a week.
- 3.2 Recorded (design note, unchanged): `Course_Schedule_API::PS_BASE_URL`
  hardcodes `PSFT_CSPRD` (`:15`); Course Catalog has a prod/QA switch.
- 3.3 No follow-up change needed for 3.1 (fixed). New deviations D1/D2 are
  proposed as backlog tickets instead (see below).

### New deviations found in this pass

- **D1 — internal REST errors are misreported as empty data.**
  `rest_do_request()` returns a `WP_REST_Response`, never a `WP_Error`
  (WordPress `WP_REST_Server::dispatch()` converts errors via
  `error_to_response()`). Affects `ClassSchedule::theHTML()` (`:158`),
  `getCachedCourses()` (`:145`), `CourseDetailTemplate.php:25`, and
  `course_detail_title()` (`:74`, harmless there). Test gap: the stubs in
  `tests/php/ClassScheduleTest.php:21-25,209-224` and
  `tests/php/CourseDetailTemplateTest.php:190-193,256` return a raw
  `WP_Error`, so the suites pass on branches real WordPress never reaches.
  Fix direction: check `$response->is_error()` (or status ≥ 400) and make
  the test stubs return `rest_ensure_response(new WP_Error(...))`-shaped
  responses.
- **D2 — Time column sorts by leading hour.** `sortClassSchedule()` uses
  `parseFloat`, so "1:30 PM" sorts before "10:00 AM". Low severity.
