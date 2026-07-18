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
	global $tests, $failed;
	echo "\n" . ( $tests - $failed ) . "/$tests passed\n";
	exit( 0 === $failed ? 0 : 1 );
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
