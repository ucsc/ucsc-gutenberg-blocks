## 1. Audit against the standard

- [x] 1.1 Read all 13 baseline specs against ucsc-wp-block-dev ADR-117 (`specification-generation.md`). Record which requirements state defects as plain behavior, lack a Jira link, or miss drift.
- [x] 1.2 List code changes since the verified baseline (`defbf56..1075b1d`, excluding `openspec/` and e2e). Only `aca712c` (WPM-205, apiVersion 3) and `cc330a9` (WPM-89, owned by its own open change) touch block code.
- [x] 1.3 Read open WPM issues (2026-10-14, description and latest comments) and map them to requirements and to the 2026-09-30 deviations.

## 2. Write the deltas

- [x] 2.1 Course Catalog: block-registration, data-fetch, frontend-render.
- [x] 2.2 Class Schedule: block-registration, rest-api, course-detail-pages, frontend-render.
- [x] 2.3 Campus Directory: block-registration, audience-selection, ldap-data-access, frontend-render, information-display, profile-pages.
- [x] 2.4 `openspec validate refine-specs-to-evidence-standard --strict` passes.

## 3. Evidence

- [x] 3.1 Re-cite every defect location at `1075b1d` (file:line in `verification.md`).
- [x] 3.2 Run the apiVersion 3 assertions in Docker: Jest (`-t "API version|apiVersion"` on the three block test files) and `tests/php/PluginWiringTest.php`.
- [x] 3.3 Record evidence per requirement in `verification.md`.

## 4. Follow-up (outside this change)

- [x] 4.1 List deviations with no Jira issue in `design.md` Open Questions as proposed WPM tickets. Filing them needs approval and is not part of this change.
