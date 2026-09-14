## Context

This change documents a block that already ships. See `proposal.md` — Why for
motivation, and `specs/class-schedule/**` for the behavior contract. This
document records the design the current implementation embodies, so future
changes have stated rationale to argue with rather than inferred intent.

Constraints that shape everything below:

- **The schedule data is external and lives in PeopleSoft.** `Course_Schedule_API`
  proxies `https://my.ucsc.edu/PSIGW/RESTListeningConnector/PSFT_CSPRD`, an
  upstream this site does not control and cannot validate ahead of a request.
- **Visitors, not just editors, drive most of the interaction.** Unlike Campus
  Directory (author configures, visitor only reads), a visitor here searches,
  sorts, filters by status/columns, switches terms, copies a URL, and exports
  CSV — all client-side, against server-rendered markup.
- **Course detail pages must be linkable and bookmarkable.** Prior production
  URLs (`/courses/course/2262-50222/...`) predate this block and still appear
  in search results, bookmarks, and emails.
- **Rendering happens on a shared host with an edge proxy**, the same ~30s
  constraint noted in the Campus Directory design — a slow PeopleSoft call must
  degrade to an error message, not a 504.

## Goals / Non-Goals

**Goals:**

- Record why schedule data is proxied through a WordPress REST namespace rather
  than fetched directly from the block's render callback, since that shape is
  what makes caching and the course-detail title filter possible.
- Record the column-visibility design (editor default vs. visitor override),
  since it spans block attributes, template output, and front-end JS state.
- Record the two rewrite rules (canonical + legacy) and their relationship.

**Non-Goals:**

- Redesigning the REST proxy, the ARIA-table-as-divs markup, or the dual
  dept/subject selection model. Documenting a design is not endorsing it.
- Specifying test implementation. `ClassScheduleTest.php`,
  `CourseScheduleAPITest.php`, `CourseCatalogTest.php`, and the Jest suites
  under `src/blocks/__tests__` and `src/components/ClassSchedule/__tests__`
  already exist; gaps against this spec are follow-up work.

## Decisions

### Server-side rendering with `save()` returning `null`

Same rationale as Campus Directory: schedule data changes without anyone
touching the post (seats fill, sections get cancelled), so the block stores
only configuration (`subjectOrDept`, `department`, `subject`,
`defaultColumns`) and PHP renders live on every request.

### PeopleSoft is proxied through a WordPress REST namespace, not called inline

`Course_Schedule_API` registers `GET /ucsc/v1/terms`, `GET
/ucsc/v1/courses/{term}`, and `GET /ucsc/v1/course/{term}/{course}` as public,
unauthenticated WordPress REST routes. `ClassSchedule::theHTML()`,
`getCachedCourses()`, and `course_detail_title()` all call these routes via
`rest_do_request()` rather than querying PeopleSoft directly.

This indirection buys two things:

- **A single cache and error-handling boundary.** Every PeopleSoft call goes
  through `validate_remote_response()`, which turns a `WP_Error` or a non-2xx
  status into a uniform `WP_Error`; every caller — the block render, the CSV
  export's data source, and the document-title filter — inherits the same
  behavior.
- **The `<title>` tag can ask for course data without duplicating the fetch.**
  `course_detail_title()` issues an internal REST request for the same course
  the template will render, so the title reflects a real API response instead
  of a guess, at the cost of one extra internal round-trip per page load.

*Trade-off:* the internal `rest_do_request()` calls carry HTTP-request
overhead even though they never leave the process, and any REST-level
authentication or rate-limiting added later would apply to internal callers
too unless deliberately bypassed.

### Two REST caching lifetimes on two different pages

Terms, course lists, and course details cache for 15 minutes
(`Course_Schedule_API::CACHE_DURATION = 900`) keyed as `ucsc_ps_terms`,
`ucsc_ps_courses_<md5(term+query)>`, and `ucsc_ps_course_<term>_<course>`.
Fifteen minutes is short enough that enrollment counts stay reasonably current
without hitting PeopleSoft on every page view.

Course Catalog (a separate block reusing the same `DepartmentDropdown` /
`SubjectDropdown` components) caches its PeopleSoft data for a full week
instead — catalog descriptions change far less often than seat counts, so the
two blocks deliberately diverge on cache lifetime even though they share a
dept/subject selection UI.

### Column visibility is a two-layer default, not a single toggle

The editor configures `defaultColumns` (an array like `['seats','days']`) in
the block's second panel — the columns a *first-time visitor* sees. The
front-end Filter modal then lets that visitor override the set for their own
session; the choice is persisted to `sessionStorage` (`cs_columns`) and
restored on subsequent page loads within the same tab, independent of the
editor's configured default.

The editor default remains meaningful even after a visitor has overridden it:
`resetFilters()` in `classschedule.js` reads it back via `getDefaultColumns()`
(sourced from `data-default-columns` on the table element) rather than
hardcoding `['seats','days']`, so "reset" returns to what the site editor
intended for this specific block instance, not a global default.

Status, Course ID, and Title are always shown and are intentionally excluded
from both the editor panel and the visitor's Filter modal — they are load-
bearing for search and are not something either party can hide.

*Alternative considered:* store the visitor's override as a block attribute
override via a query param, making it shareable via URL. Rejected in favor of
`sessionStorage` — the Copy URL feature copies the current page URL verbatim
with no filter state encoded in it, so a shared link always opens to the
editor's configured default, which is the more predictable behavior for a
link shared outside the visitor's own session.

### Course detail pages: a canonical rewrite plus a legacy redirect

`add_course_detail_rewrite()` registers two rules:

- The canonical form, `/course/{term}/{class_nbr}/` → `course_term` +
  `course_id` query vars, matched by `course_detail_template()` to serve
  `CourseDetailTemplate.php`.
- A legacy form matching prior production URLs
  (`(?:.+/)?course/{term}-{class_nbr}(?:/[^/]*)?/?$`, e.g.
  `/courses/course/2262-50222/history-of-the-present`), which sets
  `legacy_redirect=1` instead of rendering directly.

`maybe_redirect_legacy_course()` runs on `template_redirect` and turns any
request carrying `legacy_redirect` into a 301 to the canonical URL. Old
bookmarks and search-indexed links keep working and get permanently
consolidated onto one URL shape rather than being served twice under two
paths — a duplicate-content and cache-fragmentation concern the redirect
avoids.

### The front-end table is ARIA-table-as-divs, matching Campus Directory's a11y pattern

`ClassScheduleTemplate.php` renders `role="table"` / `role="row"` /
`role="cell"` on plain `<div>`s rather than `<table>`/`<tr>`/`<td>`, the same
choice noted for accessibility scanners elsewhere in this plugin. Column
hide/show toggles a `hidden` class and, for sortable headers, sets
`tabindex="-1"` on the inner `<button>` so a hidden column's sort control
cannot receive keyboard focus — the tabindex lives on the button rather than
the wrapping div because a div is not natively focusable and a stray
`tabindex` on it would create a redundant focus stop once removed.

### The Filter modal is a hand-built focus trap, not a native `<dialog>`

`openFilterModal()` snapshots checkbox state (so Cancel can restore it),
moves focus into the modal, and installs a `keydown` handler that traps `Tab`
inside the modal and closes on `Escape`. Closing restores focus to whichever
element opened the modal. This predates broad `<dialog>` support in the
target browser matrix; it is deliberate manual a11y work rather than an
oversight of an available primitive.

## Risks / Trade-offs

- **The department/subject dropdown source has no request timeout** →
  `SiteSettings::departmentcode()` and `subjectcode()` — shared by this block
  and Course Catalog's editor — fetch PeopleSoft's `SCX_CLASS_DEPTS_V2` feed
  with raw `curl_init()` and `CURLOPT_TIMEOUT => 0` (unlimited), caching for a
  week only after a successful response. A hung upstream response blocks the
  editor's admin-ajax/REST request indefinitely on a cold cache. This is the
  same class of finding recorded against a different endpoint
  (`cddepartmentcode`) in `campus-directory-block`'s design, now confirmed to
  also affect Class Schedule and Course Catalog. Mitigation: a bounded
  timeout and a stale-if-error fallback, tracked as a follow-up.
- **Course Catalog and Class Schedule diverge on PeopleSoft environment
  targeting** → `Course_Schedule_API` hardcodes `PSFT_CSPRD` with no
  QA/staging override; `CourseCatalog` supports a prod/QA target switch via
  constant, env var, and (currently disabled) a request-parameter override
  gated to dev hosts. An engineer adding QA testing support to one block may
  reasonably expect the sibling block already has it. Mitigation: none
  proposed here — noting the asymmetry is the deliverable of this baseline.
- **The document-title REST round-trip has no dedicated error message** → if
  `rest_do_request()` for the title lookup errors, `course_detail_title()`
  silently leaves the default title in place; only the page body shows the
  "Error loading course details" message. Acceptable today because the two
  requests share the same upstream and will typically fail together, but
  worth naming since it means the `<title>` and page body can theoretically
  disagree.
- **CSV export and sort operate on rendered DOM text, not the underlying
  data** → `classScheduleDownloadCSV()` and `sortClassSchedule()` read
  `textContent` from table cells rather than the original API response. A
  future template change that reformats a cell's visible text (e.g. adding a
  unit suffix) silently changes CSV output and sort behavior with no code
  change to the JS itself — a coupling worth knowing about before editing the
  template.

## Migration Plan

None. No code changes, no data changes, no deploy. Per the user's direction
for this reverse-engineering pass, these delta specs are folded directly into
`openspec/specs/class-schedule/**` as the working baseline rather than staged
behind a task-verification pass first.

## Open Questions

- Should the PeopleSoft environment-targeting asymmetry (Risks, second item)
  be resolved by adding QA support to Class Schedule, or by removing it from
  Course Catalog? Deferrable — it does not change either spec, only which
  follow-up change addresses it.
- Should the legacy redirect rule ever be retired? It exists for URLs from a
  predecessor system; there is no expiration policy recorded anywhere in code
  or comments.
