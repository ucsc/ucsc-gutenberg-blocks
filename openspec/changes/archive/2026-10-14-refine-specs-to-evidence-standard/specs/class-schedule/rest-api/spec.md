## MODIFIED Requirements

### Requirement: Uniform upstream error handling

Every route SHALL treat a `wp_remote_get` failure or a non-2xx HTTP response
from PeopleSoft as a `WP_Error`, and SHALL treat a response body that fails to
decode as JSON as a distinct `WP_Error`, so no caller ever receives a
malformed or partial payload as if it were valid data.

An error response MUST NOT be cached.

**Known defect (CS-D1, no Jira yet):** internal callers
(`ClassSchedule::theHTML()`, `getCachedCourses()`, `course_detail_title()`,
`CourseDetailTemplate.php`) reach these routes via `rest_do_request()`, which
returns the error as a `WP_REST_Response` with the error's status and a
`{code, message, data}` body, never as a `WP_Error`. The callers check only
`is_wp_error()`, so they treat the error body as data and their dedicated
error branches are unreachable. The PHP test stubs return a raw `WP_Error`,
so the suites pass on branches real WordPress never reaches.

#### Scenario: Upstream failure surfaces as a REST error

- **WHEN** the PeopleSoft endpoint is unreachable
- **THEN** the WordPress REST route returns an error response
- **AND** nothing is written to the transient cache for that request

#### Scenario: Invalid JSON is not treated as valid data

- **WHEN** PeopleSoft returns a 200 response with a non-JSON body
- **THEN** the route returns a JSON-decode error
- **AND** the invalid body is not cached

#### Scenario: Internal caller receives the error as a response object (known defect CS-D1)

- **WHEN** `ClassSchedule::theHTML()` calls the courses route through
  `rest_do_request()` and PeopleSoft is unreachable
- **THEN** it receives a `WP_REST_Response` with an error status, not a
  `WP_Error`, and its `is_wp_error()` branch is not taken
