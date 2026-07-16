<?php

namespace MosPress\PluginStarterPro\Core;
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Fired during plugin activation
 *
 * @link       https://mostak-shahid.github.io/
 * @since      1.0.0
 *
 * @package    PluginStarterPro
 * @subpackage PluginStarterPro/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    PluginStarterPro
 * @subpackage PluginStarterPro/includes
 * @author     Md. Mostak Shahid <mostak.shahid@gmail.com>
 */
class Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		// $plugin_starter_options = plugin_starter_get_option();
		// update_option('plugin_starter_options', $plugin_starter_options);
		add_option('plugin_starter_do_activation_redirect', true);

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set activation flag for any one-time notices
        set_transient( 'plugin_starter_activation_notice', true, 30 );
	}
}



