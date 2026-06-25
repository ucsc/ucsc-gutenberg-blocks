#!/bin/bash
# verify.sh — smoke-verify a running wp-dev.ucsc stack. Answers three questions,
# in order, and stops at the first hard failure:
#
#   1. ALIVE  — does the site answer over HTTPS at all?
#   2. BUILT  — is the plugin's compiled asset present (build/index.js)?
#   3. RENDER — does a known page return 200 AND contain the expected block DOM?
#
# This depends on `run` having launched the stack first (docker compose up) and,
# for the render check, on the demo page existing:
#   bash bin/seed_course_cache.sh && bash bin/seed_demo_page.sh
#
# Host-side: curl reaches the host-published 443 via --resolve, accepting the
# stack's self-signed cert (-k). Logs to $UCSC_LOG_DIR/ucsc-verify.log.
#
# Usage:
#   bash bin/verify.sh
# Env (all have working defaults for the Class Schedule demo page):
#   VERIFY_BASE_URL=https://wp-dev.ucsc
#   VERIFY_RESOLVE=wp-dev.ucsc:443:127.0.0.1   (curl --resolve mapping)
#   VERIFY_PAGE=/class-schedule-demo/          (path to fetch for the render check)
#   VERIFY_PAGE_EXPECT='Class Schedule Demo'   (string proving the page rendered)
#   VERIFY_BLOCK_EXPECT='id="classSchedule"'   (marker proving the block rendered)
#   UCSC_LOG_DIR=/tmp
set -uo pipefail

case "${1:-}" in
  --help|-h|help)
    echo "Usage: bash bin/verify.sh"
    echo "Smoke-checks a RUNNING wp-dev.ucsc stack: alive, built, page+block rendered."
    echo "Run 'run' (docker compose up) and the seeders first."
    echo "Env: VERIFY_BASE_URL VERIFY_PAGE VERIFY_PAGE_EXPECT VERIFY_BLOCK_EXPECT VERIFY_RESOLVE UCSC_LOG_DIR"
    exit 0 ;;
esac

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_ROOT="$(cd "$HERE/.." && pwd)"
LOG_DIR="${UCSC_LOG_DIR:-/tmp}"
LOG="$LOG_DIR/ucsc-verify.log"
mkdir -p "$LOG_DIR"

BASE_URL="${VERIFY_BASE_URL:-https://wp-dev.ucsc}"
RESOLVE="${VERIFY_RESOLVE:-wp-dev.ucsc:443:127.0.0.1}"
PAGE="${VERIFY_PAGE:-/class-schedule-demo/}"
PAGE_EXPECT="${VERIFY_PAGE_EXPECT:-Class Schedule Demo}"
BLOCK_EXPECT="${VERIFY_BLOCK_EXPECT:-id=\"classSchedule\"}"

# Everything below is both echoed and appended to the log.
exec > >(tee "$LOG") 2>&1

fails=0
pass() { echo "  PASS  $1"; }
fail() { echo "  FAIL  $1"; fails=$((fails + 1)); }

# fetch <url> -> writes body to $BODY_FILE, echoes the HTTP status code
BODY_FILE="$(mktemp "${TMPDIR:-/tmp}/ucsc-verify-body.XXXXXX")"
trap 'rm -f "$BODY_FILE"' EXIT
fetch() {
  curl -sS -k --max-time 20 --resolve "$RESOLVE" \
    -o "$BODY_FILE" -w '%{http_code}' "$1" 2>>"$LOG" || echo "000"
}

echo "verify: $BASE_URL (resolve $RESOLVE) -> $LOG"
echo

echo "1) ALIVE"
code="$(fetch "$BASE_URL/")"
if [ "$code" = "000" ]; then
  fail "site did not answer (curl failed) — is the stack up? run 'run' first"
  echo
  echo "VERIFY: FAIL ($fails check(s) failed)  (log: $LOG)"
  exit 1
fi
if [ "$code" -ge 200 ] && [ "$code" -lt 400 ]; then
  pass "site answered HTTP $code at $BASE_URL/"
else
  fail "site returned HTTP $code at $BASE_URL/"
fi

echo "2) BUILT"
if [ -f "$PLUGIN_ROOT/build/index.js" ]; then
  pass "compiled asset present (build/index.js)"
else
  fail "build/index.js missing — run 'npm run build' (the editor script won't load)"
fi

echo "3) RENDER ($PAGE)"
code="$(fetch "$BASE_URL$PAGE")"
if [ "$code" = "200" ]; then
  pass "page returned HTTP 200"
else
  fail "page returned HTTP $code (expected 200) — is the demo page seeded?"
fi
if grep -qF "$PAGE_EXPECT" "$BODY_FILE"; then
  pass "page rendered (found: $PAGE_EXPECT)"
else
  fail "expected page content not found: $PAGE_EXPECT"
fi
if grep -qF "$BLOCK_EXPECT" "$BODY_FILE"; then
  pass "block rendered into the DOM (found: $BLOCK_EXPECT)"
else
  fail "block DOM marker not found: $BLOCK_EXPECT"
fi

echo
if [ "$fails" -eq 0 ]; then
  echo "VERIFY: PASS  (log: $LOG)"
  exit 0
else
  echo "VERIFY: FAIL ($fails check(s) failed)  (log: $LOG)"
  exit 1
fi
