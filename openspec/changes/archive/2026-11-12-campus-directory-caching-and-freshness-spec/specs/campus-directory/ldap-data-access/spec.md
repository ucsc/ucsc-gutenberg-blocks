## REMOVED Requirements

### Requirement: Results are cached

**Reason**: Caching is now specified in full by the new
`campus-directory/caching` capability. This requirement's rationale was also
wrong: it said empty results are cached for 1 minute "so an unreachable
directory is retried soon", but a lookup the directory did not answer is never
cached, and outages are handled by a separate back-off.

**Migration**: See `campus-directory/caching`: "Directory data is cached per
query and view", "Directory data cache lifetimes", "Directory outage back-off",
and "Editor department list cache". The scenarios "Repeat view is served from
cache", "Listing and profile caches are separate" and "Empty result expires
quickly" carry over there.
