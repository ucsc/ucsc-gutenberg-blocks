# JS Test Coverage

Date: 2026-08-26

Scope: `src/blocks/__tests__/`, `src/components/ClassSchedule/__tests__/`

Runner: Jest via `@wordpress/scripts` (`npm run test`, `npm run test:coverage`)

Config: `jest-unit.config.js` — extends `@wordpress/scripts` default; no enzyme;
`collectCoverageFrom: src/**/*.js` (explicit, so untouched files appear at 0%);
reporters: `text-summary`, `json-summary`, `lcov`; output: `coverage/`

---

## CampusDirectory block — 11 tests

File: `src/blocks/__tests__/CampusDirectory.test.js`

Strategy: mocks `@wordpress/components`, `@wordpress/data`, and all child components;
captures `wp.blocks.registerBlockType` config; uses `@testing-library/react` + `act`
for async fetch behaviour.

### Registration (5 tests)
- Block name is `ucscblocks/campusdirectory`
- Title is `Campus Directory`
- Icon is `welcome-learn-more`
- Category is `common`
- All 17 attributes declared: `pageLayout`, `automatedFeeds`, `cruzidList`,
  `strFacultyTypes`, `strStaffTypes`, `strGradTypes`, `manualAdd`, `addCruzids`,
  `excludeCruzids`, `displayDeptartmentAffiliates`, `linkToProfile`,
  `linkOutToCampusDirectory`, `strInformationTypes`, `strInformationTypesTable`,
  `department`, `division`, `deptOrDiv`

### save (1 test)
- Returns `null` (server-rendered)

### edit (9 tests)
- Renders without crashing
- Panel header reads `Directory Block`
- `PageLayout` and `PeopleAndInformation` child components present
- Fetches `/wp-json/ucscgutenbergblocks/v1/campusdirectoryrequirements` on mount
- Error notice shown when `automatedFeeds=true` and both `department` and
  `division` are `---`
- No error notice when a valid department is selected
- Post saving locked via `core/editor` when in invalid state
- Post saving unlocked when state is valid
- LDAP misconfiguration message shown when `ldap_pass: false` (single-site)
- Multisite link shown when `ldap_pass: false` and `multisite: true`
- Directory panel hidden when not configured correctly

---

## ClassSchedule block — 14 tests

File: `src/blocks/__tests__/ClassSchedule.test.js`

Strategy: mocks `@wordpress/components` (Panel, PanelBody, RadioControl,
CheckboxControl) and child dropdowns; mocks `window.location`; captures
`registerBlockType` config.

### Registration (5 tests)
- Block name is `ucscblocks/classschedule`
- Title is `Class Schedule`
- Icon is `schedule`
- Category is `common`
- Attributes: `subjectOrDept` (string), `department` (string), `subject` (string),
  `defaultColumns` (array)

### save (1 test)
- Returns `null` (server-rendered)

### edit (5 tests)
- Renders without crashing
- Panel header reads `Class Schedule Block`
- Defaults `subjectOrDept` to `dept` when undefined (calls `setAttributes`)
- SubjectDropdown disabled when `subjectOrDept === 'dept'`; DepartmentDropdown enabled
- DepartmentDropdown disabled when `subjectOrDept === 'subject'`; SubjectDropdown enabled
- Radio onChange calls `setAttributes({ subjectOrDept: 'subject' })`

### deprecated (3 tests)
- One deprecation entry
- `migrate()` removes `useNewServer` from old attributes, leaves others intact
- Deprecated `save()` returns `null`

---

## CourseCatalog block — 9 tests

File: `src/blocks/__tests__/CourseCatalog.test.js`

Strategy: same pattern as ClassSchedule; no `CheckboxControl` mock needed (no
`defaultColumns` panel in this block).

### Registration (5 tests)
- Block name is `ucscblocks/coursecatalog`
- Title is `Course Catalog`
- Icon is `book-alt`
- Category is `common`
- Attributes: `subjectOrDept` (string), `department` (string), `subject` (string)

### save (1 test)
- Returns `null` (server-rendered)

### edit (4 tests)
- Renders without crashing
- Panel header reads `Course Catalog Block`
- Defaults `subjectOrDept` to `dept` when undefined
- Dropdown enabled/disabled logic mirrors ClassSchedule
- Radio onChange calls `setAttributes`

---

## ClassSchedule frontend component — 22 tests

File: `src/components/ClassSchedule/__tests__/classschedule.test.js`

Strategy: builds a full DOM fixture mirroring `ClassScheduleTemplate.php`;
`require`s `classschedule.js` fresh per test via `jest.resetModules()`;
exercises the module's exported globals (`window.classScheduleSearch`,
`window.applyFilters`, `window.resetFilters`, `window.sortClassSchedule`,
`window.openFilterModal`, `window.classScheduleDownloadCSV`).

### Search (4 tests)
- Filters rows by title; updates live count
- Matches on courseId, location, instructor, class number, and status text
- Clears filter and restores all rows when search emptied
- Respects active status-filter state while searching

### Status filters (1 test)
- Unchecked statuses hide rows; search re-applied; live count updated

### Sort (3 tests)
- Text column sorts ascending, toggles to descending on second click
- Seats column sorts numerically by open seat count
- Only sorted column carries `aria-sort` attribute and `.ascending/.descending` class

### Filter modal and column visibility (5 tests)
- Column toggles show/hide header and body cells; grid template columns updated
- Hidden sortable headers get `tabindex="-1"`; visible ones get `tabindex="0"`
- Applied column choices persisted to `sessionStorage`; modal closed on Apply
- Cancel / Escape restores pre-open checkbox states without touching the table
- Reset returns columns to `[seats, days]` defaults, all statuses on, clears search
  and `sessionStorage`

### sessionStorage restore (1 test)
- On `DOMContentLoaded`, saved column choices restored to checkboxes and column visibility

### CSV export (2 tests)
- Exports only visible columns and visible rows; double-quotes fields containing quotes
- Names the file after the selected term (`Fall_2026.csv`); revokes object URL

---

## Coverage gaps

### No JS tests at all
- `src/components/DepartmentDropdown.js`
- `src/components/SubjectDropdown.js`
- `src/components/DivisionDropdown.js`
- All CampusDirectory sub-components:
  `PageLayout.js`, `PeopleAndInformation.js`, `InformationToDisplay.js`,
  `InformationToDisplayTable.js`, `CheckboxGroupControl.js`, `AutomatedFeeds.js`,
  `CampusDirectoryDepartmentDropdown.js`
- CourseCatalog has no frontend component JS (no `coursecatalog.js` equivalent to
  `classschedule.js`), so nothing to test there

### Behavioural gaps in existing tests
- CampusDirectory stale `deptOrDiv` state: saving not locked when `deptOrDiv='dept'`,
  `department='---'`, but a valid `division` remains (audit medium finding)
- ClassSchedule `defaultColumns` column-visibility checkbox behaviour not tested
  in the editor test (covered instead in the frontend component test)
- No test for two ClassSchedule blocks on the same page conflicting via shared
  global IDs (audit medium finding)
