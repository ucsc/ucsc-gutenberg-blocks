<?php
/**
 * Dependency-free tests for CourseCatalog feed target and cache controls.
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/CourseCatalogTest.php
 */

define( 'WEEK_IN_SECONDS', 604800 );

$actions            = array();
$current_user_id    = 0;
$can_manage_options = false;
$transients         = array();
$remote_requests    = array();
$remote_response    = array(
	'code' => 200,
	'body' => '<?xml version="1.0"?><catalog><course><subject>LIT</subject><catalog_nbr>1</catalog_nbr><title>Intro</title><level>Lower Division</level><units>5</units><description>Test</description></course></catalog>',
);

function add_action( $hook, $callback ) {
	global $actions;
	$actions[ $hook ] = $callback;
}
function wp_enqueue_script() {}
function wp_register_script() {}
function wp_enqueue_style() {}
function wp_register_style() {}
function plugins_url( $path ) {
	return 'https://example.ucsc.edu/wp-content/plugins/ucsc-gutenberg-blocks/' . $path;
}
function plugin_dir_path( $file ) {
	return dirname( $file ) . '/';
}
function filemtime( $file ) {
	return 1;
}
function is_user_logged_in() {
	global $current_user_id;
	return 0 !== $current_user_id;
}
function current_user_can( $capability ) {
	global $can_manage_options;
	return 'manage_options' === $capability && $can_manage_options;
}
function wp_unslash( $value ) {
	return stripslashes( $value );
}
function get_transient( $key ) {
	global $transients;
	return isset( $transients[ $key ] ) ? $transients[ $key ] : false;
}
function set_transient( $key, $value, $expiration ) {
	global $transients;
	$transients[ $key ] = array(
		'value'      => $value,
		'expiration' => $expiration,
	);
	return true;
}
function wp_remote_post( $url, $args ) {
	global $remote_requests, $remote_response;
	$remote_requests[] = array(
		'url'  => $url,
		'args' => $args,
	);
	return $remote_response;
}
function wp_remote_retrieve_response_code( $response ) {
	return $response['code'];
}
function wp_remote_retrieve_body( $response ) {
	return $response['body'];
}
function is_wp_error( $value ) {
	return $value instanceof WP_Error;
}
function get_block_wrapper_attributes( $attributes = array() ) {
	return isset( $attributes['id'] ) ? 'id="' . $attributes['id'] . '"' : '';
}

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

class Test_WPDB {
	public $options = 'wp_options';
	public $last_query;

	public function esc_like( $text ) {
		return addcslashes( $text, '_%\\' );
	}

	public function prepare( $query, ...$args ) {
		foreach ( $args as $arg ) {
			$query = preg_replace( '/%s/', "'" . $arg . "'", $query, 1 );
		}
		return $query;
	}

	public function query( $query ) {
		$this->last_query = $query;
		return 2;
	}
}

$wpdb = new Test_WPDB();

require __DIR__ . '/../../classes/CourseCatalog.php';

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

function reset_test_state() {
	global $current_user_id, $can_manage_options, $transients, $remote_requests, $remote_response, $wpdb;

	$current_user_id    = 0;
	$can_manage_options = false;
	$transients         = array();
	$remote_requests    = array();
	$remote_response    = array(
		'code' => 200,
		'body' => '<?xml version="1.0"?><catalog><course><subject>LIT</subject><catalog_nbr>1</catalog_nbr><title>Intro</title><level>Lower Division</level><units>5</units><description>Test</description></course></catalog>',
	);
	$wpdb->last_query   = null;

	$_GET    = array();
	$_SERVER = array();

	putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET' );
	putenv( 'UCSC_COURSE_CATALOG_BYPASS_CACHE' );
	putenv( 'UCSC_COURSE_CATALOG_ALLOW_REQUEST_OVERRIDE' );
}

function make_catalog() {
	reset_test_state();
	return new CourseCatalog();
}

echo "PeopleSoft target selection:\n";

$catalog = make_catalog();
$target  = $catalog->getPeopleSoftTarget();
check( 'defaults to production target', 'prod' === $target['target'] );
check( 'defaults to production Host header value', 'my.prd.ais.aws.ucsc.edu' === $target['host'] );

$catalog = make_catalog();
putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET=qa' );
$target = $catalog->getPeopleSoftTarget();
check( 'maps qa env alias to csqa target', 'csqa' === $target['target'] );
check( 'uses CSQA To header value', 'PSFT_CSQA' === $target['to'] );

$catalog = make_catalog();
putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET=definitely-not-real' );
$target = $catalog->getPeopleSoftTarget();
check( 'falls back to production for unknown env target', 'prod' === $target['target'] );

echo "request override gating:\n";

$catalog = make_catalog();
putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET=csqa' );
$_SERVER['HTTP_HOST']                 = 'wp-dev.ucsc:443';
$_GET['ucsc_course_catalog_target']   = 'prod';
$current_user_id                      = 1;
$can_manage_options                   = true;
$target                              = $catalog->getPeopleSoftTarget();
check( 'admin can override target on dev host with port', 'prod' === $target['target'] );

$catalog = make_catalog();
putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET=csqa' );
$_SERVER['HTTP_HOST']                 = 'wordpress.ucsc.edu:443';
$_GET['ucsc_course_catalog_target']   = 'prod';
$current_user_id                      = 1;
$can_manage_options                   = true;
$target                              = $catalog->getPeopleSoftTarget();
check( 'admin cannot override target on production host by default', 'csqa' === $target['target'] );

$catalog = make_catalog();
putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET=csqa' );
$_SERVER['HTTP_HOST']                 = 'localhost:8080';
$_GET['ucsc_course_catalog_target']   = 'prod';
$target                              = $catalog->getPeopleSoftTarget();
check( 'logged-out request cannot override target on dev host', 'csqa' === $target['target'] );

$catalog = make_catalog();
putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET=csqa' );
putenv( 'UCSC_COURSE_CATALOG_ALLOW_REQUEST_OVERRIDE=true' );
$_SERVER['HTTP_HOST']                 = 'wordpress.ucsc.edu';
$_GET['ucsc_course_catalog_target']   = 'prod';
$current_user_id                      = 1;
$can_manage_options                   = true;
$target                              = $catalog->getPeopleSoftTarget();
check( 'explicit server opt-in allows production-host admin override', 'prod' === $target['target'] );

echo "cache controls:\n";

$catalog = make_catalog();
check( 'cache bypass defaults off', false === $catalog->shouldBypassCache() );

$catalog = make_catalog();
putenv( 'UCSC_COURSE_CATALOG_BYPASS_CACHE=true' );
check( 'env var enables cache bypass', true === $catalog->shouldBypassCache() );

$catalog = make_catalog();
putenv( 'UCSC_COURSE_CATALOG_BYPASS_CACHE=true' );
$_SERVER['HTTP_HOST']                         = 'wp-dev.ucsc';
$_GET['ucsc_course_catalog_bypass_cache']     = '0';
$current_user_id                              = 1;
$can_manage_options                           = true;
check( 'admin request can disable cache bypass on dev host', false === $catalog->shouldBypassCache() );

$catalog = make_catalog();
$catalog->getCachedCourses(
	array(
		'subjectOrDept' => 'dept',
		'department'    => 'lit',
		'subject'       => '',
	)
);
check( 'successful response is cached under target-aware key', isset( $transients['course-catalog-prod-lit-dept'] ) );
check( 'successful response uses one remote request', 1 === count( $remote_requests ) );

$catalog = make_catalog();
putenv( 'UCSC_COURSE_CATALOG_BYPASS_CACHE=true' );
$catalog->getCachedCourses(
	array(
		'subjectOrDept' => 'dept',
		'department'    => 'lit',
		'subject'       => '',
	)
);
check( 'cache bypass does not store successful response', array() === $transients );
check( 'cache bypass still calls the remote feed', 1 === count( $remote_requests ) );

echo "cache clearing:\n";

$deleted = CourseCatalog::clearCachedCourses( 'qa' );
check( 'cache clear returns deleted row count', 2 === $deleted );
check( 'cache clear maps qa alias to csqa transient prefix', false !== strpos( $wpdb->last_query, '_transient_course-catalog-csqa-' ) );

$deleted = CourseCatalog::clearCachedCourses();
check( 'cache clear all returns deleted row count', 2 === $deleted );
check( 'cache clear all targets all course catalog transients', false !== strpos( $wpdb->last_query, '_transient_course-catalog-' ) );

echo "\n" . ( $tests - $failed ) . "/$tests passed\n";
exit( 0 === $failed ? 0 : 1 );
