## MODIFIED Requirements

### Requirement: Three listing layouts

The system SHALL render a listing in one of three layouts chosen by the author:
list, tiled, or table.

The list and tiled layouts MUST render each person as a card carrying their name
and the selected fields as labeled entries (the tiled layout hides the labels
visually). The table layout MUST render a header row naming each selected
field, followed by one row per person.

**Known defect (CD-D4, no Jira yet):** the table layout renders a
simplified cell per field: only the first directory value, the raw value text
(no website label split, no combined office location), a duplicate copy of the
value in a CSS-hidden `<strong>`, and a PHP warning for each selected field a
person lacks.

**Known defect (WPM-107):** the table layout renders each person's name in a
`<td>`, not a row header (`<th scope="row">`), so cells are announced without
the person they belong to.

**Known defect (WPM-106):** in the table and tiled layouts the field labels
are hidden with `display: none`, which removes them from the accessibility
tree; screen readers announce values without their labels.

**Known defect (WPM-12, PR open):** when the configuration matches nobody,
the table layout renders its header row with zero rows and no empty-state
message.

#### Scenario: Table layout headers

- **WHEN** the table layout is rendered with Title and Phone selected
- **THEN** the table has a Name column plus a Title column and a Phone column

#### Scenario: List layout entries

- **WHEN** the list layout is rendered
- **THEN** each person renders as a card with their name and one labeled entry
  per selected field that has a value

#### Scenario: Empty listing

- **WHEN** the configuration matches nobody
- **THEN** the listing container renders with no people and no error output

#### Scenario: Empty table listing has no message (known defect WPM-12)

- **WHEN** the table layout's configuration matches nobody
- **THEN** the table header row renders with no person rows and no message

#### Scenario: Table cell for a missing field (known defect CD-D4)

- **WHEN** the table layout has Phone selected and a person has no phone
  number
- **THEN** a PHP warning is emitted for that person's Phone cell

### Requirement: Name linking in listings

Each person's name SHALL link according to the block's name-linking
configuration: to `https://campusdirectory.ucsc.edu/cd_detail?uid=<cruzid>`,
to this site's profile via the relative query form
`?directoryprofilecruzid=<cruzid>`, or not at all.

When Photo is selected and name linking is enabled, the portrait MUST link to
the same destination as the name.

**Open policy question (WPM-198):** the portrait and the name are two
separate links to the same destination, which doubles tab stops and
announcements per person (WCAG 2.4.4). Whether to merge them into one link or
take the portrait link out of the tab order has not been decided.

#### Scenario: Name and portrait link together

- **WHEN** name linking is enabled and Photo is selected
- **THEN** both the name and the portrait link to the same profile destination

#### Scenario: Linking disabled

- **WHEN** name linking is disabled
- **THEN** neither the name nor the portrait is a link

### Requirement: Microformat markup

Listings SHALL carry h-card microformat markup so directory data is
machine-readable by campus tooling.

In the list and tiled layouts, each person MUST be marked as an h-card, with
their name marked as the card's name and, where rendered, their profile link,
email addresses, and websites marked with the corresponding microformat
properties.

**Known defect (CD-D4, no Jira yet):** the table layout carries only
`p-name` and `u-url` on the name, with no `h-card` container.

#### Scenario: Person is an h-card

- **WHEN** a list or tiled listing renders
- **THEN** each person is marked up as an h-card with a name property

#### Scenario: Contact properties are marked

- **WHEN** email and website fields are selected and render
- **THEN** they carry the email and website microformat properties

#### Scenario: Table rows are not h-cards (known defect CD-D4)

- **WHEN** a table listing renders
- **THEN** no row carries the `h-card` class
