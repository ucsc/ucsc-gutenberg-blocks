## MODIFIED Requirements

### Requirement: Vacant positions

The author SHALL be able to list a position that has no incumbent by entering
`%Name%Title%` in place of a CruzID, so an org listing can show an open role.

A vacant entry MUST render with only the supplied name and title, without a
directory record. (In a manual list the entry is still included verbatim as a
`uid` clause in the query, where it matches nothing.)

**Known defect (CD-D6, no Jira yet):** because a vacant entry has no CruzID, a
listing with name linking enabled links its name to an empty local profile
(`?directoryprofilecruzid=`) and emits PHP warnings.

#### Scenario: Vacant entry in a manual list

- **WHEN** the author enters `%Vacant%Assistant Director%` in the manual list
- **THEN** the listing renders an entry named "Vacant" with the title
  "Assistant Director" in the position given

#### Scenario: Vacant entry appended to a feed

- **WHEN** the author enters a vacant entry in the add list of an automated feed
- **THEN** the entry is appended after the people the feed matched

#### Scenario: Vacant entry with name linking (known defect CD-D6)

- **WHEN** a listing with local-profile name linking contains a vacant entry
- **THEN** the vacant name links to `?directoryprofilecruzid=` with no CruzID
