# Course Catalog / Frontend Render Specification

## Purpose

Renders a searchable, sortable, expandable catalog table for visitors, so a
visitor can scan course titles and levels, then reveal a full description
in-place without leaving the page.

## Requirements

### Requirement: Upstream errors render a plain-language message

When the data-fetch layer returns an error, the block SHALL render a single
"Course Catalog data is temporarily unavailable" message and SHALL NOT attempt
to render a table, a search box, or expand/collapse controls.

#### Scenario: Fetch error suppresses the whole table

- **WHEN** the catalog data fetch errors
- **THEN** only the unavailability message is rendered

### Requirement: Each course renders as a header row plus a hidden detail row

For every course returned, the system SHALL render one visible row with
course ID (subject + catalog number), title, level, and units, immediately
followed by one hidden row containing the full course description.

The catalog number SHALL be embedded as a hidden numeric sort key alongside
the visible subject text, and the level SHALL be embedded as a hidden
numeric rank (Lower Division < Upper Division < Graduate < unrecognized)
alongside its visible text label, so both can sort correctly despite being
displayed as text.

#### Scenario: Description is present but hidden by default

- **WHEN** a course is rendered
- **THEN** its description row exists in the DOM with a class that hides it
  from view until expanded

### Requirement: Clicking a course row toggles its description

Clicking anywhere on a course's header row (other than the expand/collapse-all
controls) SHALL toggle visibility of that course's own description row only,
without affecting any other row's expanded state.

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
remaining non-numeric text when two numeric keys are equal.

#### Scenario: Sorting keeps descriptions attached to their course

- **WHEN** the table is sorted by course title
- **THEN** each description row moves with its corresponding header row and
  remains directly after it

#### Scenario: Numeric hidden key drives sort order

- **WHEN** the Course # column is sorted
- **THEN** rows order by the hidden numeric catalog number, not by the
  visible subject-code text

### Requirement: Search filters by visible and hidden row text

The search box SHALL filter course pairs (header + description) by matching
against the header row's visible cell text; a course pair SHALL remain
visible if either its header row or its description row contains the search
term, so a match found only in the long-form description still surfaces the
course.

#### Scenario: Search matches text only present in the description

- **WHEN** a visitor searches for a term that appears only in a course's
  description, not its title
- **THEN** that course's header and description rows both remain visible
