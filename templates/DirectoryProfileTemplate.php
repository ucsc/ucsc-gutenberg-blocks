<?php

$page_content = "<h1>Testing 1-2-3</h1>";
$page_title = "New Title";

// Filter 'the_title' WordPress variable for use in 'content-plugin.php'
add_filter('the_title', $page_title);

// Filter 'the_content' WordPress variable for use in 'content-plugin.php'
add_filter('the_content', $page_content);

if ( file_exists( get_theme_file_path( 'header-plugin.php' ) ) ) {
	get_header( 'plugin' );
}

// Call content using get_template_part()
if ( file_exists( get_theme_file_path( 'content-plugin.php' ) ) ) {
    get_template_part( 'content', 'plugin' );
}

if ( file_exists( get_theme_file_path( 'footer-plugin.php' ) ) ) {
	get_footer( 'plugin' );
}

