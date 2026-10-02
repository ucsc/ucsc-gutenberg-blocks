<?php
/**
 * Record a dated coverage snapshot in docs/coverage/ so progress can be
 * compared over time in git.
 *
 * Reads the reports that composer test:coverage and npm run test:coverage
 * leave in coverage/ (clover.xml, coverage-summary.json, lcov.info), writes
 * docs/coverage/<date>.md with the totals and every file that still has
 * uncovered lines, and adds or replaces that date's row in
 * docs/coverage/README.md. A second run on the same date replaces that date's
 * snapshot. Out-of-scope files are already left out of the reports by
 * tests/coverage-exclude.txt.
 *
 * Usage: php tests/coverage-snapshot.php <YYYY-MM-DD> <commit-sha>
 */

if ( $argc < 3 || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $argv[1] ) ) {
	fwrite( STDERR, "Usage: php tests/coverage-snapshot.php <YYYY-MM-DD> <commit-sha>\n" );
	exit( 1 );
}

$date   = $argv[1];
$sha    = $argv[2];
$root   = dirname( __DIR__ );
$clover = "$root/coverage/clover.xml";
$jsum   = "$root/coverage/coverage-summary.json";
$lcov   = "$root/coverage/lcov.info";

foreach ( array( $clover, $jsum, $lcov ) as $report ) {
	if ( ! file_exists( $report ) ) {
		fwrite( STDERR, "Missing $report. Run PHP and JS coverage first (scripts/test-tiers.sh all).\n" );
		exit( 1 );
	}
}

/**
 * Collapse sorted line numbers into ranges: 3, 4, 5, 9 becomes "3-5, 9".
 */
function ucsc_line_ranges( array $lines ) {
	sort( $lines );
	$ranges = array();
	$start  = $prev = null;
	foreach ( $lines as $line ) {
		if ( null !== $prev && $line === $prev + 1 ) {
			$prev = $line;
			continue;
		}
		if ( null !== $start ) {
			$ranges[] = $start === $prev ? "$start" : "$start-$prev";
		}
		$start = $prev = $line;
	}
	if ( null !== $start ) {
		$ranges[] = $start === $prev ? "$start" : "$start-$prev";
	}
	return implode( ', ', $ranges );
}

function ucsc_pct( $covered, $total ) {
	return $total ? sprintf( '%.1f%%', 100 * $covered / $total ) : 'n/a';
}

// PHP: per-file statements and uncovered lines from clover.xml.
$php_files = array();
$php_total = $php_covered = 0;
foreach ( simplexml_load_file( $clover )->xpath( '//file' ) as $file ) {
	$name   = ltrim( substr( (string) $file['name'], strlen( $root ) ), '/' );
	$total  = (int) $file->metrics['statements'];
	$hit    = (int) $file->metrics['coveredstatements'];
	$missed = array();
	foreach ( $file->line as $line ) {
		if ( 'stmt' === (string) $line['type'] && 0 === (int) $line['count'] ) {
			$missed[] = (int) $line['num'];
		}
	}
	$php_total   += $total;
	$php_covered += $hit;
	$php_files[ $name ] = array( $hit, $total, $missed );
}

// JS: per-file lines from coverage-summary.json, uncovered lines from lcov.info.
$summary = json_decode( file_get_contents( $jsum ), true );
$js_rel  = function ( $path ) {
	$at = strpos( $path, '/src/' );
	return false === $at ? $path : substr( $path, $at + 1 );
};
$js_missed = array();
$current   = null;
foreach ( file( $lcov, FILE_IGNORE_NEW_LINES ) as $row ) {
	if ( 0 === strpos( $row, 'SF:' ) ) {
		$current = $js_rel( substr( $row, 3 ) );
		$js_missed[ $current ] = array();
	} elseif ( 0 === strpos( $row, 'DA:' ) ) {
		list( $num, $count ) = explode( ',', substr( $row, 3 ) );
		if ( 0 === (int) $count ) {
			$js_missed[ $current ][] = (int) $num;
		}
	}
}
$js_files = array();
foreach ( $summary as $path => $metrics ) {
	if ( 'total' === $path ) {
		continue;
	}
	$name = $js_rel( $path );
	$js_files[ $name ] = array(
		$metrics['lines']['covered'],
		$metrics['lines']['total'],
		isset( $js_missed[ $name ] ) ? $js_missed[ $name ] : array(),
	);
}
$js_total = $summary['total'];

// Snapshot file.
$gap_rows = function ( array $files ) {
	ksort( $files );
	$rows = array();
	foreach ( $files as $name => $data ) {
		list( $hit, $total, $missed ) = $data;
		if ( $hit < $total ) {
			$rows[] = sprintf( '| `%s` | %s | %d | %s |', $name, ucsc_pct( $hit, $total ), $total - $hit, ucsc_line_ranges( $missed ) );
		}
	}
	return $rows;
};
$php_gaps = $gap_rows( $php_files );
$js_gaps  = $gap_rows( $js_files );
$php_pct  = ucsc_pct( $php_covered, $php_total );
$js_pct   = sprintf( '%.1f%%', $js_total['lines']['pct'] );
$none     = '| none | | | |';

$md   = array();
$md[] = "# Coverage snapshot $date";
$md[] = '';
$md[] = "- Commit: `$sha`";
$md[] = '- Command: `scripts/test-tiers.sh all` (writes this file via `composer coverage:snapshot`)';
$md[] = '- Scope: campus-directory, class-schedule, course-catalog and their shared code; files listed in `tests/coverage-exclude.txt` are not counted';
$md[] = '';
$md[] = '## Totals';
$md[] = '';
$md[] = '| Suite | Measure | Covered | Total | Coverage |';
$md[] = '| --- | --- | --- | --- | --- |';
$md[] = sprintf( '| PHP | statements | %d | %d | %s |', $php_covered, $php_total, $php_pct );
foreach ( array( 'lines', 'statements', 'branches', 'functions' ) as $measure ) {
	$m    = $js_total[ $measure ];
	$md[] = sprintf( '| JS | %s | %d | %d | %.1f%% |', $measure, $m['covered'], $m['total'], $m['pct'] );
}
$md[] = '';
$md[] = sprintf(
	'%d of %d PHP files and %d of %d JS files are fully covered.',
	count( $php_files ) - count( $php_gaps ),
	count( $php_files ),
	count( $js_files ) - count( $js_gaps ),
	count( $js_files )
);
$md[] = '';
$md[] = '## Gaps: PHP files with uncovered statements';
$md[] = '';
$md[] = '| File | Coverage | Uncovered | Lines |';
$md[] = '| --- | --- | --- | --- |';
$md   = array_merge( $md, $php_gaps ? $php_gaps : array( $none ) );
$md[] = '';
$md[] = '## Gaps: JS files with uncovered lines';
$md[] = '';
$md[] = '| File | Coverage | Uncovered | Lines |';
$md[] = '| --- | --- | --- | --- |';
$md   = array_merge( $md, $js_gaps ? $js_gaps : array( $none ) );
$md[] = '';

$dir = "$root/docs/coverage";
@mkdir( $dir, 0777, true );
file_put_contents( "$dir/$date.md", implode( "\n", $md ) );

// Index: one row per date, newest first.
$index = "$dir/README.md";
$rows  = array();
if ( file_exists( $index ) ) {
	foreach ( file( $index, FILE_IGNORE_NEW_LINES ) as $row ) {
		if ( preg_match( '/^\| \[(\d{4}-\d{2}-\d{2})\]/', $row, $m ) && $m[1] !== $date ) {
			$rows[ $m[1] ] = $row;
		}
	}
}
$rows[ $date ] = sprintf(
	'| [%s](%s.md) | `%s` | %s | %s | %d | %d |',
	$date,
	$date,
	$sha,
	$php_pct,
	$js_pct,
	count( $php_gaps ),
	count( $js_gaps )
);
krsort( $rows );

$head = array(
	'# Coverage history',
	'',
	'One snapshot per date, written by `scripts/test-tiers.sh all`. Each snapshot',
	'lists the totals and every in-scope file that still has uncovered lines.',
	'Out-of-scope blocks are listed in `tests/coverage-exclude.txt` and never',
	'counted as gaps.',
	'',
	'| Date | Commit | PHP statements | JS lines | PHP files with gaps | JS files with gaps |',
	'| --- | --- | --- | --- | --- | --- |',
);
file_put_contents( $index, implode( "\n", array_merge( $head, array_values( $rows ) ) ) . "\n" );

echo "Coverage snapshot: docs/coverage/$date.md (PHP $php_pct, JS $js_pct lines)\n";
