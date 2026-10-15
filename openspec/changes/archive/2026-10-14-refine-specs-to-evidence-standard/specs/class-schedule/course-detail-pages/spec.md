## MODIFIED Requirements

### Requirement: Missing or unreachable course data degrades to a message

When the course lookup errors, returns no data, or has no primary section,
the system SHALL render a plain-language message inside the page chrome
rather than a partially-rendered page or a fatal error.

An empty response SHALL render "Course not found." A response with no
`primary_section` SHALL render "Course information unavailable."

**Known defect (CS-D1, no Jira yet):** because the internal
`rest_do_request()` call returns an upstream failure as a `WP_REST_Response`
carrying error data (never a `WP_Error`), an unreachable PeopleSoft API
renders "Course information unavailable." instead of an error message; the
"Error loading course details" branch is unreachable. The scenario
"Unreachable API shows a message without course fields" pins this behavior.

#### Scenario: Unreachable API shows a message without course fields

- **WHEN** the course detail REST call fails upstream
- **THEN** the page shows "Course information unavailable." and renders no
  course fields

#### Scenario: Nonexistent course shows a not-found message

- **WHEN** the course detail response is empty for a given term/class number
- **THEN** the page states the course was not found
