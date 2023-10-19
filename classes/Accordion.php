<?php

class Accordion {

	function __construct() {
		add_action( 'init', array( $this, 'adminAssets' ) );
	}

	function adminAssets() {
		wp_register_style(
			'accordion',
			plugins_url( '../src/components/Accordion/editor.css', __FILE__ ),
			array(),
			filemtime( plugin_dir_path( __FILE__ ) . '../src/components/Accordion/editor.css' )
		);
		wp_enqueue_style( 'accordion' );

		register_block_type(
			'ucscblocks/accordion',
			array(
				'editor_script'   => 'ucscblocks',
				'render_callback' => array( $this, 'theHTML' ),
			)
		);
	}

	function theHTML( $attributes, $content ) {
		$open = ( $attributes['openOnPageLoad'] === true ) ? 'open' : '';
		return '<details class="ucsc-block-accordion"' . $open . '>
							<summary>' . $attributes['title'] . '</summary>'
		. $content .
		'</details>';
	}
}
