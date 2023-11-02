<?php

/*
  Plugin Name: UCSC Gutenberg Blocks (deprecated)
  Description: Custom UCSC Gutenberg Blocks. The blocks in this plugin are deprecated. They will be replaced with a new plugin.
  Version: 1.1.23
  Author: UCSC
  Author URI: https://www.ucsc.edu/
*/
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// include_once(plugin_dir_path(__FILE__) . 'classes/UCSCGutenbergDemoBlock1.php');
// include_once(plugin_dir_path(__FILE__) . 'classes/UCSCGutenbergDemoBlock2.php');
// include_once(plugin_dir_path(__FILE__) . 'classes/ContentSharer.php');

include_once(plugin_dir_path(__FILE__) . 'classes/CourseCatalog.php');
include_once(plugin_dir_path(__FILE__) . 'classes/CampusDirectory.php');
include_once(plugin_dir_path(__FILE__) . 'classes/ClassSchedule.php');
include_once(plugin_dir_path(__FILE__) . 'classes/Accordion.php');
include_once(plugin_dir_path(__FILE__) . 'classes/AccordionWrapper.php');

// New option for using shortcode without Gutenberg blocks
include_once(plugin_dir_path(__FILE__) . 'classes/CampusDirectoryShortcode.php');

// include_once(plugin_dir_path(__FILE__) . 'classes/FeedbackForm.php');
include_once(plugin_dir_path(__FILE__) . 'classes/SiteSettings.php');


add_action('admin_enqueue_scripts', 'registerJSBuild');

function registerJSBuild() {
  wp_enqueue_script('ucscblocks', plugin_dir_url(__FILE__) . 'build/index.js', array('wp-blocks','wp-element', 'wp-components', 'wp-block-editor'));
}




// $UCSCGutenbergDemoBlock1 = new UCSCGutenbergDemoBlock1();
// $UCSCGutenbergDemoBlock2 = new UCSCGutenbergDemoBlock2();
// $ContentSharer = new ContentSharer();

$CourseCatalog = new CourseCatalog();
$CampusDirectory = new CampusDirectory();
$ClassSchedule = new ClassSchedule();
$Accordion = new Accordion();
$AccordionWrapper = new AccordionWrapper();
$SiteSettings = new SiteSettings();

$CampusDirectoryShortcode = new CampusDirectoryShortcode();

// $FeedbackForm = new FeedbackForm();
