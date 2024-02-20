<?php

namespace wams\frontend;

use Inc\Wams;

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;


if (!class_exists('wams\frontend\Ajax_Hooks')) {


	/**
	 * Class Admin_Ajax_Hooks
	 * @package um\admin\core
	 */
	class Ajax_Hooks
	{
		/**
		 * Admin_Columns constructor.
		 */
		function __construct()
		{
			add_action('wp_ajax_telegram_ajax_request', array(WAMS()->telegram_notifications(), 'telegram_ajax_handler'));
			add_action('wp_ajax_web_notifications_frontend_ajax_request', array(WAMS()->web_notifications(), 'web_notifications_ajax_handler'));

			// add_action('wp_ajax_wams_same_page_update', array(WAMS()->admin_settings(), 'same_page_update_ajax'));
		}
	}
}
