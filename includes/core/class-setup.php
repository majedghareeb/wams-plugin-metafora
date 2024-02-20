<?php

namespace wams\core;


if (!defined('ABSPATH')) exit;


if (!class_exists('wams\core\Setup')) {


	/**
	 * Class Setup
	 *
	 * @package wams\core
	 */
	class Setup
	{


		/**
		 * Setup constructor.
		 */
		function __construct()
		{
		}


		/**
		 * Run setup
		 */
		function run_setup()
		{
			$this->create_db();
			$this->install_basics();
			// $this->install_default_forms();
			$this->set_default_settings();
		}


		/**
		 * Create custom DB tables
		 */
		function create_db()
		{
			global $wpdb;

			$user_logins_table_name =  $wpdb->prefix . 'wams_user_logins';
			$wams_notifications =  $wpdb->prefix . 'wams_notifications';

			$charset_collate = $wpdb->get_charset_collate();
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			if ($this->check_db_table_if_exists($user_logins_table_name)) {
				$sql = "CREATE TABLE {$wpdb->prefix}wams_user_logins (
					`id` bigint(20) unsigned NOT NULL auto_increment,
					`user_id` bigint(20) unsigned NOT NULL default '0',
					`user_login` varchar(255) default NULL,
					`ip_address` varchar(15) default NULL,
					`browser` varchar(255) default NULL,
					PRIMARY KEY  (id),
					KEY `user_id_indx` (`user_id`)
					) $charset_collate;";
				dbDelta($sql);
			}
			if ($this->check_db_table_if_exists($wams_notifications)) {
				$sql2 = "CREATE TABLE {$wpdb->prefix}wams_notifications (
					id int(11) unsigned NOT NULL auto_increment,
					time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					user tinytext NOT NULL,
					status tinytext NOT NULL,
					photo varchar(255) DEFAULT '' NOT NULL,
					type tinytext NOT NULL,
					url varchar(255) DEFAULT '' NOT NULL,
					content text NOT NULL,
					PRIMARY KEY  (id)
					) $charset_collate;";

				dbDelta($sql2);
			}
		}

		function check_db_table_if_exists($table_name)
		{
			global $wpdb;
			$sql = "SHOW TABLES LIKE '$table_name'";
			$result = $wpdb->get_var($sql); // Adjust this line if you're not using WordPress

			if ($result === false) {
				// Error occurred while checking for table existence
				WAMS()->Common()->Logger()::error("Error: " . $wpdb->error); // Adjust this line if you're not using WordPress
				return false;
			}

			if ($result !== null) {
				// Table already exists
				WAMS()->Common()->Logger()::info("Table '$table_name' already exists.");
				return false;
			}
			return true;
		}




		/**
		 * Basics
		 */
		function install_basics()
		{
			if (!get_theme_mod('topbar_theme_mode_button')) {
				set_theme_mod('topbar_theme_mode_button', 1);
				set_theme_mod('topbar_fullscreen_button', 1);
				set_theme_mod('topbar_language_button', 0);
				set_theme_mod('topbar_icons_box_button', 0);
				set_theme_mod('topbar_notifications_button', 0);
				// update_option('wams_options', 'no key');
			}
		}


		/**
		 * Default Forms
		 */
		function install_default_forms()
		{
			if (current_user_can('manage_options') && !get_option('wams_is_installed')) {
				$options = get_option('wams_options', array());

				update_option('wams_is_installed', 1);

				//Install default options
				foreach (WAMS()->config()->settings_defaults as $key => $value) {
					$options[$key] = $value;
				}

				// Install Core Forms
				foreach (WAMS()->config()->core_forms as $id) {

					/**
					If page does not exist
					Create it
					 **/
					$page_exists = WAMS()->query()->find_post_id('wams_form', '_wams_core', $id);
					if (!$page_exists) {

						if ($id == 'register') {
							$title = 'Default Registration';
						} else if ($id == 'login') {
							$title = 'Default Login';
						} else {
							$title = 'Default Profile';
						}

						$form = array(
							'post_type' 	  	=> 'wams_form',
							'post_title'		=> $title,
							'post_status'		=> 'publish',
							'post_author'   	=> get_current_user_id(),
						);

						$form_id = wp_insert_post($form);

						foreach (WAMS()->config()->core_form_meta[$id] as $key => $value) {
							if ($key == '_wams_custom_fields') {
								$array = unserialize($value);
								update_post_meta($form_id, $key, $array);
							} else {
								update_post_meta($form_id, $key, $value);
							}
						}

						$core_forms[$id] = $form_id;
					}
					/** DONE **/
				}

				if (isset($core_forms)) {
					update_option('wams_core_forms', $core_forms);
				}

				// Install Core Directories
				foreach (WAMS()->config()->core_directories as $id) {

					/**
					If page does not exist
					Create it
					 **/
					$page_exists = WAMS()->query()->find_post_id('wams_directory', '_wams_core', $id);
					if (!$page_exists) {

						$title = 'Members';

						$form = array(
							'post_type' 	  	=> 'wams_directory',
							'post_title'		=> $title,
							'post_status'		=> 'publish',
							'post_author'   	=> get_current_user_id(),
						);

						$form_id = wp_insert_post($form);

						foreach (WAMS()->config()->core_directory_meta[$id] as $key => $value) {
							if ($key == '_wams_custom_fields') {
								$array = unserialize($value);
								update_post_meta($form_id, $key, $array);
							} else {
								update_post_meta($form_id, $key, $value);
							}
						}

						$core_directories[$id] = $form_id;
					}
					/** DONE **/
				}

				if (isset($core_directories)) {
					update_option('wams_core_directories', $core_directories);
				}

				update_option('wams_options', $options);
			}
		}


		/**
		 * Install Pre-defined pages with shortcodes
		 */
		public function install_default_pages()
		{
			if (!current_user_can('manage_options')) {
				return;
			}

			$core_forms       = get_option('wams_core_forms', array());
			$setup_shortcodes = array_merge($core_forms);

			//Install Core Pages
			$core_pages = array();
			foreach (WAMS()->config()->core_pages as $slug => $array) {

				$page_exists = WAMS()->query()->find_post_id('page', '_wams_core', $slug);
				if ($page_exists) {
					$core_pages[$slug] = $page_exists;
					continue;
				}

				//If page does not exist - create it
				if ('logout' === $slug) {
					$content = '';
				} elseif ('account' === $slug) {
					$content = '[wams_account]';
				} elseif ('password-reset' === $slug) {
					$content = '[wams_password]';
				} elseif ('user' === $slug) {
					$content = '[wams form_id="' . $setup_shortcodes['profile'] . '"]';
				} else {
					$content = '[wams form_id="' . $setup_shortcodes[$slug] . '"]';
				}

				/**
				 * Filters WAMS predefined pages content when set up the predefined page.
				 *
				 * @param {string} $content Predefined page content.
				 * @param {string} $slug    Predefined page slug (key).
				 *
				 * @return {string} Predefined page content.
				 *
				 * @since 2.1.0
				 * @hook wams_setup_predefined_page_content
				 *
				 * @example <caption>Set WAMS predefined pages content with key = 'my_page_key'.</caption>
				 * function my_wams_setup_predefined_page_content( $content, $slug ) {
				 *     // your code here
				 *     if ( 'my_page_key' === $slug ) {
				 *         $content = __( 'My Page content', 'my-translate-key' );
				 *     }
				 *     return $pages;
				 * }
				 * add_filter( 'wams_setup_predefined_page_content', 'my_wams_setup_predefined_page_content' );
				 */
				$content = apply_filters('wams_setup_predefined_page_content', $content, $slug);

				$user_page = array(
					'post_title'     => $array['title'],
					'post_content'   => $content,
					'post_name'      => $slug,
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'post_author'    => get_current_user_id(),
					'comment_status' => 'closed',
				);

				$post_id = wp_insert_post($user_page);
				update_post_meta($post_id, '_wams_core', $slug);

				$core_pages[$slug] = $post_id;
			}

			$options = get_option('wams_options', array());

			foreach ($core_pages as $slug => $page_id) {
				$key             = WAMS()->options()->get_core_page_id($slug);
				$options[$key] = $page_id;
			}

			update_option('wams_options', $options);

			// reset rewrite rules after first install of core pages
			WAMS()->rewrite()->reset_rules();
		}


		/**
		 * Set default WAMS settings
		 */
		function set_default_settings()
		{


			foreach (WAMS()->config()->settings_defaults as $key => $value) {
				$option = get_option($key, []);
				//set new options to default
				if (!$option || empty($option)) {
					add_option($key, $value);
				}
			}
		}


		/**
		 * Insert Menu Item and Related Page
		 *
		 * @param Int $menu_id
		 * @param Array $menu_item
		 * @return Int|False Inserted Menu ID or False if failed
		 */
		public function insert_menu_and_page($menu_id, $menu_item)
		{

			$inserted_menu =  wp_update_nav_menu_item($menu_id, 0, [
				'menu-item-title' => $menu_item['title'],
				'menu-item-url' => $menu_item['url'],
				'menu-item-status' => 'publish',
				'menu-item-parent' => $menu_item['parent'],
			]);
			if (!is_wp_error($inserted_menu)) {
				$icon = ($menu_item['icon'] == '') ? $menu_item['icon'] : 'bi bi-diamond-fill';
				update_post_meta($inserted_menu, '_menu_item_icon', $icon);
				echo 'Menu ID ' . $inserted_menu . ' : ' . $menu_item['title'] . ' has been inserted <br>';

				Logger::info('Menu ID : ' . $inserted_menu . ' has been inserted!', 'System Setup');
				if ($menu_item['type'] == 'post_type' && $menu_item['object'] == 'page') {
					$connected_page = $menu_item['connected_page'];
					$new_page = wp_insert_post($connected_page);
					if (!is_wp_error($new_page)) {
						$inserted_menu =  wp_update_nav_menu_item($menu_id, $inserted_menu, [
							'menu-item-object' => $menu_item['object'],
							'menu-item-object-id' => $new_page,
							'menu-item-type' => $menu_item['type'],
							'menu-item-type-label' => $menu_item['type_label'],
						]);
						echo 'Page ID ' . $new_page . ' : ' . $connected_page['post_title'] . ' has been inserted <br>';
						Logger::info('Page ID : ' . $new_page . ' has been inserted!', 'System Setup');
					}
				}
				return $inserted_menu;
			} else {
				echo 'error';
				return false;
			}
		}
		/**
		 * Get Insert Menus JSON File  and inserted
		 *
		 * @return void
		 */
		public function insert_exported_menus($file)
		{
			// delete current pages
			$pages = get_pages();
			foreach ($pages as $page) {
				if ($page->post_title !== 'test')
					wp_delete_post($page->ID);
			}
			$current_menus = wp_get_nav_menus();
			foreach ($current_menus as $current_menu) {
				$current_menus[$current_menu->name] = $current_menu->term_id;
			}
			// echo '<pre>' . print_r($current_menus, true) . '</pre>';
			$menu_items = [];
			if ($file == null) return false;
			$json_data = file_get_contents(WAMS_INPUT_PATH . 'assets/export/' . $file);
			if (!$json_data) return false;
			// Decode the JSON data
			$menu_items = json_decode($json_data, true);

			if (!is_array($menu_items)) return false;

			foreach ($menu_items as $name => $items) {

				// echo '<pre>' . print_r($name, true) . '</pre>';
				$menu_id = (array_key_exists($name, $current_menus))  ?  $current_menus[$name] : wp_create_nav_menu($name);

				echo $name . ' has menu id ' . $menu_id . ' <br>';
				foreach ($items as $menu_item) {

					$parent = $menu_item['parent'];
					if ($parent  == 0) {
						$parent_id = $this->insert_menu_and_page($menu_id, $menu_item);
						// echo '<pre>' . print_r($menu_item, true) . '</pre>';

						if (!$parent_id) {
							echo 'Failed To Insert Parent menu item!';
							Logger::error('Failed To Insert Parent menu item!', 'System Setup');
						} else {
							foreach ($items as $item) {
								if ($item['parent'] == $menu_item['id']) {
									// echo '<pre>' . print_r($item, true) . '</pre>';
									$child_id =  $this->insert_menu_and_page($menu_id, $item);

									if (!$child_id) {
										echo 'Failed To Insert Child menu item!';
										Logger::error('Failed To Insert Child menu item!', 'System Setup');
									}
								}
							}
						}
					}
				}
			}
		}

		/**
		 * ImportGravity Forms from JSON File
		 *
		 * @return void
		 */
		public function import_gravity_forms($file)
		{
			$forms_json_file = WAMS_INPUT_PATH . 'assets/export/' . $file;
			if (file_exists($forms_json_file)) {
				echo 'File exisits';
				$imported_forms = GFExport::import_file($forms_json_file);
				if ($imported_forms > 0) {
					Logger::info('No. of Forms:  ' . $imported_forms . ' has been imported', 'System Setup');
					echo $imported_forms . ' has been imported';
					$this->create_page_for_forms();
				}
			}
		}
		/**
		 * Created Pages for Each Form
		 *
		 * @return void
		 */
		public function create_page_for_forms()
		{
			$forms = GFFormsModel::get_forms();
			if (!empty($forms)) {
				foreach ($forms as $form) {
					$page = [
						'post_title' => $form->title,
						'post_name' => $form->title,
						'post_status' => 'publish',
						'post_author'  => get_current_user_id(),
						'post_type'  => 'page',
						'post_content' => '[gravityform id="' . $form->id . '" title="false" description="false"]',
					];
					$new_page = wp_insert_post($page);
					if (!is_wp_error($new_page)) {
						echo 'New Page with ID: ' . $new_page . ' Has been created';
						Logger::info('New Page with ID: ' . $new_page . ' has been created', 'System Setup');
					}
				}
			}
		}
		/**
		 * Created Pages for Each View
		 *
		 * @return void
		 */
		public function create_page_for_views()
		{
			$views = GVCommon::get_all_views();
			if (!empty($views)) {
				foreach ($views as $view) {
					$page = [
						'post_title' => $view->post_title,
						'post_name' => $view->post_name,
						'post_status' => $view->post_status,
						'post_author'  => get_current_user_id(),
						'post_type'  => 'page',
						'post_content' => '[gravityview id="' . $view->ID . '"]',
					];
					$new_page = wp_insert_post($page);
					if (!is_wp_error($new_page)) {
						echo 'View Page with ID: ' . $new_page . ' Has been created';
						Logger::info('View Page with ID: ' . $new_page . ' has been created', 'System Setup');
					}
				}
			}
		}
		/**
		 * Created Menu Item
		 *
		 * @return void
		 */
		public function create_nav_menu_items($page_id, $page_title)
		{


			$pages = get_pages();
			foreach ($pages as $page) {
				// echo '<pre>' . print_r($menus, true) . '</pre>';
				$new_menu =  wp_update_nav_menu_item($main_menu_id, 0, [
					'menu-item-title' => $page->post_title,
					'menu-item-status' => $page->post_status,
					'menu-item-parent' => 0,
					'menu-item-object' => 'page',
					'menu-item-object-id' => $page->ID,
					'menu-item-type' => 'post_type',
					'menu-item-type-label' => 'page',
				]);


				if (!is_wp_error($new_menu)) {
					update_post_meta($new_menu, '_menu_item_icon', 'bi bi-diamond-fill');

					Logger::info('Menu ID : ' . $new_menu . ' has been inserted!', 'System Setup');
				} else {
					echo 'error creating a  menu item';
				}
			}
			foreach ($default_menu_items as $menu_item) {
				if (strpos($menu_item['url'], $page['post_name']) !== false) {
					echo $menu_item['title'] . '<br>';
					$new_menu =  wp_update_nav_menu_item($user_menu_id, 0, [
						'menu-item-title' => $menu_item['title'],
						'menu-item-status' => 'publish',
						'menu-item-object' => $menu_item['object'],
						'menu-item-object-id' => $new_page,
						'menu-item-type' => $menu_item['type'],
						'menu-item-type-label' => $menu_item['type_label'],
					]);
					if (!is_wp_error($new_menu)) {
						update_post_meta($new_menu, '_menu_item_icon', $menu_item['icon']);

						Logger::info('Menu ID : ' . $new_menu . ' has been inserted!', 'System Setup');
					} else {
						echo 'error creating a  menu item';
					}
				}
				# code...
			}
		}
		public function create_page_menu_item($page_id, $title, $menu_id, $icon = 'bi bi-diamond-fill')
		{
			$new_menu =  wp_update_nav_menu_item($menu_id, 0, [
				'menu-item-title' => $title,
				'menu-item-status' => 'publish',
				'menu-item-object-id' => $page_id,
				'menu-item-parent' => 0,
				'menu-item-object' => 'page',
				'menu-item-type' => 'post_type',
				'menu-item-type-label' => 'page',
			]);
			if (!is_wp_error($new_menu)) {
				update_post_meta($new_menu, '_menu_item_icon', $icon);
				Logger::info('Menu ID : ' . $new_menu . ' has been inserted!', 'System Setup');
			} else {
				echo 'error creating a  menu item';
			}
		}
		public function create_default_pages()
		{
			$current_menus = wp_get_nav_menus();
			if (!$current_menus) {
				$current_menus = $this->create_nav_menu();
			}
			foreach ($current_menus as $menu) {
				if ($menu->name ==  'Main Menu') {
					$main_menu_id = $menu->term_id;
				}
				// check if User menu exists
				if ($menu->name ==  'User Menu') {
					$user_menu_id = $menu->term_id;
				}
			}
			$default_pages = [
				[
					"post_title" => "Home",
					"post_name" => "home",
					"post_status" => "publish",
					"post_content" => "[home-page]",
					"post_template" => "",
					"post_parent" => 0,
					"post_author" => 1,
					"post_type" => "page"
				],
				[
					"post_title" => "Inbox",
					"post_name" => "inbox",
					"post_status" => "publish",
					"post_content" => '[gravityflow page="inbox"]',
					"post_template" => "",
					"post_parent" => 0,
					"post_author" => 1,
					"post_type" => "page"
				],
				[
					"post_title" => "Status",
					"post_name" => "status",
					"post_status" => "publish",
					"post_content" => '[gravityflow page="status"]',
					"post_template" => "",
					"post_parent" => 0,
					"post_author" => 1,
					"post_type" => "page"
				],
				[
					"post_title" => "Account",
					"post_name" => "account",
					"post_status" => "publish",
					"post_content" => "[ultimatemember_account]",
					"post_template" => "",
					"post_parent" => 0,
					"post_author" => 1,
					"post_type" => "page"
				],
				[
					"post_title" => "User",
					"post_name" => "user",
					"post_status" => "publish",
					"post_content" => "[ultimatemember form_id=\"5\"]",
					"post_template" => "",
					"post_parent" => 0,
					"post_author" => 1,
					"post_type" => "page"
				],
				[
					"post_title" => "Members",
					"post_name" => "members",
					"post_status" => "publish",
					"post_content" => "[ultimatemember form_id=\"6\"]",
					"post_template" => "",
					"post_parent" => 0,
					"post_author" => 1,
					"post_type" => "page"
				],
				[
					"post_title" => "Notifications",
					"post_name" => "notifications",
					"post_status" => "publish",
					"post_content" => "[ultimatemember_notifications]",
					"post_template" => "",
					"post_parent" => 0,
					"post_author" => 1,
					"post_type" => "page"
				],
				[
					"post_title" => "Logout",
					"post_name" => "logout",
					"post_status" => "publish",
					"post_content" => "",
					"post_template" => "",
					"post_parent" => 0,
					"post_author" => 1,
					"post_type" => "page"
				]
			];
			$default_menu_items = [
				[
					"title" => "خروج",
					"url" => "\/logout\/",
					"parent" => "0",
					"object" => "page",
					"type" => "post_type",
					"type_label" => "UM Logout",
					"icon" => "bi bi-door-closed-fill",
				],
				[
					"title" => "الاشعارات",
					"url" => "\/notifications\/",
					"parent" => "0",
					"object" => "page",
					"type" => "post_type",
					"type_label" => "UM Notifications",
					"icon" => "bi bi-bell-fill me-2",
				],
				[
					"title" => "الموظفين",
					"url" => "\/members\/",
					"parent" => "0",
					"object" => "page",
					"type" => "post_type",
					"type_label" => "UM Members",
					"icon" => "bi bi-people me-2",
				],
				[
					"title" => "الملف الشخصي",
					"url" => "\/user\/",
					"parent" => "0",
					"object" => "page",
					"type" => "post_type",
					"type_label" => "UM User",
					"icon" => "bi bi-person-badge-fill me-2",
				],
				[
					"title" => "الحساب",
					"url" => "\/account\/",
					"parent" => "0",
					"object" => "page",
					"type" => "post_type",
					"type_label" => "UM Account",
					"icon" => "bi bi-gear-fill me-2",
				],
				[
					"title" => "الطلبات",
					"url" => "\/status\/",
					"parent" => "0",
					"object" => "page",
					"type" => "post_type",
					"type_label" => "Page",
					"icon" => "bi bi-hourglass-top me-2",
				],
				[
					"title" => "المهام",
					"url" => "\/inbox\/",
					"parent" => "0",
					"object" => "page",
					"type" => "post_type",
					"type_label" => "Page",
					"icon" => "bi bi-card-checklist me-2",
				],
				[
					"title" => "الصفحة الرئيسية",
					"url" => "\/home\/",
					"parent" => "0",
					"object" => "page",
					"type" => "post_type",
					"type_label" => "Front Page",
					"icon" => "bi bi-house",
				],
			];
			foreach ($default_pages as $page) {
				// echo '<pre>' . print_r($page, true) . '</pre>';
				$new_page = wp_insert_post($page);
				if (!is_wp_error($new_page)) {
					foreach ($default_menu_items as $menu) {
						if (strpos($menu['url'], $page['post_name']) !== false) {
							$this->create_page_menu_item($new_page, $menu['title'], $user_menu_id, $menu['icon']);
						}
					}
					echo 'New Page with ID: ' . $new_page . ' Has been created';
					Logger::info('New Page with ID: ' . $new_page . ' has been created', 'System Setup');
				}
			}
		}
		/**
		 * Get Gravityview JSON File  and inserted
		 *
		 * @return void
		 */
		public function import_gravityview($view, $connected_form)
		{
			// delete current pages
			$gv_data = [];
			$gv_posts = get_posts([
				'post_type' => 'gravityview',
				'post_status' => 'publish',
				'numberposts' => -1
			]);
			// foreach ($gv_posts as $view) {
			//     wp_delete_post($view->ID);
			// }

			// echo '<pre>' . print_r($connected_form, true) . '</pre>';
			$gv_items = [];
			if ($view == null) return false;
			$json_data = file_get_contents(WAMS_INPUT_PATH . 'assets/export/exported_gv.json');
			if (!$json_data) return false;
			// Decode the JSON data
			$gv_items = json_decode($json_data, true);

			if (!is_array($gv_items)) return false;

			foreach ($gv_items as $gv_item) {

				if ($view == $gv_item['conntected_form_id']) :
					$gv = [
						'post_title' => $gv_item['post_title'],
						'post_name' => $gv_item['post_name'],
						'post_status' => $gv_item['post_status'],
						'post_author' => $gv_item['post_author'],
						'post_type' => $gv_item['post_type'],
					];
					$old_gv_id = $gv_item['ID'];
					$gv_meta = $gv_item['post_meta'];
					// echo '<pre>' . print_r($gv_meta, true) . '</pre>';
					$new_gv_id = wp_insert_post($gv);
					if (!is_wp_error($new_gv_id)) {
						foreach ($gv_meta as $key => $values) {
							foreach ($values as $gv_meta_fields) {
								echo '<pre> fields: ' . print_r($gv_meta_fields, true) . '</pre>';
								if (is_serialized($gv_meta_fields)) {
									switch ($key) {
										case '_gravityview_directory_fields':
											$fields = unserialize($gv_meta_fields);
											foreach ($fields as $k1 => $v1) {

												foreach ($v1 as $k2 => $v2) {
													foreach ($v2 as $k3 => $v3) {
														if ($k3 == 'form_id') {
															$fields[$k1][$k2][$k3] = $connected_form;
														}
													}
												}
											}
											// echo '<pre> fields: ' . print_r($fields, true) . '</pre>';
											add_post_meta($new_gv_id, $key, $fields);
											break;
										case '_gravityview_directory_widgets':
											$widgets = unserialize($gv_meta_fields);
											foreach ($widgets as $k1 => $v1) {
												foreach ($v1 as $k2 => $v2) {
													foreach ($v2 as $k3 => $v3) {
														if ($k3 == 'form_id') {
															$widgets[$k1][$k2][$k3] = $connected_form;
															// echo $key . ' : ' .  $v . '<br>';
														}
													}
												}
											}

											// echo '<pre>' . print_r(($widgets), true) . '</pre>';
											add_post_meta($new_gv_id, $key, $widgets);
											break;
										default:
											add_post_meta($new_gv_id, $key, $values);
											break;
									}
								} else {
									if ($key == '_gravityview_form_id') {
										add_post_meta($new_gv_id, $key, $connected_form);
									} else {
										add_post_meta($new_gv_id, $key, $gv_meta_fields);
									}
								}
							}
						}
						// replace existing pages
						$this->replace_gv_id_in_pages($old_gv_id, $new_gv_id);
					}
					break;
				endif;
			}
		}
		/**
		 * Export Menus as JSON File to assets/export folder
		 *
		 * @return void
		 */
		public function export_menus_and_pages()
		{
			$menus = wp_get_nav_menus();
			// echo '<pre>' . print_r($menus, true) . '</pre>';
			$menu_items = [];

			// Loop through the menus
			foreach ($menus as $menu) {

				// Get all the menu items in the current menu
				$menu_items_in_this_menu = wp_get_nav_menu_items($menu->term_id);

				// Loop through the menu items in the current menu
				foreach ($menu_items_in_this_menu as $menu_item) {
					$page_data = [];
					if ($menu_item->type == 'post_type' && $menu_item->object == 'page') {
						$page = get_post($menu_item->object_id);
						$page_data = [
							'post_title' => $page->post_title,
							'post_name' => $page->post_name,
							'post_status' => $page->post_status,
							'post_content' => $page->post_content,
							'post_template' => $page->post_template,
							'post_parent' => $page->post_parent,
							'post_author'  => get_current_user_id(),
							'post_type'  => $page->post_type,
						];
						// wp_insert_post($page_data);
					}
					// Add the menu item to the array
					$menu_items[$menu->name][] = [
						'id' => $menu_item->ID,
						'title' => $menu_item->title,
						'url' => $menu_item->url,
						'parent' => $menu_item->menu_item_parent,
						'object' => $menu_item->object,
						'object_id' => $menu_item->object_id,
						'object' => $menu_item->object,
						'type' => $menu_item->type,
						'type_label' => $menu_item->type_label,
						'icon' => get_post_meta($menu_item->ID)['_menu_item_icon'][0],
						'connected_page' => $page_data,

					];
				}
				// Convert the menu items array to JSON

			}
			$menu_json = json_encode($menu_items, JSON_PRETTY_PRINT);

			// Save the JSON to a file
			$file_path = 'exported_menu_' . date('Y-m-d-H') . '.json';
			file_put_contents(WAMS_INPUT_PATH . 'assets/export/' . $file_path, $menu_json);

			echo "Menu exported to $file_path";
			Logger::info('Menus and Pages exported to : ' . $file_path . ' successfully', 'System Setup');
		}
		/**
		 * Export Gravity Views as JSON File to assets/export folder
		 *
		 * @return void
		 */
		public function export_gravity_views()
		{
			$gv_data = [];
			$gv_posts = get_posts([
				'post_type' => 'gravityview',
				'post_status' => 'publish',
				'numberposts' => 20
			]);
			if (!$gv_posts) return false;

			foreach ($gv_posts as $gravityview) {
				$gv_meta = get_post_meta($gravityview->ID);
				$conntected_form_id = $gv_meta['_gravityview_form_id'][0] ? $gv_meta['_gravityview_form_id'][0] : 0;
				// echo '<pre>' . print_r($conntected_form_id, true) . '</pre>';
				if ($conntected_form_id > 0)
					$form = GFFormsModel::get_form($conntected_form_id);
				if ($form) {
					$conntected_form_title = $form->title;
				}
				$gv_data[] = [
					'ID' => $gravityview->ID,
					'post_title' => $gravityview->post_title,
					'post_name' => $gravityview->post_name,
					'post_status' => $gravityview->post_status,
					'post_author'  => get_current_user_id(),
					'post_type'  => $gravityview->post_type,
					'conntected_form_id' => $conntected_form_id,
					'conntected_form_title' => $conntected_form_title,
					'post_meta'  => $gv_meta,
				];
			}
			$gv_json = json_encode($gv_data, JSON_PRETTY_PRINT);
			// Save the JSON to a file
			$file_path = WAMS_INPUT_PATH . 'assets/export/' . 'exported_gv.json';
			if (file_exists($file_path)) {
				// Attempt to rename the file.
				if (rename($file_path, $file_path . '.backup.' . date('Ymdh'))) {
					echo "File renamed successfully.";
				} else {
					echo "Failed to rename the file.";
				}
			} else {
				echo "The file does not exist.";
			}
			file_put_contents($file_path, $gv_json);

			echo "GravityViews exported to $file_path";
			Logger::info('GravityViews exported to : ' . $file_path . ' successfully', 'System Setup');
		}
		/**
		 * search for Gravityview ID shortcodes in pages and replace it with newly imported GV
		 *
		 * @param [type] $gv_id
		 * @return void
		 */
		public function replace_gv_id_in_pages($old_gv_id, $new_gv_id)
		{
			$pages = get_pages();
			foreach ($pages as $page) {

				if (strpos($page->post_content, '[gravityview')) {
					$pattern = "/\d+/";

					if (preg_match($pattern, $page->post_content, $matches)) {
						if ($matches[0] == $old_gv_id) {
							$updated_content = preg_replace($pattern, $new_gv_id, $page->post_content);
							$page->post_content =  $updated_content;
							wp_update_post($page);
							echo "Updated Shortcode: " . $updated_content;
						}
					} else {
						echo "No GV ID found in the Content.";
					}
				}
			}
		}


		public function create_nav_menu()
		{
			$current_menus = wp_get_nav_menus();
			if (!$current_menus || empty($current_menus)) {
				$main_menu_id = wp_create_nav_menu('Main Menu');
				$user_menu_id = wp_create_nav_menu('User Menu');
			} else {
				foreach ($current_menus as $menu) {
					// check if Main menu exists
					if (strpos($menu->name, 'Main') !== false) {
						$main_menu_id = $menu->term_id;
					} else {
						$main_menu_id = wp_create_nav_menu('Main Menu');
					}
					// check if User menu exists
					if (strpos($menu->name, 'User') !== false) {
						$user_menu_id = $menu->term_id;
					} else {
						$user_menu_id = wp_create_nav_menu('User Menu');
					}
				}
			}
			$theme_locations = get_theme_mod('nav_menu_locations');
			foreach ($theme_locations as $key => $id) {
				if (strpos($key, 'main') !== false && $id == 0) {
					$theme_locations[$key] = $main_menu_id;
				}
				if (strpos($key, 'top') !== false && $id == 0) {
					$theme_locations[$key] = $user_menu_id;
				}
				set_theme_mod('nav_menu_locations', $theme_locations);
			}
			return wp_get_nav_menus();
		}
	}
}
