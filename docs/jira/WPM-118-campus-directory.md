# campus-directory — close the five untested units

**Jira:** WPM-118 · **Parent:** WPM-115 · **Type:** Task

## Current state (measured 2026-08-25)

The best-covered of the three blocks: 8 of 13 units are named by a test, with a
417-line PHP suite, a 271-line Jest suite, and a 164-line e2e spec.

Five units are named by **no test of any kind** — 889 lines total, the largest
concentration of untested code in the plugin:

| Unit | LOC | Why it matters |
|---|---|---|
| `classes/CampusDirectoryShortcode.php` | 410 | Public shortcode entry point. Largest untested unit in the plugin |
| `templates/CampusDirectoryTemplate.php` | 164 | Front-end markup and escaping surface |
| `src/components/CampusDirectory/InformationToDisplay.js` | 127 | Editor control |
| `src/components/CampusDirectory/InformationToDisplayTable.js` | 121 | Editor control |
| `src/components/CampusDirectory/CheckboxGroupControl.js` | 67 | Editor control |

Already covered and out of scope: `CampusDirectory.php`, `CampusDirectoryAPI.php`,
`DirectoryProfileTemplate.php`, `CampusDirectory.js`, `AutomatedFeeds.js`,
`CampusDirectoryDepartmentDropdown.js`, `PageLayout.js`, `PeopleAndInformation.js`.

## Scope

**PHP (`validate php create`)**

- `CampusDirectoryShortcode.php` — attribute parsing and defaults, sanitization of
  user-supplied attributes, and the render path. Start here; it is both the
  largest gap and a public input surface.
- `CampusDirectoryTemplate.php` — render the template against fixture data and
  assert escaping of every interpolated value.

**Jest (`validate jest create`)** — depends on Enablement A

- `InformationToDisplay.js`, `InformationToDisplayTable.js`,
  `CheckboxGroupControl.js` — attribute wiring, control state, and the callback
  contract with the parent block.

## Acceptance criteria

- [ ] All five units are named by a real test that exercises them.
- [ ] Each new test has been **seen to fail** without the behaviour it guards,
      and the demonstration is noted on the ticket. A test that names a unit
      without exercising it does not count.
- [ ] The shortcode tests assert sanitization of attacker-controlled attribute
      values, not just happy-path rendering.
- [ ] Suites run in Docker only — no host PHP or Node.
- [ ] Coverage report re-run afterwards and the movement recorded.

## Out of scope

- LDAP integration behaviour (needs VPN; belongs in e2e).
- The existing e2e spec — it works and is not being extended here.
- Accessibility testing — covered separately by WPM-38 / WPM-46 / WPM-50.
