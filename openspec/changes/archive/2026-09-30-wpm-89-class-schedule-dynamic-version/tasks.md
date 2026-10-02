## 1. PHP: expose the plugin version to the editor

- [x] 1.1 In `tests/php/PluginWiringTest.php`, add a recording `wp_localize_script` stub. Add checks that `ucscblocks` is localized as `ucscBlocksConfig` with `version` equal to the plugin header (`1.2.1`) in both production and local-environment runs, never the build mtime. Verify the new checks fail when run in-container (`php tests/php/PluginWiringTest.php`).
- [x] 1.2 In `registerJSBuild()` in `index.php`, call `wp_localize_script('ucscblocks', 'ucscBlocksConfig', array('version' => $plugin_version))` after the enqueue. Verify the 1.1 checks and all existing PluginWiringTest checks pass.

## 2. Editor: read the version instead of the literal

- [x] 2.1 In `src/blocks/__tests__/ClassSchedule.test.js`, add tests: with `window.ucscBlocksConfig = { version: '9.9.9' }` the panel renders `version 9.9.9`; with the global unset or `version: ''` no version label renders; the text `1.1.38` never renders. Verify they fail under in-container `npm test`.
- [x] 2.2 In `src/blocks/ClassSchedule.js`, replace the hard-coded `<small>version 1.1.38</small>` with a render guarded on `window.ucscBlocksConfig?.version`, keeping the existing styling. Verify the 2.1 tests and the full Jest suite pass.

## 3. Build and live check

- [x] 3.1 Rebuild in-container (`npm run build` via `plugin_npm_start`). Verify `build/index.js` no longer contains `1.1.38` and `npm run lint:js` reports no new errors in `ClassSchedule.js`.
- [x] 3.2 In the running wp-dev.ucsc editor, insert or open a Class Schedule block. Verify the settings panel shows `version 1.2.1`, matching the `index.php` header, and that the page source defines `ucscBlocksConfig`.
