## Purpose

Determines which people a Campus Directory listing contains — either everyone
matching an organizational feed, or a hand-curated list — so a unit's page stays
current without anyone maintaining it by hand.

## ADDED Requirements

### Requirement: Two audience modes

The block SHALL offer exactly two mutually exclusive ways to determine who
appears: an automated feed derived from the directory, or a manual list of
CruzIDs supplied by the author.

The automated feed MUST be the default for a newly inserted block.

#### Scenario: Default is an automated feed

- **WHEN** an author inserts the block
- **THEN** the automated feed mode is selected

#### Scenario: Switching to a manual list

- **WHEN** the author selects the manual list mode
- **THEN** the feed controls are hidden and a CruzID list input is shown

### Requirement: Manual list preserves author order

In manual list mode the author SHALL supply CruzIDs separated by commas, and
the listing MUST render those people in the order given rather than in
directory order.

A CruzID that matches no directory record MUST be omitted from the listing
without failing the render or displacing the remaining people.

#### Scenario: Order is honored

- **WHEN** the author enters three CruzIDs in a deliberate order
- **THEN** the listing renders those three people in that order

#### Scenario: Unknown CruzID is skipped

- **WHEN** one of the entered CruzIDs matches no directory record
- **THEN** the listing renders the remaining people in order and omits the
  unmatched entry

### Requirement: Organizational scope for automated feeds

An automated feed SHALL be scoped either to a department or to a division,
chosen explicitly by the author, with the value selected from a list of scopes
that actually occur in the directory.

The lists MUST be sorted alphabetically and MUST offer a `---` placeholder
meaning "no scope selected". The control for the inactive choice MUST be
disabled so that a department and a division cannot both be active at once.

#### Scenario: Department scope

- **WHEN** the author chooses department scope and selects a department
- **THEN** the listing contains people whose directory department is that
  department

#### Scenario: Division scope

- **WHEN** the author chooses division scope and selects a division
- **THEN** the listing contains people whose directory division is that division

#### Scenario: Inactive scope control is disabled

- **WHEN** the author chooses department scope
- **THEN** the division control is disabled

### Requirement: Affiliation narrowing

An automated feed SHALL be narrowed by affiliation so a unit can publish, for
example, only its regular faculty. The author selects from three groups:
faculty types, staff types, and graduate students.

Faculty types MUST offer an "All" option meaning every faculty affiliation,
alongside the specific types: Regular Faculty, Lecturer, Emeriti, Research
Professor, Researcher, Adjunct Faculty, Visiting Scholar, Graduate Student
Instructor, and Retired. Selecting "All" MUST take precedence over any
individual faculty type.

Staff types MUST offer Regular Staff, Researcher, and Postdoctoral Scholar.
Selecting all three MUST be equivalent to "every staff affiliation". Selecting
Regular Staff MUST mean staff *excluding* the specialized types the author did
not select, so that a listing of regular staff does not silently include
postdoctoral scholars.

Selections across the three groups MUST combine as a union: a listing with
faculty and graduate students selected contains both.

#### Scenario: All faculty

- **WHEN** the author selects "All" under faculty types
- **THEN** the listing contains every faculty member in the selected scope

#### Scenario: Specific faculty types

- **WHEN** the author selects only Lecturer and Emeriti
- **THEN** the listing contains only faculty of those two types

#### Scenario: Regular staff excludes specialized types

- **WHEN** the author selects Regular Staff but not Postdoctoral Scholar
- **THEN** the listing contains staff who are not postdoctoral scholars

#### Scenario: Groups union together

- **WHEN** the author selects faculty types and graduate students
- **THEN** the listing contains both faculty and graduate students in the scope

#### Scenario: No affiliation selected

- **WHEN** the author selects no affiliation in any group
- **THEN** the listing renders empty rather than falling back to the whole
  directory

### Requirement: Department affiliates

When an automated feed is scoped to a department, the author SHALL be able to
include people affiliated with that department rather than only those whose
home department it is — for example a History faculty member who also teaches
a College Nine core course.

This option MUST be unavailable when the feed is scoped to a division, and
MUST default to off because it is unusual for administrative units.

#### Scenario: Affiliates included

- **WHEN** department scope is selected and affiliates are enabled
- **THEN** the listing contains people affiliated with the department in
  addition to those whose home department it is

#### Scenario: Affiliates unavailable for divisions

- **WHEN** the author switches to division scope
- **THEN** the affiliates option is not offered

### Requirement: Feed overrides

An automated feed SHALL accept two optional override lists: CruzIDs to add to
the feed's results, and CruzIDs to exclude from them.

Exclusions MUST apply only as a subtraction from a feed that already matches
someone. An exclusion list MUST NOT be able to widen a listing — in particular,
excluding people from an empty feed MUST yield an empty listing, never the whole
directory.

#### Scenario: Exclusion removes a person

- **WHEN** a feed matches a person whose CruzID is in the exclude list
- **THEN** that person does not appear in the listing

#### Scenario: Addition includes someone outside the feed

- **WHEN** a CruzID outside the feed's scope is in the add list
- **THEN** that person appears in the listing

#### Scenario: Exclusion against an empty feed

- **WHEN** the feed matches nobody and an exclude list is supplied
- **THEN** the listing renders empty

### Requirement: Vacant positions

The author SHALL be able to list a position that has no incumbent by entering
`%Name%Title%` in place of a CruzID, so an org listing can show an open role.

A vacant entry MUST render with only the supplied name and title and MUST NOT
trigger a directory lookup.

#### Scenario: Vacant entry in a manual list

- **WHEN** the author enters `%Vacant%Assistant Director%` in the manual list
- **THEN** the listing renders an entry named "Vacant" with the title
  "Assistant Director" in the position given

#### Scenario: Vacant entry appended to a feed

- **WHEN** the author enters a vacant entry in the add list of an automated feed
- **THEN** the entry is appended after the people the feed matched
