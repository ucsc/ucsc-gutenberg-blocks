## Purpose

Defines how the Campus Directory block presents itself in the WordPress editor:
the attributes it persists, the panels an author sees, the layout choice that
drives every other decision, and the guards that stop an author publishing a
listing that cannot resolve to anyone.

## ADDED Requirements

### Requirement: Block identity and render model

The system SHALL register a dynamic block named `ucscblocks/campusdirectory`
whose editor `save` output is empty and whose front-end markup is produced
entirely on the server at render time.

Because the block saves no markup, changes to directory data or to rendering
MUST take effect on already-published posts without re-saving them, and a post
containing the block MUST NOT produce a block-validation error when the
rendering changes.

#### Scenario: Published block reflects current directory data

- **WHEN** a post containing a saved Campus Directory block is viewed after the
  underlying directory records have changed
- **THEN** the rendered listing reflects the current directory data
- **AND** the post's stored content is unchanged

#### Scenario: Editor stores configuration only

- **WHEN** an author saves a post containing the block
- **THEN** the stored block content contains only the block's configuration
  attributes and no person data

### Requirement: Persisted configuration attributes

The block SHALL persist the author's configuration as block attributes so that
reopening the post restores the editor to the exact state it was saved in.

The attribute set MUST include: layout selection (`pageLayout`); audience mode
(`automatedFeeds`, `deptOrDiv`, `department`, `division`, `cruzidList`);
affiliation-type selections (`strFacultyTypes`, `strStaffTypes`,
`strGradTypes`); feed overrides (`manualAdd`, `addCruzids`, `excludeCruzids`,
`displayDeptartmentAffiliates`); field visibility (`strInformationTypes`,
`strInformationTypesTable`); and name-linking behavior (`linkToProfile`,
`linkOutToCampusDirectory`).

#### Scenario: Configuration round-trips

- **WHEN** an author configures the block, saves the post, and reopens it
- **THEN** every control in the editor shows the value that was saved

#### Scenario: Attribute names are stable

- **WHEN** a post saved by an earlier release of the block is opened
- **THEN** its stored attributes are read without loss and without a validation
  error

### Requirement: Editor panel structure

The editor SHALL present the block's controls in two panels: a layout panel that
selects the presentation, and a people-and-information panel that selects the
audience and the fields shown for each person.

The layout panel MUST offer exactly three mutually exclusive choices — list,
tiled, and table — and MUST default to list when the block is first inserted.

#### Scenario: Newly inserted block has a layout

- **WHEN** an author inserts the block for the first time
- **THEN** the layout is set to list without the author choosing it

#### Scenario: Layout choice drives the information panel

- **WHEN** the author changes the layout between table and a non-table layout
- **THEN** the field-visibility controls shown are the ones belonging to the
  newly selected layout family

### Requirement: LDAP configuration gate

The block SHALL determine, before showing its configuration panels, whether the
site has a directory credential configured, by consulting a public read-only
endpoint at `ucscgutenbergblocks/v1/campusdirectoryrequirements`.

When no credential is configured, the editor MUST replace the configuration
panels with setup guidance that links to the settings screen where the
credential is set. On a multisite install the guidance MUST also offer the
network-level settings screen, because a network-level credential takes
precedence over a site-level one.

#### Scenario: Credential missing on a single site

- **WHEN** an author inserts the block on a site with no directory credential
- **THEN** the configuration panels are not shown
- **AND** the block displays guidance linking to the site settings screen

#### Scenario: Credential missing on a multisite install

- **WHEN** an author inserts the block on a multisite install with no directory
  credential
- **THEN** the guidance offers both the network settings screen and the site
  settings screen

#### Scenario: Credential present

- **WHEN** an author inserts the block on a site with a directory credential
  configured
- **THEN** the configuration panels are shown

### Requirement: Publish guard for unresolvable feeds

When the block is configured to use an automated feed, the system SHALL prevent
the post from being saved unless a department or a division has been selected,
and MUST show the author a non-dismissible error explaining why.

This guard exists because an automated feed with no organizational scope
resolves to an arbitrary slice of the directory rather than to the intended
unit.

#### Scenario: Automated feed with no scope blocks saving

- **WHEN** the block uses an automated feed and neither a department nor a
  division is selected
- **THEN** post saving is locked
- **AND** an error notice explains that a valid department or division is
  required

#### Scenario: Selecting a scope restores saving

- **WHEN** the author then selects a department or a division
- **THEN** post saving is unlocked and the error notice is removed

#### Scenario: Manual list is not subject to the guard

- **WHEN** the block uses a manual list of people rather than an automated feed
- **THEN** post saving is not locked by this guard
