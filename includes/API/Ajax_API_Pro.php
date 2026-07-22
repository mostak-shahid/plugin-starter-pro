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
		add_action('wp_ajax_plugin_starter_ajax_install_plugins', [$this, 'ajax_install_plugins']);		
		add_action('wp_ajax_plugin_starter_ajax_plugins_status', [$this, 'ajax_plugins_status']);	
    }   

	public function ajax_install_plugins() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( 'Permission denied' );
		}

		if (
			isset( $_POST['_admin_nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_admin_nonce'] ) ), 'plugin_starter_admin_nonce' )
		) {
			$sub_action    = isset( $_POST['sub_action'] ) ? sanitize_text_field( wp_unslash( $_POST['sub_action'] ) ) : '';
			$plugin_slug   = isset( $_POST['plugin_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_slug'] ) ) : '';
			$plugin_file   = isset( $_POST['plugin_file'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_file'] ) ) : '';
			$plugin_source = isset( $_POST['plugin_source'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_source'] ) ) : 'internal';

			include_once ABSPATH . 'wp-admin/includes/file.php';
			include_once ABSPATH . 'wp-admin/includes/misc.php';
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			if ( $sub_action === 'install' || $sub_action === 'install_activate' ) {

				if ( $plugin_source === 'external' ) {
					$download_url = isset( $_POST['download_url'] ) ? sanitize_url( wp_unslash( $_POST['download_url'] ) ) : '';

					$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
					$installed = $upgrader->install( $download_url );

					if ( is_wp_error( $installed ) ) {
						wp_send_json_error( 'Install failed: ' . $installed->get_error_message() );
					}

					// Initialize WP_Filesystem
					global $wp_filesystem;
					if ( ! $wp_filesystem || ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
						WP_Filesystem();
					}

					$extracted_dir = WP_PLUGIN_DIR . '/' . $plugin_slug;
					$destination   = WP_PLUGIN_DIR . '/' . $plugin_slug;

					if ( is_dir( $extracted_dir ) && $extracted_dir !== $destination ) {
						if ( ! $wp_filesystem->move( $extracted_dir, $destination ) ) {
							wp_send_json_error( 'Failed to move plugin directory using WP_Filesystem.' );
						}
					}
				} else {
					include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

					$api = plugins_api( 'plugin_information', [ 'slug' => $plugin_slug, 'fields' => [ 'sections' => false ] ] );
					if ( is_wp_error( $api ) ) {
						wp_send_json_error( [ 'message' => 'Plugin info fetch failed' ] );
					}

					$upgrader       = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
					$install_result = $upgrader->install( $api->download_link );

					if ( is_wp_error( $install_result ) ) {
						wp_send_json_error( [ 'message' => 'Install failed: ' . $install_result->get_error_message() ] );
					}
				}

				if ( $sub_action === 'install' ) {
					wp_send_json_success( 'not_active.' );
				}
			}

			if ( $sub_action === 'install_activate' || $sub_action === 'activate' ) {
				$result = activate_plugin( WP_PLUGIN_DIR . '/' . $plugin_file );
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( 'Activation failed: ' . $result->get_error_message() );
				} else {
					wp_send_json_success( 'active.' );
				}
			}

			wp_send_json_error( [ 'error_message' => esc_html__( 'Unknown action.', 'plugin-starter' ) ] );
		} else {
			wp_send_json_error( [ 'error_message' => esc_html__( 'Nonce verification failed. Please try again.', 'plugin-starter' ) ] );
		}

		wp_die();
	}	  
	public function ajax_plugins_status()
	{

		if (!current_user_can('install_plugins')) {
			wp_send_json_error(array('error_message' => esc_html__('Permission denied', 'plugin-starter')));
		}
		if (isset($_POST['_admin_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_admin_nonce'])), 'plugin_starter_admin_nonce')) {
			// $slug = isset($_POST['slug']) ? sanitize_text_field(wp_unslash($_POST['slug'])) : '';
			$file = isset($_POST['file']) ? sanitize_text_field(wp_unslash($_POST['file'])) : '';
			$status = 'not_installed';
			if (!is_plugin_active($file) && !file_exists(WP_PLUGIN_DIR . '/' . $file)) {
				$status = 'not_installed';
			} elseif (!is_plugin_active($file) && file_exists(WP_PLUGIN_DIR . '/' . $file)) {
				$status = 'installed';
			} elseif (is_plugin_active($file)) {
				$status = 'activated';
			}
			wp_send_json_success(
				array(
					'file' => $file,
					'success_message' => esc_html($status)
				)
			);
		} else {
			wp_send_json_error(array('error_message' => esc_html__('Nonce verification failed. Please try again.', 'plugin-starter')));
			// wp_die(esc_html__('Nonce verification failed. Please try again.', 'plugin-starter'));
		}
		wp_die();
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
						esc_html__('Plugin Deactivated', 'plugin-starter-pro'),
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