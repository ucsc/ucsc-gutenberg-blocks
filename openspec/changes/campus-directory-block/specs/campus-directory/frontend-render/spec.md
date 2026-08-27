## Purpose

Defines the public output of a Campus Directory listing — the three layouts, how
selected fields are presented within each, and the escaping and accessibility
guarantees that apply to directory data the site does not control.

## ADDED Requirements

### Requirement: Three listing layouts

The system SHALL render a listing in one of three layouts chosen by the author:
list, tiled, or table.

The list and tiled layouts MUST render each person as a card carrying their name
and the selected fields as labeled entries. The table layout MUST render a
header row naming each selected field, followed by one row per person.

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

### Requirement: Field presentation

Selected fields SHALL be presented according to their kind rather than as raw
text.

Email fields MUST render as `mailto:` links. Website fields MUST render as links
whose visible text is the label the directory supplies alongside the URL, with
the URL itself used only as the destination. Office location MUST combine the
building name and the location detail into a single readable value. A field with
multiple directory values MUST render every value, not only the first.

#### Scenario: Email renders as a link

- **WHEN** Campus Email is selected and a person has an address
- **THEN** the address renders as a `mailto:` link

#### Scenario: Website label is used as link text

- **WHEN** Website is selected and the directory supplies a URL with a label
- **THEN** the link text is the label and the destination is the URL

#### Scenario: Multiple values are all rendered

- **WHEN** a person has more than one campus email address
- **THEN** every address renders

#### Scenario: Office location is combined

- **WHEN** Office Location is selected
- **THEN** the building name and location detail render as one value

### Requirement: Portraits

When Photo is selected in a list or tiled layout, each person SHALL render a
portrait sourced from the campus directory photo service by CruzID.

A portrait that fails to load MUST fall back to a campus placeholder image
rather than a broken image. Every portrait MUST carry alternative text
identifying whose portrait it is. A vacant position, which has no CruzID, MUST
render the placeholder.

#### Scenario: Portrait renders with alt text

- **WHEN** Photo is selected and a person has a CruzID
- **THEN** their portrait renders with alternative text naming them

#### Scenario: Portrait fails to load

- **WHEN** the photo service does not return an image for a person
- **THEN** the campus placeholder image renders in its place

#### Scenario: Vacant position portrait

- **WHEN** Photo is selected and the listing contains a vacant position
- **THEN** the placeholder image renders for that entry

### Requirement: Name linking in listings

Each person's name SHALL link according to the block's name-linking
configuration: to their entry on the campus-wide directory, to a profile served
by this site, or not at all.

When Photo is selected and name linking is enabled, the portrait MUST link to
the same destination as the name.

#### Scenario: Name and portrait link together

- **WHEN** name linking is enabled and Photo is selected
- **THEN** both the name and the portrait link to the same profile destination

#### Scenario: Linking disabled

- **WHEN** name linking is disabled
- **THEN** neither the name nor the portrait is a link

### Requirement: Directory data is escaped on output

All directory-derived values MUST be escaped for the context they are rendered
into — text, attribute, or URL — before being emitted.

Directory records are maintained outside this site, so a value containing markup
or attribute-breaking characters MUST render as literal text and MUST NOT be
able to inject markup or script into a page. This applies to every rendering
surface: the list, tiled, and table layouts, the profile page, and the
shortcode.

#### Scenario: Markup in a name

- **WHEN** a person's directory name contains markup
- **THEN** the listing renders it as literal text
- **AND** no markup from the value is executed

#### Scenario: Attribute-breaking value in a link

- **WHEN** a directory value rendered into a link attribute contains quote
  characters
- **THEN** the rendered attribute remains well-formed

#### Scenario: Malformed email

- **WHEN** a directory email value is not a valid address
- **THEN** the output does not produce a malformed `mailto:` link

### Requirement: Microformat markup

Listings SHALL carry h-card microformat markup so directory data is
machine-readable by campus tooling.

Each person MUST be marked as an h-card, with their name marked as the card's
name and, where rendered, their profile link, email addresses, and websites
marked with the corresponding microformat properties.

#### Scenario: Person is an h-card

- **WHEN** a listing renders
- **THEN** each person is marked up as an h-card with a name property

#### Scenario: Contact properties are marked

- **WHEN** email and website fields are selected and render
- **THEN** they carry the email and website microformat properties
