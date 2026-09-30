## ADDED Requirements

### Requirement: Editor version label reflects installed plugin version

The Class Schedule block's editor settings panel SHALL show a version label
whose value is the `Version` header of the installed plugin. The value SHALL
NOT be hard-coded in the editor script, so each release updates the label with
no source edit.

If the plugin version is not available to the editor, the panel SHALL omit the
version label entirely rather than show an empty, placeholder or stale value.

#### Scenario: Label matches the plugin header
- **WHEN** the plugin header declares `Version: 1.2.1` and an editor opens a
  Class Schedule block's settings panel
- **THEN** the panel shows `version 1.2.1`

#### Scenario: Label follows a release bump
- **WHEN** the plugin header version changes (for example to `1.3.0`) and the
  editor is reloaded
- **THEN** the panel shows `version 1.3.0` without any change to the editor
  script source

#### Scenario: Version unavailable
- **WHEN** the editor opens a Class Schedule block's settings panel and no
  plugin version has been provided to the editor
- **THEN** no version label is rendered in the panel

#### Scenario: Development cache-busting does not leak into the label
- **WHEN** the site runs in a local or development environment, where the
  editor script is cache-busted by file modification time
- **THEN** the panel still shows the plugin header version, not the timestamp
