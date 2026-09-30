## Why

The Class Schedule block's editor sidebar shows a hard-coded `version 1.1.38`,
but the plugin is at 1.2.1. The label drifts on every release, so editors and
support staff can't trust it to identify what's deployed (WPM-89).

## What Changes

- The version label in the Class Schedule editor sidebar shows the plugin's
  real version, taken from the plugin header. The release tooling already
  updates that header.
- The hard-coded version string is removed from the editor script.
- If no version is available, the label is not shown, rather than showing a
  wrong or placeholder value.

## Capabilities

### New Capabilities

_None._

### Modified Capabilities

- `class-schedule/block-registration`: adds a requirement that the editor's
  version label reflects the installed plugin version.

## Impact

- `index.php`: the editor script registration also passes the plugin version to
  the editor script.
- `src/blocks/ClassSchedule.js`: the version label reads the passed-in value
  instead of a literal.
- Tests: `src/blocks/__tests__/ClassSchedule.test.js` (Jest) and
  `tests/php/PluginWiringTest.php` (PHP wiring).
- `build/`: must be rebuilt so the shipped editor bundle no longer contains the
  literal.
- No front-end, REST or block-attribute changes. Existing saved blocks are
  unaffected.
