<?php

namespace wams\common;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\common\Gravityflow_Custom_Step')) {

	/**
	 * Class Gravityflow_Custom_Step
	 * @package wams\common
	 */
	class Gravityflow_Custom_Step
	{

		/**
		 * Gravityflow_Custom_Step constructor.
		 */
		function __construct()

		{
			add_action('gravityflow_loaded', [$this, 'load']);
			// Register the step

			// define('WAMS_GF_SEARCH_VERSION', '1.0');
			define('WAMS_GF_CUSTOM_STEP_PATH', plugin_dir_path(__FILE__));
			define('WAMS_GF_CUSTOM_STEP_URL', plugin_dir_url(__FILE__));
			// $wams_seach_client_field_settings = get_option('wams_seach_client_field_settings');

			// if ($wams_seach_client_field_settings && $wams_seach_client_field_settings['enable_seach_client_field'] == 'on') {
			// 	add_action('gform_loaded', array($this, 'load'), 5);
			// 	define('GF_SEARCH_SITE_ID', $wams_seach_client_field_settings['client_site_id'] ?? 0);
			// 	define('GF_SEARCH_FORM_ID', $wams_seach_client_field_settings['client_add_new_form'] ?? 0);
			// 	define('GF_SEARCH_CLIENT_TYPE_FIELD_ID', $wams_seach_client_field_settings['client_type'] ?? 0);
			// }
		}

		public static function load()
		{

			if (!class_exists('Gravity_Flow_Steps')) {
				return;
			}

			require_once(WAMS_GF_CUSTOM_STEP_PATH . 'gravityflow-cs/class-custom-step.php');

			\Gravity_Flow_Steps::register(new \Gravity_Flow_Step_Wams_Process());
		}
	}
}
