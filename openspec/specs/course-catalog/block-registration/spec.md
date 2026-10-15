# Course Catalog / Block Registration Specification

## Purpose

Lets a site editor pin a Course Catalog block to one department or one
subject, so a browsable course catalog table can be embedded on a department
page without further configuration by anyone else.

## Requirements

### Requirement: Block registration and attributes

The system SHALL register a server-rendered WordPress block
`ucscblocks/coursecatalog` with `save()` returning `null`, so all front-end
markup is produced by the PHP render callback (`CourseCatalog::theHTML()`) on
every request.

The block SHALL be registered with block API version 3 in both the PHP
registration (`'api_version' => 3`) and the editor registration
(`apiVersion: 3`), and the editor's `edit` output SHALL be wrapped in a single
element carrying `useBlockProps()`, so the block renders inside the iframed
editor canvas (WPM-205).

The block SHALL declare these attributes: `subjectOrDept` (string, `"dept"`
or `"subject"`), `department` (string), `subject` (string).

#### Scenario: Save produces no stored markup

- **WHEN** the block is saved in the editor
- **THEN** the post content for this block contains no rendered HTML, only
  the block comment and its attributes

#### Scenario: Block registers at API version 3

- **WHEN** the editor script registers `ucscblocks/coursecatalog` and PHP
  registers its block type
- **THEN** both registrations report block API version 3
- **AND** the block's editor controls render inside a wrapper carrying the
  block props

### Requirement: Department vs. subject selection

The editor SHALL let the author choose between pinning the block to a
department or to a subject via a radio control, and SHALL show a dropdown for
whichever mode is active while disabling the other, using the same
`DepartmentDropdown` and `SubjectDropdown` components as the Class Schedule
block.

`subjectOrDept` MUST default to `"dept"` when the block is first inserted and
no value has been set; the editor writes this default into the attributes on
first render. The server render does not apply its own default (see
`data-fetch`).

The dropdown option lists come from `ucscgutenbergblocks/v1/departmentcode`
and `/subjectcode`, which cache the parsed PeopleSoft list for one week and do
not cache a transport failure or non-2xx response.

**Known defect (WPM-207):** when a dropdown endpoint answers with an HTTP
error page instead of JSON, the editor throws an unhandled JSON parse error
and the dropdown shows "Dropdown Loading..." indefinitely, with no message.

**Known defect (CS-D3, WPM-212):** a 2xx response whose body cannot be
parsed into options is cached as a list containing only `---` for one week,
so the dropdown offers no real choices until the transient expires.

#### Scenario: Default mode on insert

- **WHEN** an author inserts a new Course Catalog block
- **THEN** the department dropdown is enabled and the subject dropdown is
  disabled

#### Scenario: Switching modes swaps which dropdown is active

- **WHEN** the author selects "Subject" mode
- **THEN** the subject dropdown becomes enabled and the department dropdown
  becomes disabled

#### Scenario: Dropdown options are shared with Class Schedule

- **WHEN** the department dropdown loads its options
- **THEN** it fetches from the same `ucscgutenbergblocks/v1/departmentcode`
  endpoint the Class Schedule block's editor uses

#### Scenario: Dropdown endpoint returns an HTTP error page (known defect WPM-207)

- **WHEN** the `departmentcode` or `subjectcode` endpoint answers with an
  HTML error response such as a 429
- **THEN** the dropdown remains on "Dropdown Loading..." and the editor
  console records an unhandled JSON parse error

### Requirement: Front-end assets load on every page

The system SHALL register and enqueue the catalog's `tablesorter.js` and
`tablesorter.css` on `wp_enqueue_scripts` for every front-end request,
regardless of whether a Course Catalog block is present on the page.

#### Scenario: Assets present without the block

- **WHEN** a front-end page with no Course Catalog block is requested
- **THEN** `tablesorter.js` and `tablesorter.css` are still enqueued
