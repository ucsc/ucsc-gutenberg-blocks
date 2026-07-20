# Campus Directory Block Audit

Date: 2026-07-17

Scope: `classes/CampusDirectory.php`, `classes/CampusDirectoryAPI.php`, `classes/CampusDirectoryShortcode.php`, `templates/CampusDirectoryTemplate.php`, `templates/DirectoryProfileTemplate.php`, `src/blocks/CampusDirectory.js`, Campus Directory editor components, and related tests.

Tools used:

- `node /Users/henryh/_code/_tools/ucsc-node-review/bin/ucsc-node-review.mjs . --json --out .claude/ucsc-node-review-gutenberg.json`
- `docker run --rm -v /Users/henryh/_code/_tools/ucsc-php-review:/app -v /Users/henryh/_code/_campuspress/wp-dev.ucsc/public/wp-content/plugins/ucsc-gutenberg-blocks:/target -w /app composer:2 bin/ucsc-php-review /target --json --out /target/.claude/ucsc-php-review-gutenberg.json`
- `docker run --rm -v /Users/henryh/_code/_campuspress/wp-dev.ucsc/public/wp-content/plugins/ucsc-gutenberg-blocks:/plugin -w /plugin php:8.1-cli php tests/php/CampusDirectoryTest.php`
- `npm test -- --runInBand`

Tool notes:

- `ucsc-node-review`: ESLint and security rules produced target findings plus many WordPress-global/default-config mismatches; dependency-cruiser, jscpd, large-assets, and missing-alt-text were clean. `npm-audit` skipped because the sandbox could not reach `registry.npmjs.org`.
- `ucsc-php-review`: PHPCS/PHPStan/PHPMD produced many default-rule and missing-WordPress-stub messages; no duplicate PHP blocks, debug statements, or large assets were found.
- Campus Directory PHP tests passed 10/10. Full JS unit suite passed 44/44.

## Architecture And Attack Surface

Campus Directory is a server-rendered dynamic block registered in `classes/CampusDirectory.php:107-119`. It renders public directory listings through `CampusDirectoryAPI`, public profile pages through `DirectoryProfileTemplate.php`, and a public shortcode through `CampusDirectoryShortcode.php`. Data comes from LDAP, block attributes saved by editors, query vars for profile routes, and site/network LDAP configuration options. Public REST support exists for editor configuration discovery at `classes/CampusDirectory.php:38-43`; department/division option endpoints are in `classes/SiteSettings.php`.

Generated/dependency directories excluded from manual review: `node_modules/`, `build/`, and raw runner JSON except for summary signals.

## Findings

### High: LDAP filter injection in CruzID inputs

Files: `classes/CampusDirectoryAPI.php:86`, `classes/CampusDirectoryAPI.php:323`, `classes/CampusDirectoryAPI.php:335`

Evidence and path: manual `cruzidList`, profile-route CruzID values, `excludeCruzids`, and `addCruzids` are concatenated into LDAP filters as `(uid=...)` without `ldap_escape()`. The resulting filter is passed to `ldap_search()` at `classes/CampusDirectoryAPI.php:122`.

Impact: a crafted CruzID such as LDAP metacharacters in a query var or saved block attribute can alter filter logic, broaden queries, or create expensive/invalid LDAP requests.

Remediation: normalize CruzIDs with a strict allowlist such as `/^[a-z0-9._-]+$/i`; reject invalid values or escape with `ldap_escape($value, '', LDAP_ESCAPE_FILTER)` before interpolation.

Confidence: high.

Suggested validation: pass `jsmith)(|(uid=*))(` through profile, manual, add, and exclude paths; assert the generated filter rejects or escapes metacharacters.

### High: unescaped LDAP data in directory listing template

Files: `templates/CampusDirectoryTemplate.php:38`, `templates/CampusDirectoryTemplate.php:48-61`, `templates/CampusDirectoryTemplate.php:93-95`, `templates/CampusDirectoryTemplate.php:107-132`, `templates/CampusDirectoryTemplate.php:150`

Evidence and path: LDAP-derived names, titles, emails, website URLs/labels, office fields, and profile URLs are echoed directly into HTML text and attributes. `DirectoryProfileTemplate.php` mostly escapes equivalent profile output, so this is a listing-template gap.

Impact: public XSS or malformed links if LDAP/public directory data contains markup, quotes, or attribute-breaking payloads.

Remediation: use `esc_html()` for text, `esc_attr()` for attributes, `esc_url()` for URLs, and validate email values before building `mailto:` links.

Confidence: high.

Suggested validation: render a mocked person with `cn`, email, and website fields containing `"><script>alert(1)</script>` and assert no raw payload appears.

### High: unescaped LDAP data in shortcode output

Files: `classes/CampusDirectoryShortcode.php:304`, `classes/CampusDirectoryShortcode.php:326`, `classes/CampusDirectoryShortcode.php:334`, `classes/CampusDirectoryShortcode.php:352`, `classes/CampusDirectoryShortcode.php:360`, `classes/CampusDirectoryShortcode.php:376`, `classes/CampusDirectoryShortcode.php:383`

Evidence and path: shortcode helpers concatenate LDAP values into link text, `href`, `mailto:`, IDs, and image/style-related output without WordPress escaping.

Impact: public-facing shortcode profile output can XSS independently of the better-escaped profile template.

Remediation: escape at every helper boundary with `esc_html()`, `esc_attr()`, `esc_url()`, and validated `mailto:` values. Treat image data as trusted only after strict base64 validation.

Confidence: high.

Suggested validation: mock shortcode LDAP data with malicious `cn`, `mail`, `labeleduri`, and `uid`; assert rendered HTML contains escaped text and safe attributes.

### Medium: editor validation permits stale dept/div automated feed states

Files: `src/blocks/CampusDirectory.js:61`, `src/components/CampusDirectory/PeopleAndInformation.js:157`, `classes/CampusDirectoryAPI.php:296`

Evidence and path: the editor locks saving only when both `department` and `division` are `---`. If `deptOrDiv === 'dept'`, `department === '---'`, and a stale valid `division` remains, saving is allowed; PHP then treats the dept/div as unset and applies only affiliation filters, with a size limit.

Impact: a published block can render an arbitrary first 50 people matching selected affiliations rather than the intended department or division.

Remediation: validate against the active `deptOrDiv`: require a valid department for `dept`, a valid division for `div`, and clear inactive dropdown state when toggling.

Confidence: high.

Suggested validation: render the editor with `automatedFeeds=true`, `deptOrDiv='dept'`, `department='---'`, and `division='Arts Division'`; assert saving is locked.

### Medium: public department dropdown endpoint can block or fail on upstream errors

Files: `classes/SiteSettings.php:25`, `classes/SiteSettings.php:48`

Evidence and path: the public `/cddepartmentcode/` endpoint fetches `https://campusdirectory.ucsc.edu/` with `file_get_contents()` when the transient is cold, then dereferences DOM nodes without robust response or parse checks.

Impact: any visitor can trigger a blocking outbound request on a cold cache; upstream failure can degrade REST/editor availability with warnings or fatal behavior.

Remediation: use `wp_remote_get()` with explicit timeout, validate status/body, check DOM nodes before dereferencing, return a controlled `WP_Error` or cached fallback, and consider limiting endpoint access to editor/admin contexts if it is only needed in the editor.

Confidence: medium-high.

Suggested validation: mock failed remote response or empty HTML and assert the endpoint returns a controlled error/fallback without warnings.

## Remediation Order

1. Escape Campus Directory listing and shortcode output.
2. Escape/validate all CruzID values before LDAP filter construction.
3. Fix editor state validation for `deptOrDiv`.
4. Harden the public dropdown endpoint and add failure-path tests.

## Test Gaps

- No tests cover LDAP filter escaping.
- No tests cover listing-template or shortcode XSS escaping.
- No tests cover stale `deptOrDiv` editor state.
- No tests cover SiteSettings remote failure handling.
