#!/bin/bash
# validate-php.sh — PHP test driver. Runs BOTH PHP suites with no host PHP:
#   1. standalone dependency-free tests (tests/php/*.php), each its own process
#   2. the PHPUnit template suite (tests/phpunit/, via the bundled phpunit.phar)
# everything inside a php:8.1-cli container with the plugin mounted at /plugin.
#
# Logs to $UCSC_LOG_DIR/ucsc-validate-php.log (default /tmp). Exits non-zero if
# any standalone file or the PHPUnit run fails.
#
# Usage:
#   bash bin/validate-php.sh
# Env:
#   UCSC_LOG_DIR=/tmp     where the .log file is written
#   PHP_IMAGE=php:8.1-cli  container image used for both suites
set -uo pipefail

case "${1:-}" in
  --help|-h|help)
    echo "Usage: bash bin/validate-php.sh"
    echo "Runs tests/php/*.php (standalone) + tests/phpunit (PHPUnit) in php:8.1-cli."
    echo "Env: UCSC_LOG_DIR=/tmp  PHP_IMAGE=php:8.1-cli"
    exit 0 ;;
esac

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_ROOT="$(cd "$HERE/.." && pwd)"
LOG_DIR="${UCSC_LOG_DIR:-/tmp}"
LOG="$LOG_DIR/ucsc-validate-php.log"
IMAGE="${PHP_IMAGE:-php:8.1-cli}"
mkdir -p "$LOG_DIR"

if ! docker info >/dev/null 2>&1; then
  echo "Docker daemon not running — start Docker Desktop." | tee "$LOG" >&2
  exit 1
fi

echo "PHP validation (image=$IMAGE) -> $LOG"
docker run --rm -v "$PLUGIN_ROOT:/plugin" -w /plugin "$IMAGE" sh -c '
  rc=0
  echo "=== standalone (tests/php/*.php) ==="
  found=0
  for f in tests/php/*.php; do
    [ -e "$f" ] || continue
    found=1
    echo "--- $f ---"
    php "$f" || rc=1
  done
  [ "$found" = 1 ] || echo "(no standalone tests found)"
  echo
  echo "=== phpunit (tests/phpunit) ==="
  if [ -f phpunit.phar ] && [ -d tests/phpunit ]; then
    php phpunit.phar --bootstrap tests/phpunit/bootstrap.php tests/phpunit || rc=1
  else
    echo "(phpunit.phar or tests/phpunit missing — skipping)"
  fi
  exit $rc
' 2>&1 | tee "$LOG"
rc=${PIPESTATUS[0]}

if [ "$rc" -eq 0 ]; then
  echo "PHP: PASS  (log: $LOG)"
else
  echo "PHP: FAIL  (log: $LOG)"
fi
exit "$rc"
