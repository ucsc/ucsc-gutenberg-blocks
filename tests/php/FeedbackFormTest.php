<?php
/**
 * Dependency-free tests for the FeedbackForm block.
 *
 * Covers REST submission validation (missing-field reporting), the
 * field-specific sanitization path (email vs. text), and the rendered form
 * markup.
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/FeedbackFormTest.php
 */

$actions       = array();
$sanitize_calls = array();

function add_action( $hook, $callback ) {
	global $actions;
	$actions[ $hook ][] = $callback;
}

function register_block_type() {}
function register_rest_route() {}

function sanitize_email( $value ) {
	global $sanitize_calls;
	$sanitize_calls[] = array( 'fn' => 'sanitize_email', 'value' => $value );
	return trim( $value );
}

function sanitize_text_field( $value ) {
	global $sanitize_calls;
	$sanitize_calls[] = array( 'fn' => 'sanitize_text_field', 'value' => $value );
	return trim( strip_tags( $value ) );
}

class WP_REST_Response {
	public $data;

	public function __construct( $data = null ) {
		$this->data = $data;
	}

	public function get_data() {
		return $this->data;
	}
}

class Test_REST_Request {
	private $params;

	public function __construct( $params = array() ) {
		$this->params = $params;
	}

	public function get_params() {
		return $this->params;
	}
}

require __DIR__ . '/../../classes/FeedbackForm.php';

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
	global $sanitize_calls;
	$sanitize_calls = array();
}

function make_form() {
	reset_test_state();
	return new FeedbackForm();
}

function valid_submission() {
	return array(
		'name'         => 'Sammy Slug',
		'email'        => 'sammy@ucsc.edu',
		'affiliations' => 'student',
		'topic'        => 'content',
		'message'      => 'Looks great.',
	);
}

function field_named( $calls, $value ) {
	foreach ( $calls as $call ) {
		if ( $value === $call['value'] ) {
			return $call['fn'];
		}
	}
	return null;
}

echo "submission validation:\n";

$form     = make_form();
$response = $form->submit_feedback( new Test_REST_Request( array() ) );
$data     = $response->get_data();
check( 'empty submission returns a WP_REST_Response', $response instanceof WP_REST_Response );
check( 'empty submission is not successful', false === $data['success'] );
check( 'empty submission reports every missing field', array( 'name', 'email', 'affiliations', 'topic', 'message' ) === $data['missing_fields'] );

$form     = make_form();
$partial  = valid_submission();
unset( $partial['email'], $partial['message'] );
$response = $form->submit_feedback( new Test_REST_Request( $partial ) );
$data     = $response->get_data();
check( 'partial submission is not successful', false === $data['success'] );
check( 'partial submission reports only the missing fields', array( 'email', 'message' ) === $data['missing_fields'] );
check( 'partial submission lists missing fields in the message', false !== strpos( $data['message'], 'email, message' ) );

$form     = make_form();
$blank    = valid_submission();
$blank['name'] = '   ';
$response = $form->submit_feedback( new Test_REST_Request( $blank ) );
$data     = $response->get_data();
// empty() treats a whitespace-only string as non-empty, so this should pass validation.
check( 'whitespace-only name is treated as present', true === $data['success'] );

echo "successful submission:\n";

$form     = make_form();
$response = $form->submit_feedback( new Test_REST_Request( valid_submission() ) );
$data     = $response->get_data();
check( 'complete submission is successful', true === $data['success'] );
check( 'complete submission returns the thank-you message', 'Thank you for your feedback!' === $data['message'] );
check( 'complete submission reports no missing fields', ! isset( $data['missing_fields'] ) );

echo "field sanitization:\n";

$form = make_form();
$form->submit_feedback( new Test_REST_Request( valid_submission() ) );
check( 'email is sanitized with sanitize_email', 'sanitize_email' === field_named( $sanitize_calls, 'sammy@ucsc.edu' ) );
check( 'name is sanitized with sanitize_text_field', 'sanitize_text_field' === field_named( $sanitize_calls, 'Sammy Slug' ) );
check( 'message is sanitized with sanitize_text_field', 'sanitize_text_field' === field_named( $sanitize_calls, 'Looks great.' ) );
check( 'every field is sanitized exactly once', 5 === count( $sanitize_calls ) );

$form = make_form();
$form->submit_feedback( new Test_REST_Request( array() ) );
check( 'invalid submission never reaches sanitization', array() === $sanitize_calls );

echo "rendered form markup:\n";

$form = make_form();
$html = $form->theHTML(
	array(
		'name'                       => 'Your Name',
		'namePlaceholder'            => 'Enter name',
		'email'                      => 'Your Email',
		'emailPlaceholder'           => 'Enter email',
		'affiliation'                => 'Your Affiliation',
		'affiliationOtherPlaceholder' => 'Other affiliation',
		'topic'                      => 'Your Topic',
		'message'                    => 'Your Message',
		'messagePlaceholder'         => 'Enter message',
	)
);
check( 'form renders the configured name label', false !== strpos( $html, 'Your Name' ) );
check( 'form renders the configured email placeholder', false !== strpos( $html, 'Enter email' ) );
check( 'form renders the configured message placeholder', false !== strpos( $html, 'Enter message' ) );
check( 'form includes the submit button', false !== strpos( $html, 'type="submit"' ) );
check( 'form includes the feedback form element', false !== strpos( $html, 'id="feedback-form"' ) );

echo "\n" . ( $tests - $failed ) . "/$tests passed\n";
exit( 0 === $failed ? 0 : 1 );
