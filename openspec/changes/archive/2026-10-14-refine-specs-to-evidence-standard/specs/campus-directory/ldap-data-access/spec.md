## MODIFIED Requirements

### Requirement: Untrusted values are escaped before querying

Every value that reaches a directory query from author configuration, a saved
block attribute, or a URL — CruzIDs, departments, divisions, and scope
selectors — MUST be escaped for LDAP filter syntax before it is combined into a
filter.

A value containing LDAP filter metacharacters MUST NOT be able to alter the
structure of the resulting query, broaden its scope, or make it match records
the configuration did not select.

**Known defect (CD-D1, no Jira yet):** the faculty-type keys from the saved
`strFacultyTypes` attribute are interpolated unescaped into
`(ucscpersonpubfacultytype=...)` clauses. Exploiting it needs an
author-crafted block attribute, and the injected clauses stay inside the
scoped AND. CruzIDs, department, division, and `deptOrDiv` are escaped.

#### Scenario: Metacharacters in a CruzID

- **WHEN** a CruzID containing LDAP filter metacharacters is supplied through a
  manual list, an add or exclude list, or a profile URL
- **THEN** the resulting query treats it as a literal value
- **AND** the listing does not include records outside the configured scope

#### Scenario: Metacharacters in a department

- **WHEN** a saved department value contains LDAP filter metacharacters
- **THEN** the resulting query treats it as a literal value

#### Scenario: Metacharacters in a faculty-type key (known defect CD-D1)

- **WHEN** a saved `strFacultyTypes` key contains LDAP filter metacharacters
- **THEN** the key is inserted into the filter unescaped
