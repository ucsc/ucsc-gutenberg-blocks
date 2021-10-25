<?php

/*
  Plugin Name: UCSC Gutenberg Blocks
  Description: Custom UCSC Gutenberg Blocks.
  Version: 1.0
  Author: UCSC
  Author URI: https://www.ucsc.edu/
*/
error_reporting();
if (!defined('ABSPATH')) exit; // Exit if accessed directly
include(plugin_dir_path(__FILE__) . 'classes/UCSCGutenbergDemoBlock1.php');
wp_register_script('ucscblocks', plugin_dir_url(__FILE__) . 'build/index.js', array('wp-blocks', 'wp-element'));

$UCSCGutenbergDemoBlock1 = new UCSCGutenbergDemoBlock1();
