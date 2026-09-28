<?php
/**
 * Dependency-free tests for the LDAP settings admin in SiteSettings.
 *
 * WPM-186: the network settings save handler, its notice, the network and
 * per-site settings pages, and settings registration. These store the LDAP
 * credentials Campus Directory depends on and had no test.
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/SiteSettingsAdminTest.php
 */

define( 'ABSPATH', sys_get_temp_dir() . '/wp-mock/' );
@mkdir( ABSPATH . 'wp-admin/includes', 0777, true );
@file_put_contents( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php', "<?php\nclass WP_Filesystem_Base {}\n" );
@file_put_contents( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php', "<?php\nclass WP_Filesystem_Direct extends WP_Filesystem_Base {}\n" );
define( 'WP_PLUGIN_DIR', '/wp-content/plugins' );

// ── Recording stubs ─────────────────────────────────────────────────────────

$calls        = array(); // ordered log of side effects
$site_options = array();
$options      = array();
$nonce_valid  = true;
$admin_pages  = array();
$settings_api = array();

class Test_Nonce_Failed extends Exception {}
class Test_Redirect_Called extends Exception {}

function check_admin_referer( $action ) {
	global $calls, $nonce_valid;
	$calls[] = 'check_admin_referer:' . $action;
	if ( ! $nonce_valid ) {
		// WordPress dies here; throw so the test keeps running.
		throw new Test_Nonce_Failed();
	}
	return 1;
}
function update_site_option( $name, $value ) {
	global $calls, $site_options;
	$calls[]               = 'update_site_option:' . $name;
	$site_options[ $name ] = $value;
	return true;
}
function get_site_option( $name, $default = false ) {
	global $site_options;
	return $site_options[ $name ] ?? $default;
}
function get_option( $name, $default = false ) {
	global $options;
	return $options[ $name ] ?? $default;
}
function add_query_arg( $args, $url ) {
	return $url . '?' . http_build_query( $args );
}
function network_admin_url( $path ) {
	return 'https://example.ucsc.edu/wp-admin/network/' . $path;
}
function wp_redirect( $location, $status = 302 ) {
	global $calls;
	$calls[] = 'wp_redirect:' . $location;
	// networkSaveSettings() exits after redirecting; throw to keep running.
	throw new Test_Redirect_Called();
}
function add_submenu_page( ...$args ) {
	global $admin_pages;
	$admin_pages['submenu'] = $args;
}
function add_options_page( ...$args ) {
	global $admin_pages;
	$admin_pages['options'] = $args;
}
function get_plugin_data( $file ) {
	return array( 'Version' => '9.9.9', 'file' => $file );
}
function wp_nonce_field( $action ) {
	echo '<input type="hidden" name="_wpnonce" data-action="' . $action . '" />';
}
function submit_button() {
	echo '<input type="submit" />';
}
function add_settings_section( ...$args ) {
	global $settings_api;
	$settings_api['sections'][] = $args;
}
function add_settings_field( $id, $title, $callback, $page, $section ) {
	global $settings_api;
	$settings_api['fields'][ $id ] = array( 'title' => $title, 'callback' => $callback, 'page' => $page, 'section' => $section );
}
function register_setting( $group, $name, $args ) {
	global $settings_api;
	$settings_api['settings'][ $group . '/' . $name ] = $args;
}
function settings_fields( $group ) {
	echo '<input type="hidden" name="option_page" value="' . $group . '" />';
}
function do_settings_sections( $page ) {
	echo '<!--sections:' . $page . '-->';
}

require __DIR__ . '/helpers/harness.php';
require __DIR__ . '/../../classes/SiteSettings.php';

function reset_admin_state() {
	global $calls, $site_options, $options, $nonce_valid, $admin_pages, $settings_api;
	$calls        = array();
	$site_options = array();
	$options      = array();
	$nonce_valid  = true;
	$admin_pages  = array();
	$settings_api = array();
	$_POST        = array();
	$_GET         = array();
}

function capture( $callback ) {
	ob_start();
	call_user_func( $callback );
	return ob_get_clean();
}

$settings = new SiteSettings();
$hostile  = '"><script>alert(1)</script>';

echo "network settings save:\n";

reset_admin_state();
$_POST = array( 'ldap_api_key' => 'pw', 'ldap_cn' => 'pbsci-wordpress', 'ldap_url' => 'ldap-blue.ucsc.edu' );
try {
	$settings->networkSaveSettings();
} catch ( Test_Redirect_Called $e ) {
}
check( 'verifies the ucscplugin-validate nonce before writing anything', 'check_admin_referer:ucscplugin-validate' === ( $calls[0] ?? '' ) );
check( 'saves the LDAP key, CN and URL as network options', array( 'ldap_api_key' => 'pw', 'ldap_cn' => 'pbsci-wordpress', 'ldap_url' => 'ldap-blue.ucsc.edu' ) === $site_options );
check( 'redirects back to the network settings page with updated=1', 'wp_redirect:https://example.ucsc.edu/wp-admin/network/settings.php?page=ucsc-gutenberg-blocks-network-settings&updated=1' === end( $calls ) );

reset_admin_state();
$nonce_valid = false;
$_POST       = array( 'ldap_api_key' => 'attacker', 'ldap_cn' => 'x', 'ldap_url' => 'evil.example.com' );
try {
	$settings->networkSaveSettings();
} catch ( Test_Nonce_Failed $e ) {
}
check( 'a failed nonce check writes no options', array() === $site_options );

echo "\nnetwork settings notice:\n";

reset_admin_state();
$_GET = array( 'page' => 'ucsc-gutenberg-blocks-network-settings', 'updated' => '1' );
check( 'shows "Settings updated." after a save', false !== strpos( capture( array( $settings, 'networkSettingsNotifications' ) ), 'Settings updated.' ) );

reset_admin_state();
$_GET = array( 'page' => 'ucsc-gutenberg-blocks-network-settings' );
check( 'shows no notice without updated', '' === capture( array( $settings, 'networkSettingsNotifications' ) ) );

reset_admin_state();
$_GET = array( 'page' => 'some-other-page', 'updated' => '1' );
check( 'shows no notice on other admin pages', '' === capture( array( $settings, 'networkSettingsNotifications' ) ) );

echo "\nnetwork settings page:\n";

reset_admin_state();
$settings->networkSettingsLink();
// Current behavior: manage_options (WP convention for network pages is
// manage_network_options; network admin itself is super-admin only).
check( 'adds the network settings page under settings.php, gated by manage_options',
	'settings.php' === ( $admin_pages['submenu'][0] ?? '' )
	&& 'manage_options' === ( $admin_pages['submenu'][3] ?? '' )
	&& 'ucsc-gutenberg-blocks-network-settings' === ( $admin_pages['submenu'][4] ?? '' )
);

reset_admin_state();
$site_options = array( 'ldap_api_key' => $hostile, 'ldap_cn' => $hostile, 'ldap_url' => $hostile );
$html         = capture( array( $settings, 'networkSettingsPage' ) );
check( 'posts to edit.php?action=ucscplugin with the ucscplugin-validate nonce', false !== strpos( $html, 'action="edit.php?action=ucscplugin"' ) && false !== strpos( $html, 'data-action="ucscplugin-validate"' ) );
check( 'shows the plugin version', false !== strpos( $html, 'Version: 9.9.9' ) );
check( 'renders the three stored network values escaped', 3 === substr_count( $html, 'value="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;"' ) && false === strpos( $html, '<script>' ) );

echo "\nper-site settings:\n";

reset_admin_state();
$settings->settings();
check( 'registers the three LDAP fields on the plugin settings page', array( 'ldap_api_key', 'ldap_cn', 'ldap_url' ) === array_keys( $settings_api['fields'] ?? array() ) );
$sanitized = array_filter( $settings_api['settings'] ?? array(), function ( $args ) {
	return 'sanitize_text_field' === $args['sanitize_callback'] && '' === $args['default'];
} );
check( 'registers each LDAP setting for the site and network groups with sanitize_text_field', 6 === count( $sanitized ) );

reset_admin_state();
$options = array( 'ldap_api_key' => $hostile, 'ldap_cn' => $hostile, 'ldap_url' => $hostile );
$fields  = capture( array( $settings, 'ldapKeyHTML' ) ) . capture( array( $settings, 'ldapCN' ) ) . capture( array( $settings, 'ldapURL' ) );
check( 'field inputs render the stored per-site values escaped', 3 === substr_count( $fields, 'value="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;"' ) && false === strpos( $fields, '<script>' ) );
check( 'field inputs post ldap_api_key, ldap_cn and ldap_url', false !== strpos( $fields, 'name="ldap_api_key"' ) && false !== strpos( $fields, 'name="ldap_cn"' ) && false !== strpos( $fields, 'name="ldap_url"' ) );

reset_admin_state();
$settings->settingsLink();
check( 'adds the per-site settings page gated by manage_options', 'manage_options' === ( $admin_pages['options'][2] ?? '' ) && 'ucsc_gutenberg_blocks_settings_page' === ( $admin_pages['options'][3] ?? '' ) );

$html = capture( array( $settings, 'settingsPageHTML' ) );
check( 'per-site page posts to options.php with the ucsc_gutenberg_blocks settings group', false !== strpos( $html, 'action="options.php"' ) && false !== strpos( $html, 'value="ucsc_gutenberg_blocks"' ) && false !== strpos( $html, '<!--sections:ucsc_gutenberg_blocks_settings_page-->' ) );

finish_tests();
