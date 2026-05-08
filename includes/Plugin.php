<?php

namespace MosPress\PluginStarterPro;

defined('ABSPATH') || exit;
use MosPress\PluginStarterPro\Core\More;

class Plugin {
	public function __construct() {
		add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
		new More();
	}
	public function enqueue_scripts() {
		wp_enqueue_style('authguard-pro-style', AUTHGUARD_PRO_URL . 'assets/css/style.css', [], AUTHGUARD_VERSION);
		wp_enqueue_script('authguard-pro-script', AUTHGUARD_PRO_URL . 'assets/js/script.js', ['jquery'], AUTHGUARD_VERSION, true);		
	}
}
