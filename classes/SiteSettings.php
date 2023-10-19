<?php

require_once plugin_dir_path( __FILE__ ) . 'CampusDirectoryAPI.php';

class ucsc_services_block_class_site_settings {

	function __construct() {
		add_action( 'admin_menu', array( $this, 'settingsLink' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );
		add_action( 'network_admin_menu', array( $this, 'networkSettingsLink' ) );
		add_action( 'network_admin_edit_ucscplugin', array( $this, 'networkSaveSettings' ) );
		add_action( 'network_admin_notices', array( $this, 'networkSettingsNotifications' ) );
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'ucscserviceblocks/v1',
					'/departmentcode/',
					array(
						'methods'             => 'GET',
						'callback'            => array( $this, 'departmentcode' ),
						'permission_callback' => function () {
							return true;
						},
					)
				);
				register_rest_route(
					'ucscserviceblocks/v1',
					'/subjectcode/',
					array(
						'methods'             => 'GET',
						'callback'            => array( $this, 'subjectcode' ),
						'permission_callback' => function () {
							return true;
						},
					)
				);
				register_rest_route(
					'ucscserviceblocks/v1',
					'/cddepartmentcode/',
					array(
						'methods'             => 'GET',
						'callback'            => array( $this, 'cddepartmentcode' ),
						'permission_callback' => function () {
							return true;
						},
					)
				);
				register_rest_route(
					'ucscserviceblocks/v1',
					'/divisioncode/',
					array(
						'methods'             => 'GET',
						'callback'            => array( $this, 'divisioncode' ),
						'permission_callback' => function () {
							return true;
						},
					)
				);
			}
		);
	}

	function cddepartmentcode() {
		$retDepts = get_transient( 'ucsc_cddepartmentcode' );
		if ( ! $retDepts ) {
			$retDepts   = array();
			$retDepts[] = array(
				'label' => '---',
				'value' => '---',
			);

			$htmlString = file_get_contents( 'https://campusdirectory.ucsc.edu/' );

			$doc = new DOMDocument();
			@$doc->loadHTML( $htmlString );
			$dept_select = $doc->getElementById( 'ucscpersonpubdepartmentnumber' );

			$deptNodelist = $dept_select->childNodes;

			foreach ( $deptNodelist as $deptNode ) {
				$dept = trim( $deptNode->nodeValue );
				if ( strlen( $dept ) ) {
					$retDepts[] = array(
						'label' => $dept,
						'value' => $dept,
					);
				}
			}
			set_transient( 'ucsc_cddepartmentcode', $retDepts, WEEK_IN_SECONDS );
		}

		return new WP_REST_Response( $retDepts );
	}

	function divisioncode() {
		// $campusDirectoryAPI = new CampusDirectoryAPI(['automatedFeeds' => true]);
		// return new WP_REST_Response($campusDirectoryAPI->getDirDropdowns('ucscpersonpubdivision'));

		$ret   = array();
		$ret[] = array(
			'label' => '---',
			'value' => '---',
		);
		$ret[] = array(
			'label' => 'Academic Affairs',
			'value' => 'Academic Affairs',
		);
		$ret[] = array(
			'label' => 'Academic Personnel Office',
			'value' => 'Academic Personnel Office',
		);
		$ret[] = array(
			'label' => 'Arts Division',
			'value' => 'Arts Division',
		);
		$ret[] = array(
			'label' => 'Baskin School of Engineering',
			'value' => 'Baskin School of Engineering',
		);
		$ret[] = array(
			'label' => "Chancellor's Office/EVC",
			'value' => "Chancellor's Office/EVC",
		);
		$ret[] = array(
			'label' => 'Division of Finance, Operations and Administration',
			'value' => 'Division of Finance, Operations and Administration',
		);
		$ret[] = array(
			'label' => 'Division of Global Engagement',
			'value' => 'Division of Global Engagement',
		);
		$ret[] = array(
			'label' => 'Graduate Studies Division',
			'value' => 'Graduate Studies Division',
		);
		$ret[] = array(
			'label' => 'Humanities Division',
			'value' => 'Humanities Division',
		);
		$ret[] = array(
			'label' => 'Information Technology Services',
			'value' => 'Information Technology Services',
		);
		$ret[] = array(
			'label' => 'Library, University',
			'value' => 'Library, University',
		);
		$ret[] = array(
			'label' => 'Office of Research',
			'value' => 'Office of Research',
		);
		$ret[] = array(
			'label' => 'Physical & Biological Sciences Division',
			'value' => 'Physical & Biological Sciences Division',
		);
		$ret[] = array(
			'label' => 'Planning and Budget',
			'value' => 'Planning and Budget',
		);
		$ret[] = array(
			'label' => 'Silicon Valley Initiatives',
			'value' => 'Silicon Valley Initiatives',
		);
		$ret[] = array(
			'label' => 'Social Sciences Division',
			'value' => 'Social Sciences Division',
		);
		$ret[] = array(
			'label' => 'Staff Human Resources',
			'value' => 'Staff Human Resources',
		);
		$ret[] = array(
			'label' => 'Student Affairs and Success',
			'value' => 'Student Affairs and Success',
		);
		$ret[] = array(
			'label' => 'Student Services',
			'value' => 'Student Services',
		);
		$ret[] = array(
			'label' => 'Undergraduate Education',
			'value' => 'Undergraduate Education',
		);
		$ret[] = array(
			'label' => 'University Extension',
			'value' => 'University Extension',
		);
		$ret[] = array(
			'label' => 'University Relations',
			'value' => 'University Relations',
		);
		$ret[] = array(
			'label' => 'Visiting Faculty/Staff',
			'value' => 'Visiting Faculty/Staff',
		);

		return new WP_REST_Response( $ret );
	}

	function departmentcode() {

		$retDepts = get_transient( 'ucsc_depts' );
		if ( ! $retDepts ) {
			$curl = curl_init();

			curl_setopt_array(
				$curl,
				array(
					CURLOPT_URL            => 'https://my.ucsc.edu/PSIGW/RESTListeningConnector/PSFT_CSPRD/SCX_CLASS_DEPTS_V2.v2/' . date( 'Y', strtotime( '-7 months' ) ) . '/Fall',
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING       => '',
					CURLOPT_MAXREDIRS      => 10,
					CURLOPT_TIMEOUT        => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST  => 'GET',
				)
			);

			$response = curl_exec( $curl );

			curl_close( $curl );
			$arrResponse = json_decode( $response, true );
			$depts       = $arrResponse['depts'];
			$retDepts    = array();
			for ( $i = 0; $i < count( $depts ); $i++ ) {
					$retDepts[] = array(
						'label' => $depts[ $i ]['description'],
						'value' => $depts[ $i ]['code'],
					);
			}

			function cmp( $a, $b ) {
				return strcmp( $a['label'], $b['label'] );
			}

			usort( $retDepts, 'cmp' );

			array_unshift(
				$retDepts,
				array(
					'label' => '---',
					'value' => '---',
				)
			);

			set_transient( 'ucsc_depts', $retDepts, WEEK_IN_SECONDS );
		}
		return new WP_REST_Response( $retDepts );
	}

	function subjectcode() {

		$retDepts = get_transient( 'ucsc_subjects' );
		if ( ! $retDepts ) {
			$curl = curl_init();

			curl_setopt_array(
				$curl,
				array(
					CURLOPT_URL            => 'https://my.ucsc.edu/PSIGW/RESTListeningConnector/PSFT_CSPRD/SCX_CLASS_DEPTS_V2.v2/' . date( 'Y', strtotime( '-7 months' ) ) . '/Fall',
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING       => '',
					CURLOPT_MAXREDIRS      => 10,
					CURLOPT_TIMEOUT        => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST  => 'GET',
				)
			);

			$response = curl_exec( $curl );

			curl_close( $curl );
			$arrResponse = json_decode( $response, true );
			$depts       = $arrResponse['depts'];
			$retDepts    = array();
			for ( $i = 0; $i < count( $depts ); $i++ ) {
				if ( array_key_exists( 'subjects', $depts[ $i ] ) ) {
					for ( $j = 0; $j < count( $depts[ $i ]['subjects'] ); $j++ ) {
						$retDepts[] = array(
							'label' => $depts[ $i ]['subjects'][ $j ]['description'],
							'value' => $depts[ $i ]['subjects'][ $j ]['code'],
						);
					}
				}
			}

			function cmp( $a, $b ) {
				return strcmp( $a['label'], $b['label'] );
			}

			usort( $retDepts, 'cmp' );

			array_unshift(
				$retDepts,
				array(
					'label' => '---',
					'value' => '---',
				)
			);

			set_transient( 'ucsc_subjects', $retDepts, WEEK_IN_SECONDS );
		}
		return new WP_REST_Response( $retDepts );
	}

	function networkSettingsNotifications() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'ucsc-service-blocks-network-settings' && isset( $_GET['updated'] ) ) {
			echo '<div id="message" class="updated notice is-dismissible"><p>Settings updated.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
		}
	}

	function networkSaveSettings() {
		check_admin_referer( 'ucscplugin-validate' ); // Nonce security check

		update_site_option( 'ldap_api_key', $_POST['ldap_api_key'] );
		update_site_option( 'ldap_cn', $_POST['ldap_cn'] );

		wp_redirect(
			add_query_arg(
				array(
					'page'    => 'ucsc-service-blocks-network-settings',
					'updated' => true,
				),
				network_admin_url( 'settings.php' )
			)
		);

		exit;
	}

	function networkSettingsLink() {
		add_submenu_page(
			'settings.php', // Parent element
			'UCSC Service Blocks Network Settings', // Text in browser title bar
			'UCSC Service Blocks Network Settings', // Text to be displayed in the menu.
			'manage_options', // Capability
			'ucsc-service-blocks-network-settings', // Page slug, will be displayed in URL
			array( $this, 'networkSettingsPage' ) // Callback function which displays the page
		);
	}

	function networkSettingsPage() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/ucsc-service-blocks/index.php' );
		echo '<div class="wrap">
	<h1>UCSC Service Blocks Network Settings</h1>
	<h3>Version: ' . $plugin_data['Version'] . '</h3>
	<form method="post" action="edit.php?action=ucscplugin">';
		wp_nonce_field( 'ucscplugin-validate' );
		echo '
			<table class="form-table">
				<tr>
					<th scope="row"><label for="ldap_api_key">LDAP API Key</label></th>
					<td>
						<input name="ldap_api_key" class="regular-text" type="text" id="ldap_api_key" value="' . esc_attr( get_site_option( 'ldap_api_key' ) ) . '" />
					</td>
		</tr>
		<tr>
		  <th scope="row"><label for="ldap_cn">LDAP CN</label></th>
		  <td>
						<input name="ldap_cn" class="regular-text" type="text" id="ldap_cn" value="' . esc_attr( get_site_option( 'ldap_cn' ) ) . '" />
					</td>
				</tr>
			</table>';
		submit_button();
		echo '</form></div>';
	}

	function settings() {
		add_settings_section( 'ucsc_service_blocks_section', null, null, 'ucsc_service_blocks_settings_page' );

		add_settings_field( 'ldap_api_key', 'LDAP API Key', array( $this, 'ldapKeyHTML' ), 'ucsc_service_blocks_settings_page', 'ucsc_service_blocks_section' );
		add_settings_field( 'ldap_cn', 'LDAP CN', array( $this, 'ldapCN' ), 'ucsc_service_blocks_settings_page', 'ucsc_service_blocks_section' );

		register_setting(
			'ucsc_service_blocks',
			'ldap_api_key',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);
		register_setting(
			'ucsc_service_blocks',
			'ldap_cn',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		register_setting(
			'ucsc_network_settings',
			'ldap_api_key',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);
		register_setting(
			'ucsc_network_settings',
			'ldap_cn',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);
	}

	function ldapKeyHTML() {
		?>
	<input type="text" name="ldap_api_key" value="<?php echo esc_attr( get_option( 'ldap_api_key' ) ); ?>" />
		<?php
	}

	function ldapCN() {
		?>
	<input type="text" name="ldap_cn" value="<?php echo esc_attr( get_option( 'ldap_cn' ) ); ?>" />
		<?php
	}

	function settingsLink() {
		add_options_page( 'UCSC Service Blocks', 'UCSC Service Blocks', 'manage_options', 'ucsc-service-blocks-plugin-settings', array( $this, 'settingsPageHTML' ) );
	}

	function settingsPageHTML() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/ucsc-service-blocks/index.php' );
		?>
	<div class="wrap">
		<h1>UCSC Service Blocks Settings</h1>
		<h3>Version: <?php echo $plugin_data['Version']; ?></h3>
		<form action="options.php" method="POST">
		<?php
		settings_fields( 'ucsc_service_blocks' );
		do_settings_sections( 'ucsc_service_blocks_settings_page' );
		submit_button();
		?>
		</form>
	</div>
		<?php
	}
}
