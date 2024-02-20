<?php

namespace wams\admin\modules;

use wams\admin\core\Admin_Settings_API;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\modules\Domains_Settings')) {

	/**
	 * Class Admin_Settings
	 * @package um\admin\core
	 */
	class Domains_Settings
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
		 * Admin_Settings constructor.
		 */
		/**
		 * Admin_Menu constructor.
		 */
		function __construct()

		{
			$this->settings_api = new Admin_Settings_API();
			$this->init_variables();
			$this->settings_api->set_sections($this->page['sections']);
			$this->settings_api->set_fields($this->page['fields']);
			$this->settings_api->addSubpages($this->page['subpage']);
			$this->settings_api->register();
		}

		/**
		 * 
		 */
		public function init_variables()
		{
			$wams_scheduled_actions = [];
			if (is_plugin_active('action-scheduler')) {
				// foreach ($this->hooks as $key => $hook) {
				// 	$settings_fields['wams_action_scheduler_settings'][] =  array(
				// 		'name'    => $hook,
				// 		'label'   => $hook,
				// 		'desc'    => __('Enable', 'wams'),
				// 		'type'    => 'checkbox',
				// 		'default' => 'no',
				// 	);
				// }
			}
			$active_pages = WAMS()->query()->wp_pages();
			$this->page = [
				'subpage' => [
					[
						'parent_slug' => 'wams',
						'page_title' => 'Domains Settings',
						'menu_title' => 'Domains Settings',
						'capability' => 'edit_wams_settings',
						'menu_slug' => 'wams_domains_settings',
						'callback' => function () {
							$this->settings_api->show_settings_page($title = 'Domains Settings');
						}
					]
				],
				'sections' => [
					[
						'id'    => 'wams_domains_settings',
						'title' => __('Domains Settings', 'wams'),
					],
					[
						'id'    => 'wams_social_media_settings',
						'title' => __('Social Media Settings', 'wams')
					],
					[
						'id'    => 'wams_pages_settings',
						'title' => __('Pages Settings', 'wams'),
						'desc' => '<h4>Pages</h4>'
					],
				],
				'fields' => [
					'wams_domains_settings' => [
						[
							'name'              => 'Website Name',
							'label'             => __('Website Name', 'wams'),
							'desc'              => __('Title Of the Website', 'wams'),
							'placeholder'       => __('name', 'wams'),
							'type'              => 'text',
							'default'           => get_bloginfo('name'),
							'sanitize_callback' => 'sanitize_text_field'
						],
					],
					'wams_pages_settings' => [
						[
							'name'              => 'workflow_inbox',
							'label'             => __('Inbox', 'wams'),
							'desc'              => __('Please select the Inbox Page', 'wams'),
							'type'              => 'select',
							'options'				=> $active_pages,
							// 'sanitize_callback' => 'sanitize_text_field'
						],
						[
							'name'              => 'workflow_status',
							'label'             => __('Status', 'wams'),
							'desc'              => __('Please select the Status Page', 'wams'),
							'type'              => 'select',
							'options'				=> $active_pages
							// 'sanitize_callback' => 'sanitize_text_field'
						],
						[
							'name'              => 'tasks_calendar',
							'label'             => __('Tasks Calendar', 'wams'),
							'desc'              => __('Please select the Tasks Calendar Page', 'wams'),
							'type'              => 'select',
							'options'				=> $active_pages
							// 'sanitize_callback' => 'sanitize_text_field'
						],
					]
				]

			];
		}
	}
}
