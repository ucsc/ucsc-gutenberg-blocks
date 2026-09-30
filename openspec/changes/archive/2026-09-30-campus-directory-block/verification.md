## Verification record — campus-directory-block

Verified 2026-09-30 against `origin/main` at `defbf56`. Evidence is code
reading (file:line) unless marked **[live]** (run in the wp-dev.ucsc Docker
`wp` container with LDAP results seeded into the transient cache, so no VPN
or directory was needed) or **[test]** (asserted by an existing suite).
Editor tasks marked "running editor" were closed with code + Jest evidence;
the block editor itself was not driven.

### Drift since the baseline commit (b6a9aa0)

- `CheckboxGroupControl` gained a React `key` (WPM-130) — no behavior change.
- `DirectoryProfileTemplate.php` wraps `linkify()` in `function_exists` so the
  template can be included twice in one request — no behavior change.
- WPM-154 did not touch `cddepartmentcode()`; it already used
  `wp_remote_get()` with a 30s timeout at the baseline.

### Group 1 — field customization

- 1.1 Confirmed — 15 labels in `CampusDirectoryAPI::getInformationToDisplay()`
  (`classes/CampusDirectoryAPI.php:53-69`) match `InformationToDisplay.js:47-63`;
  the table list is the same minus Photo. Correction: Mailing Address maps to
  `combineaddressfields`, which no record has, so it never renders (D5).
- 1.2 Confirmed (`InformationToDisplay.js:65-74`,
  `InformationToDisplayTable.js:63-68`) **[test]** "passes the expected
  default checked labels".
- 1.3 Confirmed — no Photo in the table list **[test]**; the table branch of
  `CampusDirectoryTemplate.php:25-75` emits no `<img>`.
- 1.4 Confirmed by code — `CheckboxGroupControl.js:18-30` writes every label
  (true/false) on first mount and `:53-59` re-serializes the full object on
  each change.
- 1.5 Confirmed by code — list/tiled and table write separate attributes
  (`strInformationTypes` / `strInformationTypesTable`) and each component only
  touches its own (`PeopleAndInformation.js:259-274`).
- 1.6 Confirmed — `CampusDirectory::theHTML()` sets
  `objInformationTypesTable = []` when absent (`classes/CampusDirectory.php:128-129`)
  **[test]** "all str* attributes absent renders ... emits no warnings".
- 1.7 Confirmed — **[test]** `InformationToDisplay.test.js` "disables the
  checkbox when linkToProfile is false"; template links only when
  `linkToProfile` (`CampusDirectoryTemplate.php:48,92-98,150`).

### Group 2 — remaining capabilities

- 2.1 Confirmed with a correction — 17 attributes
  (`src/blocks/CampusDirectory.js:15-33`); `PageLayout` defaults list
  **[test]**; requirements gate `:74-82,130-160`. The publish guard checks the
  *active* scope and is correct — see 3.2.
- 2.2 Confirmed **[live]** — generated filters:
  - All faculty → `(&(ucscpersonpubdepartmentnumber=CSE)(ucscpersonpubaffiliation=Faculty))`
    (All wins over individual types)
  - Regular Staff only → `...(&(ucscpersonpubaffiliation=Staff)(!(|(ucscpersonpubstafftype=Researcher)(ucscPersonIsPostDoc=TRUE))))`
  - all three staff → `(ucscpersonpubaffiliation=Staff)`
  - faculty + grad → `(|(...Graduate)(...Faculty))` ANDed with scope
  - nothing selected → empty filter (no query)
- 2.3 Confirmed **[live]** — `manualAdd` + exclude against an empty feed
  yields `""`; **[test]** vacancy tests (WPM-172). Correction: a manual-list
  vacancy is still sent as a `uid` clause (spec reworded), and with name
  linking on it links to an empty profile with a PHP warning
  (`CampusDirectoryTemplate.php:48,93`) (D6).
- 2.4 Confirmed with a correction — 50 applies only to automated feeds whose
  active scope is `---` (`deptOrDivSet` is cleared only in
  `processDeptDivFilterString()`'s else-branch, `CampusDirectoryAPI.php:363-365`);
  manual lists, profiles, and the shortcode get 1000. Keys `md5(q)._l/_p`,
  TTL 600/60 (`:96-106`) **[test]**.
- 2.5 Mostly confirmed **[live]** — department `C*)(uid=*` becomes
  `C\2a\29\28uid=\2a`; CruzID paths escaped **[test]** (profile-route
  injection tests); `doLDAPQuery()` returns early on `""` (`:144`).
  **Deviation D1:** faculty-type keys are unescaped (`:289-292`); a saved key
  `x)(uid=*` produced `(&(ucscpersonpubaffiliation=Faculty)(ucscpersonpubfacultytype=x)(uid=*))`.
- 2.6 Confirmed for list/tiled **[live]** fixture render: mailto, website
  label split, multi-value mail. Corrections (spec updated): only email and
  website render multiple values (title "Chair" dropped); the table layout
  renders first values only, raw website text, a CSS-hidden duplicate, and
  "Undefined array key" warnings for absent fields (D4). Portrait fallback
  and alt text **[test]**.
- 2.7 Confirmed with a correction — `WP::parse_request()` resolves
  `/directory/jdoe/` to `directoryprofilecruzid=jdoe` **[live]**;
  `directory_profile_title()` returned "Jane Doe" for a seeded record and left
  "Host Page" for an unknown CruzID **[live]**; the four inline guards
  **[test]**. But `template_include` swaps to the standalone template whenever
  the query var is set (`CampusDirectory.php:17-24`, WPM-114), so the inline
  `the_content` path is not reached by ordinary requests — spec reworded.
- 2.8 Confirmed **[test]** — `DirectoryProfileTemplateTest.php` covers every
  field, multi-values, Courses Taught, all scholarly headings, omission of
  absent sections, and the not-found message with the escaped CruzID.
- 2.9 Confirmed — `ucscpersonpubfacultycourses` rendered from the profile
  record (`DirectoryProfileTemplate.php:274-278`); grep for `ucsc/v1`,
  `Course_Schedule`, `coursecatalog` across Campus Directory PHP/JS: no hits.
- 2.10 Confirmed with deviations **[live]** — 16 booleans, photo/name/
  profilelinks default true, `"true"`/`"false"` coercion, grid default
  (`classes/CampusDirectoryShortcode.php:26-56,90-94`). See D2, D3.

### Group 3 — deviations

- 3.1 **Listing template: resolved.** Every interpolation in
  `CampusDirectoryTemplate.php` now goes through `esc_html`/`esc_url`/
  `esc_attr`. Shortcode: all live output paths escaped; the unescaped
  `ucsc_cdp_read_more()` long-text path is only reachable from commented-out
  code. Profile template: covered by XSS tests **[test]**.
- 3.2 **Not reproduced — design risk was wrong.** The guard
  (`src/blocks/CampusDirectory.js:60-72`) locks on the *active* scope being
  `---`; a stale value in the inactive control does not unlock it; both
  dropdowns write `---` on mount (`CampusDirectoryDepartmentDropdown.js:15-19`,
  `DivisionDropdown.js:14-18`) **[test]** "locks post saving when switching
  back to a department placeholder". Spec and design corrected.
- 3.3 **Mostly resolved.** `cddepartmentcode()` uses `wp_remote_get()` with a
  30s timeout and returns (does not cache) a `WP_Error` on failure
  (`classes/SiteSettings.php:38-90`). Residual: still public and scrapes
  HTML; if the page loses its `<select>`, a `---`-only list is cached for a
  week. Also, `getDirDropdowns()` (LDAP-derived, 24h) is dead code — its only
  caller is commented out (`SiteSettings.php:95-96`).
- 3.4 Confirmed — `ldap_escape(..., LDAP_ESCAPE_FILTER)` in `buildUidFilter()`
  (`CampusDirectoryAPI.php:122`) and `processDeptDivFilterString()`
  (`:345-347`). The CruzID/department injection finding is closed; the
  faculty-type path (D1) is new and separate.
- 3.5 Not opened as an OpenSpec change: 3.1–3.3 are resolved or not
  reproduced. New deviations below are proposed as backlog tickets instead.

### New deviations found in this pass

- **D1 — unescaped faculty-type keys in the LDAP filter**
  (`CampusDirectoryAPI.php:289-292`). Requires an author-crafted block
  attribute; can add clauses within the scoped AND.
- **D2 — shortcode `profilelinks="false"` blanks the name**
  (`CampusDirectoryShortcode.php:305` discards `esc_html()`'s return) **[live]**.
- **D3 — shortcode attributes that do nothing / misalign.** `officehours`
  (mixed-case key) and seven commented-out attributes render nothing **[live]**;
  an unknown CruzID leaves an empty trailing card with warnings, because
  results are paired to `cruzids` by index (`:103-116`) **[live]**; an
  undefined `$options` warning on every render (`:93`).
- **D4 — table layout fidelity**: first value only, raw website text,
  hidden duplicate value, PHP warnings for absent fields
  (`CampusDirectoryTemplate.php:52-66`) **[live]**; no `h-card` on table rows.
- **D5 — Mailing Address never renders in listings** (maps to
  `combineaddressfields`; `CampusDirectoryAPI.php:66`) **[live]**.
- **D6 — vacant entries with name linking** link to
  `?directoryprofilecruzid=` and emit warnings.
