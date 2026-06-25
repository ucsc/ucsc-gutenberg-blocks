#!/bin/bash
# seed_demo_page.sh — upsert the "Class Schedule Demo" page (a configured
# ucscblocks/classschedule block on one frontend URL) and print its permalink.
#
# The PHP (bin/seed_demo_page.php) runs in the wpcli container via wp-cli over
# STDIN — no host PHP, no inline PHP in a shell string. Idempotent.
#
# Defaults to department CSE; override before running:
#   SEED_SUBJECT_OR_DEPT=subject SEED_SUBJECT=LIT bash bin/seed_demo_page.sh
set -uo pipefail

case "${1:-}" in
  --help|-h|help)
    echo "Usage: bash bin/seed_demo_page.sh   # upsert Class Schedule demo page, print URL"
    echo "Env: SEED_SUBJECT_OR_DEPT=dept|subject  SEED_DEPARTMENT=CSE  SEED_SUBJECT=LIT"
    exit 0 ;;
esac

HERE="$(cd "$(dirname "$0")" && pwd)"
PHP="$HERE/seed_demo_page.php"

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
  -e SEED_SUBJECT_OR_DEPT="${SEED_SUBJECT_OR_DEPT:-}" \
  -e SEED_DEPARTMENT="${SEED_DEPARTMENT:-}" \
  -e SEED_SUBJECT="${SEED_SUBJECT:-}" \
  wpcli wp eval-file - < "$PHP"
