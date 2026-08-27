## Purpose

Lets a site editor decide which directory fields are published for each person
in a listing, so one unit can show office hours and expertise while another
shows only a name and title, without either unit maintaining its own list.

## ADDED Requirements

### Requirement: Per-field visibility selection

The block SHALL let an author choose, from a fixed set of directory fields,
which fields are rendered for every person in the listing. Selection MUST be
per-field and independent: turning one field off MUST NOT change any other.

Fields not selected MUST NOT appear in the rendered output for any person, and
MUST NOT be requested from the directory on behalf of that listing.

The selectable fields SHALL be:

| Field label | Directory field |
|---|---|
| Pronouns | `ucscpersonpubpronouns` |
| Photo | portrait fetched from the campus directory photo service by CruzID (list and tiled layouts only) |
| Title | `title` |
| Department | `ucscpersonpubdepartmentnumber` |
| Phone | `telephonenumber` |
| Campus Email | `mail` |
| Other Email | `ucscpersonpubalternatemail` |
| Fax | `facsimiletelephonenumber` |
| Website | `ucscpersonpubwebsite` |
| Office Location | `ucscpersonpubofficelocationdetail` |
| Office Hours | `ucscpersonpubofficehours` |
| Mailstop | `ucscpersonpubmailstop` |
| Mailing Address | composed from the person's street and room fields |
| Faculty Areas of Expertise | `ucscpersonpubexpertisereference` |
| Summary of Expertise | `ucscpersonpubareaofexpertise` |

A person's name is always rendered and is not a selectable field.

#### Scenario: Deselected field disappears from every person

- **WHEN** the author deselects Office Hours and the post is viewed
- **THEN** no person in the listing renders an office hours value
- **AND** every other selected field still renders

#### Scenario: Selection applies uniformly

- **WHEN** the author selects Pronouns
- **THEN** every person in the listing who has a pronouns value renders it

#### Scenario: Person missing a selected field

- **WHEN** a selected field has no value for a given person
- **THEN** that person renders without that field
- **AND** the rest of the listing is unaffected

### Requirement: Field selection is scoped per layout family

The system SHALL keep two independent field-visibility selections: one for the
list and tiled layouts, and one for the table layout.

Switching the layout MUST NOT discard or overwrite the selection belonging to
the other layout family. An author who configures a table listing, switches to
list to compare, and switches back MUST find the table selection intact.

The table layout MUST NOT offer Photo as a selectable field, because the table
presentation has no cell for an image.

#### Scenario: Switching layouts preserves both selections

- **WHEN** the author configures fields for the table layout, switches to the
  list layout and configures different fields, then switches back to table
- **THEN** the table layout shows the field selection originally configured for
  it

#### Scenario: Photo is unavailable in the table layout

- **WHEN** the author selects the table layout
- **THEN** Photo is not offered as a selectable field

#### Scenario: List and tiled share one selection

- **WHEN** the author configures fields under the list layout and then switches
  to the tiled layout
- **THEN** the tiled layout shows the same field selection

### Requirement: Default field selection

When the block is first inserted and the author has made no field choices, the
system SHALL apply a default selection appropriate to the layout family so the
block renders something useful without configuration.

The list and tiled default MUST be: Photo, Title, Department, Phone, Campus
Email, Website, Office Location, and Office Hours. The table default MUST be:
Title, Department, Phone, and Campus Email.

Once the author has made a choice, defaults MUST NOT be reapplied on subsequent
edits.

#### Scenario: Defaults on first insert

- **WHEN** an author inserts the block and does not open the field controls
- **THEN** the list and tiled default fields are selected

#### Scenario: Defaults are not reapplied

- **WHEN** an author deselects a default field, saves, and reopens the post
- **THEN** that field remains deselected

### Requirement: Field selection persistence contract

The field selection SHALL be persisted as a JSON object mapping each field
label to a boolean, stored in `strInformationTypes` for the list and tiled
layouts and in `strInformationTypesTable` for the table layout.

The stored object MUST carry an entry for every selectable field, including
deselected ones, so that a field added to the block in a later release is
distinguishable from a field the author explicitly turned off.

A block saved before the table layout existed MAY have no table selection
stored; the system MUST render such a block without error.

#### Scenario: Every field is represented

- **WHEN** an author saves a block with only Title selected
- **THEN** the stored selection records Title as true and every other
  selectable field as false

#### Scenario: Missing table selection is tolerated

- **WHEN** a block saved without a table selection is rendered
- **THEN** rendering completes without error

### Requirement: Name linking behavior

The block SHALL let the author choose whether a person's name links to a more
detailed profile, and if so whether that link points to the campus-wide
directory at `campusdirectory.ucsc.edu` or to a profile served by this site.

Both settings MUST default to enabled. When name linking is disabled, the
choice of link destination MUST be unavailable, because it has no effect.

#### Scenario: Linking disabled renders plain names

- **WHEN** the author disables name linking
- **THEN** each person's name renders as text with no link
- **AND** the link-destination control is disabled

#### Scenario: Linking to the campus-wide directory

- **WHEN** name linking is enabled and the campus-wide directory destination is
  selected
- **THEN** each name links to that person's entry on `campusdirectory.ucsc.edu`

#### Scenario: Linking to a local profile

- **WHEN** name linking is enabled and the campus-wide directory destination is
  not selected
- **THEN** each name links to a profile served by this site for that person
