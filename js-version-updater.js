// Keeps the editor-sidebar version indicator in src/blocks/ClassSchedule.js in
// sync with the release version. Anchored on the JSX text node `>version X.Y.Z<`
// so it cannot match an unrelated string. Throws loudly rather than silently
// letting the displayed version drift if the markup is reformatted.
const regex = />version (?<vnum>[0-9]+\.[0-9]+\.[0-9]+)</;

module.exports.readVersion = function (contents) {
  const found = contents.match(regex);
  if (!found) {
    throw new Error(
      'js-version-updater: could not find a `>version X.Y.Z<` indicator. ' +
      'If the markup changed, update the regex in js-version-updater.js.'
    );
  }
  return found.groups.vnum;
};

module.exports.writeVersion = function (_contents, version) {
  if (!regex.test(_contents)) {
    throw new Error(
      'js-version-updater: could not find a `>version X.Y.Z<` indicator to write. ' +
      'If the markup changed, update the regex in js-version-updater.js.'
    );
  }
  return _contents.replace(regex, '>version ' + version + '<');
};
