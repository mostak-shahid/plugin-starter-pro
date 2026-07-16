<?php

namespace MosPress\PluginStarterPro\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Fired during plugin deactivation
 *
 * @link       https://mostak-shahid.github.io/
 * @since      1.0.0
 *
 * @package    PluginStarterPro
 * @subpackage PluginStarterPro/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    PluginStarterPro
 * @subpackage PluginStarterPro/includes
 * @author     Md. Mostak Shahid <mostak.shahid@gmail.com>
 */
class Deactivator
{
    /**
     * Run on plugin deactivation.
     *
     * This function is called when the plugin is deactivated.
     * It handles cleanup of custom tables and options.
     */
    public static function deactivate() {
        flush_rewrite_rules();
    }
}



