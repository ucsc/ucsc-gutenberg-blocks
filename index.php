<?php

/*
 * Plugin Name: UCSC Gutenberg Blocks
 * Plugin URI: https://github.com/ucsc/ucsc-gutenberg-blocks
 * Description: Custom UCSC Gutenberg Blocks.
 * Version: 1.1.31
 * Author: UC Santa Cruz
 * Author URI: https://github.com/ucsc
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly
include_once(plugin_dir_path(__FILE__) . 'classes/CourseCatalog.php');
include_once(plugin_dir_path(__FILE__) . 'classes/CampusDirectory.php');
include_once(plugin_dir_path(__FILE__) . 'classes/ClassSchedule.php');
include_once(plugin_dir_path(__FILE__) . 'src/API/Course_Schedule_API.php');
include_once(plugin_dir_path(__FILE__) . 'classes/Accordion.php');
include_once(plugin_dir_path(__FILE__) . 'classes/AccordionWrapper.php');

// New option for using shortcode without Gutenberg blocks
include_once(plugin_dir_path(__FILE__) . 'classes/CampusDirectoryShortcode.php');

include_once(plugin_dir_path(__FILE__) . 'classes/SiteSettings.php');


add_action('admin_enqueue_scripts', 'registerJSBuild');

function registerJSBuild() {
  $script_path = plugin_dir_path(__FILE__) . 'build/index.js';
  $plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), 'plugin');
  $plugin_version = !empty($plugin_data['Version']) ? $plugin_data['Version'] : false;

  $is_dev_environment =
    (defined('DOCKER_DEV') && constant('DOCKER_DEV')) ||
    (function_exists('wp_get_environment_type') && in_array(wp_get_environment_type(), array('local', 'development'), true)) ||
    (defined('WP_DEBUG') && WP_DEBUG) ||
    (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG);

  $script_version = ($is_dev_environment && file_exists($script_path)) ? filemtime($script_path) : $plugin_version;
  wp_enqueue_script('ucscblocks', plugin_dir_url(__FILE__) . 'build/index.js', array('wp-blocks','wp-element', 'wp-components', 'wp-block-editor'), $script_version);
}




// $UCSCGutenbergDemoBlock1 = new UCSCGutenbergDemoBlock1();
// $UCSCGutenbergDemoBlock2 = new UCSCGutenbergDemoBlock2();
// $ContentSharer = new ContentSharer();

$CourseCatalog = new CourseCatalog();
$CampusDirectory = new CampusDirectory();
$ClassSchedule = new ClassSchedule();

// Initialize Course Schedule API
( new Course_Schedule_API() )->init();
$Accordion = new Accordion();
$AccordionWrapper = new AccordionWrapper();
$SiteSettings = new SiteSettings();

$CampusDirectoryShortcode = new CampusDirectoryShortcode();

// $FeedbackForm = new FeedbackForm();
