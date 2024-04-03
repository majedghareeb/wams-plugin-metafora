<?php

namespace wams\admin\modules;

use wams\admin\core\Admin_Settings_API;


if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\modules\Web_Notifications')) {

	/**
	 * Class Debug
	 * @package wams\admin\Web_Notifications
	 */
	class Web_Notifications
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
			$this->settings_api->set_sections($this->page['sections']);
			$this->settings_api->set_fields($this->page['fields']);
			$this->settings_api->register();
		}


		public function init_variables()
		{
			$this->page = [
				'subpage' => [
					[
						'parent_slug' => 'wams',
						'page_title' => 'Web_Notifications',
						'menu_title' => 'Web_Notifications',
						'capability' => 'edit_wams_settings',
						'menu_slug' => 'web-notifications',
						'callback' => [$this, 'show_page']
					]
				],
				'sections' => [
					[
						'id'    => 'wams_web_notifications_settings',
						'title' => __('Notifications Settings', 'wams')
					]
				],
				'fields' => [
					'wams_web_notifications_settings' => [
						[
							'name'  => 'enabled',
							'label' => __('Enable', 'wams'),
							'desc'  => __('Enable web notifications', 'wams'),
							'default' => 'on',
							'type'  => 'checkbox'
						],
						[
							'name'  => 'sound_enabled',
							'label' => __('Enable Sound', 'wams'),
							'desc'  => __('Enable Sound on new notifications', 'wams'),
							'default' => 'on',
							'type'  => 'checkbox'
						],
						[
							'name'              => 'interval',
							'label'             => __('Refresh Interval', 'wams'),
							'desc'              => __('Time in second between each AJAX call for new notification', 'wams'),
							'placeholder'       => __(''),
							'min'               => 45,
							'max'               => 10000,
							'step'              => '1',
							'type'              => 'number',
							'default'           => '60',
						],
					]
				]

			];
		}
		public function show_page()
		{
			wp_enqueue_script("web-notifications", WAMS_URL . 'assets/js/admin/web-notifications.js', array(), '1.0.1', false);
			wp_enqueue_style("sweetalert2", WAMS_URL . 'assets/css/sweetalert2.min.css', array(), WAMS_VERSION);
			wp_enqueue_script("sweetalert2", WAMS_URL . 'assets/js/sweetalert2.min.js', array(), WAMS_VERSION, false);
			echo '<h1>Web_Notifications</h1>';
			echo '<div class="wraper">';
			echo '<div id="tabs">';
			$this->settings_api->show_navigation();
			echo '<div class="border p-3">';
			$this->settings_api->show_forms();
			echo '</div>';
			echo '</div>';
			echo '</div>';
			echo '<hr>';
			$this->web_notifications();
		}

		function web_notifications()
		{

			$users = get_users();
			include_once WAMS()->admin()->templates_path . 'web-notification.php';
		}
	}
}
