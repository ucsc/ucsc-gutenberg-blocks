## MODIFIED Requirements

### Requirement: Block identity and render model

The system SHALL register a dynamic block named `ucscblocks/campusdirectory`
whose editor `save` output is empty and whose front-end markup is produced
entirely on the server at render time.

The block SHALL be registered with block API version 3 in both the PHP
registration (`'api_version' => 3`) and the editor registration
(`apiVersion: 3`), and the editor's `edit` output SHALL be wrapped in a single
element carrying `useBlockProps()`, so the block renders inside the iframed
editor canvas (WPM-205).

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

#### Scenario: Block registers at API version 3

- **WHEN** the editor script registers `ucscblocks/campusdirectory` and PHP
  registers its block type
- **THEN** both registrations report block API version 3
- **AND** the block's editor controls render inside a wrapper carrying the
  block props

### Requirement: LDAP configuration gate

The block SHALL determine, before showing its configuration panels, whether the
site has a directory credential configured, by consulting a public read-only
endpoint at `ucscgutenbergblocks/v1/campusdirectoryrequirements`, which reports
only `ldap_pass` (whether an `ldap_api_key` is set at network or site level)
and `multisite`.

When no credential is configured, the editor MUST replace the configuration
panels with setup guidance that links to the settings screen where the
credential is set. On a multisite install the guidance MUST also offer the
network-level settings screen, because a network-level credential takes
precedence over a site-level one.

**Known defect (WPM-206):** the gate ignores `DOCKER_DEV`. In the local
wp-dev.ucsc stack the directory API binds anonymously and needs no credential,
but the editor still reports the block as not configured and hides every
control, so the block cannot be configured locally. Production behavior is
unaffected.

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

#### Scenario: Local stack without a credential (known defect WPM-206)

- **WHEN** an author inserts the block on wp-dev.ucsc with `DOCKER_DEV=docker_dev`
  and no `ldap_api_key` set
- **THEN** the configuration panels are hidden, although the front end renders
  directory listings
