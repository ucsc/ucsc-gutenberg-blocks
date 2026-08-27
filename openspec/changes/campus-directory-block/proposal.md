## Why

UCSC departments, divisions, and administrative units need to publish accurate
people listings on WordPress pages without hand-maintaining them. The Campus
Directory block pulls person records live from the campus LDAP directory and
lets a site editor decide, per block, both *who* appears and *which fields* are
shown for each person — so a faculty listing and an administrative staff roster
can be built from the same source without either exposing fields the unit does
not want published.

This change establishes the baseline specification for the block as it ships
today. No prior spec exists for it, so its behavior — including the per-person
field customization that distinguishes it from a static people list — is
undocumented and cannot be regression-checked.

## What Changes

- Registers a server-rendered WordPress block `ucscblocks/campusdirectory` via
  `wp.blocks.registerBlockType` in `src/blocks/CampusDirectory.js`, with
  `save()` returning `null` and PHP supplying all front-end markup.
- Editor exposes two panels: **Layout Type** (list / tiled / table) and
  **People and Information to Display** (audience selection plus per-field
  visibility).
- **Per-person field customization**: a checkbox group lets the editor choose
  which of 15 directory fields render for each person. The set is stored per
  layout family — `strInformationTypes` for list/tiled, `strInformationTypesTable`
  for table — so switching layout does not destroy the other layout's choices.
  Each is persisted as a JSON-stringified `{ label: boolean }` map.
- Audience is either an automated LDAP feed (department or division, narrowed by
  faculty/staff/grad affiliation types, optionally including department
  affiliates) or a hand-ordered CruzID list; automated feeds additionally accept
  add and exclude CruzID lists.
- Publishing is locked with an editor notice when an automated feed has neither
  a department nor a division selected.
- Server-side rendering in `classes/CampusDirectory.php::theHTML()` decodes the
  JSON attribute maps and renders `templates/CampusDirectoryTemplate.php`.
- `classes/CampusDirectoryAPI.php` builds escaped LDAP filters, queries the
  directory, narrows requested attributes for list views, and caches results in
  WordPress transients.
- Individual profile pages are served through a rewrite rule and rendered inline
  into the queried page's content so the active theme keeps its chrome; a
  `ucsc_profiles` shortcode provides the same profile data outside the block.
- A profile renders the person's whole directory record — including courses
  taught and ten scholarly sections — rather than the listing's selected fields.
  Course information comes from the campus directory record itself, **not** from
  the PeopleSoft feeds that back the Class Schedule and Course Catalog blocks.
- A public REST route `ucscgutenbergblocks/v1/campusdirectoryrequirements`
  reports whether an LDAP password is configured, so the editor can show setup
  guidance instead of a broken panel.

## Capabilities

### New Capabilities

- `campus-directory/block-registration`: Block registration, attribute schema,
  editor panel structure, layout selection, publish-lock validation, and the
  LDAP-configuration requirements check that gates the editor UI.
- `campus-directory/information-display`: Per-person field selection — the
  checkbox groups, their per-layout defaults, JSON persistence contract, and the
  display-label to LDAP-attribute mapping that turns those choices into a
  requested attribute set.
- `campus-directory/audience-selection`: Who appears in a listing — automated
  department/division feeds with affiliation-type narrowing and affiliates,
  manual CruzID lists, add/exclude overrides, and vacant positions.
- `campus-directory/ldap-data-access`: LDAP filter construction and escaping,
  connection and bind behavior, size and time limits, attribute narrowing, and
  transient caching of query results.
- `campus-directory/frontend-render`: Server-rendered list, tiled, and table
  layouts, including name linking to a local profile or to
  campusdirectory.ucsc.edu.
- `campus-directory/profile-pages`: The rewrite-rule profile route, inline
  rendering into page content, document title handling, the full per-person
  profile content — including courses taught and the scholarly sections, all
  read from the person's official campus directory record rather than from the
  PeopleSoft course feeds — and the `ucsc_profiles` shortcode as the alternate
  profile surface.

### Modified Capabilities

<!-- None — this is the first spec for an existing block; no prior capability exists to modify. -->

## Impact

- PHP: `classes/CampusDirectory.php`, `classes/CampusDirectoryAPI.php`,
  `classes/CampusDirectoryShortcode.php`, `classes/SiteSettings.php`,
  `templates/CampusDirectoryTemplate.php`, `templates/DirectoryProfileTemplate.php`
- JS: `src/blocks/CampusDirectory.js`, `src/components/CampusDirectory/*`
  (`PageLayout`, `PeopleAndInformation`, `AutomatedFeeds`, `InformationToDisplay`,
  `InformationToDisplayTable`, `CheckboxGroupControl`,
  `CampusDirectoryDepartmentDropdown`), `src/components/DivisionDropdown.js`
- CSS: `src/components/CampusDirectory/{editor,campusdirectory,directoryprofile,directoryprofileshortcode}.css`
- **Runtime dependency**: the PHP **LDAP** extension must be present, and the
  host must reach `ldap-blue.ucsc.edu` — which from a developer machine requires
  the UCSC **VPN**. This is why the local Docker image is custom-built.
- **Configuration dependency**: `ldap_api_key`, `ldap_cn`, and `ldap_url` read
  from site options with network-level (`get_site_option`) precedence.
- REST namespace `ucscgutenbergblocks/v1` — public, unauthenticated.
- Rewrite rule and `directoryprofilecruzid` query var are registered globally.
- Transient cache keys: `md5(<filter>) . '_l'` for list views and
  `md5(<filter>) . '_p'` for profile views.
- No new npm or Composer dependencies; uses the existing `@wordpress/scripts`
  build toolchain.
