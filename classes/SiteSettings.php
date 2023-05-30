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
        'callback' => array($this, 'departmentcode'),
        'permission_callback' => function() {return true;}
      ));
      register_rest_route('ucscgutenbergblocks/v1', '/subjectcode/', array(
        'methods' => 'GET',
        'callback' => array($this, 'subjectcode'),
        'permission_callback' => function() {return true;}
      ));
      register_rest_route('ucscgutenbergblocks/v1', '/cddepartmentcode/', array(
        'methods' => 'GET',
        'callback' => array($this, 'cddepartmentcode'),
        'permission_callback' => function() {return true;}
      ));
      register_rest_route('ucscgutenbergblocks/v1', '/divisioncode/', array(
        'methods' => 'GET',
        'callback' => array($this, 'divisioncode'),
        'permission_callback' => function() {return true;}
      ));
    });
  }

  function cddepartmentcode()
  {
    $retDepts = get_transient('ucsc_cddepartmentcode');
    if (!$retDepts) {
      $retDepts = [];
      $retDepts[] = [
        "label" => "---",
        "value" => "---"
      ];

      $htmlString = file_get_contents('https://campusdirectory.ucsc.edu/');

      $doc = new DOMDocument();
      @$doc->loadHTML($htmlString);
      $dept_select = $doc->getElementById('ucscpersonpubdepartmentnumber');

      $deptNodelist = $dept_select->childNodes;


      foreach($deptNodelist as $deptNode) {
        $dept = trim($deptNode->nodeValue);
        if (strlen($dept)) {
          $retDepts[] = [
            "label" => $dept,
            "value" => $dept
          ];
        }
      }
      set_transient('ucsc_cddepartmentcode', $retDepts, WEEK_IN_SECONDS);
    }

    return new WP_REST_Response($retDepts);
  }

  function divisioncode()
  {
    // $campusDirectoryAPI = new CampusDirectoryAPI(['automatedFeeds' => true]);
    // return new WP_REST_Response($campusDirectoryAPI->getDirDropdowns('ucscpersonpubdivision'));

    $ret = [];
    $ret[] = [
      "label" => "---",
      "value" => "---"
    ];
    $ret[] = [
      "label" => "Academic Affairs",
      "value" => "Academic Affairs"
    ];
    $ret[] = [
      "label" => "Academic Personnel Office",
      "value" => "Academic Personnel Office"
    ];
    $ret[] = [
      "label" => "Arts Division",
      "value" => "Arts Division"
    ];
    $ret[] = [
      "label" => "Baskin School of Engineering",
      "value" => "Baskin School of Engineering"
    ];
    $ret[] = [
      "label" => "Chancellor's Office/EVC",
      "value" => "Chancellor's Office/EVC"
    ];
    $ret[] = [
      "label" => "Division of Finance, Operations and Administration",
      "value" => "Division of Finance, Operations and Administration"
    ];
    $ret[] = [
      "label" => "Division of Global Engagement",
      "value" => "Division of Global Engagement"
    ];
    $ret[] = [
      "label" => "Graduate Studies Division",
      "value" => "Graduate Studies Division"
    ];
    $ret[] = [
      "label" => "Humanities Division",
      "value" => "Humanities Division"
    ];
    $ret[] = [
      "label" => "Information Technology Services",
      "value" => "Information Technology Services"
    ];
    $ret[] = [
      "label" => "Library, University",
      "value" => "Library, University"
    ];
    $ret[] = [
      "label" => "Office of Research",
      "value" => "Office of Research"
    ];
    $ret[] = [
      "label" => "Physical & Biological Sciences Division",
      "value" => "Physical & Biological Sciences Division"
    ];
    $ret[] = [
      "label" => "Planning and Budget",
      "value" => "Planning and Budget"
    ];
    $ret[] = [
      "label" => "Silicon Valley Initiatives",
      "value" => "Silicon Valley Initiatives"
    ];
    $ret[] = [
      "label" => "Social Sciences Division",
      "value" => "Social Sciences Division"
    ];
    $ret[] = [
      "label" => "Staff Human Resources",
      "value" => "Staff Human Resources"
    ];
    $ret[] = [
      "label" => "Student Affairs and Success",
      "value" => "Student Affairs and Success"
    ];
    $ret[] = [
      "label" => "Student Services",
      "value" => "Student Services"
    ];
    $ret[] = [
      "label" => "Undergraduate Education",
      "value" => "Undergraduate Education"
    ];
    $ret[] = [
      "label" => "University Extension",
      "value" => "University Extension"
    ];
    $ret[] = [
      "label" => "University Relations",
      "value" => "University Relations"
    ];
    $ret[] = [
      "label" => "Visiting Faculty/Staff",
      "value" => "Visiting Faculty/Staff"
    ];

    return new WP_REST_Response($ret);

  }

  function departmentcode()
  {

    $retDepts = get_transient('ucsc_depts');
    if (!$retDepts) {
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://my.ucsc.edu/PSIGW/RESTListeningConnector/PSFT_CSPRD/SCX_CLASS_DEPTS_V2.v2/'. date("Y", strtotime("-7 months")) .'/Fall',
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
      for($i=0; $i<count($depts); $i++) {
        $retDepts[] = [
          'label' => $depts[$i]['description'],
          'value' => $depts[$i]['code']
        ];
      }

      function cmp($a, $b) {
        return strcmp($a['label'], $b['label']);
      }

      usort($retDepts, "cmp");

      array_unshift($retDepts, [
        'label' => '---',
        'value' => '---'
      ]);

      set_transient('ucsc_depts', $retDepts, WEEK_IN_SECONDS);
    }
    return new WP_REST_Response($retDepts);

  }

  function subjectcode()
  {

    $retDepts = get_transient('ucsc_subjects');
    if (!$retDepts) {
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://my.ucsc.edu/PSIGW/RESTListeningConnector/PSFT_CSPRD/SCX_CLASS_DEPTS_V2.v2/'. date("Y", strtotime("-7 months")) .'/Fall',
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
      for($i=0; $i<count($depts); $i++) {
        if (array_key_exists('subjects', $depts[$i])) {
          for($j=0; $j<count($depts[$i]['subjects']); $j++) {
            $retDepts[] = [
              'label' => $depts[$i]['subjects'][$j]['description'],
              'value' => $depts[$i]['subjects'][$j]['code']
            ];
          }
        }
      }

      function cmp($a, $b) {
        return strcmp($a['label'], $b['label']);
      }

      usort($retDepts, "cmp");

      array_unshift($retDepts, [
        'label' => '---',
        'value' => '---'
      ]);

      set_transient('ucsc_subjects', $retDepts, WEEK_IN_SECONDS);
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
    if( ! function_exists('get_plugin_data') ){
      require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/ucsc-gutenberg-blocks/index.php');
    echo '<div class="wrap">
    <h1>UCSC Gutenberg Blocks Network Settings</h1>
    <h3>Version: ' . $plugin_data['Version'] . '</h3>
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
  {
    if( ! function_exists('get_plugin_data') ){
      require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/ucsc-gutenberg-blocks/index.php');
    ?>
    <div class="wrap">
      <h1>UCSC Gutenberg Blocks Settings</h1>
      <h3>Version: <?php echo $plugin_data['Version']; ?></h3>
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
