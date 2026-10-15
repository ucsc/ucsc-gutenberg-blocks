## MODIFIED Requirements

### Requirement: Term resolution and selection

The system SHALL determine the active term in this order: a `class_schedule_term`
query parameter matching a known term code, otherwise the term the terms API
marks as default, otherwise the first term returned.

Changing the term dropdown SHALL navigate by setting `class_schedule_term` on
the current URL, so the selected term is reflected in and restorable from the
page URL. The parameter is page-wide: every Class Schedule block on the page
resolves its term from the same `class_schedule_term` value.

**Open policy question (WPM-111):** navigating immediately on selection is a
change of context on input (WCAG 3.2.2). Whether to add an explicit submit
control or an advance warning has not been decided; until it is, the
navigate-on-change behavior above is what ships.

#### Scenario: Query param selects a specific term

- **WHEN** the page is requested with `?class_schedule_term=2262` and `2262`
  is a valid term code
- **THEN** the table renders courses for term 2262

#### Scenario: No override falls back to the default term

- **WHEN** the page is requested with no `class_schedule_term` parameter
- **THEN** the table renders courses for the term the API marked as default

#### Scenario: Changing the dropdown updates the URL

- **WHEN** a visitor selects a different term from the dropdown
- **THEN** the browser navigates to the same page with
  `class_schedule_term` set to the newly selected term's code

### Requirement: Unconfigured, empty, or failed results produce a message instead of a table

When the block's department or subject attribute is unset (or `"---"`), the
system SHALL render a message asking the site editor to configure the block,
rather than an empty table, and SHALL NOT request courses. When a valid
department or subject is configured but returns no courses, the system SHALL
render a distinct "no courses found" message. When the terms list is empty,
the system SHALL render "No terms available." instead of a table.

An upstream PeopleSoft failure SHALL render one of these plain-language
messages rather than a partial table or a fatal error.

**Known defect (CS-D1, no Jira yet):** because the internal
`rest_do_request()` call returns the route's error as a `WP_REST_Response`
(never a `WP_Error`), a failed terms request renders "No terms available." and
a failed courses request renders "No courses found for the selected
criteria.", so an outage is reported to visitors as missing data. The
dedicated "Error loading terms" branch in `theHTML()` is unreachable.

#### Scenario: Unconfigured block prompts for configuration

- **WHEN** the block has no department or subject configured
- **THEN** the rendered output asks the editor to select one from the block
  settings, and no table is rendered
- **AND** no courses request is made

#### Scenario: Configured but empty result set

- **WHEN** the block is configured with a valid department that has no
  courses for the active term
- **THEN** the rendered output states no courses were found, without asking
  for configuration

#### Scenario: PeopleSoft outage renders a message, not a table

- **WHEN** the PeopleSoft terms or course-list request fails
- **THEN** the block renders a single plain-language paragraph and no table
- **AND** the failed response is not cached

#### Scenario: Outage is worded as missing data (known defect CS-D1)

- **WHEN** the PeopleSoft course-list request fails for a configured block
- **THEN** the paragraph reads "No courses found for the selected criteria."
  rather than reporting an error

### Requirement: Column sort toggles direction on repeated clicks

Clicking a sortable column header SHALL sort all rows by that column;
clicking the same header again SHALL reverse the sort direction, and clicking
a different header SHALL start that column ascending. The active header SHALL
carry `aria-sort` for the current direction.

Values are compared by the cell's rendered text: when both texts begin with a
number (JavaScript `parseFloat`), they SHALL be compared by that leading
number; otherwise they SHALL be compared with `localeCompare`. Seats
(`"N open / M total"`) therefore sorts by open seats.

**Known defect (CS-D2, no Jira yet):** Time (`"10:00 AM - ..."`) sorts by its
leading hour without regard to minutes or AM/PM, so "1:30 PM" sorts before
"10:00 AM".

#### Scenario: Repeated click reverses sort order

- **WHEN** a visitor clicks the Course ID header twice in succession
- **THEN** the table is sorted ascending after the first click and descending
  after the second

#### Scenario: Seats sorts by open-seat count

- **WHEN** a visitor sorts by Seats with rows showing "5 open", "40 open",
  and "12 open"
- **THEN** the ascending order is 5, 12, 40

#### Scenario: Time sorts by leading hour (known defect CS-D2)

- **WHEN** a visitor sorts by Time ascending with rows starting "10:00 AM"
  and "1:30 PM"
- **THEN** the "1:30 PM" row sorts first

### Requirement: Column visibility is configurable by the visitor within a session

The Filter modal SHALL let a visitor toggle any of the seven optional columns
independent of the editor-configured default, applying changes only when
Apply is clicked (Cancel or the backdrop MUST restore the state as it was
when the modal opened; Escape also closes it the same way). Applied column
choices SHALL persist to `sessionStorage` so they survive navigation within
the same tab, and "Reset all filters" SHALL restore the editor-configured
default rather than a fixed default, re-check every status, and clear the
search box, immediately re-filtering the table. Status choices are not
persisted.

While the modal is open, focus SHALL be trapped inside it; closing it SHALL
return focus to the element that opened it. The modal itself is a
`role="dialog"` with `aria-modal="true"` labelled by its title.

**Known defect (WPM-197):** the Filter button that opens the modal carries no
`aria-haspopup`, `aria-controls` or `aria-expanded`, so screen-reader users
get no cue that it opens a dialog.

#### Scenario: Cancel discards unapplied changes

- **WHEN** a visitor opens the Filter modal, toggles a column, and clicks
  Cancel
- **THEN** the table's visible columns are unchanged from before the modal
  opened

#### Scenario: Applied column choice survives navigation

- **WHEN** a visitor applies a column change, clicks into a course detail
  page, and returns
- **THEN** the previously applied column visibility is restored

#### Scenario: Reset restores the block's configured default, not a fixed one

- **WHEN** a block is configured with `defaultColumns: ['time','location']`
  and a visitor who has changed columns clicks "Reset all filters"
- **THEN** Time and Location become the only visible optional columns
