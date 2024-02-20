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

			add_action('wp_ajax_site_setup_ajax_request', array(WAMS()->admin()->ajax_handler(), 'site_setup_ajax_handler'));
			add_action('wp_ajax_db_cleanup_ajax_request', array(WAMS()->admin()->ajax_handler(), 'db_cleanup_ajax_handler'));
			add_action('wp_ajax_web_notifications_admin_ajax_request', array(WAMS()->admin()->ajax_handler(), 'web_notifications_ajax_handler'));

			// add_action('wp_ajax_wams_same_page_update', array(WAMS()->admin_settings(), 'same_page_update_ajax'));
		}

		function do_ajax_action()
		{
			return wp_send_json(['message' => "TEST AJAX from Admin " . __METHOD__]);
		}
	}
}
