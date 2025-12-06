<?php
/**
 * Plugin Name: Plugin Starter Pro
 */
function modify_plugin_starter_default_options( $plugin_starter_default_options ) {
    $plugin_starter_default_options['tools']['from_pro'] = '<p>Login Form From plugin-starter-pro</p>';
    return $plugin_starter_default_options;
}
add_filter( 'plugin_starter_default_options_modify', 'modify_plugin_starter_default_options' );