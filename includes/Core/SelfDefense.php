<?php

namespace MosPress\PluginStarterPro\Core;

if (! defined('ABSPATH')) exit;

use MosPress\PluginStarter\Helpers\Utils;
use MosPress\PluginStarter\Helpers\CryptoHelper;

class SelfDefense
{
	protected $options;
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
		$this->options = Utils::plugin_starter_get_option();

		if (isset($this->options['utilities']['tools']['self_defense']) && $this->options['utilities']['tools']['self_defense'] == 1) {
			// Hook to enqueue scripts strictly on the plugins page
			add_action('admin_enqueue_scripts', [$this, 'plugin_starter_enqueue_deactivation_scripts']);
			// Render HTML content in the footer of the plugins page
			add_action('admin_footer', [$this, 'plugin_starter_render_modal_html']);
			// AJAX handler to verify password
			add_action('wp_ajax_verify_user_password', [$this, 'verify_user_password_ajax']);
		}
	}

	/**
	 * Enqueues inline script attached to jQuery handle specifically on plugins.php page.
	 */
	public function plugin_starter_enqueue_deactivation_scripts($hook)
	{
		global $pagenow;

		// Ensure this only triggers exactly on the plugins.php backend page
		if ($pagenow !== 'plugins.php') {
			return;
		}

		$user_id = get_current_user_id();
		$transient_lock = get_transient('pd_lock_' . $user_id);
		$attempts_left = 3 - (int)get_transient('pd_attempts_' . $user_id);

		$inline_javascript = "
            jQuery(document).ready(function($) {
                var deactivateUrl = '';
                var isLocked = " . ($transient_lock ? 'true' : 'false') . ";

                // Intercept deactivate link click
                $('tr[data-plugin=\"plugin-starter-pro/plugin-starter-pro.php\"] .deactivate a').on('click', function(e) {
                    e.preventDefault();
                    
                    if (isLocked) {
                        alert('Too many incorrect attempts. Deactivation is locked for 10 minutes.');
                        return;
                    }

                    deactivateUrl = $(this).attr('href');
                    $('#password-modal').show();
                    $('#plugin-password').focus();
                });
                
                // Close modal
                $('#cancel-deactivation, #password-modal').on('click', function(e) {
                    if (e.target === this) {
                        $('#password-modal').hide();
                        $('#plugin-password').val('');
                        $('#password-error').hide();
                    }
                });
                
                // Verify password and deactivate
                $('#verify-password').on('click', function() {
                    if (isLocked) return;

                    var password = $('#plugin-password').val();
                    if (!password) return;
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'verify_user_password',
                            password: password,
                            nonce: '" . esc_js(wp_create_nonce('verify_password_nonce')) . "'
                        },
                        success: function(response) {
                            if (response.success) {
                                window.location.href = deactivateUrl;
                            } else {
                                $('#password-error').text(response.data.message).show();
                                $('#plugin-password').val('').focus();
                                
                                // Check if backend locked the user out on this request
                                if (response.data.locked) {
                                    isLocked = true;
                                    setTimeout(function() {
                                        $('#password-modal').hide();
                                    }, 1000);
                                }
                            }
                        }
                    });
                });
                
                // Allow Enter key to submit
                $('#plugin-password').on('keypress', function(e) {
                    if (e.which === 13) {
                        $('#verify-password').click();
                    }
                });
            });
        ";

		wp_add_inline_script('jquery', $inline_javascript);
	}

	/**
	 * Renders structural modal HTML inside admin footer only on plugins.php page.
	 */
	public function plugin_starter_render_modal_html()
	{
		global $pagenow;
		if ($pagenow !== 'plugins.php') {
			return;
		}
?>
		<!-- Password Modal -->
		<div id="password-modal" style="display:none;">
			<div class="modal-content">
				<div class="modal-header">Verify Your Password</div>
				<div class="modal-body">
					<label for="plugin-password">Enter your current password to deactivate this plugin:</label>
					<input type="password" id="plugin-password" class="modal-input" placeholder="Password">
					<div id="password-error" class="modal-error"></div>
				</div>
				<div class="modal-footer">
					<button type="button" id="cancel-deactivation" class="button">Cancel</button>
					<button type="button" id="verify-password" class="button button-primary">Verify & Deactivate</button>
				</div>
			</div>
		</div>
<?php
	}

	/**
	 * AJAX handler with explicit 3-attempt penalty/10-minute lockout logic.
	 */
	public static function verify_user_password_ajax()
	{
		check_ajax_referer('verify_password_nonce', 'nonce');

		$user = wp_get_current_user();
		$user_id = $user->ID;

		// Check if the user is currently locked out
		if (get_transient('pd_lock_' . $user_id)) {
			wp_send_json_error(array(
				'message' => 'Too many failed attempts. Try again in 10 minutes.',
				'locked'  => true
			));
		}

		$password = isset($_POST['password']) ? sanitize_text_field(wp_unslash($_POST['password'])) : '';

		// Process password check
		if (wp_check_password($password, $user->user_pass, $user_id)) {
			// Success: Clear failed attempt tracking counters
			delete_transient('pd_attempts_' . $user_id);
			wp_send_json_success(array('message' => 'Password verified'));
		} else {
			// Failure: Increment the attempt registry counter
			$attempts = (int)get_transient('pd_attempts_' . $user_id);
			$attempts++;

			if ($attempts >= 3) {
				// Lock the user out across 10 minutes (600 seconds)
				set_transient('pd_lock_' . $user_id, true, 600);
				delete_transient('pd_attempts_' . $user_id); // Reset tracking context

				wp_send_json_error(array(
					'message' => 'Too many failed attempts. This action is locked for 10 minutes.',
					'locked'  => true
				));
			} else {
				// Save incremental failures for up to 2 hours
				set_transient('pd_attempts_' . $user_id, $attempts, 2 * HOUR_IN_SECONDS);
				$remaining = 3 - $attempts;

				wp_send_json_error(array(
					'message' => sprintf('Incorrect password. %d attempts remaining.', $remaining),
					'locked'  => false
				));
			}
		}
	}

	public static function handle_deactivation()
	{
		if (!function_exists('is_plugin_active')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';
		$secret_key = isset($_GET['secret_key']) ? sanitize_text_field(wp_unslash($_GET['secret_key'])) : '';
		if ($action && $secret_key) {
			$plugin_starter_deactive_key = get_option('plugin_starter_deactive_key');
			$decrypted_secret_key = CryptoHelper::decrypt($plugin_starter_deactive_key);

			if ($secret_key == $decrypted_secret_key) {
				$plugins_deactivated = [];

				$pro_plugin = 'plugin-starter-pro/plugin-starter-pro.php';
				if (is_plugin_active($pro_plugin)) {
					deactivate_plugins($pro_plugin);
					$plugins_deactivated[] = 'Plugin Starter Pro';
				}

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
