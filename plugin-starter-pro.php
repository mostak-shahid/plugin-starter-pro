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
define('PLUGIN_STARTER_PRO_NAME', 'Plugin Starter');
define('PLUGIN_STARTER_PRO_PATH', plugin_dir_path(__FILE__));
define('PLUGIN_STARTER_PRO_URL', plugin_dir_url(__FILE__));
define('PLUGIN_STARTER_PRO_MAIN_FILE', __FILE__);

require_once PLUGIN_STARTER_PRO_PATH . '/vendor/autoload.php';
require_once PLUGIN_STARTER_PRO_PATH . '../plugin-starter/plugin-starter-functions.php';

function modify_plugin_starter_default_options( $plugin_starter_default_options ) {
    $plugin_starter_default_options['tools']['from_pro'] = '<p>Login Form From plugin-starter-pro</p>';
    return $plugin_starter_default_options;
}
add_filter( 'plugin_starter_default_options_modify', 'modify_plugin_starter_default_options' );

// use MosPress\PluginStarterPro\Plugin;

// // Plugin::get_instance();
// new Plugin();