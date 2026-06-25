#!/bin/bash
#
# Run the Class Schedule e2e suite entirely in Docker — no local Node, Chrome,
# PHP, or Python required. Builds a Node+Chromium image and drives the live
# wp-dev.ucsc frontend from inside the container, reaching the host's published
# 443 via --add-host=wp-dev.ucsc:host-gateway.
#
# Usage (from anywhere):
#   bash tests/e2e/run-e2e.sh
#   CLASS_SCHEDULE_E2E_URL=https://wp-dev.ucsc/some-page/ bash tests/e2e/run-e2e.sh
#
# Prerequisites: the wp-dev.ucsc stack is up and a public page with the block is
# published (see the `run` skill: driver.sh launch + seed_demo_page.sh).
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
IMAGE="ucsc-gutenberg-blocks-e2e"

if ! docker info >/dev/null 2>&1; then
	echo "Docker daemon not running — start Docker Desktop." >&2
	exit 1
fi

echo "Building e2e image ($IMAGE)..."
docker build -q -t "$IMAGE" "$SCRIPT_DIR" >/dev/null

echo "Running e2e suite in container against https://wp-dev.ucsc ..."
# A named volume holds the container's own linux node_modules so the host's
# (darwin) tree is never used inside the container.
exec docker run --rm \
	--add-host=wp-dev.ucsc:host-gateway \
	-e UCSC_E2E_IN_CONTAINER=1 \
	-e CI=true \
	${CLASS_SCHEDULE_E2E_URL:+-e CLASS_SCHEDULE_E2E_URL="$CLASS_SCHEDULE_E2E_URL"} \
	-v "$PLUGIN_ROOT:/app" \
	-v ucsc-gb-e2e-node-modules:/app/node_modules \
	-w /app \
	"$IMAGE" \
	bash -lc 'if [ ! -x node_modules/.bin/wp-scripts ]; then npm ci; fi && npm run test:e2e'
