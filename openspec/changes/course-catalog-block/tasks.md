## 1. Confirm block-registration and data-fetch specs

- [ ] 1.1 Confirm attribute schema and `save()` returning `null` against `src/blocks/CourseCatalog.js` and `classes/CourseCatalog.php::renderFrontend()`.
- [ ] 1.2 Confirm the dept/subject XML query construction (`<acad_org>` vs `<subject>`, lowercased) against `getCachedCourses()`.
- [ ] 1.3 Confirm PeopleSoft target resolution (`PSFT_CSPRD`/`PSFT_CSQA`, `qa`/`test` aliasing, default-to-prod) against `getPeopleSoftTarget()` / `normalizePeopleSoftTarget()`.
- [ ] 1.4 Confirm the cache key includes target, lowercased query value, and mode, and that prod/QA entries never collide — verify by asserting two distinct transient keys for the same department against different targets.
- [ ] 1.5 Confirm a malformed-XML response is never cached and surfaces as an error — verify the `simplexml_load_string()` failure branch in `getCachedCourses()`.
- [ ] 1.6 Confirm the WP-CLI `course-catalog-cache clear` command deletes only the targeted transient rows — verify `CourseCatalog::clearCachedCourses()` against fixture transient rows for two different targets.

## 2. Confirm frontend-render spec

- [ ] 2.1 Confirm an upstream error suppresses the entire table (no partial render) — verify the `is_wp_error($courses)` branch in `theHTML()`.
- [ ] 2.2 Confirm each course renders as a header row plus one hidden description row, and that the hidden numeric sort keys (catalog number, level rank) are present alongside their visible text — verify against `theHTML()`'s row-building loop.
- [ ] 2.3 Confirm clicking a header row toggles only that row's own description — verify the click handler in `tablesorter.js`.
- [ ] 2.4 Confirm Expand all / Collapse all affect every row independent of individual toggle state — verify the `expandAlls` / `collapseAlls` handlers.
- [ ] 2.5 Confirm column sort keeps each header/description pair together and prefers the hidden numeric key when present — verify `sortTableByColumn()`'s row-pairing and `special` branch.
- [ ] 2.6 Confirm search matches text in either the header or the paired description row — verify `tableSearch()`'s two-pass check.

## 3. Record deviations rather than fixing them

- [ ] 3.1 Record `CourseCatalog::restPermissionsCheck()` as dead code (unreferenced by any route in this class) — capture file:line; do not remove it in this change.
- [ ] 3.2 Record the disabled request-level PeopleSoft target override (`isRequestOverrideAllowed()`, `getRequestTargetOverride()`) as dormant but fully implemented and unreachable from its commented-out call sites — capture the security consideration from `design.md` if it is ever re-enabled.
- [ ] 3.3 Record the corrected proposal facts (no separate template file; XML/SOAP-style fetch, not the `ucsc/v1` REST pattern; week-long cache, not "15 min") so future readers of `proposal.md` are not misled by the original draft's assumptions.
- [ ] 3.4 Confirm the PeopleSoft environment-targeting asymmetry with Class Schedule (recorded in that block's `tasks.md` 3.2) is cross-referenced here rather than duplicated as a separate open item.

## 4. Close out

- [ ] 4.1 Run the existing PHP (`CourseCatalogTest.php`) and Jest (`src/blocks/__tests__/CourseCatalog.test.js`) suites via the `validate` skill to confirm no drift while documenting.
- [ ] 4.2 Update the specs for every discrepancy found in groups 1–2 so the baseline describes shipped behavior, not intended behavior — verify with `openspec validate course-catalog-block --strict`.
- [ ] 4.3 Fold `specs/course-catalog/**` into `openspec/specs/course-catalog/**` as the working baseline for grounding remaining test development, per the direction that this reverse-engineering pass does not need to wait on full task verification first.
