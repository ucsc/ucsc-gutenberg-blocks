## Purpose

Fetches course catalog data from PeopleSoft for a selected department or
subject, isolating prod and QA environments and caching aggressively since
catalog content changes far less often than schedule/enrollment data.

## ADDED Requirements

### Requirement: XML query built from the block's dept/subject attribute

The system SHALL build an XML request body containing either an
`<acad_org>` element (department mode, lowercased) or a `<subject>` element
(subject mode, lowercased), matching the block's configured `subjectOrDept`
attribute, and SHALL POST it to the resolved PeopleSoft target's
`HttpListeningConnector` using the `SCX_SERVICE_CTLG.v1` operation.

#### Scenario: Department mode queries by acad_org

- **WHEN** the block is configured with `subjectOrDept: "dept"` and
  `department: "CSE"`
- **THEN** the outbound XML request contains `<acad_org>cse</acad_org>`

#### Scenario: Subject mode queries by subject

- **WHEN** the block is configured with `subjectOrDept: "subject"` and
  `subject: "CMPM"`
- **THEN** the outbound XML request contains `<subject>cmpm</subject>`

### Requirement: PeopleSoft target resolution

The system SHALL resolve the PeopleSoft target host to either the production
instance (`PSFT_CSPRD`) or the QA instance (`PSFT_CSQA`) based on
`UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET` (checked as a PHP constant first, then
an environment variable of the same name), normalizing the aliases `"qa"` and
`"test"` to the QA instance, and defaulting to production for any unrecognized
value.

#### Scenario: Unrecognized target value defaults to production

- **WHEN** `UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET` is set to an unrecognized
  string
- **THEN** the production PeopleSoft instance is used

#### Scenario: QA alias resolves to the QA instance

- **WHEN** the target configuration value is `"qa"` or `"test"`
- **THEN** the QA PeopleSoft instance (`PSFT_CSQA`) is used

### Requirement: Response caching is per-target and per-query, for one week

The system SHALL cache a successfully-parsed catalog response in a WordPress
transient keyed by the resolved target, the lowercased department-or-subject
value, and the selection mode, for `WEEK_IN_SECONDS`. A response MUST NOT be
cached until it has been confirmed to parse as valid XML.

#### Scenario: Prod and QA responses do not share a cache entry

- **WHEN** the same department is queried once against prod and once against
  QA
- **THEN** each is cached under a distinct key and neither read overwrites
  the other

#### Scenario: Malformed XML is not cached

- **WHEN** the upstream response is a 200 with a body that fails to parse as
  XML
- **THEN** an error is returned and no transient is written for that query

### Requirement: Upstream failures degrade to an error, not a partial render

A non-2xx HTTP response, a transport-level failure, or unparseable XML SHALL
each produce a distinct error condition that the render callback surfaces as
a plain-language unavailability message, rather than attempting to iterate
over incomplete data.

#### Scenario: Transport failure shows an unavailability message

- **WHEN** the PeopleSoft POST request fails at the transport level
- **THEN** the rendered block shows a "temporarily unavailable" message and
  attempts no table rendering

### Requirement: Cache can be cleared per-target via WP-CLI

The system SHALL expose a WP-CLI command,
`wp ucsc course-catalog-cache clear [--target=<target>]`, that deletes all
matching cached transients for the given target, or for all targets when none
is specified, and reports the number of rows deleted.

#### Scenario: Clearing one target leaves the other intact

- **WHEN** `wp ucsc course-catalog-cache clear --target=qa` is run
- **THEN** cached QA transients are deleted
- **AND** cached production transients remain
