# 01 — Two coverage coordinate systems

**Status:** open · **Raised:** 2026-08-26 · **Touches:** WPM-115, WPM-116, WPM-117, WPM-118

## The tension

WPM-115 and the `campus-directory-block` specs both answer "is this block
covered?" — in incompatible units.

```
        WPM-115's answer                    The specs' answer
     ┌──────────────────────┐          ┌──────────────────────────┐
     │  STRUCTURAL          │          │  BEHAVIORAL              │
     │  "is this unit       │          │  "is this scenario       │
     │   named by a test?"  │          │   exercised?"            │
     ├──────────────────────┤          ├──────────────────────────┤
     │  18/40 units = 45%   │          │  37 requirements         │
     │  campus-dir: 8/13    │          │  97 scenarios            │
     │                      │          │  across 6 capabilities   │
     │  denominator = FILE  │          │  denominator = BEHAVIOR  │
     └──────────────────────┘          └──────────────────────────┘
                │                                   │
                └────────────┬──────────────────────┘
                             ▼
              same block, no shared vocabulary
```

Scenario counts as written:

| Capability | Requirements | Scenarios |
|---|---:|---:|
| `audience-selection` | 7 | 19 |
| `profile-pages` | 7 | 20 |
| `ldap-data-access` | 7 | 16 |
| `frontend-render` | 6 | 17 |
| `block-registration` | 5 | 12 |
| `information-display` | 5 | 13 |
| **Total** | **37** | **97** |

## Why this is worth taking seriously

WPM-115 is unusually honest about its own limits. It says, in its own words:

- "Structural coverage is a **floor, not a measurement** — it answers 'is this
  unit tested at all', never 'how much of it runs'."
- "A test that names a unit without exercising it raises the structural number
  and guards nothing."

The epic already knows the file-denominator is wrong. It just did not have a
better one available at the time. It does now.

Compare what each system says about the same gap:

| System | What it reports for `CampusDirectoryTemplate.php` |
|---|---|
| Structural | 164 loc, 0 tests — one row in a table |
| Behavioral | 4 unexercised escaping scenarios; website label/URL split unexercised; portrait fallback unexercised; combined office location unexercised |

The second is a work queue. The first is a number that goes up when someone
writes one weak test.

## The mechanical fit

WPM-115's hardest ground rule:

> A test is not coverage until it has been seen to fail. Revert only the
> behaviour under test, confirm the new test fails for the expected reason,
> restore.

That rule is painful to apply against a *file* — "revert `CampusDirectoryTemplate.php`"
is not an instruction. It is natural against a *scenario*, because a WHEN/THEN
already names the behavior to break:

```
#### Scenario: Markup in a name
- **WHEN** a person's directory name contains markup
- **THEN** the listing renders it as literal text
```

The revert is "remove the escaping on the name". The expected failure is "raw
markup appears". The scenario is the test plan.

## What this does not replace

Line coverage and scenario coverage measure different things, and the epic's
enablement tickets are still real infrastructure work:

- **WPM-116** — Jest `collectCoverageFrom`. Without it a coverage run silently
  omits every file no test imports, which is exactly the set being hunted.
- **WPM-117** — the PHP harness is dependency-free and runs in a throwaway
  `php:8.1-cli` with neither Xdebug nor PCOV; the `wp` image ships Xdebug but
  with `xdebug.mode=debug`, and Xdebug 3 only records coverage when the mode
  includes `coverage`.

Neither is displaced by a scenario inventory. The claim is narrower: **only one
of the two is a usable work queue**, and it is the scenarios.

## Options

| Option | What it means | Cost |
|---|---|---|
| **A. Scenarios become the queue** | WPM-118 and successors get restated as "close these N scenarios" rather than "test these 5 files". Structural count stays as a coarse progress signal. | Restating open tickets; only campus-directory has scenarios today (see note 04) |
| **B. Keep them parallel** | Both numbers reported, neither reconciled. | Zero now, confusion later — two answers to "are we done" |
| **C. Map scenarios to units** | Build a scenario-to-file index so structural coverage inherits behavioral detail. | Real tooling work; a `validate coverage` extension |

## Recommendation

**A**, scoped to campus-directory first — it is the only block with scenarios,
and WPM-118 is the largest gap (889 loc across 5 untested units). Restate
WPM-118's scope in scenario terms and see whether the "seen to fail"
demonstration gets easier. If it does, the argument for the other two blocks
makes itself.

Keep WPM-116 and WPM-117 exactly as they are. They are enablement, not
measurement, and nothing here touches them.

## Acting on this

1. Restate WPM-118's scope against `specs/campus-directory/**` scenarios rather
   than the five-file table.
2. Note which scenarios the existing suites (417-line PHP, 271-line Jest,
   164-line e2e) already exercise — this is the real current baseline and nobody
   has measured it.
3. Decide whether `validate coverage` should learn to read scenarios
   (references ADR-114 and `skills/validate/references/coverage-strategy.md`).

## Caveat

This connection was noticed by the same session that wrote both artifacts, which
is exactly the situation where a pattern gets over-read. Worth a second opinion
before restating tickets.
