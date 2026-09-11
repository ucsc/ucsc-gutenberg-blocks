## Purpose

Gives every course section a canonical, linkable detail page rendered outside
the block, so a visitor can follow a course link from the schedule table (or
an old bookmarked URL) to a full description, meeting information, and
associated sections.

## ADDED Requirements

### Requirement: Canonical course detail URL

The system SHALL serve a course detail page at `/course/{term}/{class_nbr}/`
for any numeric term and class number, resolved via query vars `course_term`
and `course_id` and rendered through `CourseDetailTemplate.php`.

#### Scenario: Canonical URL renders the course template

- **WHEN** `/course/2262/50222/` is requested
- **THEN** `course_term=2262` and `course_id=50222` are set as query vars
- **AND** the course detail template is used to render the response

### Requirement: Legacy URL forms redirect permanently to the canonical form

The system SHALL recognize prior production URL forms matching
`course/{term}-{class_nbr}` (optionally prefixed with a path and suffixed
with a slug, e.g. `/courses/course/2262-50222/history-of-the-present`) and
SHALL issue a 301 redirect to the canonical `/course/{term}/{class_nbr}/`
form.

#### Scenario: Legacy URL redirects to canonical form

- **WHEN** `/courses/course/2262-50222/history-of-the-present` is requested
- **THEN** the response is a 301 redirect to `/course/2262/50222/`

### Requirement: Missing or unreachable course data degrades to a message

When the course lookup errors, returns no data, or has no primary section,
the system SHALL render a plain-language message appropriate to the failure
(load error vs. not found vs. unavailable) rather than a partially-rendered
page or a fatal error.

#### Scenario: Unreachable API shows a retry-appropriate message

- **WHEN** the course detail REST call fails
- **THEN** the page shows an error message and does not attempt to render
  course fields

#### Scenario: Nonexistent course shows a not-found message

- **WHEN** the course detail response is empty for a given term/class number
- **THEN** the page states the course was not found

### Requirement: Course identity, status, and capacity are rendered

The page SHALL render the course's status (open/closed/wait list) with both a
visual indicator and text, the full course title, subject/catalog/section
identifier, and available/enrolled/capacity seat counts computed as
`max(0, capacity - enrolled)`.

#### Scenario: Available seats never render negative

- **WHEN** enrolled exceeds capacity in the upstream data
- **THEN** available seats renders as 0, not a negative number

### Requirement: Meeting information is aggregated across all meeting patterns

When a course has one or more meeting patterns, the page SHALL render
combined days/times (joined with `;` across patterns), a deduplicated list of
locations, and a deduplicated list of instructors linking to their campus
directory profile by CruzID — except an instructor named "Staff" or lacking a
CruzID, which SHALL render as plain text.

A meeting time of `"TBA"` (case-insensitive) SHALL render as `"TBA"` rather
than being parsed as a clock time.

#### Scenario: Multiple meeting patterns are joined

- **WHEN** a course has two meeting patterns with different days
- **THEN** both day/time strings appear, separated by a semicolon

#### Scenario: Staff instructor is not linked

- **WHEN** a meeting's instructor is "Staff" with no CruzID
- **THEN** the name renders as plain text, not a link

#### Scenario: TBA time is not parsed as a clock time

- **WHEN** a meeting's start or end time is `"TBA"`
- **THEN** the rendered time is the literal string "TBA"

### Requirement: Associated sections render distinctly from the primary section

Secondary sections (discussions, labs) associated with the primary course
SHALL render in their own visually distinct blocks, each showing only the
fields present in the upstream data for that section, and SHALL exclude any
section whose class_section matches the primary section (to avoid duplicating
it).

#### Scenario: A secondary section identical to the primary is not duplicated

- **WHEN** the secondary-sections payload includes an entry with the same
  class_section as the primary section
- **THEN** that entry is not rendered as an associated section

### Requirement: Breadcrumb links back to the referring schedule page when safe

The breadcrumb's "Class Schedule" link SHALL use the HTTP referer when it is
present, passes redirect validation, and belongs to this site; otherwise it
SHALL fall back to the site's home URL.

#### Scenario: Off-site referer falls back to home

- **WHEN** the HTTP referer is an external site
- **THEN** the breadcrumb links to the site's home URL, not the external
  referer
