<?php

class AccordionWrapper
{
	function __construct()
	{
		add_action('init', array($this, 'adminAssets'));

		add_action('wp_enqueue_scripts', array($this, 'register_plugin_styles'));

	}

	function adminAssets()
	{
		register_block_type(
			'ucscblocks/accordion-wrapper', array(
			'editor_script' => 'ucscblocks',
			'render_callback' => array($this, 'theHTML')
			)
		);
	}

	public function register_plugin_styles()
	{
		$file = '../src/components/Accordion/accordionwrapper.js';
		wp_register_script(
			'accordionwrapperjs',
			plugins_url($file, __FILE__),
			array(),
			filemtime(plugin_dir_path(__FILE__) . $file),
			true
		);
		wp_enqueue_script('accordionwrapperjs');
	}

	function theHTML($attributes, $content)
	{
		return '
			<div class="accordion-wrapper">
			  <a class="expand-collapse" id="expand" href="#/">
				Expand All
			  </a>
			'
		. $content .
		'</div>';
	}
}
