<?php
/**
 * Plugin Name:       UCSC Service Blocks
 * Plugin URI:        https://github.com/ucsc/ucsc-service-blocks
 * Description:       Service blocks for UCSC WordPress Websites.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           1.0.0
 * Author:            UC Santa Cruz
 * Author URI:        https://github.com/ucsc
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ucsc
 * Domain Path:       service-blocks
 *
 * @package           ucsc-blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once plugin_dir_path( __FILE__ ) . 'classes/CourseCatalog.php';
require_once plugin_dir_path( __FILE__ ) . 'classes/CampusDirectory.php';
require_once plugin_dir_path( __FILE__ ) . 'classes/ClassSchedule.php';

// New option for using shortcode without Service blocks
require_once plugin_dir_path( __FILE__ ) . 'classes/CampusDirectoryShortcode.php';

// include_once(plugin_dir_path(__FILE__) . 'classes/FeedbackForm.php');
require_once plugin_dir_path( __FILE__ ) . 'classes/SiteSettings.php';


add_action( 'admin_enqueue_scripts', 'ucsc_service_blocks_register_js_build' );

function ucsc_service_blocks_register_js_build() {
		wp_enqueue_script( 'ucscblocks', plugin_dir_url( __FILE__ ) . 'build/index.js', array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor' ) );
}




// $UCSCServiceDemoBlock1 = new UCSCServiceDemoBlock1();
// $UCSCServiceDemoBlock2 = new UCSCServiceDemoBlock2();
// $ContentSharer = new ContentSharer();

$CourseCatalog    = new CourseCatalog();
$CampusDirectory  = new CampusDirectory();
$ClassSchedule    = new ClassSchedule();
$SiteSettings     = new SiteSettings();

$CampusDirectoryShortcode = new CampusDirectoryShortcode();

// $FeedbackForm = new FeedbackForm();

// Add link to Settings page

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'ucsc_service_blocks_plugin_action_links' );

function ucsc_service_blocks_plugin_action_links( $links ) {
	// Build and escape the URL.
	$url = esc_url( add_query_arg(
		'page',
		'ucsc_service_blocks_settings_page',
		get_admin_url() . 'options-general.php'
	) );
	// Create the link.
	$settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
	// Adds the link to the end of the array.
	array_push(
		$links,
		$settings_link
	);
	return $links;
}
