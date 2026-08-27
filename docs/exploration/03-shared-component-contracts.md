# 03 — Where does a shared component's contract live?

**Status:** open · **Raised:** 2026-08-26 · **Touches:** any cross-block change

## The observation

Every capability path in the three change proposals is block-scoped:
`campus-directory/*`, `class-schedule/*`, `course-catalog/*`. But several
components are not.

```
  ClassSchedule.js ──┐
                     ├──▶ DepartmentDropdown.js  ──▶ PeopleSoft (ucsc/v1)
  CourseCatalog.js ──┘        └─ SubjectDropdown.js

  CampusDirectory.js ──▶ CampusDirectoryDepartmentDropdown.js ──▶ SiteSettings
                    └──▶ DivisionDropdown.js                       (scrapes
                                                                campusdirectory
                                                                   .ucsc.edu)
```

**There are two department dropdowns.** `DepartmentDropdown.js` is shared by
Class Schedule and Course Catalog and is backed by PeopleSoft.
`CampusDirectoryDepartmentDropdown.js` is Campus Directory's own, backed by
`SiteSettings`, which scrapes `campusdirectory.ucsc.edu`. Same control to a
user's eye, different upstreams, different failure modes — and the Campus
Directory one sits behind the public endpoint that blocks on a cold cache
(`classes/SiteSettings.php:25,48`).

The `course-catalog-block` proposal already flags the shared pair as a blast
radius: "changes to those components affect both blocks." Nothing in the specs
captures that.

## The sharper case: sharing *within* a block

`CheckboxGroupControl.js` is used by two Campus Directory components:

```
  AutomatedFeeds.js ─────────┐
                             ├──▶ CheckboxGroupControl.js
  InformationToDisplay.js ───┤          (67 loc, 0 tests)
  InformationToDisplayTable ─┘
```

Under our capability split, `AutomatedFeeds` belongs to `audience-selection` and
`InformationToDisplay` belongs to `information-display`. So the shared control
sits under **two capabilities at once**.

And its known defect belongs to neither: it calls `useState` inside `.map()`, so
hook count depends on the label array, and it calls `setAttributes` during render
when the attribute is undefined. It works only because the label arrays are
static constants. Any change that makes the field list dynamic breaks it first.

That is a real constraint on future work with no spec home.

## Why this matters now rather than later

`openspec/specs/` is empty. Capability paths are free to choose until something
archives. After that, moving a capability is a rename across delta history.

## Options

| Option | Shape | Honest? | Cost |
|---|---|---|---|
| **A. `shared/*` capability paths** | `shared/directory-dropdowns`, `shared/checkbox-group` | Yes | Invents a domain level the project does not use elsewhere; OpenSpec guidance says follow existing flat organization |
| **B. Duplicate into each consumer** | The dropdown contract appears in both `class-schedule/*` and `course-catalog/*` | No | Zero structure, guaranteed drift — two copies disagree within a release |
| **C. Leave implicit** | Specs cover observable behavior; shared components are implementation detail, and `design.md` carries the blast-radius warning | Defensible | The cold-cache endpoint then has no spec home either, and it is a genuine availability risk |
| **D. Spec the *endpoint*, not the component** | `campus-directory/ldap-data-access` already covers graceful degradation; extend it to cover the dropdown endpoint's failure behavior | Yes | Only solves the dropdowns, not `CheckboxGroupControl` |

## Leaning

**C for components, D for endpoints.**

A shared React control genuinely is implementation detail — its contract is with
its callers, not with a user, and specs are supposed to survive implementation
changes. `CheckboxGroupControl`'s hooks problem belongs in `design.md` Risks
(where it already is) and in a refactor ticket, not in a behavior spec.

But an endpoint that any anonymous visitor can hit, which makes a blocking
outbound request on a cold cache, is not implementation detail — it is
observable availability behavior. That deserves a requirement, and
`ldap-data-access` is the natural home even though the endpoint is not LDAP.
The capability may be misnamed; `directory-data-access` would cover both.

## Acting on this

1. Decide before archive — capability names are cheap now.
2. If `ldap-data-access` is to cover the dropdown endpoint, consider renaming it
   `campus-directory/directory-data-access` in the same pass.
3. Either way, open a ticket for `CheckboxGroupControl`'s rules-of-hooks
   violation. It is 67 lines, it is untested (WPM-118 scope), and it blocks any
   future dynamic field list.
4. Separately worth asking: should the two department dropdowns converge? They
   probably should not — different upstreams — but the naming implies they are
   interchangeable and they are not.
