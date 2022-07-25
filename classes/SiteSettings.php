<?php

include_once(plugin_dir_path(__FILE__) . 'CampusDirectoryAPI.php');

class SiteSettings
{
  function __construct()
  {
    add_action('admin_menu', array($this, 'settingsLink'));
    add_action('admin_init', array($this, 'settings'));
    add_action("network_admin_menu", array($this, 'networkSettingsLink'));
    add_action('network_admin_edit_ucscplugin', array($this, 'networkSaveSettings'));
    add_action('network_admin_notices', array($this, 'networkSettingsNotifications'));
    add_action('rest_api_init', function () {
      register_rest_route('ucscgutenbergblocks/v1', '/departmentcode/', array(
        'methods' => 'GET',
        'callback' => array($this, 'departmentcode')
      ));
      register_rest_route('ucscgutenbergblocks/v1', '/cddepartmentcode/', array(
        'methods' => 'GET',
        'callback' => array($this, 'cddepartmentcode')
      ));
      register_rest_route('ucscgutenbergblocks/v1', '/divisioncode/', array(
        'methods' => 'GET',
        'callback' => array($this, 'divisioncode')
      ));
    });
  }

  function cddepartmentcode()
  {
    $campusDirectoryAPI = new CampusDirectoryAPI(['automatedFeeds' => true]);
    return new WP_REST_Response($campusDirectoryAPI->getDirDropdowns('departmentnumber'));
  }

  function divisioncode()
  {
    $campusDirectoryAPI = new CampusDirectoryAPI(['automatedFeeds' => true]);
    return new WP_REST_Response($campusDirectoryAPI->getDirDropdowns('ucscpersonpubdivision'));
  }

  function departmentcode()
  {

    $retDepts = get_transient('ucsc_departments');
    if (!$retDepts) {
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://my.ucsc.edu/PSIGW/RESTListeningConnector/PSFT_CSPRD/SCX_CLASS_DEPTS.v1/2022/Fall',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
      ));

      $response = curl_exec($curl);

      curl_close($curl);
      $arrResponse = json_decode($response, true);
      $depts = $arrResponse['depts'];
      $retDepts = [];
      $retDepts[] = [
        'label' => '---',
        'value' => '---'
      ];
      for($i=0; $i<count($depts); $i++) {
        $retDepts[] = [
          'label' => $depts[$i]['description'],
          'value' => $depts[$i]['code']
        ];
      }
      set_transient('ucsc_departments', $retDepts, WEEK_IN_SECONDS);
    }
    return new WP_REST_Response($retDepts);

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
    add_settings_section('ucsc_gutenberg_blocks_section', null, null, 'ucsc_gutenberg_blocks_settings_page');

    add_settings_field('ldap_api_key', 'LDAP API Key', array($this, 'ldapKeyHTML'), 'ucsc_gutenberg_blocks_settings_page', 'ucsc_gutenberg_blocks_section');
    register_setting('ucsc_gutenberg_blocks', 'ldap_api_key', array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));

    register_setting('ucsc_network_settings', 'ldap_api_key', array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
  }

  function ldapKeyHTML()
  { ?>
    <input type="text" name="ldap_api_key" value="<?php echo esc_attr(get_option('ldap_api_key')) ?>" />
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
        settings_fields('ucsc_gutenberg_blocks');
        do_settings_sections('ucsc_gutenberg_blocks_settings_page');
        submit_button();
        ?>
      </form>
    </div>
<?php }
}
