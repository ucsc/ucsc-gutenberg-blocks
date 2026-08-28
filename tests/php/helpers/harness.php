<?php
/**
 * Shared harness for the dependency-free PHP tests.
 *
 * Provides the pass/fail counters, check(), the final summary, and common
 * WordPress stubs. Every stub is guarded with function_exists/class_exists,
 * so a test file may define its own version of any of them BEFORE requiring
 * this file and that version wins.
 *
 * Adopted by ClassScheduleTest.php. Migrate CampusDirectoryTest.php and
 * CourseCatalogTest.php here once their in-flight feature branches merge.
 */

$tests  = 0;
$failed = 0;

// WPM-117: env-gated coverage capture
$ucsc_coverage = getenv( 'UCSC_COVERAGE' );
if ( $ucsc_coverage && function_exists( 'xdebug_start_code_coverage' ) ) {
	xdebug_start_code_coverage( XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE );
} elseif ( $ucsc_coverage && extension_loaded( 'pcov' ) ) {
	pcov\start();
}

function check( $label, $condition ) {
	global $tests, $failed;
	$tests++;

	if ( $condition ) {
		echo "  PASS  $label\n";
		return;
	}

	$failed++;
	echo "  FAIL  $label\n";
}

function finish_tests() {
	global $tests, $failed, $ucsc_coverage;
	echo "\n" . ( $tests - $failed ) . "/$tests passed\n";

	// WPM-117: emit coverage before exit
	if ( $ucsc_coverage ) {
		if ( function_exists( 'xdebug_get_code_coverage' ) ) {
			ucsc_emit_coverage( xdebug_get_code_coverage(), $ucsc_coverage );
		} elseif ( extension_loaded( 'pcov' ) ) {
			pcov\stop();
			ucsc_emit_coverage( pcov\collect( pcov\inclusive ), $ucsc_coverage );
			pcov\clear();
		}
	}

	exit( 0 === $failed ? 0 : 1 );
}

/**
 * WPM-117: Merge coverage data into JSON accumulator and emit clover.xml.
 *
 * Each test file runs in its own PHP process, so we accumulate raw coverage
 * into coverage-raw.json and regenerate clover.xml each time to avoid the
 * last file overwriting the earlier files' reports.
 *
 * @param array  $data Raw coverage array from xdebug or pcov.
 * @param string $clover_path Path to the clover.xml output file.
 */
function ucsc_emit_coverage( $data, $clover_path ) {
	$raw_path = dirname( $clover_path ) . '/coverage-raw.json';
	$merged   = file_exists( $raw_path )
		? json_decode( file_get_contents( $raw_path ), true )
		: array();

	foreach ( $data as $file => $lines ) {
		// Only plugin source; skip the tests and stubs.
		if ( false !== strpos( $file, '/tests/' ) ) {
			continue;
		}
		foreach ( $lines as $line => $state ) {
			// Skip dead code lines (-2 for xdebug).
			if ( -2 === $state ) {
				continue;
			}
			$prev = isset( $merged[ $file ][ $line ] ) ? $merged[ $file ][ $line ] : 0;
			// xdebug: 1 = executed, -1 = not executed
			// pcov: positive integer = hit count, 0 = not executed
			$hit = ( 1 === $state || $state > 0 ) ? 1 : 0;
			$merged[ $file ][ $line ] = max( $prev, $hit );
		}
	}

	@mkdir( dirname( $clover_path ), 0777, true );
	file_put_contents( $raw_path, json_encode( $merged ) );

	// Generate clover.xml from the merged data.
	$ts  = time();
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$xml .= "<coverage generated=\"$ts\">\n  <project timestamp=\"$ts\">\n";
	$total = $covered = 0;
	foreach ( $merged as $file => $lines ) {
		$fs = count( $lines );
		$fc = count( array_filter( $lines ) );
		$total += $fs;
		$covered += $fc;
		$xml .= '    <file name="' . htmlspecialchars( $file, ENT_QUOTES, 'UTF-8' ) . "\">\n";
		foreach ( $lines as $line => $hit ) {
			$xml .= "      <line num=\"$line\" type=\"stmt\" count=\"$hit\"/>\n";
		}
		$xml .= "      <metrics statements=\"$fs\" coveredstatements=\"$fc\"/>\n    </file>\n";
	}
	$xml .= "    <metrics statements=\"$total\" coveredstatements=\"$covered\"/>\n";
	$xml .= "  </project>\n</coverage>\n";
	file_put_contents( $clover_path, $xml );
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action() {}
}
if ( ! function_exists( 'add_filter' ) ) {
	function add_filter() {}
}
if ( ! function_exists( 'register_rest_route' ) ) {
	function register_rest_route() {}
}
if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $value ) {
		return trim( strip_tags( (string) $value ) );
	}
}
if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $value ) {
		return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
	}
}
if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $value ) {
		return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
	}
}
if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $value ) {
		return esc_attr( $value );
	}
}
if ( ! function_exists( 'selected' ) ) {
	function selected( $selected, $current ) {
		if ( (string) $selected === (string) $current ) {
			echo 'selected="selected"';
		}
	}
}
if ( ! function_exists( 'plugin_dir_path' ) ) {
	function plugin_dir_path( $file ) {
		return dirname( $file ) . '/';
	}
}
if ( ! function_exists( 'home_url' ) ) {
	function home_url( $path = '' ) {
		return 'https://example.ucsc.edu' . $path;
	}
}
if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $value ) {
		return $value instanceof WP_Error;
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		private $code;
		private $message;
		private $data;

		public function __construct( $code = '', $message = '', $data = array() ) {
			$this->code    = $code;
			$this->message = $message;
			$this->data    = $data;
		}

		public function get_error_code() {
			return $this->code;
		}

		public function get_error_message() {
			return $this->message;
		}

		public function get_error_data() {
			return $this->data;
		}
	}
}
