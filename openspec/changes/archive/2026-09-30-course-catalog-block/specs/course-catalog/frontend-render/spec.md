## Purpose

Renders a searchable, sortable, expandable catalog table for visitors, so a
visitor can scan course titles and levels, then reveal a full description
in-place without leaving the page.

## ADDED Requirements

### Requirement: Upstream errors render a plain-language message

When the data-fetch layer returns an error, the block SHALL render a single
"Course Catalog data is temporarily unavailable" message and SHALL NOT attempt
to render a table, a search box, or expand/collapse controls.

#### Scenario: Fetch error suppresses the whole table

- **WHEN** the catalog data fetch errors
- **THEN** only the unavailability message is rendered

### Requirement: Each course renders as a header row plus a hidden detail row

For every course returned, the system SHALL render one visible row with
course ID (subject + catalog number), title, level, and units (`"<n> Units"`),
immediately followed by one hidden row containing the full course
description, inside a `<table id="tableSorter" class="table-sortable">`
wrapped in a block root with `id="courseCatalog"`, after a search box and
Expand all / Collapse all links. Rows are rendered in upstream order.

The catalog number SHALL be wrapped in a visible `span.intsort` sort key
after the subject text, and the level SHALL carry a hidden `span.secret`
numeric rank (unrecognized = 0 < Lower Division = 1 < Upper Division = 2 <
Graduate = 3) alongside its visible label, so both sort correctly despite
being displayed as text.

Upstream course fields are echoed without HTML escaping.

#### Scenario: Description is present but hidden by default

- **WHEN** a course is rendered
- **THEN** its description row exists in the DOM with a class that hides it
  from view until expanded

### Requirement: Clicking a course row toggles its description

Clicking anywhere on a course's header row (other than the expand/collapse-all
controls) SHALL toggle visibility of that course's own description row only,
without affecting any other row's expanded state. The toggle is bound to
mouse clicks on table cells only; rows are not keyboard-focusable.

#### Scenario: Toggling one row does not affect others

- **WHEN** a visitor clicks one course row to expand its description
- **THEN** only that course's description becomes visible; all others remain
  collapsed

### Requirement: Expand all / Collapse all controls

The system SHALL provide "Expand all" and "Collapse all" controls that show
or hide every course's description row at once, independent of each row's
individually toggled state.

#### Scenario: Expand all reveals every description

- **WHEN** a visitor clicks "Expand all"
- **THEN** every course's description row becomes visible

### Requirement: Column sort operates on paired header/description rows

Clicking a sortable column header SHALL sort courses by that column while
keeping each course's header row and its immediately following description
row together as a unit; a numeric hidden sort key SHALL take precedence over
the visible text when present, falling back to alphabetic comparison of the
remaining non-numeric text when two numeric keys are equal. A column
without sort-key spans SHALL compare its full cell text as strings, so Units
sorts lexically (`"10 Units"` before `"5 Units"`). Clicking a header sorts
ascending unless that header is currently ascending, in which case it sorts
descending.

#### Scenario: Sorting keeps descriptions attached to their course

- **WHEN** the table is sorted by course title
- **THEN** each description row moves with its corresponding header row and
  remains directly after it

#### Scenario: Numeric hidden key drives sort order

- **WHEN** the Course # column is sorted
- **THEN** rows order by the hidden numeric catalog number, not by the
  visible subject-code text

### Requirement: Search filters by visible and hidden row text

The search box SHALL filter course pairs (header + description),
case-insensitively, on every keyup; a course pair SHALL remain visible if
either its header row or its description row contains the search term, so a
match found only in the long-form description still surfaces the course.

Matching runs against each cell's `innerHTML`, not its visible text, so
markup and the hidden level rank are also searchable (e.g. searching `2`
matches every Upper Division course).

#### Scenario: Search matches text only present in the description

- **WHEN** a visitor searches for a term that appears only in a course's
  description, not its title
- **THEN** that course's header and description rows both remain visible
