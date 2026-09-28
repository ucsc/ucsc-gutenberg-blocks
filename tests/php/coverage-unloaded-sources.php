<?php
/**
 * Coverage denominator: count plugin source files that no suite loads.
 *
 * Xdebug only reports files a test process actually included, so a class no
 * suite requires (ContentSharer.php, FeedbackForm.php, ...) used to be absent
 * from clover.xml instead of showing as 0% — the same gap Jest avoids with
 * collectCoverageFrom and PHPUnit with <source><include>. Run last by
 * run-php-coverage.sh, after every suite has written coverage-raw.json.
 *
 * Each missing file is included only so Xdebug (XDEBUG_CC_UNUSED) can list its
 * executable lines; every line is then recorded as not covered, because
 * including a file is not a test of it. Output is discarded and errors are
 * swallowed — a template that fatals half-way is still fully compiled.
 *
 * Coverage mode only:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin \
 *     -e UCSC_COVERAGE=/plugin/coverage/clover.xml \
 *     ucsc-gutenberg-blocks-php-test:coverage php tests/php/coverage-unloaded-sources.php
 */

require __DIR__ . '/helpers/harness.php';

if ( ! $ucsc_coverage || ! function_exists( 'xdebug_get_code_coverage' ) ) {
	echo "UCSC_COVERAGE with Xdebug is required; nothing to do.\n";
	exit( 0 );
}

$plugin_root = dirname( __DIR__, 2 );
$raw_path    = dirname( $ucsc_coverage ) . '/coverage-raw.json';
$seen        = file_exists( $raw_path ) ? json_decode( file_get_contents( $raw_path ), true ) : array();

$sources = array_merge(
	glob( $plugin_root . '/classes/*.php' ),
	glob( $plugin_root . '/templates/*.php' ),
	glob( $plugin_root . '/src/API/*.php' ),
	array( $plugin_root . '/index.php' )
);

$unloaded = array();
foreach ( $sources as $file ) {
	if ( isset( $seen[ $file ] ) ) {
		continue;
	}
	$unloaded[] = $file;
	echo '  not loaded by any suite: ' . substr( $file, strlen( $plugin_root ) + 1 ) . "\n";

	ob_start();
	try {
		@include_once $file;
	} catch ( Throwable $e ) {
		// Compiled already; only its executable lines are needed.
	}
	ob_end_clean();
}

$zeroed = array();
foreach ( xdebug_get_code_coverage() as $file => $lines ) {
	if ( ! in_array( $file, $unloaded, true ) ) {
		continue;
	}
	foreach ( $lines as $line => $state ) {
		$zeroed[ $file ][ $line ] = -2 === $state ? -2 : -1;
	}
}

ucsc_emit_coverage( $zeroed, $ucsc_coverage );
echo count( $unloaded ) . " unloaded source file(s) added to the coverage denominator\n";
exit( 0 );
