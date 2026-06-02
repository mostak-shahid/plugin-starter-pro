<?php
namespace MosPress\PluginStarterPro\Hook;
if ( ! defined( 'ABSPATH' ) ) exit;
use MosPress\PluginStarterPro\Helpers\Utils;
class Action_Hook
{
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
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
        $this->plugin_name = 'plugin-starter';
        add_action('admin_init', [$this, 'do_activation_redirect']);
				
		add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
		add_action('wp_enqueue_scripts', [$this, 'wp_enqueue_scripts']);
        add_action('upgrader_process_complete', [$this, 'update_completed'], 10, 2);
		
    }

	/**
	 * Redirect to the welcome pages.
	 *
	 * @since    1.0.0
	 */
	public function do_activation_redirect()
	{
		if (get_option('plugin_starter_do_activation_redirect')) {
			delete_option('plugin_starter_do_activation_redirect');
			wp_safe_redirect(admin_url('admin.php?page=' . $this->plugin_name));
		}
	}
	public function admin_enqueue_scripts($hook)
	{
		wp_enqueue_script('jquery');
		wp_enqueue_media();

		wp_enqueue_script('plugin-starter-admin-ajax', PLUGIN_STARTER_URL . 'assets/js/admin-ajax.js', array('jquery'), PLUGIN_STARTER_VERSION, false);
		wp_enqueue_script('plugin-starter-admin-script', PLUGIN_STARTER_URL . 'assets/js/admin.js', array('jquery'), PLUGIN_STARTER_VERSION, false);
		$ajax_params = [
			'admin_url' => admin_url(),
			'home_url' => home_url(),
			'ajax_url' => admin_url('admin-ajax.php'),
			'image_url' => PLUGIN_STARTER_URL . 'assets/images/',
			'_admin_nonce' => esc_attr(wp_create_nonce('plugin_starter_admin_nonce')),
			'api_nonce' => esc_attr(wp_create_nonce('wp_rest')),
			'get_current_user_id' => get_current_user_id(),
			'root'  => esc_url_raw( rest_url() ),
    		'nonce' => wp_create_nonce('wp_rest'),
			// 'default_colors' => plugin_starter_get_default_colors(),
			// 'default_gradients' => plugin_starter_get_default_gradients(),
			'proURL' => 'https://mostak-shahid.github.io/plugins/plugin-starter-pro.html',
			// 'isPro' => is_plugin_active( 'plugin-starter-pro/plugin-starter-pro.php' ) ? true : false,
			// 'install_plugin_wpnonce' => esc_attr(wp_create_nonce('updates')),
		];
		if (is_plugin_active( 'plugin-starter-pro/plugin-starter-pro.php' )) {
			$plugins = get_plugins();
			$version = $plugins['plugin-starter-pro/plugin-starter-pro.php']['Version'];
			$ajax_params['isPro'] = true;
			$ajax_params['proVersion'] = $version;
		}
		wp_localize_script('plugin-starter-admin-ajax', 'plugin_starter_ajax_obj', $ajax_params);

		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style('plugin-starter-admin', PLUGIN_STARTER_URL . 'assets/css/admin.css', array(), PLUGIN_STARTER_VERSION, 'all');

	}
	public function wp_enqueue_scripts($hook)
	{
		wp_enqueue_script(
			'plugin-starter-react-app',
			PLUGIN_STARTER_URL . 'build/index.js',
		);

		// NEW: Enqueue Compiled Tailwind CSS
		wp_enqueue_style(
			'my-plugin-tailwind',
			PLUGIN_STARTER_URL . 'build/index.css',
			array(),
		);

	}
	public function update_completed($upgrader_object, $options)
	{

		// If an update has taken place and the updated type is plugins and the plugins element exists
		if ($options['action'] == 'update' && $options['type'] == 'plugin' && isset($options['plugins'])) {
			foreach ($options['plugins'] as $plugin) {
				// Check to ensure it's my plugin
				if ($plugin == plugin_basename(PLUGIN_STARTER_MAIN_FILE)) {
					// do stuff here
					// $plugin_starter_options = array_replace_recursive(plugin_starter_get_option(), get_option('plugin_starter_options', []));
					// update_option('plugin_starter_options', $plugin_starter_options);
				}
			}
		}
	}
}