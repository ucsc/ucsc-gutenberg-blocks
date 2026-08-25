#!/bin/bash
#
# Run the e2e suites (class-schedule, campus-directory) entirely in Docker — no local Node, Chrome,
# PHP, or Python required. Builds a Node+Chromium image and drives the live
# wp-dev.ucsc frontend from inside the container, reaching the host's published
# 443 via --add-host=wp-dev.ucsc:host-gateway.
#
# Usage (from anywhere):
#   bash tests/e2e/run-e2e.sh
#   UCSC_CS_E2E_URL=https://wp-dev.ucsc/some-page/ bash tests/e2e/run-e2e.sh
#
# Prerequisites: the wp-dev.ucsc stack is up (docker compose up -d). The e2e
# page is seeded automatically via seed-e2e-page.php when the wpcli container
# is reachable; otherwise set UCSC_CS_E2E_URL to a page containing the block.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
IMAGE="ucsc-gutenberg-blocks-e2e"

if ! docker info >/dev/null 2>&1; then
	echo "Docker daemon not running — start Docker Desktop." >&2
	exit 1
fi

# Seed the e2e page when this plugin lives inside a wp-dev.ucsc checkout
# (plugin dir → plugins → wp-content → public → repo root).
WP_DEV_ROOT="${WP_DEV_ROOT:-$(cd "$PLUGIN_ROOT/../../../.." 2>/dev/null && pwd)}"
if [ -z "${UCSC_CS_E2E_URL:-}" ] && [ -f "$WP_DEV_ROOT/docker-compose.yml" ]; then
	echo "Seeding class-schedule e2e page (root: $WP_DEV_ROOT)..."
	SEEDED_URL="$( (cd "$WP_DEV_ROOT" && docker compose exec -T wpcli wp eval-file - < "$SCRIPT_DIR/seed-e2e-page.php") | tail -1 )" || {
		echo "Could not seed the e2e page — is the stack up? (docker compose up -d)" >&2
		exit 1
	}
	echo "e2e page: $SEEDED_URL"
	UCSC_CS_E2E_URL="$SEEDED_URL"
fi

# Seed the campus-directory profile page (WPM-114). It reproduces the department
# -site configuration: in-site profile links rather than links out to
# campusdirectory.ucsc.edu. Needs LDAP reachable (UCSC VPN) to render people.
if [ -z "${UCSC_CD_E2E_URL:-}" ] && [ -f "$WP_DEV_ROOT/docker-compose.yml" ]; then
	echo "Seeding campus-directory e2e pages (root: $WP_DEV_ROOT)..."
	CD_SEED_OUTPUT="$(cd "$WP_DEV_ROOT" && docker compose exec -T wpcli wp eval-file - < "$SCRIPT_DIR/seed-campus-directory-e2e.php")" || {
		echo "Could not seed the campus-directory e2e page — is the stack up? (docker compose up -d)" >&2
		exit 1
	}
	UCSC_CD_LALS_E2E_URL="$(printf '%s\n' "$CD_SEED_OUTPUT" | sed -n 's/^LALS_URL=//p' | tail -1)"
	UCSC_CD_E2E_URL="$(printf '%s\n' "$CD_SEED_OUTPUT" | sed -n 's/^EEB_URL=//p' | tail -1)"
	if [ -z "$UCSC_CD_E2E_URL" ]; then
		UCSC_CD_E2E_URL="$(printf '%s\n' "$CD_SEED_OUTPUT" | tail -1)"
	fi
	echo "campus-directory LALS e2e page: ${UCSC_CD_LALS_E2E_URL:-not seeded}"
	echo "campus-directory EEB e2e page: $UCSC_CD_E2E_URL"
fi

echo "Building e2e image ($IMAGE)..."
docker build -q -t "$IMAGE" "$SCRIPT_DIR" >/dev/null

echo "Running e2e suites in container..."
# A named volume holds the container's own linux node_modules so the host's
# (darwin) tree is never used inside the container.
exec docker run --rm \
	--add-host=wp-dev.ucsc:host-gateway \
	-e UCSC_E2E_IN_CONTAINER=1 \
	-e CI=true \
	${UCSC_CS_E2E_URL:+-e UCSC_CS_E2E_URL="$UCSC_CS_E2E_URL"} \
	${UCSC_CD_E2E_URL:+-e UCSC_CD_E2E_URL="$UCSC_CD_E2E_URL"} \
	${UCSC_CD_LALS_E2E_URL:+-e UCSC_CD_LALS_E2E_URL="$UCSC_CD_LALS_E2E_URL"} \
	-v "$PLUGIN_ROOT:/app" \
	-v ucsc-gutenberg-blocks-e2e-node-modules:/app/node_modules \
	-w /app \
	"$IMAGE" \
	bash -lc 'if [ ! -x node_modules/.bin/wp-scripts ]; then npm ci; fi && mkdir -p node_modules/puppeteer-core && touch node_modules/puppeteer-core/install node_modules/puppeteer-core/install.js && sed -i "s/\.removeListener(/\.off(/g; s/\.addListener(/\.on(/g" node_modules/@wordpress/scripts/config/jest-environment-puppeteer/index.js && npm run test:e2e'
