## MODIFIED Requirements

### Requirement: Block registration and attributes

The system SHALL register a server-rendered WordPress block
`ucscblocks/classschedule` with `save()` returning `null`, so all front-end
markup is produced by the PHP render callback on every request.

The block SHALL be registered with block API version 3 in both the PHP
registration (`'api_version' => 3`) and the editor registration
(`apiVersion: 3`), and the editor's `edit` output SHALL be wrapped in a single
element carrying `useBlockProps()`, so the block renders inside the iframed
editor canvas (WPM-205).

The block SHALL declare these attributes: `subjectOrDept` (string, `"dept"` or
`"subject"`), `department` (string), `subject` (string), and `defaultColumns`
(array of column keys).

#### Scenario: Save produces no stored markup

- **WHEN** the block is saved in the editor
- **THEN** the post content for this block contains no rendered HTML, only
  the block comment and its attributes

#### Scenario: Block registers at API version 3

- **WHEN** the editor script registers `ucscblocks/classschedule` and PHP
  registers its block type
- **THEN** both registrations report block API version 3
- **AND** the block's editor controls render inside a wrapper carrying the
  block props

### Requirement: Department vs. subject selection

The editor SHALL let the author choose between pinning the block to a
department or to a subject via a radio control, and SHALL show a dropdown for
whichever mode is active while disabling the other.

`subjectOrDept` MUST default to `"dept"` when the block is first inserted and
no value has been set.

The dropdown option lists come from `ucscgutenbergblocks/v1/departmentcode`
and `/subjectcode`, which cache the parsed PeopleSoft list for one week and do
not cache a transport failure or non-2xx response.

**Known defect (WPM-207):** when a dropdown endpoint answers with an HTTP
error page instead of JSON, the editor throws an unhandled JSON parse error
and the dropdown shows "Dropdown Loading..." indefinitely, with no message.

**Known defect (CS-D3, no Jira yet):** a 2xx response whose body cannot be
parsed into options is cached as a list containing only `---` for one week,
so the dropdown offers no real choices until the transient expires.

#### Scenario: Default mode on insert

- **WHEN** an author inserts a new Class Schedule block
- **THEN** the department dropdown is enabled and the subject dropdown is
  disabled

#### Scenario: Switching modes swaps which dropdown is active

- **WHEN** the author selects "Subject" mode
- **THEN** the subject dropdown becomes enabled and the department dropdown
  becomes disabled

#### Scenario: Dropdown endpoint returns an HTTP error page (known defect WPM-207)

- **WHEN** the `departmentcode` or `subjectcode` endpoint answers with an
  HTML error response such as a 429
- **THEN** the dropdown remains on "Dropdown Loading..." and the editor
  console records an unhandled JSON parse error
