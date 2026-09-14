<?php
/**
 * WPM-158: verify the class_schedule_term query-param override actually
 * works through the real $_GET / filter_input(INPUT_GET, ...) path.
 *
 * classes/ClassSchedule.php:170 reads the override via filter_input(), which
 * only reflects true request superglobals — not $_GET assigned directly in a
 * CLI process. ClassScheduleTest.php's render_schedule() helper runs in a
 * plain CLI process and therefore cannot exercise this line at all; every
 * existing scenario silently takes the "no override" branch regardless of
 * what filter_input() would return.
 *
 * This test boots tests/php/fixtures/class_schedule_term_query_endpoint.php
 * under `php -S` (PHP's built-in web server, which populates $_GET/superglobals
 * from the real request the way any SAPI request would) and curls it with and
 * without ?class_schedule_term=..., asserting on the rendered term selection.
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/ClassScheduleQueryTermTest.php
 */

require __DIR__ . '/helpers/harness.php';

$host = '127.0.0.1';
$port = 18173 + ( getmypid() % 500 ); // spread across parallel runs
$docroot = __DIR__ . '/fixtures';

$cmd = sprintf(
	'php -S %s:%d -t %s > /tmp/class_schedule_query_term_server.log 2>&1 & echo $!',
	escapeshellarg( $host ),
	$port,
	escapeshellarg( $docroot )
);
$pid = (int) trim( shell_exec( $cmd ) );

// Give the built-in server a moment to bind the port.
$ready = false;
for ( $i = 0; $i < 20; $i++ ) {
	$conn = @fsockopen( $host, $port, $errno, $errstr, 0.25 );
	if ( $conn ) {
		fclose( $conn );
		$ready = true;
		break;
	}
	usleep( 100000 );
}

function fetch_class_schedule( $host, $port, $query = '' ) {
	$url = "http://$host:$port/class_schedule_term_query_endpoint.php" . ( $query ? "?$query" : '' );
	$ch  = curl_init( $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
	$body = curl_exec( $ch );
	curl_close( $ch );
	return $body;
}

if ( ! $ready ) {
	echo "  FAIL  could not start php -S fixture server (see /tmp/class_schedule_query_term_server.log)\n";
	echo "\n0/1 passed\n";
	if ( $pid ) {
		exec( 'kill ' . escapeshellarg( (string) $pid ) . ' 2>/dev/null' );
	}
	exit( 1 );
}

// No override: falls back to the default term (2258, marked default => 'Y').
$html_no_override = fetch_class_schedule( $host, $port );
check(
	'no query param: default term (2258) is selected',
	false !== strpos( $html_no_override, 'value="2258"' )
		&& preg_match( '/value="2258"\s*\n?\s*selected="selected"/', $html_no_override )
);
check(
	'no query param: non-default term (2262) is not selected',
	! preg_match( '/value="2262"\s*\n?\s*selected="selected"/', $html_no_override )
);
check(
	'no query param: courses render for the default term',
	false !== strpos( $html_no_override, 'Algorithms (term 2258)' )
);

// Query param present and matches a real term: overrides the default.
$html_override = fetch_class_schedule( $host, $port, 'class_schedule_term=2262' );
check(
	'class_schedule_term=2262: overrides the default term selection',
	preg_match( '/value="2262"\s*\n?\s*selected="selected"/', $html_override )
);
check(
	'class_schedule_term=2262: default term (2258) is no longer selected',
	! preg_match( '/value="2258"\s*\n?\s*selected="selected"/', $html_override )
);
check(
	'class_schedule_term=2262: courses render for the overridden term',
	false !== strpos( $html_override, 'Algorithms (term 2262)' )
);

// Query param present but does not match any known term: falls back to default.
$html_unknown = fetch_class_schedule( $host, $port, 'class_schedule_term=9999' );
check(
	'class_schedule_term=9999 (unknown term): falls back to the default term',
	preg_match( '/value="2258"\s*\n?\s*selected="selected"/', $html_unknown )
);

// Query param with LDAP/XSS-style metacharacters: rejected, falls back to default,
// and is never reflected unescaped into the markup (sanitize_text_field + strict match).
$html_attack = fetch_class_schedule( $host, $port, 'class_schedule_term=' . rawurlencode( '<script>alert(1)</script>' ) );
check(
	'class_schedule_term with script tag: falls back to the default term',
	preg_match( '/value="2258"\s*\n?\s*selected="selected"/', $html_attack )
);
check(
	'class_schedule_term with script tag: attack string is not reflected unescaped',
	false === strpos( $html_attack, '<script>alert(1)</script>' )
);

if ( $pid ) {
	exec( 'kill ' . escapeshellarg( (string) $pid ) . ' 2>/dev/null' );
}

finish_tests();
