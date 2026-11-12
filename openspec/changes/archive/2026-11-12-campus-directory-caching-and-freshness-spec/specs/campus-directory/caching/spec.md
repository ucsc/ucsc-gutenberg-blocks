## Purpose

Defines how the Campus Directory block caches directory data and portraits,
how it backs off from an unavailable directory, and how out of date a rendered
block can be, so support can answer "how long until a directory change shows".

## ADDED Requirements

### Requirement: Directory data is cached per query and view

Directory data SHALL be cached per site, keyed by the exact directory query
and the kind of view that asked for it. Listings, listings that show photos,
and profiles MUST each have their own cache entry, because they request
different fields for the same people; a listing cached without photos MUST NOT
stand in for one that shows them.

Any two blocks, pages, or profile views on the same site that make the same
query for the same kind of view SHALL share one cache entry.

Nothing SHALL refresh the cache on a schedule. A page build that finds no
current entry queries the directory and stores the answer; a page build that
finds one uses it without contacting the directory.

#### Scenario: Repeat view is served from cache

- **WHEN** the same listing is built twice within 10 minutes
- **THEN** the directory is queried once

#### Scenario: Same settings on another page share the entry

- **WHEN** a second page on the same site has a block with the same audience
  and field settings as a cached block
- **THEN** it is built from the same cache entry without a directory query

#### Scenario: Listing and profile caches are separate

- **WHEN** a person appears in a cached listing and their profile is then viewed
- **THEN** the profile renders the full record rather than the narrowed listing
  record

#### Scenario: A listing without photos does not serve one with photos

- **WHEN** a listing without photos is cached and a block with the same query
  that shows photos is built
- **THEN** the directory is queried again, this time including photos

### Requirement: Directory data cache lifetimes

A directory answer with at least one person SHALL be cached for 10 minutes. An
answer with nobody in it SHALL be cached for 1 minute, so a query that matches
no one is not repeated on every page view.

A lookup the directory did not answer MUST NOT be cached: a refused bind, a
failed search, a search cut short by a time limit, or a skipped lookup during
a back-off. A search that stops at the result ceiling is an answer and SHALL be
cached.

Photo bytes MUST NOT be kept in a listing's cache entry. A profile's cache
entry holds the person's full record, including the photo.

#### Scenario: Successful lookup lifetime

- **WHEN** a query returns people
- **THEN** the answer is reused for 10 minutes

#### Scenario: Empty result expires quickly

- **WHEN** a query returns nobody
- **THEN** the empty answer is reused for 1 minute and the query is then
  repeated

#### Scenario: Failed lookup is not cached

- **WHEN** the directory refuses the bind, fails the search, or stops at its
  time limit
- **THEN** no cache entry is written and the next page build tries again,
  subject to the outage back-off

#### Scenario: Size-limited search is cached

- **WHEN** a search returns the result ceiling
- **THEN** the answer is cached like any other successful lookup

### Requirement: Directory outage back-off

When the directory host does not complete a TLS handshake within 3 seconds,
the system SHALL skip the directory for 5 minutes. When the directory refuses
the bind, the system SHALL skip it for 1 minute. While a back-off is in effect,
lookups return no people and are not cached, and no connection is attempted.

The back-off SHALL be per site and SHALL cover every directory lookup the
plugin makes on that site, including listings, profiles, and the photo route.

#### Scenario: Silent directory

- **WHEN** the directory host accepts the connection but does not complete the
  TLS handshake within 3 seconds
- **THEN** the listing renders empty
- **AND** the directory is not contacted again for 5 minutes

#### Scenario: Refused bind

- **WHEN** the directory refuses the bind
- **THEN** the listing renders empty
- **AND** the directory is not contacted again for 1 minute

### Requirement: Photos are cached separately from directory data

Portraits SHALL be stored as image files in one site's media storage per
network (the main site, unless an administrator filter names another), so
every site in the network reuses the same stored photo. Each stored file's name
MUST include part of a hash of the photo, so a changed photo gets a new URL and
no cache can keep serving the old image under the old name.

Stored photos MUST NOT be listed by the store site's public media API, and
MUST NOT be shown in its Media Library screens when the plugin is active on
that site; the image files themselves are publicly served.

Photos SHALL come only from the directory record's photo attribute. The system
MUST NOT fetch photos from the campus directory website, which does not check
whether a person is publicly listed.

#### Scenario: One stored photo serves every site

- **WHEN** a person's photo has been stored by a block on one site and a block
  on another site in the network shows the same person
- **THEN** the second site links to the already stored file without storing it
  again

#### Scenario: Changed photo gets a new address

- **WHEN** a person's directory photo changes and the new photo is stored
- **THEN** the page links to a different file URL than before
- **AND** the old stored file is deleted

### Requirement: Photo refresh follows fresh directory data

The stored photo SHALL be compared with the directory only when a fresh
directory record for that person is in hand: when a listing that shows photos
is fetched from the directory (not from cache), or when a profile is built
(from its own cached or fresh record). The photo cache MUST NOT query the
directory on its own.

When a fresh record is in hand:

- a photo that matches the stored one SHALL be left in place;
- a new or changed photo SHALL be stored and linked;
- a record with no photo (the person removed or hid it) SHALL delete the
  stored photo and show the placeholder.

When a listing is built from cache, each person's stored photo SHALL be linked
as is. A person with no stored photo whom the directory recently returned
without one SHALL get the placeholder, and that "no photo" answer SHALL be
remembered across the network for one hour. A person with neither SHALL be
linked to the photo route.

A stored photo is therefore at most as old as the cached record it was last
checked against: 10 minutes for a listing that shows photos or a profile.

#### Scenario: Photo change appears with the next fresh listing

- **WHEN** a person changes their directory photo and a listing showing them is
  next fetched from the directory
- **THEN** the new photo is stored and the listing links to it

#### Scenario: Cached listing reuses stored photos

- **WHEN** a listing showing photos is built from cache
- **THEN** each person's stored photo is linked without a directory query or a
  write

#### Scenario: Hidden photo is removed

- **WHEN** a fresh directory record has no photo for a person with a stored one
- **THEN** the stored photo is deleted and the placeholder renders

#### Scenario: Remembered "no photo"

- **WHEN** a listing is built from cache for a person the directory returned
  without a photo within the last hour, and nothing is stored for them
- **THEN** the placeholder renders without a request to the photo route

### Requirement: Photo storage limits and cleanup

One page build SHALL store at most 100 new or changed photos. People over the
limit SHALL be linked to the photo route if nothing is stored for them, or keep
their previously stored photo if one exists, until a later build stores the
new one. A write that fails SHALL show the placeholder rather than a broken
image.

Stored photos that no fresh directory record has confirmed for 24 hours SHALL
be deleted by a nightly job, never during a page view. The job SHALL do the
work at most once a night across the network.

#### Scenario: Write limit

- **WHEN** a page build would store more than 100 photos
- **THEN** 100 are stored and the rest are linked to the photo route or keep
  their older stored photo

#### Scenario: Nightly cleanup

- **WHEN** the nightly job runs and a stored photo has not been confirmed for
  24 hours
- **THEN** that photo is deleted
- **AND** photos confirmed within the last 24 hours are kept

### Requirement: Photo route

The plugin SHALL provide a public photo route for a CruzID, used only for
people whose photo is not stored yet. It looks the person up with the profile
query (sharing the profile cache), stores the photo, and redirects to the
stored file, or to the placeholder when there is no photo.

The route SHALL tell browsers to keep its answer for one hour when the
directory answered (a photo, or no photo). It MUST NOT set that browser cache
lifetime when the directory was unavailable or the person is not publicly
listed. Each response SHALL name where its answer came from in an
`X-Directory-Photo-Source` header.

#### Scenario: Photo not stored yet

- **WHEN** the browser requests the photo route for a person with a photo
- **THEN** the photo is stored and the browser is redirected to the stored file
- **AND** the browser may reuse that redirect for one hour

#### Scenario: Directory unavailable

- **WHEN** the browser requests the photo route while the directory is
  unavailable
- **THEN** it is redirected to the placeholder without a one-hour browser cache
  lifetime

### Requirement: Editor department list cache

The department list shown in the block editor SHALL be cached for one week. A
failed fetch returns an error, is not cached, and stops further fetch attempts
for 5 minutes. The division list is static and not cached.

#### Scenario: Department list is reused

- **WHEN** the editor loads the department list twice within a week
- **THEN** the campus directory website is fetched once

#### Scenario: Failed department fetch

- **WHEN** the campus directory website does not answer
- **THEN** the editor receives an error and the next attempt waits 5 minutes

### Requirement: Freshness of a rendered block

The block SHALL be rendered on the server into the page's HTML. When a page is
built, the directory data in it SHALL be at most 10 minutes older than the
directory (1 minute for an empty result), and its photos at most as old as the
record they were last checked against.

The plugin MUST NOT send page-level cache headers for pages that contain the
block. How long a host or CDN keeps and serves previously built page HTML is
outside the plugin; while that copy is served the block does not run, so the
page can show data older than 10 minutes. Changes made in the campus directory
take effect only after they reach LDAP, which is also outside the plugin.

#### Scenario: New page shows current data

- **WHEN** a directory change has reached LDAP more than 10 minutes ago and a
  page with the block is built for the first time
- **THEN** the page shows the change

#### Scenario: Host page cache serves an older copy

- **WHEN** the host serves a copy of the page built before the directory change
- **THEN** the page shows the old data until the host builds the page again,
  regardless of the plugin's 10-minute cache
