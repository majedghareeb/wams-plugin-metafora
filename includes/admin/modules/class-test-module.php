<?php

namespace wams\admin\modules;

use \wams\admin\core\Admin_Settings_API;
use \wams\core\RSS_Feed_Extractor;
use wams\admin\modules\Task_Scheduler;
use ReflectionClass;
use GFAPI;
use GVCommon;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\modules\Test_Module')) {

	/**
	 * Class Debug
	 * @package wams\admin\modules
	 */
	class Test_Module
	{
		/**
		 * @var object
		 */
		private $settings_api;
		/**
		 * @var array
		 */
		private $page;
		private $pages;

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
						'page_title' => 'Test_Module Settings',
						'menu_title' => 'Test Module',
						'capability' => 'edit_wams_settings',
						'menu_slug' => 'wams_test',
						'callback' => [$this, 'show_test_page']
					]
				]
			];
			add_action('load-wams-plugin_page_wams_test', [$this, 'on_page_load']);

			$this->pages = WAMS()->admin()->wams_get_pages();
		}

		function on_page_load()
		{
			// add_meta_box(
			// 	'my_meta_box_id',
			// 	'My Meta Box',
			// 	$this->purge_temp(),
			// 	'wams-plugin_page_wams_test',
			// 	'normal',
			// 	'default'
			// );
			// add_meta_box('metaboxes-contentbox-1', __('Users Overview', 'wams'), array($this, 'purge_temp'), 'wams_test', 'side', 'core');
		}


		/**
		 *
		 */
		function purge_temp()
		{
			// include_once WAMS()->admin()->templates_path . 'dashboard/purge.php';
		}


		function show_test_page()
		{
			echo '<h1>Test_Module Page</h1>';
			global $wpdb;
			// $sqlquery = $wpdb->query("SELECT FROM $wpdb->options WHERE option_name LIKE 'wams%%'");
			$sql =  "SELECT * FROM $wpdb->options  WHERE option_name LIKE '%wams_ga_cache%' ";

			$options = $wpdb->get_results(
				$wpdb->prepare(
					$sql
				)
			);
			if ($options) :
				foreach ($options as $option) {
					echo '<div>';
					echo '<h1>' . $option->option_name . '';
					$option_value = delete_option($option->option_name);
					echo ' Deleted!';
					echo '</h1>';
					echo '</div>';
				}
			endif;
			// $logger = new \wams\common\Logger();
			// echo $logger->set_log_file_path('new');
			// // $logger->log_dir = "new";
			// $logger::info('TEST');
			// $default = '{
			// 	"pages": [
			// 		{
			// 			"ID": 5126,
			// 			"post_title": "Account",
			// 			"post_content": "[ultimatemember_account]",
			// 			"post_type": "page"
			// 		},
			// 		{
			// 			"ID": 5120,
			// 			"post_title": "Home",
			// 			"post_content": "[home-page]",
			// 			"post_type": "page"
			// 		},
			// 		{
			// 			"ID": 5122,
			// 			"post_title": "Inbox",
			// 			"post_content": "[gravityflow page=\"inbox\"]",
			// 			"post_type": "page"
			// 		},
			// 		{
			// 			"ID": 5134,
			// 			"post_title": "Logout",
			// 			"post_content": "",
			// 			"post_type": "page"
			// 		},
			// 		{
			// 			"ID": 5130,
			// 			"post_title": "Members",
			// 			"post_content": "[ultimatemember form_id=\"6\"]",
			// 			"post_type": "page"
			// 		},
			// 		{
			// 			"ID": 5132,
			// 			"post_title": "Notifications",
			// 			"post_content": "[ultimatemember_notifications]",
			// 			"post_type": "page"
			// 		},
			// 		{
			// 			"ID": 5124,
			// 			"post_title": "Status",
			// 			"post_content": "[gravityflow page=\"status\"]",
			// 			"post_type": "page"
			// 		},
			// 		{
			// 			"ID": 5128,
			// 			"post_title": "User",
			// 			"post_content": "[ultimatemember form_id=\"5\"]",
			// 			"post_type": "page"
			// 		}
			// 	],
			// 	"main_menu": [
			// 		{
			// 			"ID": 5154,
			// 			"menu-item-title": "Account",
			// 			"menu-item-url": "http:\/\/syria-tv.local\/input\/account\/",
			// 			"menu-item-parent-id": "0",
			// 			"menu-item-object": "page",
			// 			"menu-item-object-id": "5126",
			// 			"menu-item-type": "post_type",
			// 			"menu-item-type-label": "\u0635\u0641\u062d\u0629",
			// 			"menu-item-icon": ""
			// 		},
			// 		{
			// 			"ID": 5155,
			// 			"menu-item-title": "Home",
			// 			"menu-item-url": "http:\/\/syria-tv.local\/input\/home\/",
			// 			"menu-item-parent-id": "0",
			// 			"menu-item-object": "page",
			// 			"menu-item-object-id": "5120",
			// 			"menu-item-type": "post_type",
			// 			"menu-item-type-label": "\u0635\u0641\u062d\u0629",
			// 			"menu-item-icon": ""
			// 		},
			// 		{
			// 			"ID": 5156,
			// 			"menu-item-title": "Inbox",
			// 			"menu-item-url": "http:\/\/syria-tv.local\/input\/inbox\/",
			// 			"menu-item-parent-id": "0",
			// 			"menu-item-object": "page",
			// 			"menu-item-object-id": "5122",
			// 			"menu-item-type": "post_type",
			// 			"menu-item-type-label": "\u0635\u0641\u062d\u0629",
			// 			"menu-item-icon": ""
			// 		},
			// 		{
			// 			"ID": 5157,
			// 			"menu-item-title": "Logout",
			// 			"menu-item-url": "http:\/\/syria-tv.local\/input\/logout\/",
			// 			"menu-item-parent-id": "0",
			// 			"menu-item-object": "page",
			// 			"menu-item-object-id": "5134",
			// 			"menu-item-type": "post_type",
			// 			"menu-item-type-label": "\u0635\u0641\u062d\u0629",
			// 			"menu-item-icon": ""
			// 		},
			// 		{
			// 			"ID": 5158,
			// 			"menu-item-title": "Members",
			// 			"menu-item-url": "http:\/\/syria-tv.local\/input\/members\/",
			// 			"menu-item-parent-id": "0",
			// 			"menu-item-object": "page",
			// 			"menu-item-object-id": "5130",
			// 			"menu-item-type": "post_type",
			// 			"menu-item-type-label": "\u0635\u0641\u062d\u0629",
			// 			"menu-item-icon": ""
			// 		},
			// 		{
			// 			"ID": 5159,
			// 			"menu-item-title": "Notifications",
			// 			"menu-item-url": "http:\/\/syria-tv.local\/input\/notifications\/",
			// 			"menu-item-parent-id": "0",
			// 			"menu-item-object": "page",
			// 			"menu-item-object-id": "5132",
			// 			"menu-item-type": "post_type",
			// 			"menu-item-type-label": "\u0635\u0641\u062d\u0629",
			// 			"menu-item-icon": ""
			// 		},
			// 		{
			// 			"ID": 5160,
			// 			"menu-item-title": "Status",
			// 			"menu-item-url": "http:\/\/syria-tv.local\/input\/status\/",
			// 			"menu-item-parent-id": "0",
			// 			"menu-item-object": "page",
			// 			"menu-item-object-id": "5124",
			// 			"menu-item-type": "post_type",
			// 			"menu-item-type-label": "\u0635\u0641\u062d\u0629",
			// 			"menu-item-icon": ""
			// 		},
			// 		{
			// 			"ID": 5161,
			// 			"menu-item-title": "User",
			// 			"menu-item-url": "http:\/\/syria-tv.local\/input\/user\/",
			// 			"menu-item-parent-id": "0",
			// 			"menu-item-object": "page",
			// 			"menu-item-object-id": "5128",
			// 			"menu-item-type": "post_type",
			// 			"menu-item-type-label": "\u0635\u0641\u062d\u0629",
			// 			"menu-item-icon": ""
			// 		}
			// 	],
			// 	"user_menu": [
			// 		{
			// 			"ID": 5121,
			// 			"menu-item-title": "\u0627\u0644\u0635\u0641\u062d\u0629 \u0627\u0644\u0631\u0626\u064a\u0633\u064a\u0629",
			// 			"menu-item-url": "http:\/\/syria-tv.local\/input\/home\/",
			// 			"menu-item-parent-id": "0",
			// 			"menu-item-object": "page",
			// 			"menu-item-object-id": "5120",
			// 			"menu-item-type": "post_type",
			// 			"menu-item-type-label": "\u0635\u0641\u062d\u0629",
			// 			"menu-item-icon": ""
			// 		},
			// 		{
			// 			"ID": 5123,
			// 			"menu-item-title": "\u0627\u0644\u0645\u0647\u0627\u0645",
			// 			"menu-item-url": "http:\/\/syria-tv.local\/input\/inbox\/",
			// 			"menu-item-parent-id": "0",
			// 			"menu-item-object": "page",
			// 			"menu-item-object-id": "5122",
			// 			"menu-item-type": "post_type",
			// 			"menu-item-type-label": "\u0635\u0641\u062d\u0629",
			// 			"menu-item-icon": ""
			// 		},
			// 		{
			// 			"ID": 5125,
			// 			"menu-item-title": "\u0627\u0644\u0637\u0644\u0628\u0627\u062a",
			// 			"menu-item-url": "http:\/\/syria-tv.local\/input\/status\/",
			// 			"menu-item-parent-id": "0",
			// 			"menu-item-object": "page",
			// 			"menu-item-object-id": "5124",
			// 			"menu-item-type": "post_type",
			// 			"menu-item-type-label": "\u0635\u0641\u062d\u0629",
			// 			"menu-item-icon": ""
			// 		},
			// 		{
			// 			"ID": 5127,
			// 			"menu-item-title": "\u0627\u0644\u062d\u0633\u0627\u0628",
			// 			"menu-item-url": "http:\/\/syria-tv.local\/input\/account\/",
			// 			"menu-item-parent-id": "0",
			// 			"menu-item-object": "page",
			// 			"menu-item-object-id": "5126",
			// 			"menu-item-type": "post_type",
			// 			"menu-item-type-label": "\u0635\u0641\u062d\u0629",
			// 			"menu-item-icon": ""
			// 		},
			// 		{
			// 			"ID": 5129,
			// 			"menu-item-title": "\u0627\u0644\u0645\u0644\u0641 \u0627\u0644\u0634\u062e\u0635\u064a",
			// 			"menu-item-url": "http:\/\/syria-tv.local\/input\/user\/",
			// 			"menu-item-parent-id": "0",
			// 			"menu-item-object": "page",
			// 			"menu-item-object-id": "5128",
			// 			"menu-item-type": "post_type",
			// 			"menu-item-type-label": "\u0635\u0641\u062d\u0629",
			// 			"menu-item-icon": ""
			// 		},
			// 		{
			// 			"ID": 5131,
			// 			"menu-item-title": "\u0627\u0644\u0645\u0648\u0638\u0641\u064a\u0646",
			// 			"menu-item-url": "http:\/\/syria-tv.local\/input\/members\/",
			// 			"menu-item-parent-id": "0",
			// 			"menu-item-object": "page",
			// 			"menu-item-object-id": "5130",
			// 			"menu-item-type": "post_type",
			// 			"menu-item-type-label": "\u0635\u0641\u062d\u0629",
			// 			"menu-item-icon": ""
			// 		},
			// 		{
			// 			"ID": 5133,
			// 			"menu-item-title": "\u0627\u0644\u0627\u0634\u0639\u0627\u0631\u0627\u062a",
			// 			"menu-item-url": "http:\/\/syria-tv.local\/input\/notifications\/",
			// 			"menu-item-parent-id": "0",
			// 			"menu-item-object": "page",
			// 			"menu-item-object-id": "5132",
			// 			"menu-item-type": "post_type",
			// 			"menu-item-type-label": "\u0635\u0641\u062d\u0629",
			// 			"menu-item-icon": ""
			// 		},
			// 		{
			// 			"ID": 5135,
			// 			"menu-item-title": "\u062e\u0631\u0648\u062c",
			// 			"menu-item-url": "http:\/\/syria-tv.local\/input\/logout\/",
			// 			"menu-item-parent-id": "0",
			// 			"menu-item-object": "page",
			// 			"menu-item-object-id": "5134",
			// 			"menu-item-type": "post_type",
			// 			"menu-item-type-label": "\u0635\u0641\u062d\u0629",
			// 			"menu-item-icon": ""
			// 		}
			// 	]
			// }';
			// $site_url =  get_site_url();
			// $_pages = get_pages();
			// $pages = [];
			// $user_pages = [];
			// $menu = wp_get_nav_menu_items(90);
			// $input = get_blog_details(3);
			// if (class_exists('UM')) {
			// 	$um_pages = UM()->config()->get_core_pages();
			// 	if (!empty($um_pages)) {
			// 		foreach ($um_pages as $key => $page_id) {
			// 			$page = get_post($page_id);
			// 			$user_pages[] = [
			// 				"post_title" => $page->post_title,
			// 				"post_name" => $page->post_name,
			// 				"post_type" => "page",
			// 				"post_type" => $site_url . '/' . $page->post_name
			// 			];
			// 		}
			// 	}
			// }
			// $menu_item_data = array(
			// 	'menu-item-title' => 'title',
			// 	'menu-item-url' => '/url',
			// 	'menu-item-status' => 'publish',
			// 	'menu-item-parent-id' => 0,
			// 	'menu-item-object' => 'page',
			// 	'menu-item-object-id' => 365681,
			// 	'menu-item-type' => 'page',
			// 	'menu-item-type-label' => 'Page',
			// );

			// Create or update menu item
			// $menu_item_id = wp_update_nav_menu_item(90, 0, $menu_item_data);


			// $json_file = WAMS_PATH . 'includes/admin/export/exported_menu_2023-09-07-11.json';
			// switch_to_blog(3);
			$pages = get_posts(array('post_type' => 'page', 'name' => 'notifications'));

			echo '<pre>';
			// $default_values = json_decode(WAMS()->config()->get_input_defaults());
			// print_r($input_forms = WAMS()->admin()->get_input_site_forms($input_site = 3));
			// $menus_arr = $this->readJsonFile($json_file);
			// global $wpdb;
			$message = [];
			// echo  $wpdb->prefix;
			// $pages = get_pages(array('post_type' => 'page',));

			// $photo =  $this->wams_get_default_avatar_uri();
			// $notification = WAMS()->web_notifications()->store_notification(get_current_user_id(), 'TEST2', ['photos' => $photo, 'message' => 'test message']);
			// $notification = WAMS()->web_notifications()->get_notifications();
			// print_r($notification);


			// foreach ($menus_arr as $menu_name => $menu_items) {
			// 	$this->import_menu_items($menu_name, $menu_items);
			// }
			echo '</pre>';
			WAMS()->get_template('notifications.php', '', [], true);
		}

		function wams_get_default_avatar_uri()
		{
			$uri = get_avatar_url(get_current_user_id());
			if (!$uri) {
				$uri = WAMS_URL . 'assets/img/default_avatar.jpg';
			}

			return set_url_scheme($uri);
		}

		function import_menu_items($menu_name, $menu_items)
		{
			// Get menu ID by name or create a new menu
			$menu_id = wp_create_nav_menu($menu_name);

			// Check if the menu creation was successful
			if (is_wp_error($menu_id)) {
				die("Error creating menu: " . $menu_id->get_error_message());
			}

			// Loop through the menu items
			foreach ($menu_items as $item) {
				$menu_item_data = array(
					'menu-item-title' => $item['title'],
					'menu-item-url' => $item['url'],
					'menu-item-parent-id' => $item['parent'],
					'menu-item-object' => $item['object'],
					'menu-item-object-id' => $item['object_id'],
					'menu-item-type' => $item['type'],
					'menu-item-type-label' => $item['type_label'],
					'menu-item-icon' => $item['icon'],
				);

				// Create or update menu item
				$menu_item_id = wp_update_nav_menu_item($menu_id, 0, $menu_item_data);

				// Check if the menu item creation/update was successful
				if (is_wp_error($menu_item_id)) {
					die("Error creating/updating menu item: " . $menu_item_id->get_error_message());
				}

				// If connected_page exists, update the connected page data
				if (!empty($item['connected_page'])) {
					wp_update_post(array_merge(['ID' => $menu_item_id], $item['connected_page']));
				}
			}
		}






		function readJsonFile($filename)
		{
			// Check if the file exists
			if (!file_exists($filename)) {
				die("File not found: $filename");
			}

			// Read the JSON file
			$jsonContent = file_get_contents($filename);

			// Check if the JSON content is valid
			if ($jsonContent === false) {
				die("Error reading JSON file: $filename");
			}

			// Decode JSON content to an array
			$dataArray = json_decode($jsonContent, true);

			// Check if JSON decoding was successful
			if ($dataArray === null) {
				die("Error decoding JSON content in file: $filename");
			}

			return $dataArray;
		}
	}
}