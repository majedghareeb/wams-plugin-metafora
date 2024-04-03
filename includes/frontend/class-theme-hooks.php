<?php

namespace wams\frontend;


if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\frontend\Theme_Hooks')) {

	/**
	 * Class Secure
	 *
	 * @package wams\frontend
	 *
	 * @since 1.0.0
	 */
	class Theme_Hooks
	{

		/**
		 * Theme_Hooks constructor.
		 * @since 1.0.0
		 */
		public function __construct()
		{
			add_action('wams_theme_set_menu_count', function ($class) {
				if ($class == 'workflow-inbox-count') {
					$get_inbox_count = WAMS()->web_notifications()->get_inbox_count();
					echo $get_inbox_count;
				}
			});

			add_action('gravityview/template/before', function ($gravityview) {

				if ($gravityview->entry) {
					do_shortcode('[gravityflow-wizard entry_id="' . $gravityview->entry->ID . '"]');
				}
			});

			add_filter('gravityflow_status_args', function ($args) {
				$args['filter_hidden_fields'] = array('page' => '');
				return $args;
			});
		}

		/**
		 * Adds handlers on form submissions.
		 *
		 * @since 1.0.0
		 */
		public function init()
		{
		}
	}
}
