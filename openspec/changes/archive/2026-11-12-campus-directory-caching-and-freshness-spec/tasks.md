## 1. Check the deltas against the code

- [x] 1.1 Confirm `main` is still `51b82d5` or that no commit since touches `classes/CampusDirectory*.php`, `classes/SiteSettings.php` or `templates/*Directory*`; verify with `git log 51b82d5..origin/main -- <those paths>` returning nothing (re-check every cited behavior if it does not)
- [x] 1.2 Cite each requirement in `specs/campus-directory/caching/spec.md` to a file:line at the reference commit in a new `verification.md`; done when every requirement has at least one citation
- [x] 1.3 Record each requirement's evidence level (code-inspected or test-inspected) in `verification.md`, naming the `check()` lines in `tests/php/CampusDirectoryTest.php` and `CampusDirectoryTemplateTest.php`; done when every scenario is mapped or marked code-inspected only

## 2. Run the existing evidence

- [x] 2.1 Run the Campus Directory PHP tests through the ucsc-wp-block-dev validate skill (`php` mode) and record pass counts in `verification.md`; done when the run is green or failures are listed

## 3. Validate and record gaps

- [x] 3.1 `openspec validate campus-directory-caching-and-freshness-spec --strict` passes
- [x] 3.2 List in `design.md` Open Questions the scenarios with no test (photo route `Cache-Control` and `X-Directory-Photo-Source`, the 1-minute bind back-off scope, the profile/list photo swap) as candidate WPM issues; filing them needs approval and is not part of this change
