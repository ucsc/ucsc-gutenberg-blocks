#!/bin/bash
# validate.sh — battery driver. Runs the per-type validation drivers in order
# and prints one combined PASS/FAIL summary with the path to each distinct log.
#
# By default runs all three (php, jest, e2e). Pass a subset to run only those:
#   bash bin/validate.sh            # php + jest + e2e
#   bash bin/validate.sh php jest   # just those two
#
# Each sub-driver writes its own log under $UCSC_LOG_DIR (default /tmp):
#   ucsc-validate-php.log  ucsc-validate-jest.log  ucsc-validate-e2e.log
# Exits non-zero if any selected suite fails. Note: e2e (and any data-backed
# checks) need the wp-dev.ucsc stack UP — bring it up with `run` first.
set -uo pipefail

case "${1:-}" in
  --help|-h|help)
    echo "Usage: bash bin/validate.sh [php] [jest] [e2e]"
    echo "No args = run all three. Logs: \$UCSC_LOG_DIR/ucsc-validate-<type>.log (default /tmp)."
    exit 0 ;;
esac

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOG_DIR="${UCSC_LOG_DIR:-/tmp}"

# Selection: args win, else all three in a sensible order (cheap/offline first).
if [ "$#" -gt 0 ]; then
  SUITES="$*"
else
  SUITES="php jest e2e"
fi

declare -i overall=0
results=""

run_suite() {
  suite="$1"
  driver="$HERE/validate-$suite.sh"
  if [ ! -f "$driver" ]; then
    echo "Unknown suite '$suite' (no $driver)" >&2
    results="$results\n  $suite   SKIP (no driver)"
    overall=1
    return
  fi
  echo
  echo "================================================================"
  echo "  validate: $suite"
  echo "================================================================"
  bash "$driver"
  rc=$?
  if [ "$rc" -eq 0 ]; then
    results="$results\n  $suite   PASS   ($LOG_DIR/ucsc-validate-$suite.log)"
  else
    results="$results\n  $suite   FAIL   ($LOG_DIR/ucsc-validate-$suite.log)"
    overall=1
  fi
}

for suite in $SUITES; do
  run_suite "$suite"
done

echo
echo "================================================================"
echo "  validation summary"
echo "================================================================"
printf '%b\n' "$results"
echo
if [ "$overall" -eq 0 ]; then
  echo "ALL SELECTED SUITES PASSED"
else
  echo "ONE OR MORE SUITES FAILED"
fi
exit "$overall"
