## Purpose

Proxies PeopleSoft's course-schedule feed through a WordPress REST namespace
so every consumer — the block render, the course-detail page, and the
document title — shares one cache and one error contract instead of each
calling PeopleSoft directly.

## ADDED Requirements

### Requirement: Public REST routes for terms, courses, and course detail

The system SHALL register three public, unauthenticated GET routes under the
`ucsc/v1` namespace:

- `/terms` — all available academic terms
- `/courses/{term}` — the course list for a numeric term, optionally filtered
  by a `subject` or `dept` query parameter
- `/course/{term}/{course}` — full detail for one numeric course within a term

`term` and `course` route parameters MUST be validated as numeric; requests
with non-numeric values MUST NOT reach the PeopleSoft fetch.

#### Scenario: Non-numeric term is rejected

- **WHEN** `/wp-json/ucsc/v1/courses/abc` is requested
- **THEN** the request fails route validation and no PeopleSoft call is made

#### Scenario: Course list can be filtered by subject or department

- **WHEN** `/wp-json/ucsc/v1/courses/2262?dept=CSE` is requested
- **THEN** the upstream PeopleSoft query is scoped to department `CSE`

### Requirement: Transient caching per route

Each route SHALL cache its upstream response in a WordPress transient for 15
minutes (900 seconds), keyed so that different terms and different
subject/department filters do not collide:

- Terms: fixed key `ucsc_ps_terms`
- Courses: `ucsc_ps_courses_<md5(term + normalized query string)>`
- Course detail: `ucsc_ps_course_<term>_<course>`

A cache hit MUST short-circuit before any upstream request is made.

#### Scenario: Cached response skips the upstream call

- **WHEN** a courses request is repeated within 15 minutes with the same term
  and filter
- **THEN** the cached transient is returned and no new PeopleSoft request is
  made

#### Scenario: Different filters use different cache entries

- **WHEN** `/courses/2262?dept=CSE` and `/courses/2262?subject=CMPM` are both
  requested
- **THEN** each is cached and served independently

### Requirement: Uniform upstream error handling

Every route SHALL treat a `wp_remote_get` failure or a non-2xx HTTP response
from PeopleSoft as a `WP_Error`, and SHALL treat a response body that fails to
decode as JSON as a distinct `WP_Error`, so no caller ever receives a
malformed or partial payload as if it were valid data.

An error response MUST NOT be cached.

#### Scenario: Upstream failure surfaces as a REST error

- **WHEN** the PeopleSoft endpoint is unreachable
- **THEN** the WordPress REST route returns an error response
- **AND** nothing is written to the transient cache for that request

#### Scenario: Invalid JSON is not treated as valid data

- **WHEN** PeopleSoft returns a 200 response with a non-JSON body
- **THEN** the route returns a JSON-decode error
- **AND** the invalid body is not cached
