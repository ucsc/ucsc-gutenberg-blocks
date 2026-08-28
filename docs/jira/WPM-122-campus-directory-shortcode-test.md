# Test CampusDirectoryShortcode.php (410 loc, public shortcode entry)

**Jira:** WPM-122 · **Parent:** WPM-118 · **Type:** Sub-task

## Scope

PHP tests for `classes/CampusDirectoryShortcode.php` — 410 loc, the largest untested unit in the plugin and a public input surface (security-critical).

**What it is:**
- Public shortcode entry point `[ucsc_profiles]`
- User-controlled attributes (cruzid lists, display options)
- LDAP data rendering in HTML

**Security concerns:**
- User-supplied attributes flow into HTML attributes (`id`, `href`)
- LDAP data rendered without escaping (XSS vulnerability)
- Public attack surface (any editor can insert shortcode)

## Test Coverage

File: `tests/php/CampusDirectoryShortcodeTest.php` (28 tests, 24 passing + 4 intentionally failing)

### Tests Created

**Attribute parsing (8 tests):**
- Shortcode registration
- Default attribute values
- String 'true'/'false' → boolean conversion
- Grid vs list display style selection

**Security tests (4 tests - FAIL EXPECTED):**
- XSS in cruzid attribute (uid in HTML id attribute) ❌ FAILS - documents vulnerability
- XSS in cn field (name) ❌ FAILS - documents vulnerability
- XSS in title field ❌ FAILS - documents vulnerability  
- XSS in phone number ❌ FAILS - documents vulnerability

**Rendering tests (16 tests):**
- Email rendering
- Labeled URI (website links)
- Office location rendering
- Profile links enabled/disabled
- Multiple cruzids (comma-separated)
- List display mode
- Grid display mode

### Intentional Failures

**4 tests FAIL by design** — they document known XSS vulnerabilities:

```
FAIL  Malicious script in cruzid does not render unescaped
FAIL  XSS in cn field is NOT escaped (KNOWN VULNERABILITY)
FAIL  XSS in title field is NOT escaped (KNOWN VULNERABILITY)
FAIL  XSS in phone number is NOT escaped (KNOWN VULNERABILITY)
```

These tests serve as:
1. **Security documentation** - proves vulnerabilities exist
2. **Regression guards** - will pass when escaping is added
3. **Work items** - each failing test is a fix target

## Implementation

Completed 2026-08-27 (WPM-115 enablement work).

**Files changed:**
- `tests/php/CampusDirectoryShortcodeTest.php` — New test file (28 tests, 348 lines)
- `tests/php/run-php-coverage.sh` — Added to test runner

**Technical approach:**
- Dependency-free test (no PHPUnit, runs in `php:8.1-cli`)
- Overrides `CampusDirectoryAPI->getCampusDirData()` to return fixture data
- Avoids LDAP connection requirements
- Uses instrumented harness for coverage capture

**Usage:**
```bash
# Run test alone
docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
  php tests/php/CampusDirectoryShortcodeTest.php

# Run with coverage (as part of full suite)
bash tests/php/run-php-coverage.sh
```

**Coverage impact:**
- Before: 451 statements (CampusDirectory.php, CampusDirectoryAPI.php, templates)
- After: **545 statements** (+94 from CampusDirectoryShortcode.php)
- Still 100% coverage (all tested code is fully exercised)

## Acceptance Criteria

- [x] CampusDirectoryShortcode.php is named by a real test that exercises it
- [x] Test has been seen to fail without the behavior it guards (24/28 pass; 4 fail as expected)
- [x] Shortcode tests assert sanitization gaps (4 failing security tests document XSS)
- [x] Suite runs in Docker only — no host PHP
- [x] Coverage report re-run: 451 → 545 statements (+94)

## Out of Scope

- Fixing the XSS vulnerabilities (separate security ticket needed)
- Testing LDAP integration behavior (requires VPN; belongs in E2E)
- Photo rendering (`jpegPhoto` field handling)
- Commented-out features (biography, expertise, publications - currently disabled in code)

## Security Follow-up Needed

The 4 failing tests document real XSS vulnerabilities that need fixing. Recommended fixes:

1. `render_attr_cn()` — escape `$uid` before using in `href` and `id` attributes
2. `render_attr_single_line()` — escape LDAP data with `esc_html()` before output
3. `render_attr_multi_line()` — escape each value with `esc_html()` before `join()`  
4. All render methods — audit and add escaping

When these are fixed, the 4 failing tests will pass and serve as regression guards.
