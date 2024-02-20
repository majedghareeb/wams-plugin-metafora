<?php

namespace wams\admin\modules;

use wams\admin\core\Admin_Settings_API;


if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\modules\Sample')) {

	/**
	 * Class Debug
	 * @package wams\admin\Sample
	 */
	class Sample
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
			$this->settings_api = new Admin_Settings_API();
			$this->init_variables();
			$this->settings_api->addSubpages($this->page['subpage']);
			$this->settings_api->register();
		}


		public function init_variables()
		{
			$this->page = [
				'subpage' => [
					[
						'parent_slug' => 'wams',
						'page_title' => 'Sample Settings',
						'menu_title' => 'Sample Settings',
						'capability' => 'edit_wams_settings',
						'menu_slug' => 'sample',
						'callback' => [$this, 'sample']
					]
				]
			];
		}

		function sample()
		{
			echo '<h1>Sample Page</h1>';
		}
	}
}
