<?php
namespace MosPress\PluginStarterPro\API;
if ( ! defined( 'ABSPATH' ) ) exit;
use MosPress\PluginStarterPro\API\LogsController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_Query;
use WP_REST_Server;

use MosPress\PluginStarterPro\Helpers\CryptoHelper;
/**
 * Rest API Router
 *
 * Registers all REST API endpoints and routes them to appropriate controllers
 */
class Rest_API
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
        register_rest_route(
            self::NAMESPACE, '/plugins', 
            [
                'methods' => 'GET',
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

        register_rest_route(
            self::NAMESPACE, '/feedback', 
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'plugin_starter_pro_handle_feedback'],
                'permission_callback' => function() {
                    // Secure route so only logged-in administrators can push data
                    return current_user_can('manage_options');
                }
            ]
        );
        register_rest_route(
            self::NAMESPACE, '/news', 
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'plugin_starter_pro_handle_news'],
                'permission_callback' => function() {
                    // Secure route so only logged-in administrators can push data
                    return current_user_can('manage_options');
                }
            ]
            
        );
        
    }
    public function plugin_starter_pro_handle_feedback($request) {
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
    public function plugin_starter_pro_handle_news($request) {
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

        return $news_items;
    }

}
// new Rest_Api();


