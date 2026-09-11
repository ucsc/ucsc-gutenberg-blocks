## Purpose

Renders a searchable, sortable, filterable class schedule table for
visitors, with column visibility, status filtering, CSV export, and a
shareable URL — entirely client-side against server-rendered markup, since the
underlying seat and section data changes continuously.

## ADDED Requirements

### Requirement: Term resolution and selection

The system SHALL determine the active term in this order: a `class_schedule_term`
query parameter matching a known term code, otherwise the term the terms API
marks as default, otherwise the first term returned.

Changing the term dropdown SHALL navigate by setting `class_schedule_term` on
the current URL, so the selected term is reflected in and restorable from the
page URL.

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

### Requirement: Unconfigured or empty results produce a guiding message

When the block's department or subject attribute is unset (or `"---"`), the
system SHALL render a message asking the site editor to configure the block,
rather than an empty table. When a valid department or subject is configured
but returns no courses, the system SHALL render a distinct "no courses found"
message.

#### Scenario: Unconfigured block prompts for configuration

- **WHEN** the block has no department or subject configured
- **THEN** the rendered output asks the editor to select one from the block
  settings, and no table is rendered

#### Scenario: Configured but empty result set

- **WHEN** the block is configured with a valid department that has no
  courses for the active term
- **THEN** the rendered output states no courses were found, without asking
  for configuration

### Requirement: Course rows are sorted by catalog number

Courses SHALL be sorted numerically by catalog number ascending as the
initial render order, independent of any visitor-applied column sort.

#### Scenario: Initial order is by catalog number

- **WHEN** the block renders a set of courses with catalog numbers 5, 101,
  and 12
- **THEN** they appear in the order 5, 12, 101

### Requirement: Visitor search filters visible rows

A search box SHALL filter rows by matching, case-insensitively, against
title, location, instructor, class number, course ID, and status —
mirroring the fields the predecessor Vue application searched. Filtering
SHALL combine with the active status filters (both must match for a row to
show) and SHALL update the live class-count region.

#### Scenario: Search matches instructor name

- **WHEN** a visitor types an instructor's last name into the search box
- **THEN** only rows whose instructor field contains that text remain visible

#### Scenario: Search and status filter combine

- **WHEN** a visitor has excluded "Closed" from the status filter and then
  searches for a term that matches both open and closed sections
- **THEN** only matching open (and wait-listed, if included) sections remain
  visible

### Requirement: Column sort toggles direction on repeated clicks

Clicking a sortable column header SHALL sort all rows by that column;
clicking the same header again SHALL reverse the sort direction. Numeric
columns (e.g. Seats) SHALL sort numerically when both compared values parse
as numbers, and lexically otherwise.

#### Scenario: Repeated click reverses sort order

- **WHEN** a visitor clicks the Course ID header twice in succession
- **THEN** the table is sorted ascending after the first click and descending
  after the second

### Requirement: Column visibility is configurable by the visitor within a session

The Filter modal SHALL let a visitor toggle any of the seven optional columns
independent of the editor-configured default, applying changes only when
Apply is clicked (Cancel or the backdrop MUST restore the state as it was
when the modal opened). Applied choices SHALL persist to the browser session
so they survive navigation within the same tab, and "Reset all filters" SHALL
restore the editor-configured default rather than a fixed default.

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

### Requirement: Status filtering

The Filter modal SHALL let a visitor include or exclude Open, Closed, and
Wait List sections independently; all three SHALL be checked by default.
Excluding a status MUST hide all rows carrying that status when Apply is
clicked.

#### Scenario: Excluding Closed hides closed sections

- **WHEN** a visitor unchecks "Closed" and applies filters
- **THEN** no row with closed status remains visible

### Requirement: CSV export reflects the current visible state

Downloading CSV SHALL include only currently visible (post-filter) rows and
only currently visible optional columns, plus the always-shown Course ID and
Title columns; the Status column SHALL be excluded from the export since it
is a visual indicator with no text value.

#### Scenario: Export respects active filters and columns

- **WHEN** a visitor has hidden the Instructor column and filtered out
  Closed sections, then downloads CSV
- **THEN** the exported file has no Instructor column and contains no closed
  sections

### Requirement: Copy URL copies the current page URL unmodified

The Copy URL action SHALL copy the browser's current URL to the clipboard
without encoding any client-side filter, search, or column state into it, and
SHALL confirm the action with a visible, auto-dismissing toast.

#### Scenario: Copied URL omits transient filter state

- **WHEN** a visitor has applied column and status filters and clicks Copy
  URL
- **THEN** the copied URL matches the browser's current address bar exactly,
  with no filter parameters appended

### Requirement: Live region announces the visible course count

An `aria-live="polite"` region SHALL report the number of currently visible
course rows, updated after every search, filter, or reset action.

#### Scenario: Count updates after filtering

- **WHEN** a search or status filter reduces the visible rows from 40 to 6
- **THEN** the live region's text updates to reflect 6 classes displayed
