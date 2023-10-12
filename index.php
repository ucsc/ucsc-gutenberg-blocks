<?php
/**
 * Plugin Name:       UCSC Service Blocks
 * Plugin URI:        https://github.com/ucsc/ucsc-service-blocks
 * Description:       Service blocks for UCSC WordPress Websites.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           1.1.21
 * Author:            UC Santa Cruz
 * Author URI:        https://github.com/ucsc
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ucsc
 * Domain Path:       service-blocks
 *
 * @package           ucsc-blocks
 */

if (!defined('ABSPATH')) { exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'classes/CourseCatalog.php';
require_once plugin_dir_path(__FILE__) . 'classes/CampusDirectory.php';
require_once plugin_dir_path(__FILE__) . 'classes/ClassSchedule.php';
require_once plugin_dir_path(__FILE__) . 'classes/Accordion.php';
require_once plugin_dir_path(__FILE__) . 'classes/AccordionWrapper.php';

// New option for using shortcode without Service blocks
require_once plugin_dir_path(__FILE__) . 'classes/CampusDirectoryShortcode.php';

// include_once(plugin_dir_path(__FILE__) . 'classes/FeedbackForm.php');
require_once plugin_dir_path(__FILE__) . 'classes/SiteSettings.php';


add_action('admin_enqueue_scripts', 'registerJSBuild');

function registerJSBuild()
{
		wp_enqueue_script('ucscblocks', plugin_dir_url(__FILE__) . 'build/index.js', array('wp-blocks','wp-element', 'wp-components', 'wp-block-editor'));
}




// $UCSCServiceDemoBlock1 = new UCSCServiceDemoBlock1();
// $UCSCServiceDemoBlock2 = new UCSCServiceDemoBlock2();
// $ContentSharer = new ContentSharer();

$CourseCatalog = new CourseCatalog();
$CampusDirectory = new CampusDirectory();
$ClassSchedule = new ClassSchedule();
$Accordion = new Accordion();
$AccordionWrapper = new AccordionWrapper();
$SiteSettings = new SiteSettings();

$CampusDirectoryShortcode = new CampusDirectoryShortcode();

// $FeedbackForm = new FeedbackForm();
