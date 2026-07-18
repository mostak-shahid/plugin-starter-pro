<?php
namespace MosPress\PluginStarterPro\API;
if ( ! defined( 'ABSPATH' ) ) exit;

use MosPress\PluginStarter\API\Ajax_API;
use Plugin_Upgrader;
use WP_Ajax_Upgrader_Skin;

use MosPress\PluginStarter\Helpers\CryptoHelper;

class Ajax_API_Pro extends Ajax_API
{
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
    }   

	public static function handle_deactivation(){
		if (!function_exists('is_plugin_active')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		// ?action=plugin_starter_deactivate&secret_key=xxxxxx
		$action = isset($_GET['action'])? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		$secret_key = isset($_GET['secret_key'])? sanitize_text_field( wp_unslash( $_GET['secret_key'] ) ) : '';
        error_log('action:', $action);
		if ($action && $secret_key) {
			// $encrypted_secret_key = CryptoHelper::encrypt( $secret_key );
			$plugin_starter_deactive_key = get_option('plugin_starter_deactive_key');
			$decrypted_secret_key = CryptoHelper::decrypt($plugin_starter_deactive_key);
			// echo $secret_key.'<br/>';
			// // echo $encrypted_secret_key.'<br/>';
			// echo $plugin_starter_deactive_key.'<br/>';
			// echo $decrypted_secret_key.'<br/>';
			if ($secret_key == $decrypted_secret_key) {
				$plugins_deactivated = [];

				// Deactivate Pro first
				$pro_plugin = 'plugin-starter-pro/plugin-starter-pro.php';
				if (is_plugin_active($pro_plugin)) {
					deactivate_plugins($pro_plugin);
					$plugins_deactivated[] = 'Plugin Starter Pro';
				}

				// Then Free
				$free_plugin = 'plugin-starter/plugin-starter.php';
				if (is_plugin_active($free_plugin)) {
					deactivate_plugins($free_plugin);
					$plugins_deactivated[] = 'Plugin Starter';
				}

				if (!empty($plugins_deactivated)) {
					wp_die(
						esc_html(
							'The following plugin(s) have been deactivated successfully: '
							. implode(', ', array_map('esc_html', $plugins_deactivated))
						),
						esc_html__('Plugin Deactivated', 'plugin-starter'),
						['response' => 200]
					);

				} else {
					wp_die(
						esc_html('Neither Ultimate Security nor Ultimate Security Pro is active.')
					);
				}

			}
			wp_die('Invalid Request.', 'Unauthorized', ['response' => 403]);
		}
		wp_die('Invalid Request.', 'Unauthorized', ['response' => 403]);
	}
}

// new Ajax_API();