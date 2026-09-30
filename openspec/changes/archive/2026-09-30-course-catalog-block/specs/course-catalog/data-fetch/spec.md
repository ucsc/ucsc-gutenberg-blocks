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
`HttpListeningConnector` using the `SCX_SERVICE_CTLG.v1` operation with a
30-second timeout. Any `subjectOrDept` value other than `"dept"` selects
subject mode.

The value is interpolated into the XML without escaping, and there is no
guard for an unconfigured block: a block with no `subjectOrDept` (or with
`"---"` selected) still issues an upstream request, e.g. with an empty
`<subject></subject>` element.

#### Scenario: Unconfigured block still queries upstream

- **WHEN** the block renders with no attributes set
- **THEN** a PeopleSoft request is sent with an empty `<subject></subject>`
  element

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
`"test"` to the QA instance (the canonical keys `"prod"` and `"csqa"` are also
accepted, case-insensitively), and defaulting to production for any
unrecognized value. An empty environment variable is ignored.

A request-parameter target override (`ucsc_course_catalog_target`) is
implemented but disabled; request parameters SHALL NOT change the target.

#### Scenario: Unrecognized target value defaults to production

- **WHEN** `UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET` is set to an unrecognized
  string
- **THEN** the production PeopleSoft instance is used

#### Scenario: QA alias resolves to the QA instance

- **WHEN** the target configuration value is `"qa"` or `"test"`
- **THEN** the QA PeopleSoft instance (`PSFT_CSQA`) is used

### Requirement: Response caching is per-target and per-query, for one week

The system SHALL cache a successfully-parsed catalog response body in a
WordPress transient keyed
`course-catalog-<target>-<lowercased value>-<subjectOrDept>` for
`WEEK_IN_SECONDS`. A response MUST NOT be cached until it has been confirmed
to parse as valid, non-empty XML.

When `UCSC_COURSE_CATALOG_BYPASS_CACHE` (constant, else environment variable)
is truthy, the system SHALL neither read nor write the transient.

#### Scenario: Prod and QA responses do not share a cache entry

- **WHEN** the same department is queried once against prod and once against
  QA
- **THEN** each is cached under a distinct key and neither read overwrites
  the other

#### Scenario: Malformed XML is not cached

- **WHEN** the upstream response is a 200 with a body that fails to parse as
  XML
- **THEN** an error is returned and no transient is written for that query

#### Scenario: Cache bypass skips the transient

- **WHEN** `UCSC_COURSE_CATALOG_BYPASS_CACHE` is `true`
- **THEN** every render fetches from PeopleSoft and no transient is written

### Requirement: Upstream failures degrade to an error, not a partial render

A non-2xx HTTP response, a transport-level failure, or unparseable XML SHALL
each produce a distinct `WP_Error` (the transport error itself,
`course_catalog_feed_error`, or `course_catalog_feed_xml_error`) that the
render callback surfaces as a plain-language unavailability message, rather
than attempting to iterate over incomplete data.

The XML check is a truthiness test on the parsed `SimpleXMLElement`, so a
well-formed but empty root element (e.g. `<catalog/>`, no courses) is also
treated as invalid XML: it renders the unavailability message and is not
cached.

#### Scenario: Empty catalog response is treated as unavailable

- **WHEN** PeopleSoft returns a 200 with body `<catalog/>`
- **THEN** the rendered block shows the "temporarily unavailable" message
- **AND** no transient is written, so the next render fetches again

#### Scenario: Transport failure shows an unavailability message

- **WHEN** the PeopleSoft POST request fails at the transport level
- **THEN** the rendered block shows a "temporarily unavailable" message and
  attempts no table rendering

### Requirement: Cache can be cleared per-target via WP-CLI

The system SHALL expose a WP-CLI command,
`wp ucsc course-catalog-cache clear [--target=<target>]`, that deletes all
matching cached transients (value and timeout rows) for the given target, or
for all targets when none is specified, and reports the number of option rows
deleted. The `qa`/`test` aliases resolve to `csqa`.

#### Scenario: Clearing one target leaves the other intact

- **WHEN** `wp ucsc course-catalog-cache clear --target=qa` is run
- **THEN** cached QA transients are deleted
- **AND** cached production transients remain
