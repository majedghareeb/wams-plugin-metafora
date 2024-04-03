<?php

namespace wams\admin;

use wams\common\Logger;
use wams\admin\modules\Task_Scheduler;
use WP_Site_Health;
use WAMS;
use wams\admin\modules\Input_Site_Setup;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\Admin')) {

	/**
	 * Class Admin
	 * @package um\admin
	 */
	class Admin extends Admin_Functions
	{

		/**
		 * @var string
		 */
		public $templates_path;

		/**
		 * @var array
		 */
		public $builder_input;

		/**
		 * @var array
		 */
		public $modules = [];
		public $pages = [];

		/**
		 * Admin constructor.
		 */
		public function __construct()
		{
			$this->templates_path = WAMS_PATH . 'includes/admin/templates/';
			$this->init_variables();
			$pages = get_pages(array('post_type' => 'page'));
			if ($pages) {
				foreach ($pages as $page) {
					$this->pages[$page->post_name] = $page->post_title;
				}
			}


			if (is_blog_admin()) {
				$prefix = is_network_admin() ? 'network_admin_' : '';
				add_filter("{$prefix}plugin_action_links_" . WAMS_PLUGIN, array(&$this, 'plugin_links'));
			}
		}

		/**
		 * Init admin action/filters + request handlers
		 */
		public function admin_init()
		{

			// $this->admin_menu()->register();
			// if (isset($_REQUEST['page']) && strpos($_REQUEST['page'], 'wams') === 0) {

			if (!empty($_REQUEST['wams_adm_action']) && is_admin() && current_user_can('manage_options')) {
				$action = sanitize_key($_REQUEST['page']);
				if (empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], $action)) {
					wp_die(esc_attr__('Security Check', 'wams'));
				}
				do_action('wams_admin_do_action__', $action);
				do_action("wams_admin_do_action__{$action}", $action);
			}
		}

		public function includes()
		{
			// if (is_blog_admin()) {
			if (is_blog_admin()) {
				$this->admin_dashboard();
				$this->register_modules();
				$this->enqueue();
				$this->notices();
			}
		}

		/**
		 * @since 1.0.0
		 *
		 * @return core\Admin_Menu()
		 */
		public function admin_menu()
		{
			if (empty(WAMS()->classes['admin_menu'])) {
				WAMS()->classes['admin_menu'] = new core\Admin_Menu();
			}
			return WAMS()->classes['admin_menu'];
		}

		/**
		 * Init Common Modules bewteen All Sites
		 */
		public function init_variables()
		{
			$this->modules = [
				[
					'name' => 'domains-settings',
					'title' => __('Domain Settings', 'wams'),
					'blog' => '',
					'default' => true,
				],
				[
					'name' => 'site-setup',
					'title' => __('Site Setup', 'wams'),
					'blog' => '',
					'default' => true,
				],
				[
					'name' => 'web-notifications',
					'title' => __('Web Notifications Settings', 'wams'),
					'blog' => '',
					'default' => true,
				],
				[
					'name' => 'telegram-settings',
					'title' => __('Telegram Notifications', 'wams'),
					'blog' => '',
					'default' => true,
				],
				[
					'name' => 'db-cleanup',
					'title' => __('DB Cleanup', 'wams'),
					'blog' => '',
					'default' => true,
				],
				[
					'name' => 'search-vendor-field',
					'title' => __('Search Vendor Field', 'wams'),
					'blog' => '',
					'default' => true,
				],
				[
					'name' => 'rss-fetcher',
					'title' => __('RSS Fetcher', 'wams'),
					'blog' => '',
					'default' => true,
				],
				[
					'name' => 'task-scheduler',
					'title' => __('Tasks Scheduler', 'wams'),
					'blog' => '',
					'default' => true,
				],
				[
					'name' => 'test-module',
					'title' => __('TEST PAGE', 'wams'),
					'blog' => '',
					'default' => true,
				],
				[
					'name' => 'google-analytics',
					'title' => __('Google Analytics', 'wams'),
					'blog' => '',
					'default' => true,
				],

				// [
				// 	'name' => 'system-debug',
				// 	'title' => __('System Debug', 'wams'),
				// 	'blog' => '',
				// 	'default' => true,
				// ],
				// [
				// 	'name' => 'search-client-field',
				// 	'title' => __('Search Client Field', 'wams'),
				// 	'blog' => '',
				// 	'default' => true,
				// ]
			];
			$current_blog = get_current_blog_id();
			switch ($current_blog) {
				case WAMS_MAIN_BLOG_ID:
					$main_modules =
						[
							[
								'name' => 'forms-settings',
								'title' => __('Forms Settings', 'wams'),
								'blog' => '',
								'default' => true,
							],
							[
								'name' => 'vendors-importer',
								'title' => __('Vendors Importer', 'wams'),
								'blog' => '',
								'default' => true,
							],


							[
								'name' => 'system-debug',
								'title' => __('System Debug', 'wams'),
								'blog' => '',
								'default' => true,
							],

							[
								'name' => 'server-debug',
								'title' => __('Server Debug', 'wams'),
								'blog' => '',
								'default' => true,
							],
							// [
							// 	'name' => 'server-debug',
							// 	'title' => __('Server Debug', 'wams'),
							// 	'blog' => '',
							// 	'default' => true,
							// ],
						];
					$this->modules = array_merge($this->modules, $main_modules,);
					break;
				default:
					// $subsites_modules =
					// 	[
					// 		[
					// 			'name' => 'subsite-forms-settings',
					// 			'title' => __('Forms Settings', 'wams'),
					// 			'blog' => '',
					// 			'default' => true,
					// 		],
					// 	];
					// $this->modules = array_merge($this->modules, $subsites_modules,);
					break;
			}
		}



		/**
		 * @since 1.0.0
		 *
		 * @return Admin_Dashboard
		 */
		public function admin_dashboard()
		{
			if (empty(WAMS()->classes['admin_dashboard'])) {
				WAMS()->classes['admin_dashboard'] = new Admin_Dashboard();
			}
			return WAMS()->classes['admin_dashboard'];
		}

		/**
		 * @since 1.0.0
		 *
		 * @return Register_Modules
		 */
		public function register_modules()
		{
			$wams_plugin_settings = get_option('wams_plugin_settings');
			foreach ($this->modules as $module) {
				if ($module['default'])
					if (empty(WAMS()->classes[$module['name']])) {
						$className =  "wams\\admin\\modules" . $module['blog'] . "\\" . str_replace('-', '_', ucwords($module['name'], '-'));
						// Check if the class exists before instantiating
						if (class_exists($className) && $module['default']) {
							if ($wams_plugin_settings && isset($wams_plugin_settings[$module['name']]) && $wams_plugin_settings[$module['name']] == 'on') {

								$instance = new $className();
								WAMS()->classes[] = $instance;
							}
							// Instantiate the class
						} else {
							// Handle the case where the class doesn't exist
							$modules['default'] = false;
							$message =  __('Class ' . $className . ' does not exist');
							WAMS()->admin()->notices()->add_notice(
								'modules_' . $className,
								array(
									'class'       => 'error',
									'message'     => $message,
									'dismissible' => true,
								),
								10
							);
						}
					}
				// return WAMS()->classes['register_modules'];
			}

			// WAMS()->admin()->notices()->display_notice('modules');
		}

		/**
		 *
		 */
		public function manual_upgrades_request()
		{
			$last_request = get_option('wams_last_manual_upgrades_request', false);

			if (empty($last_request) || time() > $last_request + DAY_IN_SECONDS) {

				if (is_multisite()) {
					$blogs_ids = get_sites();
					foreach ($blogs_ids as $b) {
						switch_to_blog($b->blog_id);
						wp_clean_update_cache();
						update_option('wams_last_manual_upgrades_request', time());
						restore_current_blog();
					}
				} else {
					wp_clean_update_cache();

					update_option('wams_last_manual_upgrades_request', time());
				}

				$url = add_query_arg(
					array(
						'page'   => 'wams',
						'update' => 'wams_got_updates',
					),
					admin_url('admin.php')
				);
			} else {
				$url = add_query_arg(
					array(
						'page'   => 'wams',
						'update' => 'wams_often_updates',
					),
					admin_url('admin.php')
				);
			}
			wp_safe_redirect($url);
			exit;
		}

		/**
		 * Core pages installation.
		 */
		public function install_core_pages()
		{
			WAMS()->setup()->install_default_pages();

			//check empty pages in settings
			$empty_pages = array();

			$pages = WAMS()->config()->permalinks;
			if ($pages && is_array($pages)) {
				foreach ($pages as $slug => $page_id) {
					$page = get_post($page_id);

					if (!isset($page->ID) && array_key_exists($slug, WAMS()->config()->core_pages)) {
						$empty_pages[] = $slug;
					}
				}
			}

			//if there aren't empty pages - then hide pages notice
			if (empty($empty_pages)) {
				$hidden_notices   = get_option('wams_hidden_admin_notices', array());
				$hidden_notices[] = 'wrong_pages';

				update_option('wams_hidden_admin_notices', $hidden_notices);
			}

			$url = add_query_arg(array('page' => 'wams_options'), admin_url('admin.php'));
			wp_safe_redirect($url);
			exit;
		}

		/**
		 * Purge temp uploads dir.
		 */
		public function purge_temp()
		{
			WAMS()->files()->remove_dir(WAMS()->files()->upload_temp);

			$url = add_query_arg(
				array(
					'page'   => 'wams',
					'update' => 'wams_purged_temp',
				),
				admin_url('admin.php')
			);
			wp_safe_redirect($url);
			exit;
		}
		/**
		 * Add any custom links to plugin page.
		 *
		 * @param array $links
		 *
		 * @return array
		 */
		public function plugin_links($links)
		{
			$more_links[] = '<a href="http://www.rakami.net/">' . esc_html__('Docs', 'wams') . '</a>';
			$more_links[] = '<a href="' . admin_url() . 'admin.php?page=wams">' . esc_html__('Settings', 'wams') . '</a>';

			$links = $more_links + $links;
			return $links;
		}

		/**
		 * @since 2.7.0
		 *
		 * @return Enqueue
		 */
		public function enqueue()
		{
			if (empty(WAMS()->classes['wams\admin\enqueue'])) {
				WAMS()->classes['wams\admin\enqueue'] = new Enqueue();
			}
			return WAMS()->classes['wams\admin\enqueue'];
		}

		/**
		 * @since 1.0.0
		 *
		 * @return core\Admin_Notices()
		 */
		public function notices()
		{
			if (empty(WAMS()->classes['admin_notices'])) {
				WAMS()->classes['admin_notices'] = new core\Admin_Notices();
			}
			return WAMS()->classes['admin_notices'];
		}

		/**
		 * @since 1.0.0
		 *
		 * @return Screen
		 */
		public function screen()
		{
			if (empty(WAMS()->classes['wams\admin\screen'])) {
				WAMS()->classes['wams\admin\screen'] = new Screen();
			}
			return WAMS()->classes['wams\admin\screen'];
		}
		/**
		 * @since 1.0.0
		 *
		 * @return core\System_Setup
		 */
		public function system_setup()
		{
			if (empty(WAMS()->classes['wams\admin\core\system_setup'])) {
				WAMS()->classes['wams\admin\core\system_setup'] = new core\Admin_System_Setup();
			}
			return WAMS()->classes['wams\admin\core\system_setup'];
		}

		/**
		 * @since 1.0.0
		 *
		 * @return AJAX_Handler
		 */
		public function ajax_handler()
		{
			if (empty(WAMS()->classes['wams\admin\ajax_handler'])) {
				WAMS()->classes['wams\admin\ajax_handler'] = new AJAX_Handler();
			}
			return WAMS()->classes['wams\admin\ajax_handler'];
		}
	}
}
