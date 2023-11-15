<?php
class Ucsc_Services_Blocks_Content_Sharer {

	function __construct() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'ucscserviceblocks/v1',
					'/sites/',
					array(
						'methods'             => 'GET',
						'callback'            => array( $this, 'get_sites' ),
						'permission_callback' => array( $this, 'restPermissionsCheck' ),
					)
				);
				register_rest_route(
					'ucscserviceblocks/v1',
					'/posttypes',
					array(
						'methods'             => 'GET',
						'callback'            => array( $this, 'get_posttypes' ),
						'permission_callback' => array( $this, 'restPermissionsCheck' ),
					)
				);
				register_rest_route(
					'ucscserviceblocks/v1',
					'/post',
					array(
						'methods'             => 'GET',
						'callback'            => array( $this, 'get_post' ),
						'permission_callback' => array( $this, 'restPermissionsCheck' ),
					)
				);
			}
		);
		add_action( 'init', array( $this, 'renderFrontend' ) );
	}

	function restPermissionsCheck() {
		// Restrict endpoint to only users who have the edit_posts capability.
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error( 'rest_forbidden', esc_html__( 'You can not view private data.', 'my-text-domain' ), array( 'status' => 401 ) );
		}

		return true;
	}

	function renderFrontend() {
		register_block_type(
			'ucscservice/contentsharer',
			array(
				'editor_script'   => 'ucscblocks',
				'render_callback' => array( $this, 'theHTML' ),
			)
		);
	}

	function theHTML( $attributes ) {
		switch_to_blog( $attributes['siteid'] );
		$posts = get_posts(
			array(
				'post_type'   => $attributes['postType'],
				'post_status' => 'publish',
			)
		);
		restore_current_blog();

		ob_start();

		echo "<hr><p>STARTING Content From Site ID: {$attributes['siteid']}</p><hr>";
		foreach ( $posts as $post ) {
			echo '<h2>' . $post->post_title . '</h2>';
			$content = apply_filters( 'the_content', $post->post_content );
			$blocks  = parse_blocks( $content );
			foreach ( $blocks as $block ) {
				echo render_block( $block );
				break;
			}
			echo "<hr><p>ENDING Content From Site ID: {$attributes['siteid']}</p><hr>";
		}
		$output = ob_get_contents(); // collect output
		ob_end_clean(); // Turn off ouput buffer

		return $output;
	}

	function get_post( $request ) {
		$parameters = $request->get_query_params();

		$posttype = isset( $parameters['posttype'] ) ? $parameters['posttype'] : false;
		if ( ! $posttype ) {
			return 'Set Post Type';
		}

		$siteid = isset( $parameters['siteid'] ) ? $parameters['siteid'] : false;
		if ( ! $siteid ) {
			return 'Set Site ID';
		}

		switch_to_blog( $siteid );
		$posts = get_posts(
			array(
				'post_type'   => $posttype,
				'post_status' => 'publish',
			)
		);
		restore_current_blog();

		return $posts;
	}

	function get_posttypes( $request ) {
		$parameters = $request->get_query_params();
		$siteid     = isset( $parameters['siteid'] ) ? (int) $parameters['siteid'] : false;

		if ( ! $siteid ) {
			return 'Set Site ID';
		}

		switch_to_blog( $siteid );
		$posttypes = get_post_types();
		restore_current_blog();

		return $posttypes;
	}

	function get_sites() {
		return get_sites();
	}
}
