<?php

namespace wams\admin;

use wams\admin\core\Admin_Settings_API;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\Admin_Dashboard')) {

	/**
	 * Class Admin_Settings
	 * @package um\admin\core
	 */
	class Admin_Dashboard
	{

		/**
		 * @var
		 */
		private $page;
		private $active_pages = [];
		private $settings_api;
		/**
		 * Admin_Settings constructor.
		 */
		function __construct()
		{

			$this->settings_api = new Admin_Settings_API();

			//init settings structure
			$this->init_variables();
			$this->settings_api->addPages($this->page['page'])->withSubPage('Dashboard');
			$this->settings_api->set_sections($this->page['sections']);
			$this->settings_api->set_fields($this->page['fields']);
			$this->settings_api->register();
		}
		public function init_variables()
		{
			$plugin_settings_fields = [];

			$modules = WAMS()->admin()->modules;
			if ($modules) {

				foreach ($modules as $module) {
					if (!$module['default']) continue;
					$plugin_settings_fields[] =
						array(
							'name'  => $module['name'],
							'label' => $module['title'],
							'desc'  => $module['title'],
							'type'  => 'checkbox',
						);
				}
			}
			$this->page = [
				'page' => [
					[
						'page_title' => 'WAMS Plugin',
						'menu_title' => __('WAMS Plugin', 'wams'),
						'capability' => 'edit_wams_settings',
						'menu_slug' => 'wams',
						'callback' => [$this, 'show_dashboard'],
						'icon_url' => 'dashicons-hammer',
						'position' => 110
					]
				],
				'sections' => [
					[
						'id' => 'wams_plugin_settings',
						'title' => 'Settings Manager',

					],
					[
						'id' => 'wams_update_settings',
						'title' => 'Plugin Update',
					]
				],
				'fields' => [

					'wams_plugin_settings' => $plugin_settings_fields,
					'wams_update_settings' => [
						[
							'name'  => 'enable_logs',
							'label' => 'Enable Logs',
							'desc'  => 'Allow the plugin to log to file',
							'type'  => 'checkbox',
						]
					]
				]

			];
		}


		public function show_dashboard()
		{
			echo '<h1>Dashboard 2</h1>';
			echo '<div class="wraper">';
			echo '<div id="tabs">';
			$this->settings_api->show_navigation();
			echo '<div class="border p-3">';
			$this->settings_api->show_forms();
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
	}
}
