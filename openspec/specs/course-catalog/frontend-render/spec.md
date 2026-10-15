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

**Known defect (WPM-209, CC-D6):** upstream course fields (`subject`,
`catalog_nbr`, `title`, `level`, `units`, `description`) are echoed without
HTML escaping. SimpleXML decodes entities, so `&lt;script&gt;` in the feed
becomes live markup, and it stays cached for the transient's lifetime.

**Known defect (CC-D7, WPM-217):** the element IDs `courseCatalog`,
`search`, `tableSorter`, `expandAll` and `collapseAll` are fixed, so two
Course Catalog blocks on one page emit duplicate IDs. The script itself scopes
its work per block through `closest()`.

#### Scenario: Description is present but hidden by default

- **WHEN** a course is rendered
- **THEN** its description row exists in the DOM with a class that hides it
  from view until expanded

#### Scenario: Markup in a course field is emitted unescaped (known defect WPM-209)

- **WHEN** a PeopleSoft course title contains `&lt;b&gt;x&lt;/b&gt;`
- **THEN** the rendered row contains a live `<b>` element rather than literal
  text

### Requirement: Clicking a course row toggles its description

Clicking anywhere on a course's header row (other than the expand/collapse-all
controls) SHALL toggle visibility of that course's own description row only,
without affecting any other row's expanded state.

**Known defect (WPM-109, CC-D4):** the toggle is bound to mouse clicks on
table cells only. Rows are not keyboard-focusable, the Expand all / Collapse
all controls are `<a>` elements with no `href`, and no control exposes
`aria-expanded`. WPM-109 also carries WPM-58 (course title cells styled as
underlined text that is not a link).

#### Scenario: Toggling one row does not affect others

- **WHEN** a visitor clicks one course row to expand its description
- **THEN** only that course's description becomes visible; all others remain
  collapsed

#### Scenario: Description cannot be reached by keyboard (known defect WPM-109)

- **WHEN** a keyboard-only visitor tabs through the block
- **THEN** focus never lands on a course row or on Expand all / Collapse all

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
remaining non-numeric text when two numeric keys are equal. Clicking a header
sorts ascending unless that header is currently ascending, in which case it
sorts descending.

**Known defect (CC-D5, WPM-216):** a column without sort-key spans
compares its full cell text as strings, so Units sorts lexically
(`"10 Units"` before `"5 Units"`).

**Known defect (WPM-108):** sortable headers respond to clicks only; they are
not focusable, have no keyboard handler, and expose no `aria-sort`.

#### Scenario: Sorting keeps descriptions attached to their course

- **WHEN** the table is sorted by course title
- **THEN** each description row moves with its corresponding header row and
  remains directly after it

#### Scenario: Numeric hidden key drives sort order

- **WHEN** the Course # column is sorted
- **THEN** rows order by the hidden numeric catalog number, not by the
  visible subject-code text

#### Scenario: Units sorts as text (known defect CC-D5)

- **WHEN** the Units column is sorted ascending with courses of 5 and 10 units
- **THEN** the 10-unit course sorts before the 5-unit course

### Requirement: Search filters by visible and hidden row text

The search box SHALL filter course pairs (header + description),
case-insensitively, on every keyup; a course pair SHALL remain visible if
either its header row or its description row contains the search term, so a
match found only in the long-form description still surfaces the course.

**Known defect (CC-D3, WPM-215):** matching runs against each cell's
`innerHTML`, not its visible text, so markup and the hidden level rank are
also searchable: `span`, `secret`, and the digits 0-3 match unrelated rows
(e.g. searching `2` matches every Upper Division course).

#### Scenario: Search matches text only present in the description

- **WHEN** a visitor searches for a term that appears only in a course's
  description, not its title
- **THEN** that course's header and description rows both remain visible

#### Scenario: Search matches hidden markup (known defect CC-D3)

- **WHEN** a visitor searches for `secret`
- **THEN** every course row remains visible, because every level cell
  contains a `span.secret`
