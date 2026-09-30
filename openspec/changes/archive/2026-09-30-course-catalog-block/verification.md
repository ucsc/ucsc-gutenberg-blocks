## Verification record — course-catalog-block

Verified 2026-09-30 against `origin/main` at `defbf56`. Evidence is code
reading (file:line) unless marked **[live]** (run in the wp-dev.ucsc Docker
stack via `wp eval` / WP-CLI with `pre_http_request` stubbing PeopleSoft).

### Drift since the baseline commit (b6a9aa0)

No changes to `classes/CourseCatalog.php`, `src/blocks/CourseCatalog.js`, or
`src/components/CourseCatalog/`. The shared dropdown feed changed (WPM-154) —
see 3.x below. Spec edits in this pass are corrections, not drift.

### Group 1 — block-registration and data-fetch

- 1.1 Confirmed — three string attributes, `save()` → `null`
  (`src/blocks/CourseCatalog.js:13-23,77-79`); registered in
  `renderFrontend()` (`classes/CourseCatalog.php:51-56`). The editor writes
  `subjectOrDept: "dept"` on first render (`CourseCatalog.js:29-33`); the PHP
  render has no default of its own (see D2).
- 1.2 Confirmed (`CourseCatalog.php:191-200`). Anything other than `"dept"`
  is subject mode; the value is not XML-escaped.
- 1.3 Confirmed (`:58-69,124-144`). Also accepts the canonical keys `prod` /
  `csqa`; an empty env var is skipped (`elseif (getenv(...))`).
- 1.4 Confirmed (`:201`) — key is `course-catalog-<target>-<value>-<mode>`.
- 1.5 Confirmed (`:243-261`) — `set_transient` only after a truthy parse. But
  see D1: "truthy" also rejects empty well-formed XML.
- 1.6 Confirmed **[live]** — with `course-catalog-prod-cse-dept` and
  `course-catalog-csqa-cse-dept` set, `wp ucsc course-catalog-cache clear
  --target=qa` reported 2 rows (value + timeout) and left both prod rows;
  a bare `clear` then removed the rest. Command wiring: `index.php:77-86`.
- Spec gap closed: `UCSC_COURSE_CATALOG_BYPASS_CACHE` (`:146-160`) was not in
  the spec; added.

### Group 2 — frontend-render

- 2.1 Confirmed (`CourseCatalog.php:272-280`) — only the message is rendered.
- 2.2 Confirmed with a correction: the catalog number is **visible**, wrapped
  in `span.intsort`; only the level rank (`span.secret`) is hidden
  (`:315`, `tablesorter.css:83`). Spec reworded.
- 2.3 Confirmed (`tablesorter.js:88-96`) — toggles `nextElementSibling`'s
  `active` class. Mouse-only: rows are `<tr class="pointer">` with no
  tabindex or key handler (D4).
- 2.4 Confirmed (`:137-155`) — add/remove `active` on every `.hide` row.
- 2.5 Confirmed (`:9-78`) — rows paired by index (`i`, `i+1`); `special`
  when both cells hold a `span`; `parseInt`, then non-digit text on ties.
  Plain-text columns compare strings, so Units sorts lexically (D5).
- 2.6 Confirmed (`:98-135`) — header row first, description row second.
  Matching is against `innerHTML`, so tag names and the hidden level rank
  match (D3).

### Group 3 — deviations

- 3.1 Recorded: `CourseCatalog::restPermissionsCheck()`
  (`classes/CourseCatalog.php:22-29`) has no caller in this class; the only
  `restPermissionsCheck` references are `ContentSharer.php:10,15,20`, which
  call ContentSharer's own copy.
- 3.2 Recorded: `isRequestOverrideAllowed()` (`:92-111`) and
  `getRequestTargetOverride()` (`:113-122`) are only called from the
  commented-out blocks at `:133-136` and `:149-157`. Security note from
  `design.md` stands if re-enabled.
- 3.3 Already done — `proposal.md` states the string-built markup, XML POST,
  and week-long cache.
- 3.4 Confirmed — cross-referenced at `design.md:82`, not duplicated.
- Dropdown-feed risk (design Risks, fourth item): resolved by WPM-154 — see
  `class-schedule-block/verification.md` 3.1.

### New deviations found in this pass

- **D1 — empty catalog is reported as an outage and never cached.**
  `if (!$xmlBody)` (`:245`) is false for an empty `SimpleXMLElement`.
  **[live]** a 200 `<catalog/>` response returned
  `course_catalog_feed_xml_error`, rendered "temporarily unavailable", and
  wrote no transient — so a department with no catalog courses re-queries
  PeopleSoft on every page view. (Exact PeopleSoft empty-result shape
  not confirmed.) Fix direction: `$xmlBody === false`.
- **D2 — no unconfigured-block guard.** **[live]** `theHTML([])` raised
  "Undefined array key" warnings (`:191,198`) and POSTed
  `<subject></subject>` to production. `department: "---"` sends
  `<acad_org>---</acad_org>`. Class Schedule short-circuits both cases.
- **D3 — search matches markup.** `innerHTML` search makes `span`, `secret`,
  and digits 0-3 match unrelated rows.
- **D4 — row toggle is not keyboard-accessible.**
- **D5 — Units sorts lexically.**
- **D6 — unescaped upstream output.** `$course->subject/title/level/units/
  description` are echoed raw (`:315-316`); SimpleXML decodes entities, so
  `&lt;script&gt;` in the feed becomes live markup. Trust boundary is
  PeopleSoft, so severity is low, but it is the same class as the Campus
  Directory escaping findings.
- **D7 — fixed element IDs.** `courseCatalog`, `search`, `tableSorter`,
  `expandAll`, `collapseAll` are hardcoded, so two blocks on one page emit
  duplicate IDs (the JS itself is instance-safe via `closest()`). Same class
  as Class Schedule's WPM-180.
