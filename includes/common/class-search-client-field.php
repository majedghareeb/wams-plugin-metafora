<?php

namespace wams\common;

use wams\admin\core\Admin_Settings_API;


if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\common\Search_Client_Field')) {

	/**
	 * Class Search_Client_Field
	 * @package wams\common
	 */
	class Search_Client_Field
	{
		/**
		 * @var object
		 */
		private $settings_api;

		/**
		 * @var array
		 */

		private $page;

		/**
		 * Admin_Menu constructor.
		 */
		function __construct()

		{
			define('WAMS_GF_SEARCH_VERSION', '1.0');
			define('WAMS_GF_SEARCH_PATH', plugin_dir_path(__FILE__));
			define('WAMS_GF_SEARCH_URL', plugin_dir_url(__FILE__));
			$wams_seach_client_field_settings = get_option('wams_seach_client_field_settings');

			if ($wams_seach_client_field_settings && $wams_seach_client_field_settings['enable_seach_client_field'] == 'on') {
				add_action('gform_loaded', array($this, 'load'), 5);
				define('GF_SEARCH_SITE_ID', $wams_seach_client_field_settings['client_site_id'] ?? 0);
				define('GF_SEARCH_FORM_ID', $wams_seach_client_field_settings['client_add_new_form'] ?? 0);
				define('GF_SEARCH_CLIENT_TYPE_FIELD_ID', $wams_seach_client_field_settings['client_type'] ?? 0);
			}
		}

		public static function load()
		{

			if (!method_exists('GFForms', 'include_addon_framework')) {
				return;
			}

			require_once(WAMS_GF_SEARCH_PATH . '/search-client-field/class-clientseaarchfieldaddon.php');

			\GFAddOn::register('WAMS_Search_Field_Addon');
		}
	}
}
