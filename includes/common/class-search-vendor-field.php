<?php

namespace wams\common;

use wams\admin\core\Admin_Settings_API;


if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\common\Search_Vendor_Field')) {

	/**
	 * Class Search_Vendor_Field
	 * @package wams\common
	 */
	class Search_Vendor_Field
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
			$wams_seach_vendor_field_settings = get_option('wams_seach_vendor_field_settings');

			if ($wams_seach_vendor_field_settings && $wams_seach_vendor_field_settings['enable_seach_vendor_field'] == 'on') {
				add_action('gform_loaded', array($this, 'load'), 5);
			}
		}

		public static function load()
		{

			if (!method_exists('GFForms', 'include_addon_framework')) {
				return;
			}

			require_once(WAMS_PATH . 'includes/common/search-vendor-field/class-vendorseaarchfieldaddon.php');

			\GFAddOn::register('WAMS_Search_Field_Addon');
		}
	}
}
