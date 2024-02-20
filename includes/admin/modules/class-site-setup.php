<?php

namespace wams\admin\modules;

use wams\admin\core\Admin_Settings_API;


if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\modules\Site_Setup')) {

	/**
	 * Class Debug
	 * @package wams\admin\modules
	 */
	class Site_Setup
	{
		/**
		 * @var object
		 */
		private $settings_api;

		/**
		 * @var array
		 */
		private $page;
		private $options  = [];

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
						'page_title' => 'Site Setup',
						'menu_title' => 'Site Setup',
						'capability' => 'manage_options',
						'menu_slug' => 'wams_site_setup',
						'callback' => [$this, 'wams_site_setup_page']
					]
				]
			];
		}

		public function wams_site_setup_page()
		{
			echo '<h1>Site Setup Page</h1>';
			wp_enqueue_script("site-setup", WAMS_URL . 'assets/js/admin/site-setup.js', array(), WAMS_VERSION, false);
			wp_enqueue_style("sweetalert2", WAMS_URL . 'assets/css/sweetalert2.min.css', array(), WAMS_VERSION);
			wp_enqueue_script("sweetalert2", WAMS_URL . 'assets/js/sweetalert2.min.js', array(), WAMS_VERSION, false);


			$gf_forms = \GFFormsModel::get_forms(true);
			$forms = array();
			foreach ($gf_forms as $form) {
				$form_id = absint($form->id);
				$form_title = $form->title;
				$forms[$form_id] = ['title' => $form_title, 'entry_count' => $form->entry_count];
			}

			include_once WAMS()->admin()->templates_path . 'site-setup.php';
		}
	}
}
