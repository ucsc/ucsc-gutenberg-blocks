# Session notes — 2026-09-10: OpenSpec baseline reverse-engineering + Jira gap check

Working branch at the time: `feature/WPM-145_information_display_tests`.
This file is a handoff note, not part of the plugin — delete it once you've
folded anything useful into a real ticket/PR description, or leave it if
you want the trail.

## What happened

1. **Reverse-engineered OpenSpec baseline specs for all three blocks.**
   `openspec/changes/campus-directory-block` already had full delta specs
   (proposal, design, per-capability specs, tasks) — nothing there was
   touched except confirming it was ready. `class-schedule-block` and
   `course-catalog-block` had only a `proposal.md` stub each; I wrote the
   missing `design.md`, one `specs/<capability>/spec.md` per capability, and
   `tasks.md` for both, reading the actual PHP/JS (not guessing).

2. **Corrected `course-catalog-block/proposal.md`.** The original draft
   assumed a separate `CourseCatalogTemplate.php` template file and a
   15-minute REST-proxy cache. Neither is true: `CourseCatalog::theHTML()`
   builds markup inline with no template file, and the block fetches a raw
   XML feed from PeopleSoft's `HttpListeningConnector` (not the `ucsc/v1`
   REST-proxy pattern Class Schedule uses), cached for a full week, with
   prod/QA target selection and a dormant request-level override mechanism.

3. **Synced all three changes' delta specs into `openspec/specs/`** via the
   `openspec-sync-specs` skill — this is the *first* time `openspec/specs/`
   has had any content. All 13 capability specs validate
   (`openspec validate --specs`). The three changes remain **open** in
   `openspec/changes/` with their `tasks.md` verification checklists
   unchecked (0/25 campus-directory, 0/27 class-schedule, 0/19
   course-catalog) — folding to baseline now was deliberate, per your
   direction, rather than gating on task completion.

4. **Compared the new specs against the `WPM-115` "WPM Test Coverage" Jira
   epic** (children WPM-118/119/120 per block, plus WPM-131/133/134/137
   etc.) and took four actions — see below.

## New/changed files

```
openspec/changes/class-schedule-block/design.md          (new)
openspec/changes/class-schedule-block/tasks.md            (new)
openspec/changes/class-schedule-block/specs/class-schedule/block-registration/spec.md   (new)
openspec/changes/class-schedule-block/specs/class-schedule/rest-api/spec.md             (new)
openspec/changes/class-schedule-block/specs/class-schedule/frontend-render/spec.md      (new)
openspec/changes/class-schedule-block/specs/class-schedule/course-detail-pages/spec.md  (new)

openspec/changes/course-catalog-block/proposal.md         (corrected — see item 2 above)
openspec/changes/course-catalog-block/design.md           (new)
openspec/changes/course-catalog-block/tasks.md            (new)
openspec/changes/course-catalog-block/specs/course-catalog/block-registration/spec.md   (new)
openspec/changes/course-catalog-block/specs/course-catalog/data-fetch/spec.md          (new)
openspec/changes/course-catalog-block/specs/course-catalog/frontend-render/spec.md     (new)

openspec/specs/campus-directory/**/spec.md   (new baseline, 6 capabilities)
openspec/specs/class-schedule/**/spec.md     (new baseline, 4 capabilities)
openspec/specs/course-catalog/**/spec.md     (new baseline, 3 capabilities)
```

None of this is committed yet — plain working-tree changes, per your "never
git commit or push" rule. `git status` / `git add` as you see fit.

## Jira actions taken (project WPM, epic WPM-115, cloudId
`24d95033-cc26-4d31-87b4-ce1c3d5b419d`)

- **WPM-129 re-parented** from WPM-120 (Course Catalog epic block) to
  WPM-119 (Class Schedule) — `CourseDetailTemplate.php` is only ever
  rendered via `ClassSchedule.php`'s rewrite/`template_include`, never by
  `CourseCatalog.php`. Its description was also corrected: it previously
  told whoever picks it up to add the test to `CourseCatalogTest.php`; now
  says `ClassScheduleTest.php`, with a pointer to the new
  `specs/class-schedule/course-detail-pages/spec.md`.
- **WPM-154** (new, Medium) — `SiteSettings::departmentcode()` /
  `subjectcode()` (PeopleSoft dept/subject dropdown source shared by Class
  Schedule + Course Catalog editors) use `curl_init()` with
  `CURLOPT_TIMEOUT => 0` (unlimited) and no error handling — same defect
  class WPM-134 just fixed in the sibling `cddepartmentcode()` method, but
  out of that ticket's scope.
- **WPM-155** (new, Low) — `CourseCatalog::restPermissionsCheck()` is dead
  code, never wired to a route in that class.
- **WPM-156** (new, Low) — decision/spike ticket: keep, harden, or remove
  Course Catalog's dormant PeopleSoft target-override mechanism
  (`isRequestOverrideAllowed()` / `getRequestTargetOverride()`), fully
  implemented but unreachable from its commented-out call sites.

## Still open / where to pick up

- **`tasks.md` verification checklists are unchecked** for all three
  changes. Each task is "confirm spec matches shipped behavior against
  file:line X" — not implementation work, but someone (or a future session)
  needs to actually run through them, ideally alongside `validate`
  (PHP/Jest suites) and `run` (live Docker stack) for anything LDAP- or
  browser-dependent.
- **Two open questions recorded in `class-schedule-block/design.md`:**
  whether to resolve the PeopleSoft environment-targeting asymmetry between
  Class Schedule (hardcoded prod, no QA override) and Course Catalog
  (prod/QA switch), and whether the legacy course-URL redirect rule should
  ever be retired.
- **Two open questions recorded in `course-catalog-block/design.md`:**
  whether to delete `restPermissionsCheck()` now vs. in a dedicated cleanup
  (tracked as WPM-155), and whether the PeopleSoft target override is still
  needed for any active QA workflow (tracked as WPM-156).
- **WPM-137** (multi-instance fixed-ID conflict bug in `classschedule.js`)
  is a known open bug not asserted as a hard requirement in
  `specs/class-schedule/frontend-render/spec.md` — intentionally, since the
  intended behavior isn't decided yet. Once WPM-137 resolves one way or the
  other, the spec should gain an explicit requirement (either "one instance
  per page" as a stated constraint, or "multiple instances render
  independently" as a real requirement with scenarios).
- **`coverage-gap-jira-reporting`** openspec change is still an empty stub
  (`.openspec.yaml` only) — this session's Jira comparison was done ad hoc,
  not through that change. If you want this kind of gap-check to become a
  repeatable openspec workflow, that change is the natural home for it.
- **`evaluate-test-effectiveness`** (14/24 tasks done, unrelated to this
  session's work beyond sharing the WPM-115 epic) is mid-flight separately —
  it's about test *quality* review standards, not coverage gaps; don't
  conflate the two efforts.
