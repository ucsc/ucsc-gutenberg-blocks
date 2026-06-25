#!/bin/bash
# seed_course_cache.sh — seed the PeopleSoft term + course transients so the
# ClassSchedule block renders real CSE course rows offline (no my.ucsc.edu feed,
# no UCSC VPN). Pairs with bin/seed_demo_page.sh (same default term/dept).
#
# The PHP (bin/seed_course_cache.php) runs in the wpcli container via wp-cli over
# STDIN — no host PHP, no inline PHP in a shell string. Idempotent (overwrites).
#
# Defaults to term 2258 / department CSE; override before running:
#   SEED_TERM=2252 SEED_DEPARTMENT=MATH bash bin/seed_course_cache.sh
# Clear the seed again with:
#   docker compose exec wpcli wp transient delete --all
set -uo pipefail

case "${1:-}" in
  --help|-h|help)
    echo "Usage: bash bin/seed_course_cache.sh   # seed term+course transients for the ClassSchedule block"
    echo "Env: SEED_TERM=2258  SEED_TERM_DESC='2025 Fall Quarter'  SEED_DEPARTMENT=CSE"
    echo "Clear: docker compose exec wpcli wp transient delete --all"
    exit 0 ;;
esac

HERE="$(cd "$(dirname "$0")" && pwd)"
PHP="$HERE/seed_course_cache.php"

# Walk up from the plugin's bin/ dir to the wp-dev.ucsc root (the dir holding
# docker-compose.yml). Override with WP_DEV_ROOT=/path.
ROOT="${WP_DEV_ROOT:-}"
if [ -z "$ROOT" ]; then
  d="$HERE"
  while [ "$d" != "/" ]; do
    if [ -f "$d/docker-compose.yml" ]; then ROOT="$d"; break; fi
    d="$(dirname "$d")"
  done
fi
[ -n "$ROOT" ] || { echo "ERROR: cannot locate wp-dev.ucsc root (set WP_DEV_ROOT=)" >&2; exit 2; }

cd "$ROOT" || exit 2

docker compose exec -T \
  -e SEED_TERM="${SEED_TERM:-}" \
  -e SEED_TERM_DESC="${SEED_TERM_DESC:-}" \
  -e SEED_DEPARTMENT="${SEED_DEPARTMENT:-}" \
  wpcli wp eval-file - < "$PHP"
