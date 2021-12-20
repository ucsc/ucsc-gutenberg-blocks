<?php

class SiteSettings
{
  function __construct()
  {
    add_action('admin_menu', array($this, 'settingsLink'));
    add_action('admin_init', array($this, 'settings'));
    add_action("network_admin_menu", array($this, 'networkSettingsLink'));
    add_action('network_admin_edit_ucscplugin', array($this, 'networkSaveSettings'));
    add_action('network_admin_notices', array($this, 'networkSettingsNotifications'));
  }

  function networkSettingsNotifications()
  {
    if (isset($_GET['page']) && $_GET['page'] == 'ucsc-gutenberg-blocks-network-settings' && isset($_GET['updated'])) {
      echo '<div id="message" class="updated notice is-dismissible"><p>Settings updated.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
    }
  }

  function networkSaveSettings()
  {
    check_admin_referer('ucscplugin-validate'); // Nonce security check

    update_site_option('ldap_api_key', $_POST['ldap_api_key']);

    wp_redirect(add_query_arg(
      array(
        'page' => 'ucsc-gutenberg-blocks-network-settings',
        'updated' => true
      ),
      network_admin_url('settings.php')
    ));

    exit;
  }

  function networkSettingsLink()
  {
    add_submenu_page(
      'settings.php', // Parent element
      'UCSC Gutenberg Blocks Network Settings', // Text in browser title bar
      'UCSC Gutenberg Blocks Network Settings', // Text to be displayed in the menu.
      'manage_options', // Capability
      'ucsc-gutenberg-blocks-network-settings', // Page slug, will be displayed in URL
      array($this, 'networkSettingsPage') // Callback function which displays the page
    );
  }

  function networkSettingsPage()
  {
    echo '<div class="wrap">
		<h1>UCSC Gutenberg Blocks Network Settings</h1>
		<form method="post" action="edit.php?action=ucscplugin">';
    wp_nonce_field('ucscplugin-validate');
    echo '
			<table class="form-table">
				<tr>
					<th scope="row"><label for="ldap_api_key">LDAP API Key</label></th>
					<td>
						<input name="ldap_api_key" class="regular-text" type="text" id="ldap_api_key" value="' . esc_attr(get_site_option('ldap_api_key')) . '" />
					</td>
				</tr>
			</table>';
    submit_button();
    echo '</form></div>';
  }

  function settings()
  {
    add_settings_section('class_schedule_section', null, null, 'ucsc_gutenberg_blocks_settings_page');
    add_settings_field('class_schedule_department', 'Class Schedule Department', array($this, 'classScheduleHTML'), 'ucsc_gutenberg_blocks_settings_page', 'class_schedule_section');
    register_setting('class_schedule_settings', 'class_schedule_department', array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));

    add_settings_section('campus_directory_section', null, null, 'ucsc_gutenberg_blocks_settings_page');
    add_settings_field('campus_directory_department', 'Campus Directory Department', array($this, 'campusDirectoryHTML'), 'ucsc_gutenberg_blocks_settings_page', 'campus_directory_section');
    register_setting('campus_directory_settings', 'campus_directory_department', array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));

    add_settings_section('course_catalog_section', null, null, 'ucsc_gutenberg_blocks_settings_page');
    add_settings_field('course_catalog_subject', 'Course Catalog Subject', array($this, 'subjectHTML'), 'ucsc_gutenberg_blocks_settings_page', 'course_catalog_section');
    register_setting('course_catalog', 'course_catalog_subject', array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));

    // add_settings_section('tkp_first_section', null, null, 'ucsc_gutenberg_blocks_settings_page');
    // add_settings_field('tkp_omdb_api_key', 'OMDb API Key', array($this, 'apikeyHTML'), 'ucsc_gutenberg_blocks_settings_page', 'tkp_first_section');
    register_setting('ucsc_network_settings', 'ldap_api_key', array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
  }

  function subjectHTML()
  { ?>
    <input type="text" name="course_catalog_subject" value="<?php echo esc_attr(get_option('course_catalog_subject')) ?>" />
  <?php }

  function campusDirectoryHTML()
  { ?>
    <input type="text" name="campus_directory_department" value="<?php echo esc_attr(get_option('campus_directory_department')) ?>" />
  <?php }

  function classScheduleHTML()
  { ?>
    <input type="text" name="class_schedule_department" value="<?php echo esc_attr(get_option('class_schedule_department')) ?>" />
  <?php }

  function settingsLink()
  {
    add_options_page('UCSC Gutenberg Block Settings', 'UCSC Gutenberg Block Settings', 'manage_options', 'ucsc_gutenberg_blocks_settings_page', array($this, 'settingsPageHTML'));
  }

  function settingsPageHTML()
  { ?>
    <div class="wrap">
      <h1>UCSC Gutenberg Blocks Settings</h1>
      <form action="options.php" method="POST">
        <?php
        settings_fields('class_schedule_settings');
        settings_fields('campus_directory_settings');
        settings_fields('course_catalog');
        do_settings_sections('ucsc_gutenberg_blocks_settings_page');
        submit_button();
        ?>
      </form>
    </div>
<?php }
}
