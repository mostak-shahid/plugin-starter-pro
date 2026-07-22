<?php

namespace MosPress\PluginStarterPro\API;

if (! defined('ABSPATH')) exit;

use MosPress\PluginStarter\API\LogsController;
use MosPress\PluginStarter\API\Rest_API;
use MosPress\PluginStarter\Helpers\Utils;
use MosPress\PluginStarter\Helpers\CryptoHelper;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use Plugin_Upgrader;
use WP_Ajax_Upgrader_Skin;
use WP_REST_Server;
use WP_REST_Controller;

// Ensure WordPress core file API is loaded when needed
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/theme.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

/**
 * Rest API Router
 *
 * Registers all REST API endpoints and routes them to appropriate controllers
 */
class Rest_API_Pro extends Rest_API
{
    private const NAMESPACE = 'plugin-starter-pro/v1';
    private static $instance = null;
    /**
     * Table name
     *
     * @var string
     */
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'rest_api_init']);
    }
    public function rest_api_init()
    {
        $this->register_options_endpoints();
        $this->register_plugins_endpoints();
        $this->register_feedback_endpoints();
        $this->register_news_endpoints();
        $this->register_necessary_endpoints();
    }

    /**
     * Register options endpoints
     */
    private function register_options_endpoints()
    {
        register_rest_route( self::NAMESPACE, '/options', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'update_settings'],
            // 'permission_callback' => '__return_true'
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ]);
    }

    /**
     * Register plugins endpoints
     */
    private function register_plugins_endpoints()
    {
        register_rest_route( self::NAMESPACE, '/plugins', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => function () {
                $response = wp_remote_get('https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[author]=mostakshahid&request[per_page]=24');
                if (is_wp_error($response)) {
                    return new WP_Error('api_error', 'Failed to fetch plugins', ['status' => 500]);
                }
                return json_decode(wp_remote_retrieve_body($response), true);
            },
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ]);

        // Route for installing a plugin
        register_rest_route(self::NAMESPACE, '/plugin/install', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'install_plugin'],
            'permission_callback' => function () {
                return current_user_can('activate_plugins') && current_user_can('install_plugins') && current_user_can('delete_plugins');
            },
            'args'                => [
                'source' => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'description'       => 'URL to the zip file or WordPress repo slug',
                ],
            ],
        ]);

        // Route for toggle actions: activate, deactivate, delete
        register_rest_route(self::NAMESPACE, '/plugin/action', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'handle_plugin_action'],
            'permission_callback' => function () {
                return current_user_can('activate_plugins') && current_user_can('install_plugins') && current_user_can('delete_plugins');
            },
            'args'                => [
                'plugin' => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'description'       => 'Plugin file path (e.g., elementor/elementor.php)',
                ],
                'plugin_action' => [
                    'required'          => true,
                    'type'              => 'string',
                    'enum'              => ['activate', 'deactivate', 'delete'],
                    'sanitize_callback' => 'sanitize_key',
                ],
            ],
        ]);


        // Route for checking a plugin's status
        register_rest_route( self::NAMESPACE, '/plugin/status', [
            'methods'             => WP_REST_Server::READABLE, // GET Request
            'callback'            => [$this, 'get_plugin_status'],
            'permission_callback' => function () {
                return current_user_can('activate_plugins') && current_user_can('install_plugins') && current_user_can('delete_plugins');
            },
            'args'                => [
                'plugin' => [
                    'required'          => true,
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'description'       => 'Plugin directory and file (e.g., elementor/elementor.php) or just the folder name if checking uninstalled status.',
                ],
            ],
        ]);
    }


    /**
     * Register feedback endpoints
     */
    private function register_feedback_endpoints()
    {
        register_rest_route(
            self::NAMESPACE,
            '/feedback',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'plugin_starter_pro_handle_feedback'],
                'permission_callback' => function () {
                    // Secure route so only logged-in administrators can push data
                    return current_user_can('manage_options');
                }
            ]
        );
    }

    /**
     * Register news endpoints
     */
    private function register_news_endpoints()
    {
        register_rest_route(
            self::NAMESPACE,
            '/news',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'mos_press_get_news'],
                'permission_callback' => function () {
                    // Secure route so only logged-in administrators can push data
                    return current_user_can('manage_options');
                }
            ]
        );

        register_rest_route(
            self::NAMESPACE,
            '/news/read',
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array($this, 'mos_press_read_news'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args'                => array(
                    'id' => array(
                        'required'          => true,
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/news/read-list',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($this, 'mos_press_read_list_news'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args'                => array(
                    'id' => array(
                        'required'          => true,
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/news/unread',
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array($this, 'mos_press_unread_news'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args'                => array(
                    'id' => array(
                        'required'          => true,
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );
    }

    /**
     * Register news endpoints
     */
    private function register_necessary_endpoints()
    {
        register_rest_route(
            self::NAMESPACE,
            '/deactivation-link',
            [
                'methods' => 'GET',
                'callback' => array($this, 'get_deactivation_link'),
                'permission_callback' => '__return_true'
                // 'permission_callback' => function () {
                //     // Secure route so only logged-in administrators can push data
                //     return current_user_can('manage_options');
                // }
            ]
        );
    }
    /**
     * Get the deactivation link.
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error The response or error.
     */
    public function get_deactivation_link(WP_REST_Request $request)
    {
        // Get the encrypted key from options
        $encrypted_key = get_option('plugin_starter_deactive_key');

        if (false === $encrypted_key) {
            return new WP_Error(
                'key_not_found',
                __('Deactivation key not found. Please reactivate the plugin.', 'plugin-starter-pro'),
                array('status' => 404)
            );
        }

        // Decrypt the key
        $decrypted_key = CryptoHelper::decrypt($encrypted_key);

        if (false === $decrypted_key) {
            return new WP_Error(
                'decryption_failed',
                __('Failed to decrypt deactivation key. Please contact support.', 'plugin-starter-pro'),
                array('status' => 500)
            );
        }

        // Build the deactivation URL
        $deactivation_url = add_query_arg(
            array(
                'action' => 'plugin_starter_deactivate',
                'secret_key' => $decrypted_key,
            ),
            admin_url('admin-post.php')
        );

        // Return the response
        return new WP_REST_Response(
            array(
                'success' => true,
                'deactivation_url' => $deactivation_url,
                'message' => __('Deactivation link generated successfully.', 'plugin-starter-pro'),
                'warning' => __('This link will only work once. After deactivation, a new link will be generated on reactivation.', 'plugin-starter-pro'),
            ),
            200
        );
    }
    public function update_settings(WP_REST_Request $request) //WP_REST_Request $request
    {
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'rest_update_error',
                'Sorry, you are not allowed to update options.' . get_current_user_id(),
                array('status' => 403)
            );
        }
        $plugin_starter_options_old = Utils::plugin_starter_get_option();

        // $plugin_starter_options = map_deep(wp_unslash($request->get_param('plugin_starter_options')), 'wp_kses_post');

        $plugin_starter_options_raw = wp_unslash($request->get_param('plugin_starter_options'));
        $plugin_starter_options = map_deep($plugin_starter_options_raw, 'wp_kses_post');

        $header_footer_kses = Utils::get_header_footer_kses();

        if (isset($plugin_starter_options_raw['more']['header_content'])) {
            $plugin_starter_options['more']['header_content'] = wp_kses($plugin_starter_options_raw['more']['header_content'], $header_footer_kses);
        }
        if (isset($plugin_starter_options_raw['more']['footer_content'])) {
            $plugin_starter_options['more']['footer_content'] = wp_kses($plugin_starter_options_raw['more']['footer_content'], $header_footer_kses);
        }



        $plugin_starter_options ? update_option('plugin_starter_options', $plugin_starter_options) : '';

        LogsController::log_settings_change($plugin_starter_options_old, $plugin_starter_options);

        $response = [
            'success' => true,
            'msg'    => esc_html__('Data successfully added.', 'plugin-starter-pro')
        ];
        return new WP_REST_Response($response, 200);
    }

    /**
     * 1. Install Plugin from GitHub, Zip, or WP Repo
     */
    public function install_plugin(WP_REST_Request $request) {
        $source = $request->get_param('source');

        // Initialize WordPress Filesystem
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        
        // Setup credentials for filesystem
        $url = wp_nonce_url(rest_url(self::NAMESPACE . '/plugin/install'), 'wp_rest');
        if (false === ($creds = request_filesystem_credentials($url, '', false, false, null))) {
            return new WP_Error('fs_error', 'Filesystem credentials failed.', ['status' => 403]);
        }

        if (!WP_Filesystem($creds)) {
            return new WP_Error('fs_failed', 'Failed to initialize WP_Filesystem.', ['status' => 500]);
        }

        // Determine if it is a direct download link or a WP Repo slug
        $download_url = $source;
        if (!filter_var($source, FILTER_VALIDATE_URL)) {
            // Assume it is a slug from WordPress.org repository
            $download_url = 'https://downloads.wordpress.org/plugin/' . sanitize_key($source) . '.zip';
        }

        // Use standard WordPress Upgrader with an AJAX skin to prevent HTML output pollution
        $skin     = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader($skin);
        $result   = $upgrader->install($download_url);

        if (is_wp_error($result)) {
            return $result;
        }

        if (!$result && !empty($skin->get_errors())) {
            return new WP_Error('install_failed', $skin->get_error_messages()[0], ['status' => 400]);
        }

        $plugin_file = $upgrader->plugin_info();

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Plugin installed successfully.',
            'plugin'  => $plugin_file
        ], 200);
    }

    /**
     * Route hub to Activate, Deactivate, or Delete plugins
     */
    public function handle_plugin_action(WP_REST_Request $request) {
        $plugin = $request->get_param('plugin');
        $action = $request->get_param('plugin_action');

        switch ($action) {
            case 'activate':
                return $this->activate($plugin);
            case 'deactivate':
                return $this->deactivate($plugin);
            case 'delete':
                return $this->delete($plugin);
            default:
                return new WP_Error('invalid_action', 'Action not recognized.', ['status' => 400]);
        }
    }

    /**
     * 2. Activate Plugin
     */
    private function activate($plugin) {
        if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin)) {
            return new WP_Error('not_found', 'Plugin file does not exist.', ['status' => 404]);
        }

        $result = activate_plugin($plugin);

        if (is_wp_error($result)) {
            return $result;
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Plugin activated successfully.'
        ], 200);
    }

    /**
     * 3. Deactivate Plugin
     */
    private function deactivate($plugin) {
        if (!is_plugin_active($plugin)) {
            return new WP_Error('already_inactive', 'Plugin is not active.', ['status' => 400]);
        }

        deactivate_plugins($plugin);

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Plugin deactivated successfully.'
        ], 200);
    }

    /**
     * 4. Delete Plugin
     */
    private function delete($plugin) {
        if (is_plugin_active($plugin)) {
            return new WP_Error('cannot_delete', 'Cannot delete an active plugin. Deactivate it first.', ['status' => 400]);
        }

        if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin)) {
            return new WP_Error('not_found', 'Plugin file not found.', ['status' => 404]);
        }

        $result = delete_plugins([$plugin]);

        if (is_wp_error($result)) {
            return $result;
        }

        if (!$result) {
            return new WP_Error('delete_failed', 'Failed to delete plugin files.', ['status' => 500]);
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Plugin deleted successfully.'
        ], 200);
    }
    /**
     * 5. Get Current Plugin Status
     */
    public function get_plugin_status(WP_REST_Request $request) {
        $plugin = $request->get_param('plugin');
        
        // Define paths to check
        $plugin_file_path = WP_PLUGIN_DIR . '/' . $plugin;
        $plugin_dir_path  = WP_PLUGIN_DIR . '/' . dirname($plugin);

        // 1. Check if the plugin is uninstalled / does not exist
        if (!file_exists($plugin_file_path) && !is_dir($plugin_dir_path)) {
            return new WP_REST_Response([
                'success' => true,
                'status'  => 'uninstalled',
                'message' => 'Plugin is not installed on this server.'
            ], 200);
        }

        // 2. Check if the plugin is activated
        if (is_plugin_active($plugin)) {
            return new WP_REST_Response([
                'success' => true,
                'status'  => 'activated',
                'message' => 'Plugin is installed and active.'
            ], 200);
        }

        // 3. If it exists but isn't active, it must be deactivated
        return new WP_REST_Response([
            'success' => true,
            'status'  => 'deactivated',
            'message' => 'Plugin is installed but deactivated.'
        ], 200);
    }
    public function plugin_starter_pro_handle_feedback($request)
    {
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
    public function mos_press_get_news($request)
    {
        $data = [];
        $count = 0;
        $read_news = get_option('mos_press_read_news', []);
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

        foreach ($news_items as $news) {
            $data[] = [
                'id' => $news['id'],
                'slug' => $news['slug'],
                'title' => $news['title'],
                'tags' => $news['tags'],
                'news' => $news['news'],
                'is_read' => in_array($news['id'], $read_news, true),
            ];
            if (!in_array($news['id'], $read_news, true)) $count++;
        }

        // return $news_items;


        return new WP_REST_Response(
            array(
                'success' => true,
                'data'    => $data,
                'count'    => $count,
            ),
            200
        );
    }
    public function mos_press_read_news($request)
    {
        $read_news = get_option('mos_press_read_news', []);
        $id = absint($request->get_param('id'));

        if (!in_array($id, $read_news)) {
            $read_news[] = $id;
            update_option('mos_press_read_news', $read_news);
        }

        return new WP_REST_Response(
            array(
                'success' => true,
                'data'    => $read_news,
            ),
            200
        );
    }
    public function mos_press_unread_news($request)
    {
        $read_news = get_option('mos_press_read_news', []);
        $id = absint($request->get_param('id'));

        if (($key = array_search($id, $read_news)) !== false) {
            unset($read_news[$key]);
            update_option('mos_press_read_news', array_values($read_news));
        }

        return new WP_REST_Response(
            array(
                'success' => true,
                'data'    => $read_news,
            ),
            200
        );
    }
}
// new Rest_Api();
