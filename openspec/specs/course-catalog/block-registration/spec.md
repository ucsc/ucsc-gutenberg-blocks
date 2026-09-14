# Course Catalog / Block Registration Specification

## Purpose

Lets a site editor pin a Course Catalog block to one department or one
subject, so a browsable course catalog table can be embedded on a department
page without further configuration by anyone else.

## Requirements

### Requirement: Block registration and attributes

The system SHALL register a server-rendered WordPress block
`ucscblocks/coursecatalog` with `save()` returning `null`, so all front-end
markup is produced by the PHP render callback (`CourseCatalog::theHTML()`) on
every request.

The block SHALL declare these attributes: `subjectOrDept` (string, `"dept"`
or `"subject"`), `department` (string), `subject` (string).

#### Scenario: Save produces no stored markup

- **WHEN** the block is saved in the editor
- **THEN** the post content for this block contains no rendered HTML, only
  the block comment and its attributes

### Requirement: Department vs. subject selection

The editor SHALL let the author choose between pinning the block to a
department or to a subject via a radio control, and SHALL show a dropdown for
whichever mode is active while disabling the other, using the same
`DepartmentDropdown` and `SubjectDropdown` components as the Class Schedule
block.

`subjectOrDept` MUST default to `"dept"` when the block is first inserted and
no value has been set.

#### Scenario: Default mode on insert

- **WHEN** an author inserts a new Course Catalog block
- **THEN** the department dropdown is enabled and the subject dropdown is
  disabled

#### Scenario: Switching modes swaps which dropdown is active

- **WHEN** the author selects "Subject" mode
- **THEN** the subject dropdown becomes enabled and the department dropdown
  becomes disabled

#### Scenario: Dropdown options are shared with Class Schedule

- **WHEN** the department dropdown loads its options
- **THEN** it fetches from the same `ucscgutenbergblocks/v1/departmentcode`
  endpoint the Class Schedule block's editor uses
