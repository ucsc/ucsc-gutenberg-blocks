## Context

This change documents a block that already ships. See `proposal.md` — Why for
motivation, and `specs/campus-directory/**` for the behavior contract. This
document records the design the current implementation embodies, so that future
changes have stated rationale to argue with rather than inferred intent.

Constraints that shape everything below:

- **The directory is external and untrusted.** Person records are maintained in
  campus LDAP, not in WordPress. The site cannot validate them, cannot fix a
  malformed value, and must assume any field may contain markup.
- **The directory is expensive and often unreachable.** Reaching
  `ldap-blue.ucsc.edu` requires the PHP LDAP extension and network access that,
  from a developer machine, means the UCSC VPN. This is why the local Docker
  image is custom-built rather than an off-the-shelf WordPress runtime.
- **Rendering happens on a shared host with an edge proxy.** CampusPress fronts
  the site with a ~30s proxy timeout, so a slow directory must degrade to an
  empty section rather than a 504.
- **Editors are not technical.** The editor UI speaks in human labels ("Office
  Hours"), not directory attribute names.

## Goals / Non-Goals

**Goals:**

- Record why per-person field selection is stored the way it is, since that
  storage shape constrains every future field addition.
- Record the query-construction and caching design, which is where the block's
  cost and its safety ceilings live.
- Make the currently-open audit findings visible as risks against a written
  contract, rather than leaving them in a loose audit file.

**Non-Goals:**

- Remediating the open audit findings. The change was scoped as a baseline
  spec; remediation is a separate change (see Open Questions).
- Redesigning the attribute storage shape, the shortcode, or the editor control
  hierarchy. Documenting a design is not endorsing it.
- Specifying test implementation. Coverage work for this block is tracked in
  `docs/jira/WPM-118-campus-directory.md`.

## Decisions

### Server-side rendering with `save()` returning `null`

Directory data changes without anyone touching the post. If the block saved
markup, every published listing would freeze at its save-time contents and every
rendering fix would require re-saving posts across the network. Storing only
configuration and rendering in PHP means a directory update is live immediately
and a template fix reaches every existing post.

*Alternative considered:* a static block with client-side hydration. Rejected —
it would put an unauthenticated LDAP-backed endpoint in front of every visitor
and lose server-side caching.

### Field selection stored as a JSON string keyed by human label

`strInformationTypes` and `strInformationTypesTable` hold a JSON-stringified
`{ "Office Hours": true, ... }` map rather than typed array attributes.

Two consequences are load-bearing:

- **Every field is present, including deselected ones.** A field the author
  turned off is stored as `false`, not omitted. That is what makes a field added
  in a later release distinguishable from one the author declined — a missing
  key means "new", a `false` means "no". Dropping the `false` entries to save
  space would destroy that distinction.
- **The key is the display label, not the directory attribute.** The
  label→attribute mapping lives server-side in
  `CampusDirectoryAPI::getInformationToDisplay()`, so the directory schema can
  change without rewriting saved post content.

*Trade-offs:* a JSON-in-a-string attribute is opaque to WordPress — no schema
validation, no block-deprecation path, and a `json_decode` on every render.
Renaming a display label is a breaking change to saved content.

*Alternative considered:* typed `array`/`object` attributes with attribute
names as keys. Better on every axis except migration — it would require a
`deprecated` migration across all published posts, which is why the current
shape persists.

### Two selections, keyed by layout family, not one

The table layout has no cell for a portrait and generally shows fewer fields
than a card. A single shared selection would mean an author who compares layouts
loses the configuration for whichever one they left. Keeping
`strInformationTypes` (list, tiled) and `strInformationTypesTable` separate
makes layout switching non-destructive.

List and tiled deliberately *share* one selection: they differ in presentation,
not in what a person's card can hold.

### Requested attributes are narrowed per view, and caches are keyed per view

A listing never renders `jpegphoto` or the profile-only fields, so listing
queries request an explicit attribute allowlist (`listAttributes()`) while
profile and shortcode queries request the full entry. Pulling image data for
every person in a 200-person division listing is the single most expensive thing
this block could do.

The profile-only fields are the concrete reason the full entry is requested:
`ucscpersonpubfacultycourses` (courses taught) plus the ten scholarly fields —
research interests, biography, teaching interests, awards, selected exhibitions,
performances, presentations, publications, and recordings. None of them appear
in `listAttributes()`, so a profile that reused a listing's narrowed record would
silently render as a bare contact card.

Note what this decision also rules out: **course information on a profile comes
from the directory record, not from PeopleSoft.** The Class Schedule and Course
Catalog blocks proxy PeopleSoft through `ucsc/v1` for course *offerings*; this
block reads what a person's directory entry says they teach. Wiring the profile
to PeopleSoft would add a second upstream dependency to a page that already has
one, and would produce two answers to "what does this person teach" with no rule
for which wins. The directory record is authoritative here because it is what
the person and their department maintain.

Because the two views request different fields for the same person, they must
not share a cache entry — hence the `_l` / `_p` suffix on the `md5(filter)` key.
A shared key would let a cached listing record starve a profile of the fields it
needs.

Empty results are cached too, but for 60s rather than 600s: an unreachable
directory should retry soon, not be remembered for ten minutes as "nobody works
here".

### Filter construction: union the affiliations, then intersect the scope

`buildFilterString()` accumulates one clause per selected affiliation group
(graduate, faculty, staff), ORs them, then wraps that union in an AND with the
department or division clause. Counting clauses (`$this->count`) rather than
string-inspecting is what decides whether the `(|...)` wrapper is needed.

Two guards matter more than the shape:

- **Exclusions only subtract from a non-empty filter.** `(&(!(uid=x)))` matches
  the entire directory. `processAddExcludeFilterString()` therefore applies
  excludes only when a feed filter already exists.
- **An empty filter never queries.** `doLDAPQuery()` bails on an empty `$q`
  before connecting, because the caller wraps it as `(|{$q})` — `(|)` is
  malformed. The guard sits at the chokepoint so every caller inherits it, not
  just `getCampusDirData()`.

### Escaping at filter construction, ceilings at connection

CruzIDs and scope values are escaped with `ldap_escape(..., LDAP_ESCAPE_FILTER)`
at the point they enter a filter (`buildUidFilter()`,
`processDeptDivFilterString()`) rather than at input. Escaping at the boundary
where the value becomes syntax is the more reliable place — it cannot be
bypassed by a new caller that forgets to sanitize.

Independently, every search sets a size limit: 1000 with an organizational
scope, 50 without. The 50 is a deliberate degradation — a misconfigured feed
returns a visibly wrong small sample rather than a campus-wide dump.

### Profiles render inline, with the standalone template retained

A profile requested against a singular page is appended to that page's content
(`the_content`, priority 20) so the active theme keeps its header, navigation,
and footer (WPM-99). The `template_include` swap to `DirectoryProfileTemplate`
remains for requests that are not singular main-query pages (WPM-114).

The inline path is guarded on `is_singular()`, `is_main_query()`, `!is_admin()`,
and `get_the_ID() === get_queried_object_id()` — without all four, the profile
leaks into excerpts, secondary loops, and feeds.

### The requirements endpoint is public

`ucscgutenbergblocks/v1/campusdirectoryrequirements` reports only whether an
LDAP password is set — a boolean and a multisite flag, no credential material.
It is unauthenticated because the editor needs it before it can render, and the
information it leaks is not sensitive.

*Trade-off:* it is one more unauthenticated surface. It is cheap and touches no
external system, so it does not carry the cold-cache risk that
`SiteSettings`'s dropdown endpoint does (see Risks).

## Risks / Trade-offs

- **Listing template and shortcode emit unescaped directory data** → The
  `frontend-render` spec states the escaping requirement, but
  `CampusDirectoryTemplate.php` and `CampusDirectoryShortcode.php` do not yet
  meet it; `DirectoryProfileTemplate.php` largely does. This is the top finding
  in `audit-campus-directory.md` and is a public XSS surface fed by data this
  site does not control. Mitigation: remediate in a follow-up change, and treat
  the spec as the acceptance criterion.

- **Publish guard checks the wrong condition** → The editor locks saving only
  when `department` *and* `division` are both `---`. With `deptOrDiv === 'dept'`,
  an empty department, and a stale division left over from a previous choice,
  saving is allowed and the render falls back to affiliation-only matching —
  the 50-record ceiling then shows an arbitrary sample. The `block-registration`
  spec states the correct behavior (validate against the *active* scope).

- **The public dropdown endpoint can block on a cold cache** →
  `SiteSettings`'s department endpoint fetches `campusdirectory.ucsc.edu` with
  `file_get_contents()` and dereferences DOM nodes without robust checks. Any
  visitor can trigger the outbound request. Mitigation: `wp_remote_get()` with
  an explicit timeout, validated response, and a cached fallback.

- **LDAP + VPN makes the data path untestable in CI** → No automated suite can
  exercise a real directory query. Mitigation: fixture-driven PHP tests at the
  filter-construction and template boundaries, which is where the logic and the
  escaping live; live LDAP behavior stays a manual/e2e concern.

- **`CheckboxGroupControl` calls `useState` inside `.map()`** → Hook count
  depends on the label array, and `setAttributes` is called during render when
  the attribute is undefined. It works because the label arrays are static
  constants, but it violates the rules of hooks and will break the moment the
  field list becomes dynamic. Any change that makes fields configurable must
  restructure this component first.

- **The shortcode is a parallel implementation of profile rendering** → It
  duplicates field selection, data fetching, and markup that
  `DirectoryProfileTemplate.php` already does, with its own attribute
  vocabulary. Every profile change must be made twice or one surface drifts.

- **Documenting a design is not ratifying it** → Several decisions above
  (JSON-string attributes, the shortcode duplication) are recorded because they
  constrain future work, not because they are right.

## Migration Plan

None. No code changes, no data changes, no deploy. On `openspec archive`, the
six delta specs become the baseline under `openspec/specs/campus-directory/**`;
subsequent Campus Directory changes then carry MODIFIED deltas against them
rather than starting from nothing.

## Open Questions

- Should audit remediation be one change or three? The output-escaping work is
  large enough and urgent enough to stand alone; the publish guard and the
  dropdown endpoint are small. Deferrable — it does not change these specs, only
  which change fixes them.
- Should the `ucsc_profiles` shortcode remain a separate surface, or be
  reimplemented on top of the profile template? Deferrable — the spec describes
  the shortcode's observable contract either way.
