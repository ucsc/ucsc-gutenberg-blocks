# 02 — Normative vs. descriptive baseline

**Status:** open, but a choice has already been made implicitly · **Raised:** 2026-08-26
**Touches:** `openspec archive campus-directory-block`, `audit-campus-directory.md`

## The tension

`specs/campus-directory/frontend-render/spec.md` states:

> All directory-derived values MUST be escaped for the context they are rendered
> into — text, attribute, or URL — before being emitted.

`templates/CampusDirectoryTemplate.php` and `classes/CampusDirectoryShortcode.php`
do not do that. `templates/DirectoryProfileTemplate.php` largely does.

So the baseline spec asserts behavior the shipped code fails. That was
deliberate — tasks 3.1–3.3 of the change record the deviations rather than
fixing them — but it was a fork in the road taken without flagging it.

```
  DESCRIPTIVE baseline                NORMATIVE baseline
  spec = what ships                   spec = the contract
  ────────────────────                ────────────────────
  "listing does not escape;           "all output escapes"
   profile does"                       + deviation register

  archive → truthful baseline         archive → baseline the
  deviations live in Jira              code currently violates

  spec drifts as bugs are fixed       spec is a standing oracle
  spec cannot be a test oracle        spec accuses the code
```

## Which was chosen, and why

**Normative.** A spec that says "the listing does not escape" is a spec you
cannot test against — it notarizes the bug and turns the eventual fix into a
spec change. A spec that says "everything escapes" is a standing oracle: it
generates the test, and the test fails until the bug is fixed.

That also makes the audit findings legible as *spec violations* rather than as
loose entries in a file from 2026-07-17 that nobody diffs against anything.

## The consequence to decide deliberately

**`openspec archive` will promote a baseline the code fails on day one.**

Anyone diffing shipped behavior against `openspec/specs/campus-directory/**`
finds violations immediately. Is that a defect in the baseline or the point of
having one?

Argument that it is the point: a spec that cannot be violated is not doing work.
The gap is real, it is a public XSS surface fed by data this site does not
control, and burying it in prose that describes the bug as intended behavior
makes it less likely to get fixed.

Argument that it is a defect: OpenSpec's archive semantics treat
`openspec/specs/` as the description of the system. A baseline that is knowingly
false in six places is a weaker foundation for future MODIFIED deltas, and a
reader has no way to tell which requirements are met.

## The real problem underneath

Whichever way this goes, **the deviation register is currently in the wrong
place.** Tasks 3.1–3.3 of `campus-directory-block` hold the record of what the
code fails:

- 3.1 — output escaping gap in listing template and shortcode
- 3.2 — publish guard checks `department && division` both `---` instead of
  validating the active `deptOrDiv` (`src/blocks/CampusDirectory.js:61`,
  `src/components/CampusDirectory/PeopleAndInformation.js:157`)
- 3.3 — `SiteSettings` dropdown endpoint blocks on cold cache
  (`classes/SiteSettings.php:25,48`)

`tasks.md` evaporates when the change archives. The deviations do not.

## Options

| Option | Baseline says | Deviations live in |
|---|---|---|
| **A. Normative + durable register** | The contract | A file that survives archive — e.g. `docs/spec-deviations.md`, or Jira tickets linked from each spec |
| **B. Normative + nothing** | The contract | Nowhere. Rediscovered by whoever next diffs code against spec |
| **C. Descriptive** | What ships, bugs included | N/A — the spec *is* the bug report |
| **D. Normative + inline markers** | The contract, with a `> NOTE: not currently met` under each violated requirement | The spec itself |

## Recommendation

**A.** Keep the normative baseline — it is the more useful artifact — but move
the deviation record somewhere that outlives the change. Either a standing
`docs/spec-deviations.md`, or (better) real tickets, since three of these are
security or availability findings that deserve a queue and not a markdown file.

**D is tempting and probably wrong.** Inline "not currently met" markers rot
fast: nobody deletes them when the fix lands, and a spec littered with stale
exemptions is worse than one that is cleanly aspirational.

## Acting on this

1. Decide normative vs. descriptive before running `openspec archive`. After
   archive it is a MODIFIED delta on six requirements, not an edit.
2. If normative: create the durable deviation record, and make task 3.5's
   follow-up change real before archiving.
3. Confirm the one *closed* finding stays closed — LDAP filter injection is
   remediated (`ldap_escape()` at `classes/CampusDirectoryAPI.php:122` and
   `:339-341`), and `audit-campus-directory.md` still lists it as High. That
   audit file needs a resolution pass regardless of which option wins.
