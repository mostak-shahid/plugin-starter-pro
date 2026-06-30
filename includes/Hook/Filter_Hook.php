<?php

namespace MosPress\PluginStarterPro\Hook;

if (! defined('ABSPATH')) exit;

use MosPress\PluginStarterPro\Helpers\Utils;

class Filter_Hook
{

    private $plugin_slug;      // plugin-starter
    private $plugin_basename;  // plugin-starter/plugin-starter.php
    private static $instance = null;

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {

        // Automatically detect plugin slug + basename
        $this->plugin_basename = plugin_basename(PLUGIN_STARTER_MAIN_FILE);
        $this->plugin_slug     = dirname($this->plugin_basename);

        /**
         * Now supports:
         * plugin_action_links_plugin-starter/plugin-starter.php
         * WITHOUT hard-coding strings.
         */
        add_filter(
            "plugin_action_links_{$this->plugin_basename}",
            [$this, 'add_action_links']
        );
        add_filter('plugin_starter_default_options_modify', [$this, 'pro_options']);
    }

    /**
     * Add Settings link + dynamic injected links
     */
    public function add_action_links($links)
    {

        $default_links = [
            '<a href="' . admin_url("admin.php?page={$this->plugin_slug}") . '">' .
                esc_html__('Settings', 'plugin-starter') .
                '</a>',
            '<a href="https://mostak-shahid.github.io/plugins/plugin-starter.html" target="_blank">' .
                esc_html__('Docs', 'plugin-starter') .
                '</a>',
            '<a href="https://www.facebook.com/mospressbd" target="_blank">' .
                esc_html__('Community', 'plugin-starter') .
                '</a>',
        ];

        /**
         * Dynamic links injected from PRO plugin or remote MF
         * Example:
         * add_filter( 'plugin_starter_action_links_extra', function($links) {
         *     $links[] = '<a href="https://example.com/pro">Go Pro</a>';
         *     return $links;
         * });
         */
        $extra_links = apply_filters('plugin_starter_action_links_extra', []);

        return array_merge($default_links, $extra_links, $links);
    }
    public function pro_options($options)
    {
        $options = [
            'inputs' => [
                'props_passing' => [
                    'switch' => 1,
                ],
                'bridge' => [
                    'switch' => 1,
                ],

            ],
            'more' => [
                'enable_scripts' => false,
                'css' => '/* CSS Code Here */',
                'js' => '// JavaScript Code Here',
                'header_content' => '<!-- Content inside HEAD tag -->',
                'footer_content' => '<!-- Content inside BODY tag -->',
            ],
            'utilities' => [
                'tools' => [
                    'self_defense' => false, // delete, uninstall, none
                    'delete_data_on' => 'none', // delete, uninstall, none
                ],
            ],
        ];
        return $options;
    }
}
