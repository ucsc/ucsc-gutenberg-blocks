## MODIFIED Requirements

### Requirement: Portraits

When Photo is selected in a list or tiled layout, each person SHALL render a
portrait from the plugin's stored copy of their campus directory photo, or from
the plugin's photo route when no copy is stored yet (see
`campus-directory/caching`). Portraits SHALL load lazily, so a long listing
does not request every photo at once.

A portrait that fails to load MUST fall back to a campus placeholder image
rather than a broken image. A person known to have no photo MUST render the
placeholder without requesting the photo route. Every portrait MUST carry
alternative text identifying whose portrait it is. A vacant position, which has
no CruzID, MUST render the placeholder.

#### Scenario: Portrait renders with alt text

- **WHEN** Photo is selected and a person has a CruzID
- **THEN** their portrait renders with alternative text naming them

#### Scenario: Portrait fails to load

- **WHEN** the stored file or the photo route does not return an image for a
  person
- **THEN** the campus placeholder image renders in its place

#### Scenario: Vacant position portrait

- **WHEN** Photo is selected and the listing contains a vacant position
- **THEN** the placeholder image renders for that entry

#### Scenario: Stored portrait

- **WHEN** Photo is selected and a person's photo is stored
- **THEN** their portrait links to the stored file, not the photo route

#### Scenario: Person without a photo

- **WHEN** Photo is selected and the directory returned a person without a
  photo, in this page build or within the last hour
- **THEN** the placeholder renders without a request to the photo route
