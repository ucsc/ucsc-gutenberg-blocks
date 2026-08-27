# 04 — Spec baseline asymmetry

**Status:** open, and gets more expensive with time · **Raised:** 2026-08-26
**Touches:** archive ordering for all three block changes

## The situation

```
  openspec/changes/
  ├── campus-directory-block/   proposal + 6 specs + design + tasks   ← complete
  ├── class-schedule-block/     proposal only
  └── course-catalog-block/     proposal only

  openspec/specs/               EMPTY
```

If `campus-directory-block` archives now, Campus Directory gets six baseline
specs under `openspec/specs/campus-directory/**` and the other two blocks get
nothing — while sharing components with it (see note 03).

Every subsequent cross-block change then carries MODIFIED deltas on one side and
nothing on the other. A change to `DepartmentDropdown` has a spec to update for
Campus Directory's dropdown and no spec to update for the other two.

## Why the asymmetry exists

Not a decision — an accident of order. All three changes were created the same
day as baseline documentation of already-shipped blocks. Campus Directory got
carried through all four artifacts because a specific question was asked about
it; the other two stopped at proposal.

Both sibling proposals already name their capabilities, so the specs phase has a
contract to work from:

| Change | Capabilities declared |
|---|---|
| `class-schedule-block` | `block-registration`, `rest-api`, `frontend-render`, `course-detail-pages` |
| `course-catalog-block` | `block-registration`, `data-fetch`, `frontend-render` |

That is seven spec files that do not exist. Not trivial, but the hard part —
deciding what the capabilities are — is done.

## Cost curve

| When | Cost |
|---|---|
| Now, before anything archives | Write seven spec files against a stable, shipped implementation |
| After campus-directory archives | Same work, plus reconciling shared-component contracts against an already-frozen baseline on one side |
| In six months | Same work, plus whatever drift has accumulated, plus nobody remembers why one block has specs and two do not |

## Options

| Option | Order | Result |
|---|---|---|
| **A. Level up, then archive all three** | Write specs for the two siblings, archive together | Symmetric baseline; shared-component questions get answered once, for all three |
| **B. Archive campus-directory now** | Siblings catch up later | Fastest to a usable baseline for the block that needs it; accepts the asymmetry |
| **C. Archive campus-directory, close the siblings** | Delete the two proposal-only changes | Honest about what is actually being maintained; loses the capability analysis already done |

## Leaning

**A**, but only if the siblings' specs can be written soon. The argument for
levelling up is that notes 02 and 03 both want answering *once* — normative vs.
descriptive, and where shared contracts live — and answering them against one
block and then re-answering against two more is how baselines end up
inconsistent.

The argument against is scope: three blocks of baseline documentation is a lot
of writing for work that produces no behavior change, on a branch
(`dev/henryh/WPM-115_test_coverage`) whose actual job is test coverage.

**B is the honest fallback** if the siblings will not get written soon. An
asymmetric baseline beats no baseline, as long as the asymmetry is recorded —
which is what this note is for.

**C deserves consideration** and is easy to dismiss too fast. Two proposal-only
changes sitting unarchived indefinitely are clutter that implies more coverage
than exists.

## Acting on this

1. Decide archive ordering before running `openspec archive` on
   `campus-directory-block`.
2. If A: `/opsx:continue` on each sibling change to generate specs from the
   capabilities their proposals already declare.
3. If B: record the asymmetry somewhere the next person will see it — this note,
   promoted, or a line in the siblings' proposals.
4. Note that all of `openspec/` and `docs/` are currently **untracked** on a
   test-coverage branch. Whichever option wins, this work probably wants its own
   branch before it is committed.
