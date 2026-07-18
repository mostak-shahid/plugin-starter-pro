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
        register_rest_route(
            self::NAMESPACE,
            '/options',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'update_settings'],
                // 'permission_callback' => '__return_true'
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            )
        );
    }

    /**
     * Register plugins endpoints
     */
    private function register_plugins_endpoints()
    {
        register_rest_route(
            self::NAMESPACE,
            '/plugins',
            [
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
            ]
        );
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
                __('Deactivation key not found. Please reactivate the plugin.', 'plugin-starter'),
                array('status' => 404)
            );
        }

        // Decrypt the key
        $decrypted_key = CryptoHelper::decrypt($encrypted_key);

        if (false === $decrypted_key) {
            return new WP_Error(
                'decryption_failed',
                __('Failed to decrypt deactivation key. Please contact support.', 'plugin-starter'),
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
                'message' => __('Deactivation link generated successfully.', 'plugin-starter'),
                'warning' => __('This link will only work once. After deactivation, a new link will be generated on reactivation.', 'plugin-starter'),
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

    public function plugin_starter_ajax_install_plugins(WP_REST_Request $request)
    {

        if (!current_user_can('install_plugins')) {
            return new WP_Error(
                'rest_update_error',
                'Sorry, you are not allowed to complete this action.' . get_current_user_id(),
                array('status' => 403)
            );
        }
        $sub_action = sanitize_text_field(wp_unslash($request->get_param('sub_action')));
        $plugin_slug = sanitize_text_field(wp_unslash($request->get_param('plugin_slug')));
        $plugin_file = sanitize_text_field(wp_unslash($request->get_param('plugin_file')));
        $plugin_source = sanitize_text_field(wp_unslash($request->get_param('plugin_source')));

        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/misc.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        if ($sub_action === 'install' || $sub_action === 'install_activate') {

            if ($plugin_source === 'external') {
                $download_url = isset($_POST['download_url']) ? sanitize_url(wp_unslash($_POST['download_url'])) : '';

                $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
                $installed = $upgrader->install($download_url);

                if (is_wp_error($installed)) {
                    return new WP_Error(
                        'error_message',
                        esc_html__('Install failed: ', 'plugin-starter') . $installed->get_error_message(),
                        array('status' => 403)
                    );
                }

                // Initialize WP_Filesystem
                global $wp_filesystem;
                if (! $wp_filesystem || ! is_a($wp_filesystem, 'WP_Filesystem_Base')) {
                    WP_Filesystem();
                }

                $extracted_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
                $destination   = WP_PLUGIN_DIR . '/' . $plugin_slug;

                if (is_dir($extracted_dir) && $extracted_dir !== $destination) {
                    if (! $wp_filesystem->move($extracted_dir, $destination)) {
                        return new WP_Error(
                            'error_message',
                            esc_html__('Failed to move plugin directory using WP_Filesystem.', 'plugin-starter'),
                            array('status' => 403)
                        );
                    }
                }
            } else {
                include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

                $api = plugins_api('plugin_information', ['slug' => $plugin_slug, 'fields' => ['sections' => false]]);
                if (is_wp_error($api)) {
                    return new WP_Error(
                        'error_message',
                        esc_html__('Plugin info fetch failed', 'plugin-starter'),
                        array('status' => 403)
                    );
                }

                $upgrader       = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
                $install_result = $upgrader->install($api->download_link);

                if (is_wp_error($install_result)) {
                    return new WP_Error(
                        'error_message',
                        esc_html__('Install failed: ', 'plugin-starter') . $install_result->get_error_message(),
                        array('status' => 403)
                    );
                }
            }

            if ($sub_action === 'install') {
                $response = [
                    'success' => true,
                    'msg'    => esc_html__('Plugin successfully installed.', 'plugin-starter-pro')
                ];
                return new WP_REST_Response($response, 200);
            }
        }

        if ($sub_action === 'install_activate' || $sub_action === 'activate') {
            $result = activate_plugin(WP_PLUGIN_DIR . '/' . $plugin_file);
            if (is_wp_error($result)) {
                return new WP_Error(
                    'error_message',
                    esc_html__('Activation failed: ', 'plugin-starter') . $result->get_error_message(),
                    array('status' => 403)
                );
            } else {
                $response = [
                    'success' => true,
                    'msg'    => esc_html__('Plugin successfully activated.', 'plugin-starter-pro')
                ];
                return new WP_REST_Response($response, 200);
            }
        }
        return new WP_Error(
            'error_message',
            esc_html__('Unknown action.', 'plugin-starter'),
            array('status' => 403)
        );
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
