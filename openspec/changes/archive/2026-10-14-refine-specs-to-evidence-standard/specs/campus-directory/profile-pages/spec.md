## MODIFIED Requirements

### Requirement: Profile document title

A profile page SHALL set its document title to the person's name, so browser
tabs, bookmarks, and assistive technology identify the profile rather than
repeating the host page's title.

**Known defect (WPM-183):** the title lookup constructs `CampusDirectoryAPI`
with no node content, so every profile page load emits
`Undefined array key "automatedFeeds"` PHP warnings. The title itself is
correct.

#### Scenario: Title names the person

- **WHEN** a profile for a person with a directory name is rendered
- **THEN** the document title is that person's name

#### Scenario: Title falls back

- **WHEN** the profile's CruzID resolves to no directory record
- **THEN** the host page's own title is used unchanged

### Requirement: Profile shortcode

The system SHALL provide a `ucsc_profiles` shortcode that renders one or more
directory profiles inside arbitrary post or page content, for units whose page
is not built from the block.

The shortcode MUST accept a comma-separated `cruzids` attribute and a
`displaystyle` attribute selecting a grid or list presentation, defaulting to
grid. Profiles are fetched as a profile view (full record) and rendered in
the order given.

Name and "...more" links always point to `campusdirectory.ucsc.edu`. The
photo is the directory record's `jpegphoto` rendered as a CSS background
image, with the campus placeholder when absent and no text alternative.

**Known defect (WPM-194, CD-D3):** every render emits an
`Undefined variable $options` warning. Profiles are paired with the `cruzids`
list by position, so a CruzID that matches no displayable record shifts later
cards and leaves an empty trailing card (with PHP warnings) rather than being
skipped.

#### Scenario: Multiple people

- **WHEN** the shortcode is given several comma-separated CruzIDs
- **THEN** a profile renders for each of them

#### Scenario: List presentation

- **WHEN** the shortcode sets `displaystyle` to list
- **THEN** the profiles render in the list presentation

#### Scenario: Default presentation

- **WHEN** `displaystyle` is not supplied
- **THEN** the profiles render in the grid presentation

#### Scenario: Unknown CruzID leaves an empty card (known defect WPM-194)

- **WHEN** the shortcode's `cruzids` include one with no directory record
- **THEN** an empty trailing card renders and PHP warnings are emitted

### Requirement: Shortcode field selection

The shortcode SHALL let the author choose which fields render for each person,
mirroring the block's per-person field customization through boolean
attributes.

The selectable attributes MUST be `photo`, `name`, `title`, `phone`, `email`,
`websites`, `officelocation`, `officehours`, `expertise`, `profilelinks`,
`biography`, `areas_of_expertise`, `research_interests`, `teaching_interests`,
`awards`, and `publications`. Photo, name, and profile links MUST default to on
and every other field to off, so a bare shortcode renders a compact card.

An attribute value MUST be accepted as the strings `true` and `false`, since
shortcode attributes are always text.

Only `photo`, `name`, `title`, `phone`, `email`, `websites` (read from the
`labeleduri` attribute), `officelocation`, and `profilelinks` currently have
an effect.

**Known defect (WPM-195, CD-D3):** `officehours` reads a mixed-case key that
never exists after attributes are lowercased, so it renders nothing; the
branch it guards would also fatal, because it calls the render helpers as
global functions.

**Known defect (CD-D3, no Jira yet):** `expertise`, `biography`,
`areas_of_expertise`, `research_interests`, `teaching_interests`, `awards`,
and `publications` are commented out and render nothing.

**Known defect (CD-D2, no Jira yet):** setting `profilelinks` to `false`
renders the name as empty rather than as plain text.

**Known defect (WPM-193):** `websites="true"` fatals with a `TypeError` when a
person's website has no label.

#### Scenario: Bare shortcode renders a compact card

- **WHEN** the shortcode is used with only a `cruzids` attribute
- **THEN** each profile renders a photo, a name, and profile links, and no other
  fields

#### Scenario: Enabling a field

- **WHEN** the shortcode sets `title` to `true`
- **THEN** each profile additionally renders the person's title

#### Scenario: Disabling a default field

- **WHEN** the shortcode sets `photo` to `false`
- **THEN** no portrait renders

#### Scenario: Disabling profile links blanks the name (known defect CD-D2)

- **WHEN** the shortcode sets `profilelinks` to `false`
- **THEN** each profile renders an empty name

#### Scenario: Office hours never render (known defect WPM-195)

- **WHEN** the shortcode sets `officehours` to `true` for a person whose
  directory record has office hours
- **THEN** no office hours render
