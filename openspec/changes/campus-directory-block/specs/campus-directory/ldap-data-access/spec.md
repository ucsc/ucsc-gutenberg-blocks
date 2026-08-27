## Purpose

Governs how the block reaches the campus LDAP directory — how queries are built
from author configuration, what the site is allowed to pull, how results are
cached, and how a page behaves when the directory is unreachable.

## ADDED Requirements

### Requirement: Directory credentials and precedence

The system SHALL read its directory host, bind account, and credential from
site configuration, preferring a network-level value over a site-level one so a
multisite network can configure the directory once.

When no host or bind account is configured, the system MUST fall back to the
documented campus defaults rather than failing.

#### Scenario: Network value wins

- **WHEN** both a network-level and a site-level credential are configured
- **THEN** the network-level credential is used

#### Scenario: Site value used alone

- **WHEN** only a site-level credential is configured
- **THEN** that credential is used

#### Scenario: Host defaults

- **WHEN** no directory host is configured
- **THEN** the campus default host is used

### Requirement: Untrusted values are escaped before querying

Every value that reaches a directory query from author configuration, a saved
block attribute, or a URL — CruzIDs, departments, divisions, and scope
selectors — MUST be escaped for LDAP filter syntax before it is combined into a
filter.

A value containing LDAP filter metacharacters MUST NOT be able to alter the
structure of the resulting query, broaden its scope, or make it match records
the configuration did not select.

#### Scenario: Metacharacters in a CruzID

- **WHEN** a CruzID containing LDAP filter metacharacters is supplied through a
  manual list, an add or exclude list, or a profile URL
- **THEN** the resulting query treats it as a literal value
- **AND** the listing does not include records outside the configured scope

#### Scenario: Metacharacters in a department

- **WHEN** a saved department value contains LDAP filter metacharacters
- **THEN** the resulting query treats it as a literal value

### Requirement: Empty filters never query

The system MUST NOT issue a directory search when the configuration produces no
filter, because an empty filter expands to a malformed or unbounded search.

An empty filter MUST yield an empty listing.

#### Scenario: No filter, no query

- **WHEN** the block's configuration produces no filter
- **THEN** no directory search is issued
- **AND** the listing renders empty

### Requirement: Result ceilings

Every directory search SHALL carry a size limit so no block configuration can
pull the whole directory.

A search with an organizational scope MUST be limited to 1000 records. A search
without one MUST be limited to 50, so a misconfigured feed degrades to a small
sample rather than a campus-wide dump.

#### Scenario: Scoped search ceiling

- **WHEN** a feed with a department or division scope is queried
- **THEN** at most 1000 records are returned

#### Scenario: Unscoped search ceiling

- **WHEN** a feed without a department or division scope is queried
- **THEN** at most 50 records are returned

### Requirement: Requested fields are narrowed to the view

A listing SHALL request only the directory fields its layout can render.
Profile views, which render the full record, MAY request the complete entry.

Photo data in particular MUST NOT be requested for a listing view, because
retrieving image data for every person in a large listing is expensive and the
list and table layouts do not render it from that request.

#### Scenario: Listing narrows fields

- **WHEN** a list, tiled, or table listing is rendered
- **THEN** only the fields those layouts can render are requested from the
  directory

#### Scenario: Profile requests the full record

- **WHEN** an individual profile is rendered
- **THEN** the complete directory entry is requested

### Requirement: Results are cached

Directory results SHALL be cached so that repeated page views do not re-query
the directory.

Listing results and profile results MUST NOT share a cache entry, because they
request different fields for the same person. Successful results MUST be cached
for 10 minutes and empty results for 1 minute, so an unreachable directory is
retried soon rather than being cached as "nobody works here".

The organizational scope lists shown in the editor MUST be cached for 24 hours,
because they change rarely and are expensive to derive.

#### Scenario: Repeat view is served from cache

- **WHEN** the same listing is viewed twice within 10 minutes
- **THEN** the directory is queried once

#### Scenario: Listing and profile caches are separate

- **WHEN** a person appears in a cached listing and their profile is then viewed
- **THEN** the profile renders the full record rather than the narrowed listing
  record

#### Scenario: Empty result expires quickly

- **WHEN** a query returns nobody
- **THEN** the empty result is retried after 1 minute

### Requirement: Directory failure degrades gracefully

When the directory cannot be reached, refuses the bind, or fails a search, the
system SHALL log the failure and render an empty listing.

A directory failure MUST NOT produce a fatal error, a PHP warning in page
output, or a gateway timeout. Connection and search time limits MUST be set low
enough that a slow directory degrades to an empty section rather than an
upstream timeout.

#### Scenario: Bind refused

- **WHEN** the directory refuses the bind
- **THEN** the listing renders empty
- **AND** the failure is written to the error log

#### Scenario: Directory unreachable

- **WHEN** the directory host does not respond
- **THEN** the page renders without the listing and without a fatal error

#### Scenario: Slow directory

- **WHEN** the directory responds more slowly than the configured limits
- **THEN** the query is abandoned and the page still renders
