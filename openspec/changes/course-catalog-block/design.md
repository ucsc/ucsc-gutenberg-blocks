## Context

This change documents a block that already ships. See `proposal.md` — Why for
motivation, and `specs/course-catalog/**` for the behavior contract. This
document records the design the current implementation embodies.

Constraints that shape everything below:

- **The catalog feed is a legacy XML integration, not a modern REST API.**
  `CourseCatalog::getCachedCourses()` POSTs a small XML query document to
  PeopleSoft's `HttpListeningConnector` and parses an XML response with
  `simplexml_load_string()` — an older integration style than the JSON `ucsc/v1`
  proxy the sibling Class Schedule block uses for its own PeopleSoft data.
- **The catalog changes rarely relative to the schedule.** Course descriptions,
  units, and levels are stable within a term; seat counts are not. This is why
  the two blocks, despite sharing dropdown components, cache on very different
  timescales (see Decisions).
- **A non-prod PeopleSoft target must be reachable for testing** without
  becoming reachable to the public internet at large.

## Goals / Non-Goals

**Goals:**

- Record why this block's caching and environment-targeting design differs
  from Class Schedule's, since an engineer moving between the two blocks will
  otherwise expect parity.
- Record the request-override mechanism and why it is currently disabled
  rather than removed.
- Make the dead `restPermissionsCheck()` method and the missing separate
  template file visible as findings against the (now corrected) proposal.

**Non-Goals:**

- Redesigning the XML integration, the inline-HTML render approach, or the
  prod/QA target mechanism. Documenting a design is not endorsing it.
- Specifying test implementation. `CourseCatalogTest.php` and
  `src/blocks/__tests__/CourseCatalog.test.js` already exist; gaps against
  this spec are follow-up work.

## Decisions

### Server-side rendering with no `save()` markup, same as its sibling blocks

`save()` returns `null`; `theHTML()` renders on every request via
`register_block_type`'s `render_callback`. Consistent with Class Schedule and
Campus Directory: catalog content should be editable server-side (a feed
change, a cache clear) without requiring every embedding post to be re-saved.

### Markup is built inline in PHP, with no separate template file

Unlike Class Schedule (`ClassScheduleTemplate.php`) and Campus Directory
(`CampusDirectoryTemplate.php`), `CourseCatalog::theHTML()` builds its HTML
with string concatenation and `echo` directly in the class method, using
`ob_start()`/`ob_get_contents()` only to capture the final string for return.
The original proposal for this block assumed a template file existed or was
"equivalent"; it does not — this is a real asymmetry with the other two
blocks, not a documentation gap.

*Trade-off:* the lack of a template file means this block's presentation
cannot be swapped by a theme or child-theme override the way a
`locate_template()`-based approach would allow, and the HTML is harder to
review as markup versus as PHP control flow.

### PeopleSoft is targeted per-environment via constant/env var, with a disabled request-level override

`getPeopleSoftTarget()` resolves to `PSFT_CSPRD` (prod) or `PSFT_CSQA` (QA)
based on `UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET` (a PHP constant, falling back
to an environment variable of the same name), normalizing recognized aliases
(`qa`, `test` → `csqa`) and defaulting to prod for anything else.

A second mechanism, `getRequestTargetOverride()` / `isRequestOverrideAllowed()`,
would let a GET parameter override the target — but only for a logged-in user
with `manage_options` on an allow-listed dev host (`wp-dev.ucsc`,
`wordpress-dev.ucsc.edu`, `localhost`, `127.0.0.1`) or with an explicit
`UCSC_COURSE_CATALOG_ALLOW_REQUEST_OVERRIDE` flag set. The call sites in
`getPeopleSoftTarget()` and `shouldBypassCache()` are commented out today
("disabled now that testing is done") while the guard functions themselves
remain — the override is dormant, not deleted, so it can be re-enabled for a
future QA testing pass without rewriting the safety checks.

This is a materially different design from Class Schedule, which hardcodes
its PeopleSoft base URL with no target concept at all (see the sibling
block's design doc, Risks).

### Cache key includes the resolved target, the query value, and the mode

`getCachedCourses()` builds the transient key as
`course-catalog-<target>-<lowercased dept-or-subject value>-<subjectOrDept>`,
so prod and QA data never collide, and a department code that happens to
match a subject code (or vice versa) is still isolated by the trailing mode
segment. The week-long TTL (`WEEK_IN_SECONDS`) reflects how rarely catalog
descriptions change — several orders of magnitude longer than Class
Schedule's 15-minute API cache for the same PeopleSoft system family.

Caching happens only after `simplexml_load_string()` successfully parses the
response — a fetch that returns a 200 with unparseable XML is surfaced as a
`WP_Error` and never written to the transient, so a single bad response
cannot poison a week of subsequent requests.

### Cache is cleared via WP-CLI, not a REST endpoint

`CourseCatalog::clearCachedCourses($target)` is exposed only through a
WP-CLI command registered in `index.php`
(`wp ucsc course-catalog-cache clear [--target=<target>]`), deleting matching
`_transient_course-catalog-*` rows via a direct `$wpdb` query. There is no
web-facing cache-clear route, unlike the REST-based configuration surfaces
elsewhere in this plugin — clearing a stale catalog cache requires shell
access to the host.

### Front-end interaction is a shared, generic table sorter, not block-specific JS

`tablesorter.js` implements column sort, expand/collapse of description rows,
and a search box against any `.table-sortable` element by DOM structure and
class name alone — it has no dependency on Course Catalog attributes or PHP
output beyond the class names and row-pairing convention (each course row is
followed by a hidden description row). This is a much thinner front-end than
Class Schedule's `classschedule.js` (no status filtering, no CSV export, no
column-visibility persistence) because the catalog table has one interaction
surface: browse and expand.

## Risks / Trade-offs

- **`restPermissionsCheck()` is dead code** → `CourseCatalog.php` declares
  `restPermissionsCheck()`, but no REST route in this class references it —
  `ContentSharer.php` has its own separate copy that it actually uses. This
  method appears to be a leftover from an earlier design or a copy/paste
  artifact. Mitigation: remove it in a follow-up change; it is not part of
  this block's behavior contract.
- **XML parsing failures are the block's only cache-corruption defense** →
  `simplexml_load_string()` with `libxml_use_internal_errors(true)` catches
  malformed XML, but a response that is well-formed XML with unexpected
  structure (e.g. a schema change upstream) would parse successfully and
  could be cached for a week before anyone notices a rendering problem.
- **No separate template file means theme overrides are not possible** → see
  Decisions; noted here because it constrains how a future visual change to
  this block can be delivered (a PHP code change is the only path, not a
  theme-level template override).
- **The department/subject dropdown source has no request timeout** → same
  finding as recorded in `class-schedule-block`'s design: `SiteSettings`'s
  `departmentcode()` / `subjectcode()`, shared by both blocks' editors, use
  `curl_init()` with `CURLOPT_TIMEOUT => 0`. Not re-litigated in full here;
  see the sibling block's design doc for detail.
- **The disabled request-override mechanism is live code with no test
  coverage visible** → `isRequestOverrideAllowed()` and
  `getRequestTargetOverride()` remain fully implemented but unreachable from
  their only call sites (commented out). If re-enabled without review, the
  host allow-list and `manage_options` capability check are the only things
  preventing an authenticated low-privilege user from redirecting catalog
  fetches to an arbitrary target — worth a security-focused look before
  reactivating rather than assuming the dormant code is still correct.

## Migration Plan

None. No code changes, no data changes, no deploy. Per the user's direction
for this reverse-engineering pass, these delta specs are folded directly into
`openspec/specs/course-catalog/**` as the working baseline rather than staged
behind a task-verification pass first.

## Open Questions

- Should `restPermissionsCheck()` be deleted now, or left for a dedicated
  cleanup change? Deferrable — it does not change this block's observable
  behavior.
- Is the request-level PeopleSoft target override still needed for any active
  QA workflow, or can `isRequestOverrideAllowed()` /
  `getRequestTargetOverride()` be removed entirely? Not answerable from code
  alone — needs a decision from whoever last used it for QA testing.
