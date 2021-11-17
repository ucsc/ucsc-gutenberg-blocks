<?php

/*
  Plugin Name: UCSC Gutenberg Blocks
  Description: Custom UCSC Gutenberg Blocks.
  Version: 1.0
  Author: UCSC
  Author URI: https://www.ucsc.edu/
*/
if (!defined('ABSPATH')) exit; // Exit if accessed directly

include(plugin_dir_path(__FILE__) . 'classes/UCSCGutenbergDemoBlock1.php');
include(plugin_dir_path(__FILE__) . 'classes/UCSCGutenbergDemoBlock2.php');
include(plugin_dir_path(__FILE__) . 'classes/ContentSharer.php');
include(plugin_dir_path(__FILE__) . 'classes/CourseCatalog.php');
include(plugin_dir_path(__FILE__) . 'classes/CampusDirectory.php');
include(plugin_dir_path(__FILE__) . 'classes/ClassSchedule.php');

wp_register_script('ucscblocks', plugin_dir_url(__FILE__) . 'build/index.js', array('wp-blocks','wp-element', 'wp-components'));

$UCSCGutenbergDemoBlock1 = new UCSCGutenbergDemoBlock1();
$UCSCGutenbergDemoBlock2 = new UCSCGutenbergDemoBlock2();
$ContentSharer = new ContentSharer();
$CourseCatalog = new CourseCatalog();
$CampusDirectory = new CampusDirectory();
$ClassSchedule = new ClassSchedule();
