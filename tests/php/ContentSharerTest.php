<?php
/**
 * Dependency-free tests for the ContentSharer block.
 *
 * Covers the REST permission gate, the parameter-guard behaviour of the
 * post/posttype endpoints, the multisite blog switching, and the rendered
 * frontend output.
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/ContentSharerTest.php
 */

$actions          = array();
$can_edit_posts   = false;
$switched_blogs   = array();
$restored_blogs   = 0;
$current_blog     = 1;
$posts_fixture    = array();
$post_types       = array();
$sites_fixture    = array();

function add_action( $hook, $callback ) {
	global $actions;
	$actions[ $hook ][] = $callback;
}

function current_user_can( $capability ) {
	global $can_edit_posts;
	return 'edit_posts' === $capability && $can_edit_posts;
}

function esc_html__( $text, $domain = 'default' ) {
	return $text;
}

function register_block_type() {}
function register_rest_route() {}

function switch_to_blog( $blog_id ) {
	global $switched_blogs, $current_blog;
	$switched_blogs[] = $blog_id;
	$current_blog     = $blog_id;
	return true;
}

function restore_current_blog() {
	global $restored_blogs, $current_blog;
	$restored_blogs++;
	$current_blog = 1;
	return true;
}

function get_posts( $args ) {
	global $posts_fixture;
	$posts_fixture['last_args'] = $args;
	return isset( $posts_fixture['posts'] ) ? $posts_fixture['posts'] : array();
}

function get_post_types() {
	global $post_types;
	return $post_types;
}

function get_sites() {
	global $sites_fixture;
	return $sites_fixture;
}

function apply_filters( $hook, $value ) {
	return $value;
}

function parse_blocks( $content ) {
	// One synthetic block per call; theHTML only renders the first.
	return array(
		array( 'blockName' => 'core/paragraph', 'innerHTML' => $content ),
	);
}

function render_block( $block ) {
	return '[rendered:' . $block['innerHTML'] . ']';
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

class Test_REST_Request {
	private $params;

	public function __construct( $params = array() ) {
		$this->params = $params;
	}

	public function get_query_params() {
		return $this->params;
	}
}

require __DIR__ . '/../../classes/ContentSharer.php';

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
	global $can_edit_posts, $switched_blogs, $restored_blogs, $current_blog, $posts_fixture, $post_types, $sites_fixture;

	$can_edit_posts = false;
	$switched_blogs = array();
	$restored_blogs = 0;
	$current_blog   = 1;
	$posts_fixture  = array();
	$post_types     = array();
	$sites_fixture  = array();
}

function make_sharer() {
	reset_test_state();
	return new ContentSharer();
}

echo "REST permission gate:\n";

$sharer = make_sharer();
$result = $sharer->restPermissionsCheck();
check( 'logged-out / non-editor is rejected', $result instanceof WP_Error );
check( 'rejection carries a 401 status', $result instanceof WP_Error && 401 === $result->get_error_data()['status'] );
check( 'rejection uses rest_forbidden code', $result instanceof WP_Error && 'rest_forbidden' === $result->get_error_code() );

$sharer         = make_sharer();
$can_edit_posts = true;
check( 'editor is allowed through', true === $sharer->restPermissionsCheck() );

echo "get_post parameter guards:\n";

$sharer = make_sharer();
check( 'missing post type returns guidance string', 'Set Post Type' === $sharer->get_post( new Test_REST_Request( array() ) ) );

$sharer = make_sharer();
check(
	'post type without site id returns guidance string',
	'Set Site ID' === $sharer->get_post( new Test_REST_Request( array( 'posttype' => 'post' ) ) )
);

$sharer                  = make_sharer();
$post                    = new stdClass();
$post->post_title        = 'Hello';
$post->post_content      = 'Body';
$posts_fixture['posts']  = array( $post );
$returned                = $sharer->get_post( new Test_REST_Request( array( 'posttype' => 'page', 'siteid' => 7 ) ) );
check( 'valid request returns the post list', array( $post ) === $returned );
check( 'get_post switches to the requested blog', array( 7 ) === $switched_blogs );
check( 'get_post restores the current blog afterwards', 1 === $restored_blogs );
check( 'get_post queries the requested post type', isset( $posts_fixture['last_args'] ) && 'page' === $posts_fixture['last_args']['post_type'] );
check( 'get_post only queries published posts', 'publish' === $posts_fixture['last_args']['post_status'] );

echo "get_posttypes parameter guards:\n";

$sharer = make_sharer();
check( 'missing site id returns guidance string', 'Set Site ID' === $sharer->get_posttypes( new Test_REST_Request( array() ) ) );

$sharer     = make_sharer();
$post_types = array( 'post' => 'post', 'page' => 'page' );
$returned   = $sharer->get_posttypes( new Test_REST_Request( array( 'siteid' => '4' ) ) );
check( 'valid request returns the post types', $post_types === $returned );
check( 'site id is cast to an int before switching blogs', array( 4 ) === $switched_blogs );
check( 'get_posttypes restores the current blog afterwards', 1 === $restored_blogs );

echo "get_sites passthrough:\n";

$sharer        = make_sharer();
$sites_fixture = array( 'site-1', 'site-2' );
check( 'get_sites returns the WordPress site list', $sites_fixture === $sharer->get_sites() );

echo "rendered frontend output:\n";

$sharer                 = make_sharer();
$post                   = new stdClass();
$post->post_title       = 'Shared Title';
$post->post_content     = 'Shared body';
$posts_fixture['posts'] = array( $post );
$html                   = $sharer->theHTML( array( 'siteid' => 5, 'postType' => 'post' ) );
check( 'frontend output switches to the attribute site id', array( 5 ) === $switched_blogs );
check( 'frontend output restores the current blog', 1 === $restored_blogs );
check( 'frontend output announces the source site id', false !== strpos( $html, 'Content From Site ID: 5' ) );
check( 'frontend output includes the post title', false !== strpos( $html, '<h2>Shared Title</h2>' ) );
check( 'frontend output renders the first inner block', false !== strpos( $html, '[rendered:Shared body]' ) );

echo "\n" . ( $tests - $failed ) . "/$tests passed\n";
exit( 0 === $failed ? 0 : 1 );
