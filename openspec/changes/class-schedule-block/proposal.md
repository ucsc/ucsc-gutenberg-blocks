## Why

UCSC departments and students need an easy way to embed current class schedule
information on WordPress pages without custom development. The Class Schedule
block provides a Gutenberg editor UI for site editors to pin a department or
subject, and a public-facing table with search, filtering, and CSV export
backed by the PeopleSoft course schedule API.

## What Changes

- Registers a new WordPress block `ucscblocks/classschedule` via
  `wp.blocks.registerBlockType` in `src/blocks/ClassSchedule.js`.
- Editor panel lets authors choose dept vs. subject mode and pick a value from
  dropdowns; a second panel lets authors configure which columns are visible by
  default.
- Block attributes persist the editor's choices (`subjectOrDept`, `department`,
  `subject`, `defaultColumns`).
- A `deprecated` migration strips the now-removed `useNewServer` boolean so
  existing posts do not show a block-validation error.
- Server-side rendering in `classes/ClassSchedule.php` generates the front-end
  table from `templates/ClassScheduleTemplate.php`.
- A dedicated WP REST API proxy (`src/API/Course_Schedule_API.php`, namespace
  `ucsc/v1`) fetches terms, course lists, and course detail from PeopleSoft and
  caches results for 15 minutes.
- Public course-detail pages are reachable via registered rewrite rules.
- All PeopleSoft API data is cached via WordPress transients; no raw upstream
  calls reach the browser.

## Capabilities

### New Capabilities

- `class-schedule/block-registration`: Gutenberg block registration, attribute
  schema, editor UI (dept/subject mode, column visibility), and deprecation
  migration.
- `class-schedule/rest-api`: WordPress REST proxy for PeopleSoft terms
  (`/ucsc/v1/terms`), course list (`/ucsc/v1/courses/{term}`), and course detail
  (`/ucsc/v1/course/{term}/{course}`) with 15-minute transient caching.
- `class-schedule/frontend-render`: Server-rendered schedule table with search,
  quarter/term switching, column-visibility filter modal, and CSV export.
- `class-schedule/course-detail-pages`: Public rewrite-rule-based course detail
  pages rendered via `CourseDetailTemplate.php`.

### Modified Capabilities

<!-- None — this is a net-new block with no prior spec. -->

## Impact

- PHP: `classes/ClassSchedule.php`, `src/API/Course_Schedule_API.php`,
  `templates/ClassScheduleTemplate.php`, `templates/CourseDetailTemplate.php`
- JS: `src/blocks/ClassSchedule.js`, `src/components/ClassSchedule/classschedule.js`,
  `src/components/DepartmentDropdown.js`, `src/components/SubjectDropdown.js`
- REST namespace `ucsc/v1` — public, no authentication required
- WordPress transient cache keys: `ucsc_ps_terms`, `ucsc_ps_courses_<md5>`,
  `ucsc_ps_course_<term>_<course>`
- No new npm or Composer dependencies; uses existing `@wordpress/scripts` build toolchain
