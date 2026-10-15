## MODIFIED Requirements

### Requirement: Per-field visibility selection

The block SHALL let an author choose, from a fixed set of directory fields,
which fields are rendered for every person in the listing. Selection MUST be
per-field and independent: turning one field off MUST NOT change any other.

Fields not selected MUST NOT appear in the rendered output for any person.
Selection does not change what is requested from the directory: every listing
requests the same fixed, narrowed attribute set (see `ldap-data-access`).

The selectable fields SHALL be:

| Field label | Directory field |
|---|---|
| Pronouns | `ucscpersonpubpronouns` |
| Photo | portrait fetched from the campus directory photo service by CruzID (list and tiled layouts only) |
| Title | `title` |
| Department | `ucscpersonpubdepartmentnumber` |
| Phone | `telephonenumber` |
| Campus Email | `mail` |
| Other Email | `ucscpersonpubalternatemail` |
| Fax | `facsimiletelephonenumber` |
| Website | `ucscpersonpubwebsite` |
| Office Location | `ucscpersonpubofficelocationdetail` |
| Office Hours | `ucscpersonpubofficehours` |
| Mailstop | `ucscpersonpubmailstop` |
| Mailing Address | placeholder key `combineaddressfields` (see known defect CD-D5) |
| Faculty Areas of Expertise | `ucscpersonpubexpertisereference` |
| Summary of Expertise | `ucscpersonpubareaofexpertise` |

A person's name is always rendered and is not a selectable field.

**Known defect (CD-D5, no Jira yet):** Mailing Address maps to the placeholder
key `combineaddressfields`, which no directory record carries, so selecting it
renders nothing in any listing layout.

#### Scenario: Deselected field disappears from every person

- **WHEN** the author deselects Office Hours and the post is viewed
- **THEN** no person in the listing renders an office hours value
- **AND** every other selected field still renders

#### Scenario: Selection applies uniformly

- **WHEN** the author selects Pronouns
- **THEN** every person in the listing who has a pronouns value renders it

#### Scenario: Person missing a selected field

- **WHEN** a selected field has no value for a given person
- **THEN** that person renders without that field
- **AND** the rest of the listing is unaffected

#### Scenario: Mailing Address renders nothing (known defect CD-D5)

- **WHEN** the author selects Mailing Address for a list layout
- **THEN** no person renders a mailing address
