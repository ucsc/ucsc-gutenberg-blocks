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
  dropdowns.
- Block attributes persist the editor's choices (`subjectOrDept`, `department`,
  `subject`).
- Server-side rendering in `classes/CourseCatalog.php` generates the front-end
  table from `templates/CourseCatalogTemplate.php` (or equivalent render
  callback).
- Course catalog data is fetched from the upstream UCSC catalog source and
  cached via WordPress transients.
- No public course-detail rewrite routes — catalog entries link to external
  catalog pages.
- All upstream data is cached; no raw upstream calls reach the browser.

## Capabilities

### New Capabilities

- `course-catalog/block-registration`: Gutenberg block registration, attribute
  schema (`subjectOrDept`, `department`, `subject`), and editor UI (dept/subject
  radio + dropdown).
- `course-catalog/data-fetch`: Server-side fetch of course catalog data filtered
  by department or subject, with transient caching.
- `course-catalog/frontend-render`: Server-rendered sortable catalog table
  displaying course name, number, credits, and description for the selected
  dept/subject.

### Modified Capabilities

<!-- None — this is a net-new block with no prior spec. -->

## Impact

- PHP: `classes/CourseCatalog.php`, any catalog template file
- JS: `src/blocks/CourseCatalog.js`, `src/components/DepartmentDropdown.js`,
  `src/components/SubjectDropdown.js`
- Shared components `DepartmentDropdown` and `SubjectDropdown` are used by both
  Class Schedule and Course Catalog — changes to those components affect both blocks
- No new npm or Composer dependencies; uses existing `@wordpress/scripts` build toolchain
