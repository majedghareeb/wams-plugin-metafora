<?php

namespace wams\admin\modules;

use wams\admin\core\Admin_Settings_API;
use GFAPI;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\modules\DB_Cleanup')) {

	/**
	 * Class Debug
	 * @package wams\admin\modules
	 */
	class DB_Cleanup
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
						'page_title' => 'DB Cleanup',
						'menu_title' => 'DB Cleanup',
						'capability' => 'edit_wams_settings',
						'menu_slug' => 'wams_db_cleanup',
						'callback' => [$this, 'dbCleanup']
					]
				]
			];
		}

		public function dbCleanup()
		{
			echo ' <h1>DB Cleanup</h1>';

			wp_enqueue_script("db-check", WAMS_URL . 'assets/js/admin/db-check.js', array(), WAMS_VERSION, false);
			wp_enqueue_style("sweetalert2", WAMS_URL . 'assets/css/sweetalert2.min.css', array(), WAMS_VERSION);
			wp_enqueue_script("sweetalert2", WAMS_URL . 'assets/js/sweetalert2.min.js', array(), WAMS_VERSION, false);

			echo '<div class="alert alert-warning">This tool will delete workflow details from froms to save space in the database <br> Entries will not be deleted!</div>';
			global $wpdb;
			$entry_notes_table = $wpdb->prefix . 'gf_entry_meta';
			$total_rows = $wpdb->get_var("SELECT COUNT(*) FROM $entry_notes_table");

			if (class_exists('GFFormsModel')) {
				$forms = \GFFormsModel::get_forms();
				require_once WAMS()->admin()->templates_path . 'db-cleanup.php';
			} else {
				echo '<h1 class="alert alert-danger">GF Plugin is missing<h1>';
			}
		}
	}
}
