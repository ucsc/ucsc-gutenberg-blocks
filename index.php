<?php

/*
  Plugin Name: UCSC Gutenberg Blocks
  Description: Custom UCSC Gutenberg Blocks.
  Version: 1.0.13
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
// include_once(plugin_dir_path(__FILE__) . 'classes/FeedbackForm.php');
include_once(plugin_dir_path(__FILE__) . 'classes/SiteSettings.php');


add_action('admin_enqueue_scripts', 'registerJSBuild');

function registerJSBuild() {
  wp_enqueue_script('ucscblocks', plugin_dir_url(__FILE__) . 'build/index.js', array('wp-blocks','wp-element', 'wp-components'));
}




// $UCSCGutenbergDemoBlock1 = new UCSCGutenbergDemoBlock1();
// $UCSCGutenbergDemoBlock2 = new UCSCGutenbergDemoBlock2();
// $ContentSharer = new ContentSharer();
$CourseCatalog = new CourseCatalog();
$CampusDirectory = new CampusDirectory();
$ClassSchedule = new ClassSchedule();
$Accordion = new Accordion();
$SiteSettings = new SiteSettings();
// $FeedbackForm = new FeedbackForm();
