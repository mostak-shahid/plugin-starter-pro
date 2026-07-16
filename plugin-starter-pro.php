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
/**
 * The code that runs during plugin activation.
 * This action is documented in src/Core/Activator.php
 */
function plugin_starter_pro_activate()
{
	\MosPress\PluginStarterPro\Core\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in src/Core/Deactivator.php
 */
function plugin_starter_pro_deactivate()
{
	\MosPress\PluginStarterPro\Core\Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'plugin_starter_pro_activate');
register_deactivation_hook(__FILE__, 'plugin_starter_pro_deactivate');


/**
 * Register WP-CLI commands only if file exists
 */
if ( defined( 'WP_CLI' ) && WP_CLI && file_exists( plugin_dir_path( __FILE__ ) . 'includes/CLI/CLI_Command.php' ) ) {
    $cli_file = plugin_dir_path( __FILE__ ) . 'includes/CLI/CLI_Command.php';

    if ( file_exists( $cli_file ) ) {
        WP_CLI::add_command( 'plugin-starter-pro', 'MosPress\PluginStarterPro\CLI\CLI_Command' );
    }
}


function plugin_starter_pro_run() {
    new \MosPress\PluginStarterPro\Plugin();
}
add_action('plugins_loaded', 'plugin_starter_pro_run');


add_action('admin_enqueue_scripts', function($hook) {
    // 1. Inject Pro JavaScript into the Free Settings Screen Layout
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

