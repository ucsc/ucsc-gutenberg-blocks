# ucsc-gutenberg-blocks — Work Backlog
_Last updated: 2026-09-04_

---

## Unmerged branches (needs PR / review)

| Branch | Commits ahead | Summary |
|---|---|---|
| `dev/henryh/WPM-112_reset_filters_search` | 1 | Reset filters on search |
| `dev/henryh/WPM-115_test_coverage` | 3 | CheckboxGroupControl Jest tests (10), Jira candidate docs |
| `dev/henryh/WPM-131_course_schedule_api_tests` | 1 | CourseScheduleAPITest (39 tests) — SHA may already be in main via merge |
| `dev/henryh/WPM-132_fix_xss_campus_directory_shortcode` | 1 | XSS fix + 29 tests for CampusDirectoryShortcode |
| `dev/henryh/WPM-133_dropdown_js_tests` | 5 | Dropdown JS tests + error state; also carries WPM-127 .distignore + HERMES.md |
| `dev/henryh/test_coverage_v2` | 2 | Jira candidate docs WPM-131–137, openspec proposal |

**Note:** WPM-133 branch contains WPM-127 commits (.distignore, HERMES.md) that
are already merged to main on the WPM-127 branch. Rebase before opening PR.

---

## Open test coverage Jira issues

### Highest priority (unassigned)

| Key | Summary | Type |
|---|---|---|
| WPM-128 | Test tablesorter.js — sort comparators, direction toggle, edge cases (131 loc) | Jest |
| WPM-129 | Test CourseDetailTemplate.php rendering and escaping (461 loc) — add to CourseCatalogTest | PHP template |

### Medium priority (unassigned)

| Key | Summary | Type |
|---|---|---|
| WPM-134 | Test SiteSettings.php failure paths — remote fetch errors, unparseable HTML | PHP |

### campus-directory gaps — split from WPM-118 into individual Tasks (2026-09-04)

| Key | Summary | Type | Notes |
|---|---|---|---|
| WPM-143 | Test CampusDirectoryShortcode.php — attribute parsing, sanitization, render (410 loc) | PHP | WPM-132 branch has 29 initial assertions; check before starting |
| WPM-144 | Test CampusDirectoryTemplate.php rendering and escaping (164 loc) | PHP template | Follow ClassScheduleTemplateTest output-buffering pattern |
| WPM-145 | Jest tests for InformationToDisplay.js editor control (127 loc) | Jest | |
| WPM-146 | Jest tests for InformationToDisplayTable.js editor control (121 loc) | Jest | |
| WPM-147 | Jest tests for CheckboxGroupControl.js editor control (67 loc) | Jest | WPM-115 branch has 10 initial assertions; check before starting |

---

## Test baseline (post WPM-127 merge, 2026-09-04)

| Suite | Passing |
|---|---|
| ClassScheduleTest.php | 66 |
| CourseScheduleAPITest.php | 39 |
| CourseCatalogTest.php | 29 |
| CampusDirectoryTest.php | 28 |
| CampusDirectoryShortcodeTest.php | 24 / 28 (4 intentional XSS fails) |
| ClassScheduleTemplateTest.php | 67 |
| **Total passing** | **253** |

---

## Suggested next order

1. Open PRs for WPM-112, WPM-132 — both are single clean commits, ready now.
2. Rebase + PR WPM-133 (drop the already-merged WPM-127 commits first).
3. WPM-128 — tablesorter.js Jest tests (self-contained, no dependencies).
4. WPM-144 — CampusDirectoryTemplate.php (short, same output-buffering pattern as WPM-127).
5. WPM-143 — CampusDirectoryShortcode.php (largest PHP gap; WPM-132 gives a head start).
6. WPM-129 — CourseDetailTemplate.php (461 loc, bigger scope).
7. WPM-134 — SiteSettings.php failure paths.
8. WPM-145/146/147 — remaining Jest editor controls.
