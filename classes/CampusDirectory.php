<?php


include_once plugin_dir_path( __FILE__ ) . 'CampusDirectoryAPI.php';

class Ucsc_Services_Blocks_Campus_Directory {

	function __construct() {
		add_action( 'init', array( $this, 'renderFrontend' ) );
		add_filter(
			'query_vars',
			function ( $query_vars ) {
				$query_vars[] = 'directoryprofilecruzid';
				return $query_vars;
			}
		);

		add_action(
			'template_include',
			function ( $template ) {
				if ( get_query_var( 'directoryprofilecruzid' ) == false || get_query_var( 'directoryprofilecruzid' ) == '' ) {
					return $template;
				}

				return plugin_dir_path( __FILE__ ) . '../templates/DirectoryProfileTemplate.php';
			}
		);
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'ucscserviceblocks/v1',
					'/campusdirectoryrequirements/',
					array(
						'methods'             => 'GET',
						'callback'            => array( $this, 'requirements' ),
						'permission_callback' => function () {
							return true;
						},
					)
				);
			}
		);
	}
	function requirements() {
		$resp          = array();
		$ldap_password = get_site_option( 'ldap_api_key' );
		if ( ! $ldap_password ) {
			$ldap_password = get_option( 'ldap_api_key' );
		}
		$resp['ldap_pass'] = strlen( $ldap_password ) > 0;
		$resp['multisite'] = is_multisite();

		return new WP_REST_Response( $resp );
	}
	public function register_plugin_styles() {
		wp_register_style(
			'directoryprofile',
			plugins_url( '../src/components/CampusDirectory/directoryprofile.css', __FILE__ ),
			array(),
			filemtime( plugin_dir_path( __FILE__ ) . '../src/components/CampusDirectory/directoryprofile.css' )
		);
		wp_enqueue_style( 'directoryprofile' );

		$file = '../src/components/CampusDirectory/campusdirectory.css';
		wp_register_style(
			'campusdirectory',
			plugins_url( $file, __FILE__ ),
			array(),
			filemtime( plugin_dir_path( __FILE__ ) . $file )
		);
		wp_enqueue_style( 'campusdirectory' );
	}

	function renderFrontend() {
		wp_register_style(
			'ucscblocks-editor',
			plugins_url( '../src/components/CampusDirectory/editor.css', __FILE__ ),
			array( 'wp-edit-blocks' ),
			filemtime( plugin_dir_path( __FILE__ ) . '../src/components/CampusDirectory/editor.css' )
		);
		register_block_type(
			'ucscservice/campusdirectory',
			array(
				'editor_script'   => 'ucscblocks',
				'editor_style'    => 'ucscblocks-editor',
				'render_callback' => array( $this, 'theHTML' ),
			)
		);
		wp_register_style(
			'directoryprofile',
			plugins_url( '../src/components/CampusDirectory/directoryprofile.css', __FILE__ ),
			array(),
			filemtime( plugin_dir_path( __FILE__ ) . '../src/components/CampusDirectory/directoryprofile.css' )
		);
	}

	function theHTML( $attributes ) {
		$path                              = plugin_dir_path( __FILE__ );
		$attributes['objFacultyTypes']     = json_decode( $attributes['strFacultyTypes'], true );
		$attributes['objStaffTypes']       = json_decode( $attributes['strStaffTypes'], true );
		$attributes['objGradTypes']        = json_decode( $attributes['strGradTypes'], true );
		$attributes['objInformationTypes'] = json_decode( $attributes['strInformationTypes'], true );
		if ( $attributes['strInformationTypesTable'] != null ) {
			$attributes['objInformationTypesTable'] = json_decode( $attributes['strInformationTypesTable'], true );
		}
		$campusDirectoryAPI = new Ucsc_Services_Blocks_Campus_Directory_api( $attributes );
		$items              = $campusDirectoryAPI->Ucsc_Service_Blocks_Set_Directory_Data();
		ob_start();
		include plugin_dir_path( __FILE__ ) . '../templates/CampusDirectoryTemplate.php';

		$output = ob_get_contents(); // collect output
		ob_end_clean(); // Turn off output buffer

		return $output;
	}
}
