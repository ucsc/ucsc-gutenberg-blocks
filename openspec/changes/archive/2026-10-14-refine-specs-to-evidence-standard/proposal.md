## Why

The baseline specs for the three blocks (verified and archived 2026-09-30)
describe shipped behavior accurately, but they predate the evidence-backed
spec standard adopted by the `ucsc-wp-block-dev` plugin (its ADR-117,
`skills/develop/references/specification-generation.md`). Measured against
that standard they have three gaps:

1. **Defects read like policy.** Known defects are written as ordinary
   requirement text ("Upstream course fields are echoed without HTML
   escaping"), so a reader or agent cannot tell an intended rule from a bug
   that a test happens to pin. Only one place (the LDAP faculty-type clause)
   is labeled as a deviation.
2. **Jira is not linked.** Open WPM bugs and decisions that change what these
   requirements mean (WPM-96, WPM-109, WPM-111, WPM-193 to WPM-198, WPM-206,
   WPM-207, WPM-209 and others) are not referenced, and the 2026-09-30
   deviations (CD D1-D6, CS D1-D2, CC D1-D7) exist only in archived
   verification records.
3. **Drift since the baseline.** WPM-205 (`aca712c`) moved all three blocks to
   block API version 3 and wrapped each editor in `useBlockProps()`. No spec
   says so.

Tracked under WPM-196.

## What Changes

- Every requirement whose text describes defective behavior gets an explicit
  **Known defect** paragraph with a stable ID (`CD-Dn`, `CS-Dn`, `CC-Dn`) and
  its Jira key, or "no Jira yet". Scenarios that pin defective behavior are
  renamed to say so.
- Requirements touched by an undecided Jira question get an **Open policy
  question** paragraph naming the issue.
- Each block's registration requirement records block API version 3 and the
  `useBlockProps()` wrapper, with a scenario.
- Shared editor dropdown failure behavior (WPM-207) and the dropdown feed's
  week-long cache of a placeholder-only list are recorded on the dept/subject
  selection requirements of Class Schedule and Course Catalog.
- A fresh `verification.md` records evidence at `main` @ `1075b1d`.

No code changes. No behavior is redefined: where intended behavior has not
been decided, the spec says so instead of choosing.

## Capabilities

### New Capabilities

_None._

### Modified Capabilities

- `course-catalog/block-registration`, `course-catalog/data-fetch`,
  `course-catalog/frontend-render`
- `class-schedule/block-registration`, `class-schedule/rest-api`,
  `class-schedule/course-detail-pages`, `class-schedule/frontend-render`
- `campus-directory/block-registration`, `campus-directory/audience-selection`,
  `campus-directory/ldap-data-access`, `campus-directory/frontend-render`,
  `campus-directory/information-display`, `campus-directory/profile-pages`

## Impact

- `openspec/specs/**`: requirement text and scenario names only.
- No PHP, JS, CSS, test or build changes.
- The open `wpm-89-class-schedule-dynamic-version` change ADDs a different
  requirement to `class-schedule/block-registration`; the two changes do not
  touch the same requirement.
