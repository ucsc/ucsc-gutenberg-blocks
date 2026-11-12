# Verification: campus-directory-caching-and-freshness-spec

Reference commit: `51b82d5` (`main`, v1.3.1). `git log 51b82d5..origin/main`
over the directory classes, `SiteSettings.php` and the directory templates was
empty on 2026-11-12.

Evidence levels: **test-inspected** means a `check()` in `tests/php/` asserts
the behavior; **code-inspected** means only the code was read. Test lines are
in `tests/php/CampusDirectoryTest.php` (CDT) unless another file is named.

## campus-directory/caching

### Directory data is cached per query and view

- Code: `classes/CampusDirectoryAPI.php:147-153` (key is `md5(query)` plus
  `_l`, `_lp` or `_p`; `set_transient` is per site).
- Repeat view is served from cache: test-inspected, CDT:587, CDT:601.
- Same settings on another page share the entry: code-inspected (follows from
  the key containing only the query and view; no test builds two pages).
- Listing and profile caches are separate: test-inspected, CDT:499.
- A listing without photos does not serve one with photos: test-inspected,
  CDT:1349.

### Directory data cache lifetimes

- Code: `classes/CampusDirectoryAPI.php:161-172` (600 s / 60 s, skipped when
  `ldapFailed`), `:257-258` (only errno 0 or 4 counts as answered).
- Successful lookup lifetime: test-inspected, CDT:598.
- Empty result expires quickly: test-inspected, CDT:588.
- Failed lookup is not cached: test-inspected, CDT:1412, CDT:1421, CDT:1433.
- Size-limited search is cached: test-inspected, CDT:1440.
- Photo bytes not kept in a listing's entry: test-inspected, CDT:1335.
  Profile entry holds the photo: code-inspected
  (`CampusDirectoryAPI.php:151,158`; `templates/DirectoryProfileTemplate.php:86-90`).

### Directory outage back-off

- Code: `classes/CampusDirectoryAPI.php:224-228` (5 min after handshake),
  `:264-266` (1 min after bind refusal).
- Silent directory: test-inspected, CDT:631, CDT:639.
- Refused bind: test-inspected, CDT:1413.
- Per-site scope and coverage of profiles and the photo route:
  code-inspected (`set_transient`; the photo route calls the same lookup,
  `classes/CampusDirectory.php:57`).

### Photos are cached separately from directory data

- Code: `classes/CampusDirectoryPhotoCache.php:63-67` (store site),
  `:237` (hash in file name), `:255` (private), `:380-392` (Media Library
  hiding), `:1-14` (LDAP `jpegphoto` only).
- One stored photo serves every site: test-inspected, CDT:1274, CDT:1279,
  CDT:1286.
- Changed photo gets a new address: test-inspected, CDT:1148, CDT:1182-1184.
- Private and not from campusdirectory photo.php: test-inspected, CDT:1155,
  CDT:1400. Media Library hiding: code-inspected.

### Photo refresh follows fresh directory data

- Code: `classes/CampusDirectoryPhotoCache.php:122-186`;
  `classes/CampusDirectory.php:205` (listing hands over fresh entries);
  `templates/DirectoryProfileTemplate.php:86-91` (profile hands over its record).
- Photo change appears with the next fresh listing: test-inspected, CDT:1380,
  CDT:1182.
- Cached listing reuses stored photos: test-inspected, CDT:1211, CDT:1386.
- Hidden photo is removed: test-inspected, CDT:1192, CDT:1392-1393.
- Remembered "no photo": test-inspected, CDT:1194, CDT:1217,
  `CampusDirectoryTemplateTest.php:210`.
- Never queries LDAP itself: test-inspected, CDT:1156.

### Photo storage limits and cleanup

- Code: `classes/CampusDirectoryPhotoCache.php:43,174-183` (write limit,
  failed write), `:332-375` (prune), `:334-335` (once a night).
- Write limit: test-inspected, CDT:1239-1242.
- Failed write gives the placeholder: test-inspected, CDT:1248.
- Nightly cleanup: test-inspected, CDT:1297, CDT:1303-1306, CDT:1311,
  CDT:1324.

### Photo route

- Code: `classes/CampusDirectory.php:41,49-72`.
- Linked only when nothing is stored: test-inspected, CDT:1222, CDT:1240,
  CDT:1383.
- Redirect to stored file or placeholder, `X-Directory-Photo-Source`,
  one-hour `Cache-Control` only for `ldap`/`none`: code-inspected only. No test
  (the handler calls `exit`).

### Editor department list cache

- Code: `classes/SiteSettings.php:58-113` (`WEEK_IN_SECONDS`; 5-minute failure
  back-off at `:65`, `:75`, `:105`).
- Department list is reused: test-inspected, `SiteSettingsTest.php:266`.
- Failed department fetch: test-inspected, `SiteSettingsTest.php:178`, `:181`,
  `:221`, `:224`.

### Freshness of a rendered block

- Code: `classes/CampusDirectory.php:177` (server `render_callback`); no page
  cache header anywhere in `classes/` or `templates/` (only the photo route's,
  `CampusDirectory.php:70`).
- New page shows current data: follows from the lifetimes above;
  code-inspected.
- Host page cache serves an older copy: runtime-observed outside the plugin,
  `science.ucsc.edu` returned `cache-control: max-age=3600, must-revalidate`
  and an unchanged `last-modified` on a second request (2026-11-12). The
  plugin's own comments assume host-cached HTML
  (`src/components/CampusDirectory/photofallback.js:1-7`).

## campus-directory/frontend-render: Portraits

- Code: `templates/CampusDirectoryTemplate.php:173-189`.
- Portrait renders with alt text: test-inspected,
  `CampusDirectoryTemplateTest.php:188`.
- Portrait fails to load: test-inspected (fallback URL present),
  `CampusDirectoryTemplateTest.php:189`.
- Vacant position portrait: code-inspected (`:178-179`, empty uid gives the
  placeholder).
- Lazy loading: test-inspected, `CampusDirectoryTemplateTest.php:192`.
- Stored portrait: test-inspected, `CampusDirectoryTemplateTest.php:207`.
- Person without a photo: test-inspected, `CampusDirectoryTemplateTest.php:210`.

## campus-directory/ldap-data-access: Results are cached (removed)

Every scenario carries over to `campus-directory/caching` with the evidence
above.

## Test run

2026-11-12, working tree at `51b82d5`, `php:8.1-cli` in Docker (the repo's
`tests/php/run-php-tests.sh` method, limited to the directory files):

- `CampusDirectoryTest.php`: 188/188 passed
- `CampusDirectoryTemplateTest.php`: 73/73 passed
- `DirectoryProfileTemplateTest.php`: 102/102 passed
- `SiteSettingsTest.php`: 63/63 passed
