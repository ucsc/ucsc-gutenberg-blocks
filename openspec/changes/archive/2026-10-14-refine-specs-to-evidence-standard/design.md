## Context

The standard (ucsc-wp-block-dev ADR-117) separates two questions for every
requirement:

- **Policy:** is this behavior intended? Proposed, accepted, superseded or
  unresolved.
- **Evidence:** how do we know it ships? Code-inspected, test-inspected,
  test-executed, runtime-observed or unverified, at a stated revision.

The baseline used OpenSpec's SHALL to describe shipped behavior. That is
still the convention: SHALL here means "this is what ships", not "this was
approved". The refinement does not rewrite that convention. It marks the
places where shipped behavior is known to be wrong or undecided, so SHALL text
is never read as approval of a bug.

## Decisions

### D1. Label format

Two labeled paragraphs, placed after the requirement's normal text:

- `**Known defect (<ID>, <Jira key | no Jira yet>):** <current behavior>.`
  Used when the behavior is wrong by the spec's own intent, by an existing
  Jira bug, or by a verification deviation.
- `**Open policy question (<Jira key>):** <the question>.` Used when the
  intended behavior has not been decided.

The requirement text before the label keeps describing what ships. A label
never states the fix as a SHALL; the fix belongs to the Jira issue or a later
change.

Alternative considered: a separate "Known defects" section per spec. Rejected
because an agent retrieving one requirement would miss it.

### D2. Defect IDs

The 2026-09-30 verification pass numbered deviations per change (`D1`...).
They become block-prefixed and stable: `CD-D1`..`CD-D6` (campus-directory),
`CS-D1`..`CS-D3` (class-schedule; CS-D3 is new, see Open Questions), `CC-D1`..`CC-D7` (course-catalog). New
defects found from Jira in this pass use the Jira key alone. IDs are never
reused; a fixed defect's label is removed by the change that fixes it.

### D3. Scenarios that pin defects

A scenario describing defective behavior keeps its WHEN/THEN text (it is
accurate, and tests may assert it). A **new** scenario that pins a defect gets
`(known defect <ID>)` appended to its name. An **existing** scenario keeps its
name, because OpenSpec matches scenarios by name and a rename would drop it on
archive; the requirement's Known defect paragraph names the scenario instead.
Either way a characterization test stays traceable without presenting the
behavior as intended.

### D4. Drift from WPM-205

Block API version 3 is recorded as shipped behavior: code at `aca712c`, Jest
and PHP wiring assertions, and the e2e editor tests updated in `1075b1d`.
WPM-205 is still In Progress in Jira, but the code is on `main`.

### D5. Scope

Only requirements whose meaning changes are MODIFIED. Requirements that already
meet the standard are left alone. Accessibility tickets are referenced only
where they contradict or qualify a requirement's text. Tickets about
behavior no requirement covers (WPM-110 cancelled status, WPM-82 titles,
WPM-11 day/time search) are listed below rather than forced into a spec.

## Risks / Trade-offs

- **Labels go stale.** A fix that does not remove its label leaves the spec
  claiming a defect that is gone. Mitigation: the standard's feature/fix flow
  updates the delta spec in the same branch.
- **Jira state moves.** Statuses are read 2026-10-14; the labels cite keys,
  not statuses, so a closed ticket does not make a label wrong until the code
  changes.

## Open Questions

Deviations with no Jira issue. Proposed as WPM backlog tickets (block-name
prefix, parent WPM-115 where it fits); **not filed without approval**:

| ID | Summary |
|---|---|
| CD-D1 | LDAP faculty-type keys from `strFacultyTypes` are not escaped (`CampusDirectoryAPI.php:291`) |
| CD-D2 | `[ucsc_profiles profilelinks="false"]` renders an empty name (`CampusDirectoryShortcode.php:305` discards `esc_html()`) |
| CD-D4 | Table layout cells: first value only, raw text, hidden duplicate value, warnings for absent fields, no `h-card` (`CampusDirectoryTemplate.php`) |
| CD-D5 | Mailing Address field never renders in listings (`combineaddressfields`, `CampusDirectoryAPI.php:66`) |
| CD-D6 | Vacant entries with name linking link to `?directoryprofilecruzid=` and warn |
| CS-D1 | Internal `rest_do_request()` errors are misread as empty data (`ClassSchedule.php:146,159`, `CourseDetailTemplate.php:25`); test stubs hide it |
| CS-D2 | Time column sorts by leading hour (`parseFloat`, `classschedule.js:133`) |
| CS-D3 | Editor dropdown feed caches a `---`-only list for a week when a 2xx body cannot be parsed (`SiteSettings.php`); shared by Class Schedule and Course Catalog |
| CC-D1 | Empty `<catalog/>` response reported as an outage and never cached (`CourseCatalog.php:246`) |
| CC-D2 | Unconfigured block warns and queries PeopleSoft (`CourseCatalog.php:195,199`) |
| CC-D3 | Search matches markup and the hidden level rank (`tablesorter.js:116,124`) |
| CC-D5 | Units column sorts lexically |
| CC-D7 | Fixed element IDs duplicate with two blocks on a page (`CourseCatalog.php:270,286,292-296`) |

CD-D3 is covered by WPM-194 and WPM-195, CC-D4 by WPM-109, CC-D6 by WPM-209.
CS-D3 is a new ID for the residual gap recorded in the 2026-09-30
class-schedule verification (3.1). Other Jira bugs labeled in the specs:
WPM-12, WPM-106, WPM-107, WPM-108, WPM-183, WPM-193, WPM-197, WPM-206,
WPM-207.

Policy questions referenced in the specs: WPM-96 (catalog cache length),
WPM-111 (term dropdown navigates on change), WPM-156 (dormant PeopleSoft
target override), WPM-198 (photo and name as two links).

Not placed in a spec (no requirement covers the behavior): WPM-11, WPM-82,
WPM-110.
