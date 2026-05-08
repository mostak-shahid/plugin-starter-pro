<?php

namespace MosPress\PluginStarterPro\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

class More
{
	protected $options;

	public function __construct()
	{
		$this->options = authguard_get_option();
		if (isset($this->options['more']['enable_scripts']) && $this->options['more']['enable_scripts'] == 1) {
			add_action('wp_head', [$this, 'add_header_script'], 9999);
			add_action('wp_footer', [$this, 'add_footer_script'], 9999);
			add_action('wp_enqueue_scripts', [$this, 'add_inline_assets']);
		}
	}

	public function add_header_script()
	{
		echo wp_kses_post($this->options['more']['header_content']) ?? '';
	}

	public function add_footer_script()
	{
		echo wp_kses_post($this->options['more']['footer_content']) ?? '';
	}

	public function add_inline_assets()
	{
		if (isset($this->options['more']['css']) && !empty($this->options['more']['css'])) {
			// wp_register_style( 'authguardpro-dummy', false );
			// wp_enqueue_style( 'authguardpro-dummy' );
			wp_add_inline_style('authguard-pro-style', wp_kses_post($this->options['more']['css']));
		}

		if (isset($this->options['more']['js']) && !empty($this->options['more']['js'])) {
			// wp_register_script( 'authguardpro-dummy', false, array(), '', true );
			// wp_enqueue_script( 'authguardpro-dummy' );
			wp_add_inline_script('authguard-pro-script', wp_kses_post($this->options['more']['js']));
		}
	}
}
