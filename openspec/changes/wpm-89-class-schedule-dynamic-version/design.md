## Context

`registerJSBuild()` in `index.php` (hooked to `admin_enqueue_scripts`) already
reads the plugin `Version` header into `$plugin_version` and enqueues
`build/index.js` under the handle `ucscblocks`. In development it uses the
build file's mtime as the script `ver` for cache-busting, and the plugin
version otherwise. `src/blocks/ClassSchedule.js` renders
`<small>version 1.1.38</small>` at the bottom of its settings `PanelBody`.

Releases run `commit-and-tag-version`, which rewrites the header through
`wp-plugin-version-updater.js`. That makes the header the single source of
truth for the version.

## Goals / Non-Goals

**Goals:**
- One source of truth for the displayed version: the plugin header.
- No build step or rebuild needed for the label to pick up a new version.

**Non-Goals:**
- Showing the version on Campus Directory or Course Catalog. Only Class
  Schedule shows one today.
- Changing how the editor script is cache-busted (`ver`).
- Changing the label's styling or position.

## Decisions

**1. Pass the version at runtime with `wp_localize_script`, as WPM-89 suggests.**
Call `wp_localize_script('ucscblocks', 'ucscBlocksConfig', array('version' => $plugin_version))`
immediately after the `wp_enqueue_script` in `registerJSBuild()`. This exposes
`window.ucscBlocksConfig.version` before `build/index.js` runs.
- The ticket's `'your-classschedule-handle'` is a placeholder. `ucscblocks` is
  the real handle; the Class Schedule editor code is bundled into it.
- Use `$plugin_version`, never `$script_version`, so the development mtime
  doesn't reach the label.
- *Alternative, build-time injection* (import `package.json` or webpack
  `DefinePlugin`): rejected. `build/` would have to be rebuilt after every
  version bump, and `package.json` can drift from the header.
- *Alternative, `wp_add_inline_script`*: equivalent, but `wp_localize_script`
  is what the ticket specifies and is the familiar WordPress pattern.
  `wp_localize_script` casts values to strings, which is fine here.

**2. Guard on the JS side and hide the label when the version is missing.**
`ClassSchedule.js` reads `window.ucscBlocksConfig?.version` and renders the
`<small>` only when it is a non-empty string. This covers the Jest environment,
any context that loads the bundle without the localized object, and the
`$plugin_version === false` case. If `$plugin_version` is `false`, PHP
localizes it as `""`.

**3. Skip localizing when there is no version?**
No: always call `wp_localize_script`. Localizing `""` is harmless because the JS
guard hides it, and it keeps the PHP path branch-free.

## Risks / Trade-offs

- [Another script on the page also defines `window.ucscBlocksConfig`] → Low
  risk. The name is plugin-prefixed, and the object is only printed on admin
  pages where `ucscblocks` is enqueued.
- [Stale `build/` shipped with the old literal] → Rebuild in-container as part
  of the tasks, and check that `build/index.js` no longer contains `1.1.38`.
- [PHP wiring test harness has no `wp_localize_script` stub] → Add a recording
  stub alongside the existing `wp_enqueue_script` stub.
