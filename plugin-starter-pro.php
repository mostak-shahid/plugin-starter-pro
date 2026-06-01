<?php
/**
 * Plugin Name:         Plugin Starter Pro
 * Plugin URI:          https://mostak-shahid.github.io/plugin-starter-pro/
 * Description:         Plugin boilerplate for WordPress
 * Version:             1.0.0
 * Author:              Md. Mostak Shahid
 * Author URI:          https://mostak-shahid.github.io/
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         plugin-starter-pro
 * Domain Path:         /languages
 * Requires Plugins:    plugin-starter
 */
defined('ABSPATH') || exit;
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('PLUGIN_STARTER_PRO_VERSION', '1.0.0');
define('PLUGIN_STARTER_PRO_NAME', 'Plugin Starter Pro');
define('PLUGIN_STARTER_PRO_PATH', plugin_dir_path(__FILE__));
define('PLUGIN_STARTER_PRO_URL', plugin_dir_url(__FILE__));
define('PLUGIN_STARTER_PRO_MAIN_FILE', __FILE__);

/**
 * The core class that is used to define internationalization, 
 * caching, and others.
 */
if (file_exists(PLUGIN_STARTER_PRO_PATH . '/vendor/autoload.php')) {
    require_once PLUGIN_STARTER_PRO_PATH . '/vendor/autoload.php';
}

// 1. Inject Pro JavaScript into the Free Settings Screen Layout
add_action('admin_enqueue_scripts', function($hook) {
    // Only fire if the Free plugin menu screen is actively loading    
    if ($hook !== 'toplevel_page_plugin-starter') {
        return;
    }

    $asset_file = include(PLUGIN_STARTER_PRO_PATH . 'build/index-pro.asset.php');

    wp_enqueue_script(
        'plugin-starter-pro-features',
        PLUGIN_STARTER_PRO_URL . 'build/index-pro.js',
        $asset_file['dependencies'],
        $asset_file['version'],
        false // Load before main app if possible, or alongside
    );
});

// 2. Register the Custom WordPress REST API Endpoint
add_action('rest_api_init', function() {
    register_rest_route('plugin-starter-pro/v1', '/feedback', array(
        'methods'             => 'POST',
        'callback'            => 'plugin_starter_pro_handle_feedback',
        'permission_callback' => function() {
            // Secure route so only logged-in administrators can push data
            return current_user_can('manage_options');
        }
    ));
    register_rest_route('plugin-starter-pro/v1', '/news', array(
        'methods'             => 'GET',
        'callback'            => 'plugin_starter_pro_handle_news',
        'permission_callback' => function() {
            // Secure route so only logged-in administrators can push data
            return current_user_can('manage_options');
        }
    ));
});

// 3. Process the Contact Form Data
function plugin_starter_pro_handle_feedback($request) {
    $name    = sanitize_text_field($request->get_param('name'));
    $email   = sanitize_email($request->get_param('email'));
    $message = sanitize_textarea_field($request->get_param('message'));

    if (empty($name) || empty($email) || empty($message)) {
        return new WP_Error('missing_fields', 'All fields are mandatory.', array('status' => 400));
    }

    // Process the data (e.g., wp_mail to admin or saving to a database table)
    // For demonstration, we simulate success:
    return array(
        'success' => true,
        'message' => 'Data received securely by the pro processing engine!'
    );
}
function plugin_starter_pro_handle_news($request) {
    // Let's fetch news from this json endpoint for demonstration: https://raw.githubusercontent.com/mostak-shahid/update/refs/heads/master/plugin-news.json
    $response = wp_remote_get('https://raw.githubusercontent.com/mostak-shahid/update/refs/heads/master/plugin-news.json');
    if (is_wp_error($response)) {
        return new WP_Error('fetch_error', 'Unable to fetch news data.', array('status' => 500));
    }

    $body = wp_remote_retrieve_body($response);
    $news_items = json_decode($body, true);

    if (!is_array($news_items)) {
        return new WP_Error('parse_error', 'Unable to parse news data.', array('status' => 500));
    }

    return $news_items;
}
