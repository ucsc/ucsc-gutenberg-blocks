# Class Schedule / Block Registration Specification

## Purpose

Lets a site editor pin a Class Schedule block to one department or one
subject, and choose which optional columns visitors see by default, so a
single block instance can be embedded per department page without further
configuration by anyone else.

## Requirements

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

When `defaultColumns` has never been set (or is not an array), the system
SHALL default it to `['seats', 'days']`. Unknown column keys SHALL be ignored
at render time. An explicitly empty array SHALL hide all seven optional
columns.

Hidden columns SHALL render with a `hidden` class, and their sortable header
buttons SHALL carry `tabindex="-1"` so they cannot receive keyboard focus.

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

### Requirement: Editor version label reflects installed plugin version

The Class Schedule block's editor settings panel SHALL show a version label
whose value is the `Version` header of the installed plugin. The value SHALL
NOT be hard-coded in the editor script, so each release updates the label with
no source edit.

If the plugin version is not available to the editor, the panel SHALL omit the
version label entirely rather than show an empty, placeholder or stale value.

#### Scenario: Label matches the plugin header

- **WHEN** the plugin header declares `Version: 1.2.1` and an editor opens a
  Class Schedule block's settings panel
- **THEN** the panel shows `version 1.2.1`

#### Scenario: Label follows a release bump

- **WHEN** the plugin header version changes (for example to `1.3.0`) and the
  editor is reloaded
- **THEN** the panel shows `version 1.3.0` without any change to the editor
  script source

#### Scenario: Version unavailable

- **WHEN** the editor opens a Class Schedule block's settings panel and no
  plugin version has been provided to the editor
- **THEN** no version label is rendered in the panel

#### Scenario: Development cache-busting does not leak into the label

- **WHEN** the site runs in a local or development environment, where the
  editor script is cache-busted by file modification time
- **THEN** the panel still shows the plugin header version, not the timestamp
