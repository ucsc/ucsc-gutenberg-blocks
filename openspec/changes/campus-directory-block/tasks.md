## 1. Confirm the field-customization contract

The headline capability. Each task confirms a requirement in
`specs/campus-directory/information-display/spec.md` against shipped behavior
and records the result in this change; none of them edits block code.

- [ ] 1.1 Confirm the 15 selectable fields and their directory-field mapping match `CampusDirectoryAPI::getInformationToDisplay()` exactly — verify by diffing the spec's table against the `$dictFieldInformation` map and the label arrays in `InformationToDisplay.js` / `InformationToDisplayTable.js`; note any label present in one and not the other.
- [ ] 1.2 Confirm the per-layout defaults (8 for list/tiled, 4 for table) match `checkedByDefault` in both components — verify by comparing the spec's default lists against the arrays in source.
- [ ] 1.3 Confirm the table layout omits Photo — verify by checking `arrInformationToDisplay` in `InformationToDisplayTable.js` and that no table branch of `CampusDirectoryTemplate.php` renders a portrait.
- [ ] 1.4 Confirm the persistence contract stores every field including deselected ones — verify in the running editor (`run` skill) by selecting one field, saving, and reading the post's stored `strInformationTypes` value for `false` entries.
- [ ] 1.5 Confirm layout switching preserves both selections — verify in the running editor by configuring table fields, switching to list and changing fields, switching back, and observing the table selection intact.
- [ ] 1.6 Confirm a block saved without `strInformationTypesTable` renders without error — verify by rendering a fixture attribute set with that key absent through `CampusDirectory::theHTML()` in the PHP harness.
- [ ] 1.7 Confirm name-linking behavior and the disabled link-destination control — verify in the running editor that disabling name linking disables the campusdirectory.ucsc.edu checkbox, and that the rendered output has no name links.

## 2. Confirm the remaining capability specs

- [ ] 2.1 Confirm `block-registration` — verify block name, attribute list, panel structure, list default layout, and the requirements-endpoint gate against `src/blocks/CampusDirectory.js` and `classes/CampusDirectory.php`; note the publish-guard deviation in 3.2 rather than fixing it here.
- [ ] 2.2 Confirm `audience-selection` affiliation semantics — verify the "All faculty", "all three staff types", and "Regular Staff excludes unselected specialized types" clauses against `processFacultyFilterString()` and `processStaffFilterString()` by asserting generated filter strings in the PHP harness.
- [ ] 2.3 Confirm `audience-selection` overrides and vacant positions — verify that an exclude list against an empty feed yields an empty filter, and that `%Name%Title%` entries render without a directory lookup, by asserting against `processAddExcludeFilterString()` and `addVacantPositions()` in the PHP harness.
- [ ] 2.4 Confirm `ldap-data-access` ceilings and cache keys — verify the 50/1000 size limits, the `_l`/`_p` cache-key suffixes, and the 600s/60s TTLs against `doLDAPQuery()` and `getCampusDirData()`.
- [ ] 2.5 Confirm `ldap-data-access` escaping and the empty-filter guard — verify by passing values containing LDAP filter metacharacters through the manual list, add, exclude, and department paths and asserting the generated filter treats them literally; assert `doLDAPQuery()` returns early on an empty filter.
- [ ] 2.6 Confirm `frontend-render` field presentation — verify mailto links, website label-vs-URL split, combined office location, multi-value rendering, and the portrait fallback by rendering fixture people through `CampusDirectoryTemplate.php` in the PHP harness.
- [ ] 2.7 Confirm `profile-pages` routing and guards — verify `/directory/<cruzid>/` resolves, the query-parameter form works, the document title is set to the person's name, and the four inline-render guards hold, using the `run` skill against the Docker stack.
- [ ] 2.8 Confirm `profile-pages` profile content — verify the identity/contact fields, the Courses Taught section, and the ten scholarly sections render from a fixture record through `DirectoryProfileTemplate.php`, that empty sections are omitted entirely, and that the not-found message names the CruzID.
- [ ] 2.9 Confirm course information is sourced from the directory record only — verify `ucscpersonpubfacultycourses` arrives in the profile's full-entry query and that no Campus Directory code path calls the `ucsc/v1` course-schedule or course-catalog endpoints; grep the block's PHP and JS for those routes and assert no hits.
- [ ] 2.10 Confirm `profile-pages` shortcode defaults — verify the 16 boolean attributes, the photo/name/profilelinks defaults, string `"true"`/`"false"` coercion, and grid-vs-list selection against `CampusDirectoryShortcode::ucsc_cdp_profile_render_shortcode()`.

## 3. Record deviations rather than fixing them

This change is a baseline spec; remediation belongs to a follow-up change.
These tasks produce a written record, not code edits.

- [ ] 3.1 Record the output-escaping gap — confirm which interpolations in `CampusDirectoryTemplate.php` and `CampusDirectoryShortcode.php` still fail the `frontend-render` escaping requirement, and list them with file:line in this change's notes; verify the profile template's compliance separately.
- [ ] 3.2 Record the publish-guard deviation — confirm that `deptOrDiv='dept'`, `department='---'`, and a stale valid `division` permits saving, contrary to `block-registration`; capture the reproduction steps.
- [ ] 3.3 Record the `SiteSettings` dropdown-endpoint risk — confirm the endpoint is public and fetches upstream with `file_get_contents()` on a cold cache; note it against `ldap-data-access`'s graceful-degradation requirement.
- [ ] 3.4 Confirm the LDAP-injection finding from `audit-campus-directory.md` is closed — verify `ldap_escape()` is applied in `buildUidFilter()` and `processDeptDivFilterString()`, and mark that audit finding resolved so it is not re-reported.
- [ ] 3.5 Open a follow-up change for the deviations recorded in 3.1–3.3 — verify by `openspec list` showing the new change; do not implement it here.

## 4. Close out

- [ ] 4.1 Run the existing PHP and Jest suites in Docker to confirm no drift while documenting — verify both suites pass at their current baseline via the `validate` skill (`php`, then `jest`); no host Node or PHP.
- [ ] 4.2 Update the specs for every discrepancy found in groups 1–2 so the baseline describes shipped behavior, not intended behavior — verify with `openspec validate campus-directory-block --strict`.
- [ ] 4.3 Confirm the change is ready to archive — verify `openspec status --change campus-directory-block` reports all artifacts complete and that `specs/campus-directory/**` is the intended baseline for `openspec/specs/`.
