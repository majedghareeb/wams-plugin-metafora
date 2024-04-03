<?php

namespace wams\admin\core;

use Inc\Wams;

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;


if (!class_exists('wams\admin\core\Admin_Ajax_Hooks')) {


	/**
	 * Class Admin_Ajax_Hooks
	 * @package um\admin\core
	 */
	class Admin_Ajax_Hooks
	{


		/**
		 * Admin_Columns constructor.
		 */
		function __construct()
		{
			// add_action('wp_ajax_wams_do_ajax_action', array(WAMS()->fields(), 'do_ajax_action'));
			// add_action('wp_ajax_wams_test_ajax', array($this, 'do_ajax_action'));
			// add_action('wp_ajax_wams_rss_ajax', array($this, 'do_ajax_action'));
			// add_action('wp_ajax_wams_import_ajax', array($this, 'do_ajax_action'));
			add_action('wp_ajax_admin_ajax_request', array($this, 'do_ajax_action'));

			add_action('wp_ajax_site_setup_ajax_request', array(WAMS()->admin()->system_setup(), 'site_setup_ajax_handler'));
			add_action('wp_ajax_db_cleanup_ajax_request', array(WAMS()->admin()->ajax_handler(), 'db_cleanup_ajax_handler'));
			add_action('wp_ajax_web_notifications_admin_ajax_request', array(WAMS()->admin()->ajax_handler(), 'web_notifications_ajax_handler'));
			add_action('wp_ajax_telegram_notifications_admin_ajax_request', array(WAMS()->admin()->ajax_handler(), 'telegram_notifications_ajax_handler'));

			// add_action('wp_ajax_wams_same_page_update', array(WAMS()->admin_settings(), 'same_page_update_ajax'));
		}

		function do_ajax_action()
		{
			if (!wp_verify_nonce($_POST['nonce'], 'wams-admin-nonce') || !current_user_can('manage_options')) {
				wp_die(esc_attr__('Security Check', 'wams'));
			}

			if (empty($_POST['param'])) {
				wp_send_json_error(__('Invalid Action.', 'wams'));
			}
			switch ($_POST['param']) {
				case 'delete_option':
					$option_name = isset($_POST['option_id']) ? sanitize_text_field($_POST['option_id']) : 0;
					$option_deleted = delete_option($option_name);
					if ($option_deleted) {
						wp_send_json(['success', 'Option ' . $option_name . ' has been saved']);
					} else {
						wp_send_json(['warning', 'No Changes!']);
					}
					break;

				case 'test_google_analytics':
					$result = [];
					$ga = new \wams\core\google\GA_Api_Controller;
					$account = isset($_POST['account']) ? $_POST['account'] : 0;
					$result = $ga->get($account, 'bottomstats', '2024-01-01', '2024-01-01', '');
					// $results[] = $ga->get($account, 'bottomstats', '2024-01-01', '2024-12-31', '/');
					wp_send_json([$result, $account]);
					break;
				default:
					break;
			}
			// return wp_send_json(['message' => "TEST AJAX from Admin " . __METHOD__]);
			wp_die();
		}
	}
}
