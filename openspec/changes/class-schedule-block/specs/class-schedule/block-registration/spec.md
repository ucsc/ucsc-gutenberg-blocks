## Purpose

Lets a site editor pin a Class Schedule block to one department or one
subject, and choose which optional columns visitors see by default, so a
single block instance can be embedded per department page without further
configuration by anyone else.

## ADDED Requirements

### Requirement: Block registration and attributes

The system SHALL register a server-rendered WordPress block
`ucscblocks/classschedule` with `save()` returning `null`, so all front-end
markup is produced by the PHP render callback on every request.

The block SHALL declare these attributes: `subjectOrDept` (string, `"dept"` or
`"subject"`), `department` (string), `subject` (string), and `defaultColumns`
(array of column keys).

#### Scenario: Save produces no stored markup

- **WHEN** the block is saved in the editor
- **THEN** the post content for this block contains no rendered HTML, only
  the block comment and its attributes

### Requirement: Department vs. subject selection

The editor SHALL let the author choose between pinning the block to a
department or to a subject via a radio control, and SHALL show a dropdown for
whichever mode is active while disabling the other.

`subjectOrDept` MUST default to `"dept"` when the block is first inserted and
no value has been set.

#### Scenario: Default mode on insert

- **WHEN** an author inserts a new Class Schedule block
- **THEN** the department dropdown is enabled and the subject dropdown is
  disabled

#### Scenario: Switching modes swaps which dropdown is active

- **WHEN** the author selects "Subject" mode
- **THEN** the subject dropdown becomes enabled and the department dropdown
  becomes disabled

### Requirement: Default visible columns configuration

The editor SHALL let the author choose, from seven optional columns (Seats,
Days, Time, Location, Instructor, Class #, Enrollment), which are visible by
default when a visitor first views the rendered block. Status, Course ID, and
Title are always shown and MUST NOT be configurable here.

When `defaultColumns` has never been set, the system SHALL default it to
`['seats', 'days']`.

#### Scenario: Unconfigured block falls back to Seats and Days

- **WHEN** a block has no stored `defaultColumns` value
- **THEN** the rendered table shows the Seats and Days columns and hides the
  other four optional columns

#### Scenario: Author-configured columns take effect

- **WHEN** the author checks Time and Location and unchecks Seats and Days
- **THEN** the rendered table's default view shows Time and Location and
  hides Seats, Days, Instructor, Class #, and Enrollment

### Requirement: Deprecated attribute migration

The block SHALL migrate posts saved with the removed `useNewServer` boolean
attribute by silently discarding it, so existing content does not trigger a
block-validation error in the editor.

#### Scenario: Legacy attribute is stripped without error

- **WHEN** a post saved before `useNewServer` was removed is opened in the
  editor
- **THEN** the block loads without a validation error
- **AND** the migrated attributes contain no `useNewServer` key
