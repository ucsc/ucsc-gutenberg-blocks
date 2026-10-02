#!/bin/bash
# Run this plugin's test tiers locally in Docker. Follows the UCSC testing standard
# (baseapp doc/TESTING-STANDARDS.md, "Tiers and entry points"), minus the Laravel parts.
# A host-side wrapper only: it starts one-off containers, then calls the standard
# composer/npm test scripts, which hold the test definitions.
# Usage: scripts/test-tiers.sh [gate|all] [--php|--js] [-- extra args]
#   gate  PHP tests and Jest, no coverage (default)
#   all   PHP tests and Jest with coverage into coverage/; when both sides run and
#         pass, also writes the dated snapshot docs/coverage/<date>.md
#   -- extra args  passed to the PHP runner (with --php) or Jest (with --js), e.g.
#                  scripts/test-tiers.sh gate --php -- tests/php/CourseCatalogTest.php
# Needs only Docker: no WordPress stack, no host PHP or Node.
# Must stay Bash 3.2 compatible (macOS default).
set -u
cd "$(dirname "$0")/.." || exit 1

tier="gate"
run_php=1
run_js=1
usage="Usage: $0 [gate|all] [--php|--js] [-- extra args]"
while [ $# -gt 0 ]; do
  case "$1" in
    gate|all) tier="$1" ;;
    --php) run_js=0 ;;
    --js) run_php=0 ;;
    --) shift; break ;;
    *) echo "$usage" >&2; exit 2 ;;
  esac
  shift
done
# whatever is left in "$@" is extra args; they only make sense for one side
if [ $# -gt 0 ] && [ "$run_php" -eq 1 ] && [ "$run_js" -eq 1 ]; then
  echo "Extra args need --php or --js. $usage" >&2
  exit 2
fi

user="$(id -u):$(id -g)"
php_image="ucsc-gutenberg-blocks-php-test:coverage"
node_image="node:22-alpine"
status=0

# JS runs first: Jest empties coverage/ before writing its reports, which would
# delete the PHP reports if PHP ran first.
if [ "$run_js" -eq 1 ]; then
  case "$tier" in
    gate) npm_script="test" ;;
    all) npm_script="test:coverage" ;;
  esac
  if [ ! -d node_modules ]; then
    docker run --rm -u "$user" -e HOME=/tmp -v "$PWD":/plugin -w /plugin "$node_image" npm ci || exit 1
  fi
  docker run --rm -u "$user" -e HOME=/tmp -v "$PWD":/plugin -w /plugin \
    "$node_image" npm run "$npm_script" -- "$@" || status=1
fi

if [ "$run_php" -eq 1 ]; then
  case "$tier" in
    gate) composer_script="test"; xdebug_mode="off" ;;
    all) composer_script="test:coverage"; xdebug_mode="coverage" ;;
  esac
  docker build -q -f tests/php/Dockerfile.coverage -t "$php_image" tests/php >/dev/null || exit 1
  docker run --rm -u "$user" -e HOME=/tmp -e XDEBUG_MODE="$xdebug_mode" -v "$PWD":/plugin -w /plugin \
    "$php_image" composer "$composer_script" -- "$@" || status=1
fi

if [ "$tier" = "all" ] && [ "$run_php" -eq 1 ] && [ "$run_js" -eq 1 ] && [ $# -eq 0 ]; then
  if [ "$status" -eq 0 ]; then
    sha="$(git rev-parse --short HEAD)"
    if ! git diff --quiet HEAD -- classes templates src index.php tests jest-unit.config.js; then
      sha="$sha plus uncommitted changes"
    fi
    docker run --rm -u "$user" -e HOME=/tmp -v "$PWD":/plugin -w /plugin \
      "$php_image" composer coverage:snapshot -- "$(date +%F)" "$sha" || status=1
  else
    echo "Tests failed, so no coverage snapshot was written." >&2
  fi
fi

exit $status
