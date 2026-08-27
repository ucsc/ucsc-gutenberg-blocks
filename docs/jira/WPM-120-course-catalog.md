# course-catalog — untested tablesorter, template covered by path assertion only

**Jira:** WPM-120 · **Parent:** WPM-115 · **Type:** Task

## Current state (measured 2026-08-25)

3 of 4 units named by a test, with a 363-line PHP suite and a 152-line Jest suite.
Two real gaps, one of them hidden behind a weak match.

**1. `src/components/CourseCatalog/tablesorter.js` — 131 loc, no test of any kind.**
Client-side table sorting for the catalog listing.

**2. `templates/CourseDetailTemplate.php` — 461 loc, covered by a path assertion.**
Its only match is `tests/php/ClassScheduleTest.php:373`:

```php
check( 'uses the course detail template when term and id are set',
       false !== strpos( $template, 'templates/CourseDetailTemplate.php' ) );
```

That asserts the correct template *path is selected*. It never renders the
template, so 461 lines of markup and escaping are unexercised. It is also
asserted from the ClassSchedule suite rather than the CourseCatalog one.

**3. No e2e spec.** campus-directory and class-schedule each have one;
course-catalog has none.

## Scope

**Jest (`validate jest create`)** — depends on Enablement A

- `tablesorter.js` — sort comparators, direction toggling, and behaviour on empty
  or malformed rows.

**PHP (`validate php create`)**

- Add a test in `CourseCatalogTest.php` that renders `CourseDetailTemplate.php`
  against fixture course data and asserts escaping plus the detail-page structure.
- Leave the existing path assertion in place; it tests selection, which is a
  different thing worth keeping.

**E2E (optional, size first)**

- Assess whether a course-catalog spec is worth adding now, given the direction to
  freeze rather than grow the puppeteer suite. If yes, keep it to the critical
  path: listing renders, a course opens its detail view.

## Acceptance criteria

- [ ] `tablesorter.js` has a Jest test exercising its sort logic.
- [ ] A PHP test renders `CourseDetailTemplate.php` rather than asserting its path.
- [ ] Each new test has been **seen to fail** without the behaviour it guards.
- [ ] E2E decision recorded on the ticket either way.
- [ ] Docker only — no host PHP or Node.

## Out of scope

- Accessibility testing — covered separately by WPM-42 / WPM-45.
- Re-opening WPM-104 (suite fatal on `filemtime()` stub redeclaration, already Done).
