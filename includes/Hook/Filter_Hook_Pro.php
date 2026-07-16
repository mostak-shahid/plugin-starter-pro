<?php

namespace MosPress\PluginStarterPro\Hook;

if (! defined('ABSPATH')) exit;

use MosPress\PluginStarter\Hook\Filter_Hook;
use MosPress\PluginStarterPro\Helpers\Utils;

class Filter_Hook_Pro extends Filter_Hook
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
        $this->plugin_basename = plugin_basename(PLUGIN_STARTER_PRO_MAIN_FILE);
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
        add_filter('plugin_starter_default_options_details_modify', [ $this, 'pro_options_details' ]);
    }

    /**
     * Add Settings link + dynamic injected links
     */
    public function add_action_links($links)
    {

        $default_links = [
            '<a href="' . admin_url("admin.php?page=plugin-starter") . '">' .
                esc_html__('Settings', 'plugin-starter-pro') .
                '</a>',
            '<a href="https://mostak-shahid.github.io/plugins/plugin-starter.html" target="_blank">' .
                esc_html__('Docs', 'plugin-starter-pro') .
                '</a>',
            '<a href="https://www.facebook.com/mospressbd" target="_blank">' .
                esc_html__('Community', 'plugin-starter-pro') .
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
                ],
            ],
        ];
        return $options;
    }
    public function pro_options_details($options) {
        $options = [
            'more' => [
                'enable_scripts' => [
                    'title' => __('Enable Scripts', 'plugin-starter-pro'),
                    'intro' => __('Enable/Disable "Scripts" functionalities', 'plugin-starter-pro'),
                    'hint' => __('This is a hints for...', 'plugin-starter-pro'),
                    // 'before' => __('This is a before text for Text Input', 'plugin-starter-pro'),
                    // 'after' => __('This is a after text for Text Input', 'plugin-starter-pro'),
                    'url' => '/settings/more',
                ],
                'css' => [
                    'title' => __('CSS Editor', 'plugin-starter-pro'),
                    'intro' => __('Add any custom CSS code if necessary', 'plugin-starter-pro'),
                    'hint' => __('This is a hints for...', 'plugin-starter-pro'),
                    'url' => '/settings/more',
                ],
                'js' => [
                    'title' => __('JS Editor', 'plugin-starter-pro'),
                    'intro' => __('Add any custom JS code if necessary', 'plugin-starter-pro'),
                    'hint' => __('This is a hints for...', 'plugin-starter-pro'),
                    'url' => '/settings/more',
                ],
                'header_content' => [
                    'title' => __('Header Content', 'plugin-starter-pro'),
                    'intro' => __('Add any custom HTML code for <head> tag if necessary', 'plugin-starter-pro'),
                    'hint' => __('This is a hints for...', 'plugin-starter-pro'),
                    'url' => '/settings/more',
                ],
                'footer_content' => [
                    'title' => __('Footer Content', 'plugin-starter-pro'),
                    'intro' => __('Add any custom HTML code for <body> tag if necessary', 'plugin-starter-pro'),
                    'hint' => __('This is a hints for...', 'plugin-starter-pro'),
                    'url' => '/settings/more',
                ],
            ],
            'utilities' => [
                'tools' => [
                    'self_defense' => [
                        'title' => __('Self Defense', 'plugin-starter-pro'),
                        'intro' => __('Password requirement for Deactivation.', 'plugin-starter-pro'),
                        'hint' => __('This is a hints for...', 'plugin-starter-pro'),
                        'url' => '/settings/utilities/tools',
                    ],
                ],
            ],
        ];
        return $options;
    }
}
