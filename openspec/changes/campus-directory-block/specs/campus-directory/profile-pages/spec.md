## Purpose

Covers the individual-person surfaces that a listing links to: the pretty
`/directory/<cruzid>/` profile route served by this site, and the
`ucsc_profiles` shortcode that embeds the same profile data into arbitrary
content.

## ADDED Requirements

### Requirement: Profile route

The system SHALL serve an individual directory profile at
`/directory/<cruzid>/`, and MUST also honor the equivalent
`directoryprofilecruzid` query parameter so profiles remain reachable when
pretty permalinks are unavailable.

A profile URL naming a CruzID with no directory record MUST render an explicit
not-found message naming that CruzID, rather than a fatal error, a blank
region, or a redirect.

#### Scenario: Pretty profile URL

- **WHEN** a visitor requests `/directory/jsmith/`
- **THEN** the profile for that person renders

#### Scenario: Query parameter form

- **WHEN** a visitor requests a page with the `directoryprofilecruzid`
  parameter set
- **THEN** the profile for that person renders

#### Scenario: Unknown CruzID

- **WHEN** a profile URL names a CruzID with no directory record
- **THEN** the page renders a message naming that CruzID as not found
- **AND** no fatal error is produced

### Requirement: Profile keeps theme chrome

When a profile is requested against a singular page of the site, the profile
SHALL render within that page's content so the active theme keeps its header,
navigation, and footer.

The profile MUST render only for the main query on a singular page, and MUST NOT
be injected into excerpts, secondary loops, feeds, or admin screens.

#### Scenario: Profile renders inside the themed page

- **WHEN** a profile is requested against a singular page
- **THEN** the profile renders within that page's content
- **AND** the theme's header, navigation, and footer are present

#### Scenario: Secondary loops are untouched

- **WHEN** a page containing a secondary loop or an excerpt is rendered while a
  profile query variable is set
- **THEN** the profile is not injected into that loop or excerpt

### Requirement: Profile document title

A profile page SHALL set its document title to the person's name, so browser
tabs, bookmarks, and assistive technology identify the profile rather than
repeating the host page's title.

#### Scenario: Title names the person

- **WHEN** a profile for a person with a directory name is rendered
- **THEN** the document title is that person's name

#### Scenario: Title falls back

- **WHEN** the profile's CruzID resolves to no directory record
- **THEN** the host page's own title is used unchanged

### Requirement: Profile content

An individual profile SHALL render the person's full campus directory record,
which is substantially richer than anything a listing shows.

The profile MUST render, when present: title, division, department,
affiliations, phone, campus and alternate email, fax, website, office location
and room, office hours, mail stop, mailing address, faculty areas of expertise,
and **courses taught**.

The profile MUST additionally render these scholarly sections when present:
Summary of Expertise, Research Interests, Biography/Education and Training,
Teaching Interests, Awards/Honors and Grants, Selected Exhibitions, Selected
Performances, Selected Presentations, Selected Publications, and Selected
Recordings.

A field or section with no value MUST be omitted entirely — no empty heading,
no empty list. A field with multiple directory values MUST render every value.

Unlike a listing, a profile's content is NOT subject to the block's per-person
field selection: field selection governs listings, and a profile shows the whole
record.

#### Scenario: Courses taught render

- **WHEN** a profile is rendered for a person whose directory record lists
  courses taught
- **THEN** a "Courses Taught" section renders every listed course

#### Scenario: Absent section is omitted

- **WHEN** a person's directory record has no research interests
- **THEN** no Research Interests heading appears on the profile

#### Scenario: Profile ignores listing field selection

- **WHEN** a listing is configured to show only Title, and a visitor opens a
  person's profile from it
- **THEN** the profile renders the person's full record, not only their title

#### Scenario: Multiple values render

- **WHEN** a person's record carries several titles or several phone numbers
- **THEN** every value renders

### Requirement: Course information comes from the campus directory record

Course information on a profile — the courses a person teaches — SHALL be read
from that person's official UCSC campus directory record, retrieved in the same
query that supplies the rest of the profile.

The system MUST NOT source this block's course information from the PeopleSoft
course-schedule or course-catalog feeds. Those feeds describe course offerings,
not people, and are consumed by separate blocks through a different REST
namespace; reading them here would introduce a second upstream dependency and
two disagreeing answers to "what does this person teach".

Because course data arrives with the rest of the record, it MUST be subject to
the same caching, escaping, and failure behavior as every other profile field —
it is not separately fetched, separately cached, or separately refreshed.

#### Scenario: Courses come from the directory record

- **WHEN** a profile renders courses taught
- **THEN** the values come from the person's campus directory record
- **AND** no request is made to a course-schedule or course-catalog service

#### Scenario: Course data shares the profile cache

- **WHEN** a profile is rendered twice within the profile cache window
- **THEN** courses taught render both times from one directory query

#### Scenario: Directory failure hides courses with everything else

- **WHEN** the directory is unreachable
- **THEN** the profile renders no courses and no course-specific error

### Requirement: Profile shortcode

The system SHALL provide a `ucsc_profiles` shortcode that renders one or more
directory profiles inside arbitrary post or page content, for units whose page
is not built from the block.

The shortcode MUST accept a comma-separated `cruzids` attribute and a
`displaystyle` attribute selecting a grid or list presentation, defaulting to
grid.

#### Scenario: Multiple people

- **WHEN** the shortcode is given several comma-separated CruzIDs
- **THEN** a profile renders for each of them

#### Scenario: List presentation

- **WHEN** the shortcode sets `displaystyle` to list
- **THEN** the profiles render in the list presentation

#### Scenario: Default presentation

- **WHEN** `displaystyle` is not supplied
- **THEN** the profiles render in the grid presentation

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
