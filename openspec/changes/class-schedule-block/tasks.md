## 1. Confirm block-registration and rest-api specs

- [ ] 1.1 Confirm attribute schema and `save()` returning `null` against `src/blocks/ClassSchedule.js` and `classes/ClassSchedule.php::adminAssets()`.
- [ ] 1.2 Confirm the `useNewServer` deprecation migration strips the key without validation error — verify by loading a fixture post attribute set containing `useNewServer` through the block's `deprecated` array.
- [ ] 1.3 Confirm the seven optional columns and the `['seats','days']` fallback default match `columnOptions` in `ClassSchedule.js` and `$toggle_columns` / `$default_columns` in `ClassScheduleTemplate.php`.
- [ ] 1.4 Confirm the three `ucsc/v1` REST routes, their numeric-param validation, and 15-minute transient cache keys against `Course_Schedule_API::register_routes()`.
- [ ] 1.5 Confirm cache-key isolation between different dept/subject filters for the same term — verify by asserting distinct `md5()` keys for two different query strings.
- [ ] 1.6 Confirm upstream failure and invalid-JSON paths return `WP_Error` and are never cached — verify via `validate_remote_response()` and the `json_last_error()` branch in each of `get_terms()`, `get_courses()`, `get_course_details()`.

## 2. Confirm frontend-render and course-detail-pages specs

- [ ] 2.1 Confirm term resolution order (query param → API default → first term) against `ClassSchedule::theHTML()`.
- [ ] 2.2 Confirm unconfigured vs. empty-result messaging is distinct — verify both branches in `theHTML()` render the expected text.
- [ ] 2.3 Confirm initial sort is by catalog number ascending — verify the `usort()` comparator in `theHTML()`.
- [ ] 2.4 Confirm search matches title, location, instructor, class number, course ID, and status, and combines with active status filters — verify `rowMatchesSearch()` and `applyStatusFilters()` in `classschedule.js` (Jest: `classschedule.test.js`).
- [ ] 2.5 Confirm column sort toggles direction on repeated header clicks and sorts numeric Seats values numerically — verify `sortClassSchedule()`.
- [ ] 2.6 Confirm the Filter modal's Cancel/backdrop-close restores pre-open state, Apply persists to `sessionStorage`, and Reset restores the block's configured `defaultColumns` (not a hardcoded default) — verify `openFilterModal()`, `closeFilterModal()`, `applyFilters()`, `resetFilters()`, `getDefaultColumns()`.
- [ ] 2.7 Confirm CSV export excludes the Status column, includes only visible columns and visible (post-filter) rows — verify `classScheduleDownloadCSV()`.
- [ ] 2.8 Confirm Copy URL copies the unmodified current URL with no filter state encoded — verify `classScheduleCopyUrl()` against the current `window.location.href`.
- [ ] 2.9 Confirm the live class-count region updates after search, filter, and reset — verify `updateClassCount()` call sites.
- [ ] 2.10 Confirm the canonical rewrite (`/course/{term}/{class_nbr}/`) and the legacy redirect rule both resolve correctly, and that the legacy match issues a 301 — verify `add_course_detail_rewrite()` and `maybe_redirect_legacy_course()` using the `run` skill against the Docker stack.
- [ ] 2.11 Confirm course detail error/not-found/unavailable messaging is distinct per failure mode — verify the three early-return branches in `CourseDetailTemplate.php`.
- [ ] 2.12 Confirm available seats never renders negative when enrolled exceeds capacity — verify the `max(0, ...)` computation.
- [ ] 2.13 Confirm meeting aggregation: multi-pattern day/time joining, TBA passthrough, deduplicated locations/instructors, and that "Staff" or CruzID-less instructors are not linked — verify against fixture meeting data through `CourseDetailTemplate.php`.
- [ ] 2.14 Confirm a secondary section matching the primary section's `class_section` is excluded from the associated-sections list.
- [ ] 2.15 Confirm the breadcrumb falls back to the site home URL for an off-site or invalid referer — verify `wp_validate_redirect()` usage in `CourseDetailTemplate.php`.

## 3. Record deviations rather than fixing them

- [ ] 3.1 Record the unbounded-timeout risk in the shared `SiteSettings::departmentcode()` / `subjectcode()` dropdown source (used by both this block and Course Catalog) — capture file:line and note it is the same class of finding as `campus-directory-block`'s dropdown-endpoint risk, against a different endpoint.
- [ ] 3.2 Record the PeopleSoft environment-targeting asymmetry between this block (hardcoded `PSFT_CSPRD`, no override) and Course Catalog (prod/QA target switch) — capture as a design note, not a defect to fix here.
- [ ] 3.3 Open a follow-up change for 3.1 if not already covered by a Course Catalog or shared-infrastructure change; do not implement it here.

## 4. Close out

- [ ] 4.1 Run the existing PHP (`ClassScheduleTest.php`, `CourseScheduleAPITest.php`) and Jest (`src/blocks/__tests__/ClassSchedule.test.js`, `src/components/ClassSchedule/__tests__/classschedule.test.js`) suites via the `validate` skill to confirm no drift while documenting.
- [ ] 4.2 Update the specs for every discrepancy found in groups 1–2 so the baseline describes shipped behavior, not intended behavior — verify with `openspec validate class-schedule-block --strict`.
- [ ] 4.3 Fold `specs/class-schedule/**` into `openspec/specs/class-schedule/**` as the working baseline for grounding remaining test development, per the direction that this reverse-engineering pass does not need to wait on full task verification first.
