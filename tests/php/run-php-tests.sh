#!/usr/bin/env bash
# Run the dependency-free PHP test suite without coverage.
#
# Runs inside a PHP container (composer test). From the Mac, use
# scripts/test-tiers.sh, which starts that container.
#
# Usage: bash tests/php/run-php-tests.sh [tests/php/<X>Test.php ...]

set -euo pipefail

PLUGIN_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$PLUGIN_ROOT"

source tests/php/test-files.sh
if [ $# -gt 0 ]; then
	PHP_TEST_FILES=("$@")
fi

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
	if php "$test_file"; then
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
