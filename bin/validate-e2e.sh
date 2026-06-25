#!/bin/bash
# validate-e2e.sh — e2e driver. Thin wrapper over tests/e2e/run-e2e.sh (the
# self-contained Node+Chromium runner that drives the live wp-dev.ucsc
# frontend). Adds distinct logging so the battery (bin/validate.sh) can report
# e2e separately from php/jest.
#
# Requires the wp-dev.ucsc stack to be UP with a published page containing the
# block — bring it up with the `run` skill / docker compose first, then seed:
#   bash bin/seed_course_cache.sh && bash bin/seed_demo_page.sh
#
# Logs to $UCSC_LOG_DIR/ucsc-validate-e2e.log (default /tmp). Any env consumed by
# run-e2e.sh (e.g. CLASS_SCHEDULE_E2E_URL) is passed straight through.
#
# Usage:
#   bash bin/validate-e2e.sh
# Env:
#   UCSC_LOG_DIR=/tmp   where the .log file is written
set -uo pipefail

case "${1:-}" in
  --help|-h|help)
    echo "Usage: bash bin/validate-e2e.sh"
    echo "Wraps tests/e2e/run-e2e.sh (Node+Chromium container vs live wp-dev.ucsc)."
    echo "Env: UCSC_LOG_DIR=/tmp  CLASS_SCHEDULE_E2E_URL=<page url>"
    exit 0 ;;
esac

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_ROOT="$(cd "$HERE/.." && pwd)"
LOG_DIR="${UCSC_LOG_DIR:-/tmp}"
LOG="$LOG_DIR/ucsc-validate-e2e.log"
RUNNER="$PLUGIN_ROOT/tests/e2e/run-e2e.sh"
mkdir -p "$LOG_DIR"

if [ ! -f "$RUNNER" ]; then
  echo "e2e runner not found at $RUNNER" | tee "$LOG" >&2
  exit 2
fi

echo "e2e validation -> $LOG"
bash "$RUNNER" 2>&1 | tee "$LOG"
rc=${PIPESTATUS[0]}

if [ "$rc" -eq 0 ]; then
  echo "E2E: PASS  (log: $LOG)"
else
  echo "E2E: FAIL  (log: $LOG)"
fi
exit "$rc"
