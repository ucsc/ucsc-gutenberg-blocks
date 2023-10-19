<?php

/**
 * Doc: a doc
 */
class UCSCServiceDemoBlock2 {

	function __construct() {
		add_action( 'init', array( $this, 'adminAssets' ) );
	}

	function adminAssets() {
		register_block_type(
			'ucsc/block2',
			array(
				'editor_script'   => 'ucscblocks',
				'render_callback' => array( $this, 'theHTML' ),
			)
		);
	}

	function theHTML( $attributes ) {
		return '<p>Today, in block 2, the sky is ' . $attributes['skyColor'] . '  and the grass is ' . $attributes['grassColor'] . '!!!</p>';
	}
}
