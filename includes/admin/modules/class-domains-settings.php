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
			$sites = [];
			foreach (get_sites() as $site) {
				$sites[$site->blog_id] =  $site->path;
			}
			$profiles_list = [];
			$wams_ga_options = get_option('wams_ga_options');
			$ga4_profiles_list =  false;
			if (is_array($ga4_profiles_list)) {
				foreach ($ga4_profiles_list as $profile) {
					$profiles_list[$profile[1]] = $profile[0];
				}
			}
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
						'callback' => [$this, 'domain_settings']
					]
				],
				'sections' => [
					[
						'id'    => 'wams_domains_settings',
						'title' => __('Domains Settings', 'wams'),
					],
					[
						'id'    => 'wams_api_settings',
						'title' => __('API Settings', 'wams'),
						'desc'	=> 'API Token:' .  md5('wams_api_token') . '<br>' . 'API Key:' .  md5('wams_api_key'),
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
							'name'              => 'blog_id',
							'label'             => __('Blog ID', 'wams'),
							'placeholder'       => __('ID', 'wams'),
							'type'              => 'select',
							'default'           => get_current_blog_id(),
							'options' => $sites
						],
						[
							'name'              => 'Website Name',
							'label'             => __('Website Name', 'wams'),
							'desc'              => __('Title Of the Website', 'wams'),
							'placeholder'       => __('name', 'wams'),
							'type'              => 'text',
							'default'           => get_bloginfo('name'),
							'sanitize_callback' => 'sanitize_text_field'
						],
						[
							'name'              => 'project_name',
							'label'             => __('Project Name', 'wams'),
							'desc'              => __('Project Name', 'wams'),
							'placeholder'       => __('name', 'wams'),
							'type'              => 'text',
							'default'           => get_bloginfo('name'),
							'sanitize_callback' => 'sanitize_text_field'
						],
						[
							'name'              => 'domain_name',
							'label'             => __('Domain Name', 'wams'),
							'desc'              => __('Domain Name', 'wams'),
							'placeholder'       => __('name', 'wams'),
							'type'              => 'text',
							'default'           => parse_url(get_bloginfo('url'), PHP_URL_HOST),
							'sanitize_callback' => 'sanitize_text_field'
						],
						[
							'name'              => 'ga_account',
							'label'             => __('Google Analytics 4 Account ID', 'wams'),
							'type'              => 'select',
							'options' => $profiles_list
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

		public function domain_settings()
		{
			$this->settings_api->show_settings_page("Domain Settings");
		}
	}
}
