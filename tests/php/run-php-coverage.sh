#!/usr/bin/env bash
# WPM-117: Run the PHP test suite with coverage instrumentation.
#
# Usage:
#   bash tests/php/run-php-coverage.sh
#
# Output:
#   coverage/php/clover.xml      - Clover XML coverage report
#   coverage/php/coverage-raw.json - Raw merged coverage (intermediate)
#
# Without UCSC_COVERAGE set, the tests run normally with no coverage overhead.
# With it set, the harness captures coverage and merges across all test files.

set -euo pipefail

PLUGIN_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$PLUGIN_ROOT"

IMAGE_NAME="ucsc-gutenberg-blocks-php-test:coverage"
COVERAGE_DIR="$PLUGIN_ROOT/coverage/php"
COVERAGE_CLOVER="$COVERAGE_DIR/clover.xml"
COVERAGE_RAW="$COVERAGE_DIR/coverage-raw.json"

# Build the coverage-enabled image if it doesn't exist
if ! docker images "$IMAGE_NAME" --format '{{.Repository}}:{{.Tag}}' | grep -q "$IMAGE_NAME"; then
	echo "Building coverage test image..."
	docker build -f tests/php/Dockerfile.coverage -t "$IMAGE_NAME" tests/php
fi

# Clear the accumulator so we start fresh (otherwise stale hits from previous runs persist)
rm -f "$COVERAGE_RAW"
mkdir -p "$COVERAGE_DIR"

echo "Running PHP tests with coverage..."
echo

# Find all PHP test files
TEST_FILES=(tests/php/CampusDirectoryTest.php tests/php/CampusDirectoryShortcodeTest.php tests/php/ClassScheduleTest.php tests/php/CourseCatalogTest.php tests/php/CourseScheduleAPITest.php)

PASSED=0
FAILED=0

for test_file in "${TEST_FILES[@]}"; do
	if [ ! -f "$test_file" ]; then
		echo "⚠️  $test_file not found, skipping"
		continue
	fi

	echo "▸ Running $(basename "$test_file")..."
	if docker run --rm \
		-v "$PLUGIN_ROOT:/plugin" \
		-w /plugin \
		-e "UCSC_COVERAGE=/plugin/coverage/php/clover.xml" \
		"$IMAGE_NAME" \
		php "$test_file"; then
		PASSED=$((PASSED + 1))
	else
		FAILED=$((FAILED + 1))
	fi
	echo
done

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo

if [ "$FAILED" -eq 0 ]; then
	echo "✓ All $PASSED test suites passed"
else
	echo "✗ $FAILED test suite(s) failed, $PASSED passed"
fi

if [ -f "$COVERAGE_CLOVER" ]; then
	# Extract coverage summary from clover.xml
	TOTAL_STATEMENTS=$(grep -o 'statements="[0-9]*"' "$COVERAGE_CLOVER" | tail -1 | grep -o '[0-9]*')
	COVERED_STATEMENTS=$(grep -o 'coveredstatements="[0-9]*"' "$COVERAGE_CLOVER" | tail -1 | grep -o '[0-9]*')
	
	if [ "$TOTAL_STATEMENTS" -gt 0 ]; then
		PERCENT=$(awk "BEGIN {printf \"%.2f\", ($COVERED_STATEMENTS / $TOTAL_STATEMENTS) * 100}")
		echo
		echo "PHP Coverage: $PERCENT% ($COVERED_STATEMENTS / $TOTAL_STATEMENTS statements)"
		echo "Clover report: $COVERAGE_CLOVER"
	else
		echo
		echo "⚠️  Coverage report generated but no statements found"
	fi
else
	echo
	echo "⚠️  No coverage report generated"
fi

echo

exit "$FAILED"
