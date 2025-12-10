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
function modify_plugin_starter_default_options( $plugin_starter_default_options ) {
    $plugin_starter_default_options['tools']['from_pro'] = '<p>Login Form From plugin-starter-pro</p>';
    return $plugin_starter_default_options;
}
add_filter( 'plugin_starter_default_options_modify', 'modify_plugin_starter_default_options' );