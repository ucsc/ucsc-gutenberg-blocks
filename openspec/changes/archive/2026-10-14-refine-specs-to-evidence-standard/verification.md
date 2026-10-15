## Verification record — refine-specs-to-evidence-standard

Verified 2026-10-14 against `main` at `1075b1d` (branch
`dev/henryh/WPM-196_spec_standard_refinement`). Jira read 2026-10-14.

Evidence tags: plain file:line = code read; **[test]** = assertion read and
executed in Docker on this date; **[jira-live]** = a live probe recorded in
the cited Jira comment (2026-10-09, `1075b1d`), not re-run here;
**[2026-09-30]** = carried from the archived baseline verification, with the
code unchanged since (`git diff defbf56..1075b1d` touches only the lines in
group 1).

### 1. Drift: block API version 3 (WPM-205, `aca712c`)

| Block | PHP | Editor | Evidence |
|---|---|---|---|
| Course Catalog | `classes/CourseCatalog.php:53` | `src/blocks/CourseCatalog.js:11,28` | **[test]** `CourseCatalog.test.js:55`; `PluginWiringTest.php:266` |
| Class Schedule | `classes/ClassSchedule.php:116` | `src/blocks/ClassSchedule.js:14,38` | **[test]** `ClassSchedule.test.js:69`; `PluginWiringTest.php:248` |
| Campus Directory | `classes/CampusDirectory.php` (`register_block_type`) | `src/blocks/CampusDirectory.js:13,56` | **[test]** `CampusDirectory.test.js:86`; `PluginWiringTest.php:236` |

Runs: Jest 3 passed (55 skipped by `-t`); PluginWiringTest 47/47 passed.

### 2. Course Catalog

- **CC-D1** `classes/CourseCatalog.php:245-246` (`if (!$xmlBody)`) **[2026-09-30]** live.
- **CC-D2** `CourseCatalog.php:195,199` read attributes without defaults **[2026-09-30]** live.
- **CC-D3** `src/components/CourseCatalog/tablesorter.js:116,124` match on `innerHTML`.
- **CC-D4 / WPM-109** row toggle and expand/collapse are mouse-only; `CourseCatalog.php:292-293` `<a>` without `href`.
- **CC-D5** Units cell has no sort-key span (`CourseCatalog.php:316`).
- **CC-D6 / WPM-209** `CourseCatalog.php:316-317` echo upstream fields raw **[jira-live]**.
- **CC-D7** fixed IDs at `CourseCatalog.php:270,286,292-293,296`.
- **WPM-108** headers click-only (issue description; not re-probed).
- **WPM-96** cache lifetime `CourseCatalog.php:261` (`WEEK_IN_SECONDS`).
- **WPM-156** override call sites commented out **[jira-live]** (2026-10-09 comment).
- **WPM-207** dropdown HTTP-error hang **[jira-live]**; `tests/e2e/failure-paths.spec.js` holds four tests tagged RED for it.
- **CS-D3** dropdown feed `---`-only cache **[2026-09-30]** (class-schedule verification 3.1).

### 3. Class Schedule

- **CS-D1** `classes/ClassSchedule.php:144-146,157-159`, `templates/CourseDetailTemplate.php:23-25` check only `is_wp_error()` after `rest_do_request()` **[2026-09-30]**.
- **CS-D2** `src/components/ClassSchedule/classschedule.js:133-134` `parseFloat`.
- **WPM-111** term dropdown navigates on change (issue description; behavior matches the existing scenario).
- **WPM-197** Filter button lacks `aria-haspopup` / `aria-controls` / `aria-expanded` (issue description, DOM probe 2026-10-05).

### 4. Campus Directory

- **CD-D1** `classes/CampusDirectoryAPI.php:291` interpolates the faculty-type key.
- **CD-D2** `classes/CampusDirectoryShortcode.php:305` discards `esc_html()` **[2026-09-30]** live.
- **CD-D3 / WPM-194** undefined `$options` passed at `CampusDirectoryShortcode.php:91,93`; positional pairing **[jira-live]**.
- **CD-D3 / WPM-195** office hours key case **[jira-live]**.
- **CD-D4** table cell rendering `templates/CampusDirectoryTemplate.php` **[2026-09-30]** live.
- **CD-D5** `CampusDirectoryAPI.php:66` maps Mailing Address to `combineaddressfields` **[2026-09-30]** live.
- **CD-D6** vacant entries with name linking **[2026-09-30]**.
- **WPM-12** empty table, PR open (2026-10-13 comment); the existing "Empty listing" scenario is unchanged.
- **WPM-106 / WPM-107 / WPM-198** issue descriptions (CSS and DOM evidence cited there).
- **WPM-183** title-lookup warnings **[jira-live]**.
- **WPM-193** shortcode website `TypeError` **[jira-live]**.
- **WPM-206** `CampusDirectoryAPI.php:146` honors `DOCKER_DEV`; the requirements endpoint (`CampusDirectory.php:29`) does not **[jira-live]**.

### Not verified here

- No browser run on this date: the stack was down, and no requirement's
  wording depends on a new runtime observation. Runtime claims are carried
  from the dated sources above.
- The WPM-89 version-label requirement belongs to the open
  `wpm-89-class-schedule-dynamic-version` change and is not covered here.
