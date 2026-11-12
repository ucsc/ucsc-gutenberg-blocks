## Why

RITM0207088 asked how long a Campus Directory block takes to show a person's
directory change, and the specs could not answer it. Caching is described in one
requirement of `ldap-data-access`, part of its rationale is wrong (it says empty
results are cached briefly so an unreachable directory is retried soon, but
failed lookups are not cached at all), and the photo cache shipped in 1.3.0 and
1.3.1 (commits `4239401`, `4a0ef01`) is not in any spec. `frontend-render` still
says portraits come from "the campus directory photo service by CruzID", which
the code no longer does.

This change writes down, from the code on `main` at `51b82d5` (v1.3.1), how the
plugin caches directory data and photos, how long each can be out of date, and
where the plugin's responsibility ends and the host's page cache begins.

## What Changes

- New capability `campus-directory/caching` that states, as shipped behavior:
  - Directory data: a per-site cache keyed by the exact query and view (list,
    list with photos, profile), 10 minutes for results, 1 minute for an empty
    result, never for a failed lookup. Blocks and pages with the same settings
    share one entry. Nothing refreshes on a schedule; a page view that misses
    the cache queries LDAP.
  - Outage back-off: 5 minutes after LDAP fails the 3-second handshake check,
    1 minute after a refused bind.
  - Photos, cached separately from data: stored once per network on one store
    site, refreshed only when a fresh LDAP entry for the person arrives, named
    by content hash, deleted when the person hides their photo, "no photo"
    remembered network-wide for one hour, at most 100 photo writes per render,
    and unconfirmed photos pruned nightly after 24 hours.
  - The REST photo route as a fallback, with its one-hour browser cache.
  - The editor's department list cache (one week; failures not cached), moved
    here unchanged.
  - Freshness bounds: what the plugin guarantees (data at most 10 minutes old
    when a page is built) and what it does not control (the host's full-page
    cache, which can serve previously built HTML for longer; the plugin sends
    no page cache headers).
- `campus-directory/ldap-data-access`: remove "Results are cached"; its content
  moves to the new capability with the rationale corrected.
- `campus-directory/frontend-render`: modify "Portraits" so the source is the
  stored photo (or the REST route when not stored yet), not the campus
  directory photo service.

No code changes. Every new statement is checked against the code and existing
PHP tests at `51b82d5`.

## Capabilities

### New Capabilities

- `campus-directory/caching`: how the plugin caches directory data and photos,
  backs off from an unavailable directory, and how fresh a rendered block can
  be.

### Modified Capabilities

- `campus-directory/ldap-data-access`: "Results are cached" is removed and
  superseded by `campus-directory/caching`.
- `campus-directory/frontend-render`: "Portraits" describes the stored photo
  and REST fallback instead of the campus directory photo service.

## Impact

- Specs only: `openspec/specs/campus-directory/{caching,ldap-data-access,frontend-render}/spec.md`
  after archive.
- No PHP, JS, template or test changes. Gaps in test evidence (the photo
  route's `Cache-Control` header has no test) are recorded, not fixed.
- Gives support a citable answer for questions like RITM0207088.
