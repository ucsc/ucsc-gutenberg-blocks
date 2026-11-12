## Context

See proposal.md for why. The specs follow the evidence standard from
`2026-10-14-refine-specs-to-evidence-standard`: SHALL describes what ships,
and known defects are labeled, never written as the fix. The source of truth
is `main` at `51b82d5` (v1.3.1), not what CampusPress currently runs.

Directory data and photos use different mechanisms:

- Data: a per-site WordPress transient per query and view (`_l`, `_lp`, `_p`
  suffixes), 600 s or 60 s.
- Photos: private attachments on one store site, kept current by comparing a
  hash whenever a fresh LDAP entry is in hand, plus a network-wide "no photo"
  site transient (1 h) and a nightly prune.
- Page HTML: the block renders server-side, so the host's page cache can hold
  its output. On the PBSci CampusPress site, `science.ucsc.edu` returned
  `cache-control: max-age=3600, must-revalidate`, and a second request one
  second later had the same `last-modified` (observed 2026-11-12). The plugin
  sends no such header for pages.

## Goals / Non-Goals

**Goals:**

- One capability a reader can use to answer "how fresh is this block".
- Keep data caching and photo caching as separate requirements, since they
  refresh differently.
- State the plugin/host boundary without specifying host behavior.

**Non-Goals:**

- Changing any cache lifetime or adding page cache headers.
- Specifying CampusPress's page cache. Its lifetime may differ per site and is
  not ours to promise; the spec only says the plugin does not control it.
- Fixing gaps in test evidence.

## Decisions

### D1. New `campus-directory/caching` capability rather than growing `ldap-data-access`

Photo caching is not LDAP access, and the freshness question spans data,
photos, and the host. Alternative: modify "Results are cached" in place and add
photo requirements to `frontend-render`. Rejected because the answer to one
question would be split across three files.

"Directory failure degrades gracefully" stays in `ldap-data-access`; its WPM-233
scenario already matches the 5-minute back-off. The new "Directory outage
back-off" requirement adds the 1-minute bind back-off and the per-site scope.

### D2. Requirements avoid internal names

Per the spec instructions, requirements say "cache entry", "photo route" and
"store site", not transient keys or class names. Exceptions: the
`X-Directory-Photo-Source` header, because clients and support can see it.

### D3. Host page cache stated as a boundary

The freshness requirement promises only what the plugin controls (data at most
10 minutes old when a page is built) and states that a host copy of the HTML
can be older. Alternative: specify a one-hour bound from the CampusPress
observation. Rejected: observed on one site only, and the host can change it.

## Risks / Trade-offs

- [Spec says SHALL for behavior only code-inspected] → Tasks record the
  evidence level per requirement; the photo route's `Cache-Control` and
  `X-Directory-Photo-Source` headers have no PHP test.
- [Profile and list photos are synced from different cached records] → A
  profile built from a 10-minute-old cached record could store an older photo
  than a listing stored moments before, until the next fresh record. This is
  inferred from code, not observed; see Open Questions. The spec's "at most as
  old as the record it was last checked against" holds either way.

## Migration Plan

Spec-only. Archive with `openspec archive`; it creates
`openspec/specs/campus-directory/caching/spec.md`, removes "Results are
cached" from `ldap-data-access`, and replaces "Portraits" in
`frontend-render`. Nothing to deploy or roll back.

## Open Questions

- Can a profile view swap a newer stored photo back to an older one within the
  10-minute window (see Risks)? If confirmed, it becomes a labeled known defect
  with a Jira issue; the requirements here do not change.
- Does clicking Update on a page clear the CampusPress page cache for that
  page? Useful for support answers; outside the plugin's spec.

Candidate WPM issues from `verification.md` (not filed; filing needs approval):

- Photo route: no test for the redirect target, `X-Directory-Photo-Source`, or
  the one-hour `Cache-Control` only for `ldap`/`none` (handler calls `exit`).
- Outage back-off: no test that it covers profiles and the photo route, or
  that it is per site.
- Shared cache entry across pages with the same settings: no test.
- Media Library hiding on the store site: no test.
- Profile/list photo swap (see Risks): unverified.
- Portraits spec gap: `photofallback.js` swaps every unloaded directory photo
  to the placeholder after 4 seconds when none has loaded. Not stated in
  "Portraits"; left for a later change rather than widening this one.
