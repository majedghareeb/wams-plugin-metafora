<?php

namespace wams;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\Config')) {

	/**
	 * Class Config
	 *
	 * Class with global variables for WAMS
	 *
	 *
	 * @package wams
	 */
	class Config
	{

		/**
		 * @var mixed|void
		 */
		public $core_pages;
		public $input_defaults;

		/**
		 * @var array
		 */
		public $core_global_meta_all;

		/**
		 * @var
		 */
		public $perms;

		/**
		 * @var
		 */
		public $nonadmin_perms;

		/**
		 * @var mixed|void
		 */
		public $email_notifications;

		/**
		 * @var mixed|void
		 */
		public $settings_defaults;

		/**
		 * @var array
		 */
		public $permalinks;

		/**
		 * Config constructor.
		 */
		public function __construct()
		{

			$this->core_pages = array(
				'workflow_inbox',
				'workflow_status',
				'tasks_calendar',
			);

			$this->core_pages = apply_filters(
				'wams_core_pages',
				array(
					'workflow_inbox'           => array('title' => __('Workflow Inbox', 'wams')),
					'workflow_status'          => array('title' => __('Workflow Status', 'wams')),
					'tasks_calendar'       => array('title' => __('Tasks Calendar', 'wams')),
				)
			);

			//settings defaults
			$this->settings_defaults = array(
				'wams_forms_settings'        => [
					'company_form' => 12,
					'project_form' => 9,
					'domain_form' => 18,
					'user_form' => 2,
					'urls_form' => 16,
					'vendor_projects_form' => 14,
					'vendor_personal_details_form' => 5,
					'vendor_banking_details_form' => 6,
					'vendor_rss_form' => 23,
				],
				'wams_domain_form_settings'        => [
					'domain_name' => 1,
					'host_name' => 6,
					'domain_url' => 3,
					'domain_project' => 8,
				],
				'wams_project_form_settings'        => [
					'project_name' => 1,
					'project_code' => 6,
					'project_manager' => 4,
				],
				'wams_urls_form_settings'        => [
					'project' => 12,
					'domain' => 9,
					'link' => 18,
					'title' => 2,
					'description' => 16,
					'creator' => 14,
					'pub_date' => 5,
					'thumbnail' => 6,
					'thumbnail_description' => 23,
				],
				'wams_vendor_form_settings'        => [
					'vendor_name' => 1,
					'vendor_arabic_name' => 2,
					'vendor_type' => 5,
					'vendor_project' => 17,
					'vendor_sap_id' => 6,
				],
				'wams_url_ignored_authors'        => [
					'ignored_authors' => '',
				],
				'wams_plugin_settings'       => [
					'domains-settings' => 'off',
					'forms-settings' => 'off',
					'google-analytics' => 'off',
					'telegram-settings' => 'off',
					'test-module' => 'off',
					'task-scheduler' => 'off',
					'db-check' => 'off',
					'vendors-importer' => 'off',
					'rss-fetcher' => 'off',
					'system-debug' => 'off',
					'server-debug' => 'off',
				],
				'wams_plugin_update'                   => 'on',
				'wams_archivable_forms'                        => [],
				'wams_social_media_settings'                        => [],
				'wams_ga_options'                        => [],
				'wams_rss_fetcher_settings'                        => [],
				'wams_rss_fetcher_scheduler'                        => [],
				'wams_task_scheduler'                        => [],
				'wams_telegram_settings'                        => [
					'wams_telegram_api' => [],
					'wams_telegram_channel' => [],
					'wams_telegram_general' => [
						'enable' => 'off'
					]

				],
				'wams_core_pages_settings'                        => [
					'workflow_inbox' => 'inbox',
					'workflow_status' => 'status',
					'tasks_calendar' => 'tasks-calendar',
				],
				'wams_update_settings'            => 'on',
				'wams_debug_settings'            => 'on',
				'wams_rss_settings'            => '',
				'display_name'                          => 'full_name',
			);
			foreach ($this->core_pages as $page_s => $page) {
				$page_id = WAMS()->options()->get_core_page_id($page_s);
				$this->settings_defaults[$page_id] = '';
			}
			$this->settings_defaults = apply_filters('wams_default_settings_values', $this->settings_defaults);
			$this->permalinks = $this->get_core_pages();
		}


		/**
		 * Get WAMS Pages
		 *
		 * @return array
		 */
		function get_core_pages()
		{
			$permalink = array();
			$core_pages = array_keys($this->core_pages);
			if (empty($core_pages)) {
				return $permalink;
			}

			foreach ($core_pages as $page_key) {
				$page_option_key = WAMS()->options()->get_core_page_id($page_key);
				$permalink[$page_key] = WAMS()->options()->get($page_option_key);
			}

			return $permalink;
		}


		/**
		 * @todo make config class not cycled
		 */
		function set_core_page()
		{
			$this->core_pages = apply_filters('wams_core_pages', array(
				'workflow_inbox'           => array('title' => __('Workflow Inbox', 'wams')),
				'workflow_status'          => array('title' => __('Workflow Status', 'wams')),
				'tasks_calendar'       => array('title' => __('Tasks Calendar', 'wams')),
			));
		}
		//end class

		function get_input_defaults()
		{
			return $this->input_defaults = '{
				"pages": [
					{
						"ID": 5126,
						"post_title": "Account",
						"post_content": "[ultimatemember_account]",
						"post_type": "page"
					},
					{
						"ID": 5120,
						"post_title": "Home",
						"post_content": "[home-page]",
						"post_type": "page"
					},
					{
						"ID": 5122,
						"post_title": "Inbox",
						"post_content": "[gravityflow page=\"inbox\"]",
						"post_type": "page"
					},
					{
						"ID": 5134,
						"post_title": "Logout",
						"post_content": "",
						"post_type": "page"
					},
					{
						"ID": 5130,
						"post_title": "Members",
						"post_content": "[ultimatemember form_id=\"6\"]",
						"post_type": "page"
					},
					{
						"ID": 5132,
						"post_title": "Notifications",
						"post_content": "[ultimatemember_notifications]",
						"post_type": "page"
					},
					{
						"ID": 5124,
						"post_title": "Status",
						"post_content": "[gravityflow page=\"status\"]",
						"post_type": "page"
					},
					{
						"ID": 5128,
						"post_title": "User",
						"post_content": "[ultimatemember form_id=\"5\"]",
						"post_type": "page"
					}
				],
				"main_menu": [
					{
						"ID": 5154,
						"menu-item-title": "Account",
						"menu-item-url": "\/input\/account\/",
						"menu-item-parent-id": "0",
						"menu-item-object": "page",
						"menu-item-object-id": "5126",
						"menu-item-type": "post_type",
						"menu-item-type-label": "\u0635\u0641\u062d\u0629",
						"menu-item-icon": ""
					},
					{
						"ID": 5155,
						"menu-item-title": "Home",
						"menu-item-url": "\/input\/home\/",
						"menu-item-parent-id": "0",
						"menu-item-object": "page",
						"menu-item-object-id": "5120",
						"menu-item-type": "post_type",
						"menu-item-type-label": "\u0635\u0641\u062d\u0629",
						"menu-item-icon": ""
					},
					{
						"ID": 5156,
						"menu-item-title": "Inbox",
						"menu-item-url": "\/input\/inbox\/",
						"menu-item-parent-id": "0",
						"menu-item-object": "page",
						"menu-item-object-id": "5122",
						"menu-item-type": "post_type",
						"menu-item-type-label": "\u0635\u0641\u062d\u0629",
						"menu-item-icon": ""
					},
					{
						"ID": 5157,
						"menu-item-title": "Logout",
						"menu-item-url": "\/input\/logout\/",
						"menu-item-parent-id": "0",
						"menu-item-object": "page",
						"menu-item-object-id": "5134",
						"menu-item-type": "post_type",
						"menu-item-type-label": "\u0635\u0641\u062d\u0629",
						"menu-item-icon": ""
					},
					{
						"ID": 5158,
						"menu-item-title": "Members",
						"menu-item-url": "\/input\/members\/",
						"menu-item-parent-id": "0",
						"menu-item-object": "page",
						"menu-item-object-id": "5130",
						"menu-item-type": "post_type",
						"menu-item-type-label": "\u0635\u0641\u062d\u0629",
						"menu-item-icon": ""
					},
					{
						"ID": 5159,
						"menu-item-title": "Notifications",
						"menu-item-url": "\/input\/notifications\/",
						"menu-item-parent-id": "0",
						"menu-item-object": "page",
						"menu-item-object-id": "5132",
						"menu-item-type": "post_type",
						"menu-item-type-label": "\u0635\u0641\u062d\u0629",
						"menu-item-icon": ""
					},
					{
						"ID": 5160,
						"menu-item-title": "Status",
						"menu-item-url": "\/input\/status\/",
						"menu-item-parent-id": "0",
						"menu-item-object": "page",
						"menu-item-object-id": "5124",
						"menu-item-type": "post_type",
						"menu-item-type-label": "\u0635\u0641\u062d\u0629",
						"menu-item-icon": ""
					},
					{
						"ID": 5161,
						"menu-item-title": "User",
						"menu-item-url": "\/input\/user\/",
						"menu-item-parent-id": "0",
						"menu-item-object": "page",
						"menu-item-object-id": "5128",
						"menu-item-type": "post_type",
						"menu-item-type-label": "\u0635\u0641\u062d\u0629",
						"menu-item-icon": ""
					}
				],
				"user_menu": [
					{
						"ID": 5121,
						"menu-item-title": "\u0627\u0644\u0635\u0641\u062d\u0629 \u0627\u0644\u0631\u0626\u064a\u0633\u064a\u0629",
						"menu-item-url": "\/input\/home\/",
						"menu-item-parent-id": "0",
						"menu-item-object": "page",
						"menu-item-object-id": "5120",
						"menu-item-type": "post_type",
						"menu-item-type-label": "\u0635\u0641\u062d\u0629",
						"menu-item-icon": ""
					},
					{
						"ID": 5123,
						"menu-item-title": "\u0627\u0644\u0645\u0647\u0627\u0645",
						"menu-item-url": "\/input\/inbox\/",
						"menu-item-parent-id": "0",
						"menu-item-object": "page",
						"menu-item-object-id": "5122",
						"menu-item-type": "post_type",
						"menu-item-type-label": "\u0635\u0641\u062d\u0629",
						"menu-item-icon": ""
					},
					{
						"ID": 5125,
						"menu-item-title": "\u0627\u0644\u0637\u0644\u0628\u0627\u062a",
						"menu-item-url": "\/input\/status\/",
						"menu-item-parent-id": "0",
						"menu-item-object": "page",
						"menu-item-object-id": "5124",
						"menu-item-type": "post_type",
						"menu-item-type-label": "\u0635\u0641\u062d\u0629",
						"menu-item-icon": ""
					},
					{
						"ID": 5127,
						"menu-item-title": "\u0627\u0644\u062d\u0633\u0627\u0628",
						"menu-item-url": "\/input\/account\/",
						"menu-item-parent-id": "0",
						"menu-item-object": "page",
						"menu-item-object-id": "5126",
						"menu-item-type": "post_type",
						"menu-item-type-label": "\u0635\u0641\u062d\u0629",
						"menu-item-icon": ""
					},
					{
						"ID": 5129,
						"menu-item-title": "\u0627\u0644\u0645\u0644\u0641 \u0627\u0644\u0634\u062e\u0635\u064a",
						"menu-item-url": "\/input\/user\/",
						"menu-item-parent-id": "0",
						"menu-item-object": "page",
						"menu-item-object-id": "5128",
						"menu-item-type": "post_type",
						"menu-item-type-label": "\u0635\u0641\u062d\u0629",
						"menu-item-icon": ""
					},
					{
						"ID": 5131,
						"menu-item-title": "\u0627\u0644\u0645\u0648\u0638\u0641\u064a\u0646",
						"menu-item-url": "\/input\/members\/",
						"menu-item-parent-id": "0",
						"menu-item-object": "page",
						"menu-item-object-id": "5130",
						"menu-item-type": "post_type",
						"menu-item-type-label": "\u0635\u0641\u062d\u0629",
						"menu-item-icon": ""
					},
					{
						"ID": 5133,
						"menu-item-title": "\u0627\u0644\u0627\u0634\u0639\u0627\u0631\u0627\u062a",
						"menu-item-url": "\/input\/notifications\/",
						"menu-item-parent-id": "0",
						"menu-item-object": "page",
						"menu-item-object-id": "5132",
						"menu-item-type": "post_type",
						"menu-item-type-label": "\u0635\u0641\u062d\u0629",
						"menu-item-icon": ""
					},
					{
						"ID": 5135,
						"menu-item-title": "\u062e\u0631\u0648\u062c",
						"menu-item-url": "\/input\/logout\/",
						"menu-item-parent-id": "0",
						"menu-item-object": "page",
						"menu-item-object-id": "5134",
						"menu-item-type": "post_type",
						"menu-item-type-label": "\u0635\u0641\u062d\u0629",
						"menu-item-icon": ""
					}
				]
			}';
		}
	}
}
