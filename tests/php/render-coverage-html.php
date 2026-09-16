<?php
/**
 * Render a small human-readable HTML summary from the PHP harness coverage data.
 *
 * The PHP tests intentionally avoid PHPUnit and Composer dependencies, so this
 * fills the same artifact slot as PHPUnit's --coverage-html with the data the
 * local harness already emits.
 */

if ( $argc < 3 ) {
	fwrite( STDERR, "Usage: php tests/php/render-coverage-html.php <coverage-raw.json> <coverage/html>\n" );
	exit( 1 );
}

$raw_path = $argv[1];
$out_dir  = rtrim( $argv[2], '/' );

if ( ! file_exists( $raw_path ) ) {
	fwrite( STDERR, "Coverage raw data not found: $raw_path\n" );
	exit( 1 );
}

$coverage = json_decode( file_get_contents( $raw_path ), true );
if ( ! is_array( $coverage ) ) {
	fwrite( STDERR, "Coverage raw data is not valid JSON: $raw_path\n" );
	exit( 1 );
}

ksort( $coverage );

$rows          = '';
$total_lines   = 0;
$covered_lines = 0;

foreach ( $coverage as $file => $lines ) {
	ksort( $lines, SORT_NUMERIC );

	$statements = count( $lines );
	$covered    = count( array_filter( $lines ) );
	$percent    = $statements > 0 ? ( $covered / $statements ) * 100 : 100;

	$total_lines   += $statements;
	$covered_lines += $covered;

	$rows .= sprintf(
		"<tr><td>%s</td><td>%d</td><td>%d</td><td>%.2f%%</td></tr>\n",
		htmlspecialchars( $file, ENT_QUOTES, 'UTF-8' ),
		$covered,
		$statements,
		$percent
	);
}

$total_percent = $total_lines > 0 ? ( $covered_lines / $total_lines ) * 100 : 100;

@mkdir( $out_dir, 0777, true );

$html = sprintf(
	'<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>PHP Coverage</title>
<style>
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 2rem; color: #222; }
table { border-collapse: collapse; width: 100%%; }
th, td { border-bottom: 1px solid #ddd; padding: 0.45rem 0.6rem; text-align: left; }
th { background: #f5f5f5; }
.summary { font-size: 1.2rem; margin-bottom: 1rem; }
</style>
</head>
<body>
<h1>PHP Coverage</h1>
<p class="summary">%.2f%% (%d / %d statements)</p>
<table>
<thead><tr><th>File</th><th>Covered</th><th>Statements</th><th>Coverage</th></tr></thead>
<tbody>
%s</tbody>
</table>
</body>
</html>
',
	$total_percent,
	$covered_lines,
	$total_lines,
	$rows
);

file_put_contents( $out_dir . '/index.html', $html );
