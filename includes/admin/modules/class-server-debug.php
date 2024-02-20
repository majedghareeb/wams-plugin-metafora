<?php

namespace wams\admin\modules;

use wams\admin\core\Admin_Settings_API;
use \wams\common\Logger;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\modules\Server_Debug')) {

	/**
	 * Class Debug
	 * @package wams\admin\modules
	 */
	class Server_Debug
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
						'page_title' => 'Server Debug',
						'menu_title' => 'Server Debug',
						'capability' => 'edit_wams_settings',
						'menu_slug' => 'wams_server_debug',
						'callback' => array($this, 'show_debug_page')
					]
				]
			];
		}

		function show_debug_page()
		{
			echo '<h1>Server Debug Page</h1>';
			echo '<div class="wrap">';
			$this->show_server_debug();
			echo '</div>';
		}
		public function show_server_debug()
		{

			global $wp;
			$page = $wp->request;
			// echo $page;
			wams()->admin()->notices()->display_notice('debug-module');


			$debug_log = file_get_contents(ABSPATH . 'wp-content/debug.log');
			$log_entries = explode("\n", $debug_log);
			include_once WAMS()->admin()->templates_path . 'server-debug.php';

			// Handle clearing of debug.log file
			if (isset($_POST['clear_debug_log']) && $_POST['clear_debug_log'] === '1') {
				$debug_log_file = ABSPATH . 'wp-content/debug.log';
				file_put_contents($debug_log_file, '');
				wams()->admin()->notices()->add_notice('debug-module', array(
					'class'         => 'updated',
					'message'       => 'Debug log has been cleared',
					'dismissible'   => true
				), 1);

				header("Refresh:0;");
			}
		}
		public function system_debug()
		{
			// $shortcodes = Wams_Public::getShortcodes();
			global $wpdb;
			// $sqlquery = $wpdb->query("SELECT FROM $wpdb->options WHERE option_name LIKE 'wams%%'");
			$sql =  "SELECT * FROM $wpdb->options  WHERE option_name LIKE '%wams%' ";

			$options = $wpdb->get_results(
				$wpdb->prepare(
					$sql
				)
			);
			include_once WAMS()->admin()->templates_path . 'system-debug.php';
			// require_once(WAMS_PLUGIN_PATH . "admin/partials/system_debug.php");
		}

		public function system_logs()
		{
			if (isset($_REQUEST['Clear'])) {
				// print_r($_REQUEST);
				Logger::clearLogs();
			}
			echo '<h2>System Logs</h2>';

			$logs =  Logger::readLogs();

			echo '<div id="" class="content alert alert-info">';
			echo ($logs);

			echo '<form method="post" action="' . $_SERVER['REQUEST_URI'] . '">';

			echo '<div><button class="btn btn-primary" type="submit" name="Clear" value="Clear">Clear Logs</button></div>';
			echo '</form>';
			echo '</div>';
		}
		public function system_checkup()
		{


			$required_plugins = array(
				'gravityforms',
				'gravityformsuserregistration',
				'gravityperks',
				'gravityflow',
				'gravityview',
				'gravityview-advanced-filter',
				'gravityview-diy',
				'ultimate-member',
				'um-notifications',
				'action-scheduler',
				'action-scheduler',


			);
			$optional_plugins = array(
				'advanced-database-cleaner-pro',
				'disable-admin-notices',
				'gp-nested-forms',
				'gp-populate-anything',
				'gwreadonly',
				'gravityview-datatables',
				'um-verified-users',
				'um-online',
				'advanced-database-cleaner-pro',

			);
			$required_plugins_status = new \admin\Plugin_Installer($required_plugins);
			$optional_plugins_status = new \admin\Plugin_Installer($optional_plugins);


			$require_plugins = $required_plugins_status->getPluginsStatus();
			$optional_plugins = $optional_plugins_status->getPluginsStatus();
			// print_r($require_plugins);
			$required_plugins_status->print_table($require_plugins, 'Required Plugins');
			$required_plugins_status->print_table($optional_plugins, 'Optional Plugins');

			// echo "<pre>";
			// print_r(json_decode(get_option('wams_ga_options')));
		}
	}
}
