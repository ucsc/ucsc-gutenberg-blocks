#!/usr/bin/env bash
# Run the dependency-free PHP test suite in Docker without coverage.

set -euo pipefail

PLUGIN_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$PLUGIN_ROOT"

source tests/php/test-files.sh

IMAGE_NAME="php:8.1-cli"

PASSED=0
FAILED=0

echo "Running PHP tests..."
echo

for test_file in "${PHP_TEST_FILES[@]}"; do
	if [ ! -f "$test_file" ]; then
		echo "WARN $test_file not found, skipping"
		continue
	fi

	echo "> Running $(basename "$test_file")..."
	if docker run --rm \
		-v "$PLUGIN_ROOT:/plugin" \
		-w /plugin \
		"$IMAGE_NAME" \
		php "$test_file"; then
		PASSED=$((PASSED + 1))
	else
		FAILED=$((FAILED + 1))
	fi
	echo
done

echo "------------------------------------------------------------------------"
if [ "$FAILED" -eq 0 ]; then
	echo "All $PASSED PHP test suites passed"
else
	echo "$FAILED PHP test suite(s) failed, $PASSED passed"
fi

exit "$FAILED"
