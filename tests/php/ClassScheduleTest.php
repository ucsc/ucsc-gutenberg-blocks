<?php
/**
 * Plain-PHP tests for ClassSchedule::theHTML().
 *
 * The container has PHP 8.1 but no PHPUnit/Composer, so this is a dependency-free
 * runner. theHTML() is a pure function (no WordPress calls), so we only stub the
 * WP hook functions the class constructor touches, then assert on the returned markup.
 *
 * Run inside the Docker WP container:
 *   docker exec ucsc-wordpress-wp php \
 *     /var/www/html/wp-content/plugins/ucsc-gutenberg-blocks/tests/php/ClassScheduleTest.php
 */

// --- Minimal WP stubs so the class file loads and constructs ---------------
if ( ! function_exists( 'add_action' ) )          { function add_action() {} }
if ( ! function_exists( 'register_rest_route' ) ) { function register_rest_route() {} }

require __DIR__ . '/../../classes/ClassSchedule.php';

// --- Tiny assertion harness -------------------------------------------------
$tests  = 0;
$failed = 0;

function check( $label, $cond ) {
    global $tests, $failed;
    $tests++;
    if ( $cond ) {
        echo "  PASS  $label\n";
    } else {
        $failed++;
        echo "  FAIL  $label\n";
    }
}

$cs = new ClassSchedule();

$PROD = 'https://webapps.ucsc.edu/wcsi';
$STG  = 'https://webapps.stg.web.aws.ucsc.edu/wcsi';

// --- useNewServer toggles the base URL -------------------------------------
echo "useNewServer base URL:\n";
$prod = $cs->theHTML( array( 'subjectOrDept' => 'dept', 'department' => 'CSE' ) );
check( 'defaults to prod webapps host', strpos( $prod, $PROD ) !== false );
check( 'prod build does not reference staging host', strpos( $prod, $STG ) === false );

$staging = $cs->theHTML( array( 'subjectOrDept' => 'dept', 'department' => 'CSE', 'useNewServer' => true ) );
check( 'useNewServer=true points at staging host', strpos( $staging, $STG ) !== false );

$falsey = $cs->theHTML( array( 'subjectOrDept' => 'dept', 'department' => 'CSE', 'useNewServer' => false ) );
check( 'useNewServer=false stays on prod host', strpos( $falsey, $PROD ) !== false );

// --- dept vs subject branching ---------------------------------------------
echo "dept/subject branching:\n";
$dept = $cs->theHTML( array( 'subjectOrDept' => 'dept', 'department' => 'CSE' ) );
check( 'dept mode emits department attribute', strpos( $dept, 'department="CSE"' ) !== false );
check( 'dept mode omits subject attribute', strpos( $dept, 'subject=' ) === false );

$subject = $cs->theHTML( array( 'subjectOrDept' => 'subject', 'subject' => 'AMS' ) );
check( 'subject mode emits subject attribute', strpos( $subject, 'subject="AMS"' ) !== false );
check( 'subject mode blanks department attribute', strpos( $subject, 'department=""' ) !== false );

// --- markup shape -----------------------------------------------------------
echo "markup shape:\n";
check( 'renders #wcsi mount node', strpos( $dept, 'id="wcsi"' ) !== false );
check( 'loads app.js from chosen host', strpos( $staging, $STG . '/js/app.js' ) !== false );
check( 'links app.css from chosen host', strpos( $staging, $STG . '/css/app.css' ) !== false );

// --- summary ----------------------------------------------------------------
echo "\n" . ( $tests - $failed ) . "/$tests passed\n";
exit( $failed === 0 ? 0 : 1 );
