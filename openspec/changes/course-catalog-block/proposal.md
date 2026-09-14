## Why

UCSC departments need a way to embed an up-to-date course catalog on WordPress
pages so prospective students and advisors can browse offerings by department or
subject without leaving the site. The Course Catalog block provides a Gutenberg
editor UI for selecting a department or subject and a public-facing sortable
table driven by the UCSC course catalog data feed.

## What Changes

- Registers a new WordPress block `ucscblocks/coursecatalog` via
  `wp.blocks.registerBlockType` in `src/blocks/CourseCatalog.js`.
- Editor panel lets authors choose dept vs. subject mode and select a value from
  dropdowns (the same `DepartmentDropdown` / `SubjectDropdown` components the
  Class Schedule block uses).
- Block attributes persist the editor's choices (`subjectOrDept`, `department`,
  `subject`).
- Server-side rendering in `classes/CourseCatalog.php::theHTML()` builds the
  front-end markup directly (string-built HTML, not a separate template file)
  from an XML feed response.
- Course catalog data is fetched by POSTing an XML query to PeopleSoft's
  `HttpListeningConnector` (`SCX_SERVICE_CTLG.v1` operation), targeting either
  the `PSFT_CSPRD` (prod) or `PSFT_CSQA` (QA) instance based on a constant/env
  var, and is cached via WordPress transients for a full week — only once the
  response has been confirmed to parse as XML, so a malformed 200 response
  never poisons the cache.
- A WP-CLI command (`wp ucsc course-catalog-cache clear [--target=<target>]`,
  registered in `index.php`) clears cached transients for one or all targets.
- No public course-detail rewrite routes — catalog entries render inline in
  an expandable table row rather than linking to a separate detail page.
- All upstream data is cached; no raw upstream calls reach the browser.

## Capabilities

### New Capabilities

- `course-catalog/block-registration`: Gutenberg block registration, attribute
  schema (`subjectOrDept`, `department`, `subject`), and editor UI (dept/subject
  radio + dropdown).
- `course-catalog/data-fetch`: Server-side XML fetch of course catalog data
  filtered by department or subject, prod/QA target selection, transient
  caching keyed per target and query, and the WP-CLI cache-clear command.
- `course-catalog/frontend-render`: Server-rendered sortable, searchable,
  expandable catalog table displaying course number, title, level, units, and
  an expandable description row for the selected dept/subject.

### Modified Capabilities

<!-- None — this is the first spec for an existing block; no prior capability exists to modify. -->

## Impact

- PHP: `classes/CourseCatalog.php` (no separate template file — markup is
  built inline in `theHTML()`)
- JS: `src/blocks/CourseCatalog.js`, `src/components/DepartmentDropdown.js`,
  `src/components/SubjectDropdown.js`, `src/components/CourseCatalog/tablesorter.js`
- CSS: `src/components/CourseCatalog/tablesorter.css`
- Shared components `DepartmentDropdown` and `SubjectDropdown` are used by both
  Class Schedule and Course Catalog — changes to those components affect both blocks
- WP-CLI: `wp ucsc course-catalog-cache clear [--target=<target>]` (registered
  in `index.php`, calls `CourseCatalog::clearCachedCourses()`)
- No new npm or Composer dependencies; uses existing `@wordpress/scripts` build toolchain
