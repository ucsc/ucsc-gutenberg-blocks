#!/bin/bash
# validate-jest.sh — Jest unit-test driver. Runs the JS block unit tests
# (src/blocks/__tests__/*, via `wp-scripts test-unit-js`) entirely in Docker —
# no host Node required. A named volume holds the container's own linux
# node_modules so the host's darwin tree is never used inside the container.
#
# Logs to $UCSC_LOG_DIR/ucsc-validate-jest.log (default /tmp).
#
# Usage:
#   bash bin/validate-jest.sh
# Env:
#   UCSC_LOG_DIR=/tmp           where the .log file is written
#   NODE_IMAGE=node:20-bookworm-slim
set -uo pipefail

case "${1:-}" in
  --help|-h|help)
    echo "Usage: bash bin/validate-jest.sh"
    echo "Runs 'npm test' (wp-scripts test-unit-js) in a Node container."
    echo "Env: UCSC_LOG_DIR=/tmp  NODE_IMAGE=node:20-bookworm-slim"
    exit 0 ;;
esac

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_ROOT="$(cd "$HERE/.." && pwd)"
LOG_DIR="${UCSC_LOG_DIR:-/tmp}"
LOG="$LOG_DIR/ucsc-validate-jest.log"
IMAGE="${NODE_IMAGE:-node:20-bookworm-slim}"
mkdir -p "$LOG_DIR"

if ! docker info >/dev/null 2>&1; then
  echo "Docker daemon not running — start Docker Desktop." | tee "$LOG" >&2
  exit 1
fi

echo "Jest validation (image=$IMAGE) -> $LOG"
docker run --rm \
  -e CI=true \
  -v "$PLUGIN_ROOT:/app" \
  -v ucsc-gb-jest-node-modules:/app/node_modules \
  -w /app \
  "$IMAGE" \
  bash -lc 'if [ ! -x node_modules/.bin/wp-scripts ]; then npm ci; fi && npm test -- --ci' \
  2>&1 | tee "$LOG"
rc=${PIPESTATUS[0]}

if [ "$rc" -eq 0 ]; then
  echo "JEST: PASS  (log: $LOG)"
else
  echo "JEST: FAIL  (log: $LOG)"
fi
exit "$rc"
