<?php

namespace wams\admin\core;


if (!defined('ABSPATH')) {
	exit;
}


if (!class_exists('wams\admin\core\Admin_Theme_Updater')) {


	/**
	 * Class Admin_Theme_Updater
	 * @package um\admin\core
	 */
	class Admin_Theme_Updater
	{


		/**
		 * Restored themes
		 * @var array
		 */
		private $restored = array();


		/**
		 * Saved themes
		 * @var array
		 */
		private $saved = array();


		/**
		 * Constructor.
		 */
		public function __construct()
		{
			add_filter('upgrader_package_options', array($this, 'upgrader_package_options'), 40, 1);
			add_action('upgrader_process_complete', array($this, 'upgrader_process_complete'), 40, 2);
		}


		/**
		 * Copy directory
		 * @param string $src
		 * @param string $dest
		 */
		public static function recurse_copy($src, $dest)
		{

			if (!is_dir($dest)) {
				@mkdir($dest, 0777, true);
			}

			$dir = opendir($src);
			while (false !== ($file = readdir($dir))) {
				if (($file != '.') && ($file != '..')) {
					if (is_dir($src . DIRECTORY_SEPARATOR . $file)) {
						self::recurse_copy($src . DIRECTORY_SEPARATOR . $file, $dest . DIRECTORY_SEPARATOR . $file);
					} else {
						copy($src . DIRECTORY_SEPARATOR . $file, $dest . DIRECTORY_SEPARATOR . $file);
					}
				}
			}
			closedir($dir);
		}


		/**
		 * Restore WAMS templates to theme directory
		 *
		 * @param string $name
		 * @return void
		 */
		public function restore_templates($name = '')
		{
			$theme = wp_get_theme($name);

			if (empty($theme) || !$theme->exists()) {
				return;
			}
			if (isset($this->restored[$theme->get('Name')])) {
				return;
			}
			if (empty($this->saved[$theme->get('Name')])) {
				return;
			}

			$old_version = get_option('theme_version ' . $theme->get('Name'));
			$version = $theme->get('Version');
			if ($old_version === $version) {
				return;
			}

			$temp_dir = WAMS()->uploader()->get_core_temp_dir() . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme->get('template');
			if (!is_dir($temp_dir)) {
				return;
			}

			$wams_dir = $theme->get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'wams';
			@mkdir($wams_dir, 0777, true);

			$src = realpath($temp_dir);
			$dest = realpath($wams_dir);
			if ($src && $dest) {
				self::recurse_copy($src, $dest);
				error_log("WAMS Log. Theme '" . $theme->get('template') . "' templates restored.");
				WAMS()->files()->remove_dir($src);
			} else {
				error_log("WAMS Error. Can not restore theme templates.");
			}

			delete_option('theme_version ' . $theme->get('Name'));
			$this->restored[$theme->get('Name')] = $theme->get('Version');
		}


		/**
		 * Save WAMS templates to temp directory
		 * @param string $name
		 *
		 * @return void
		 */
		public function save_templates($name = '')
		{
			$theme = wp_get_theme($name);

			if (empty($theme) || !$theme->exists()) {
				return;
			}
			if (isset($this->restored[$theme->get('Name')])) {
				return;
			}
			if (isset($this->saved[$theme->get('Name')])) {
				return;
			}

			$wams_dir = $theme->get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'wams';
			if (!is_dir($wams_dir)) {
				return;
			}

			$temp_dir = WAMS()->uploader()->get_core_temp_dir() . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme->get('template');
			@mkdir($temp_dir, 0777, true);

			$src = realpath($wams_dir);
			$dest = realpath($temp_dir);
			if ($src && $dest) {
				self::recurse_copy($src, $dest);
				error_log("WAMS Log. Theme '" . $theme->get('template') . "' templates saved.");
			} else {
				error_log("WAMS Error. Can not save theme templates.");
			}

			update_option('theme_version ' . $theme->get('Name'), $theme->get('Version'));
			$this->saved[$theme->get('Name')] = $theme->get('Version');
		}


		/**
		 * Filter: upgrader_package_options
		 * 
		 * @param array $options
		 * @return array
		 */
		function upgrader_package_options($options)
		{
			if (isset($options['hook_extra']) && isset($options['hook_extra']['theme'])) {
				$this->save_templates($options['hook_extra']['theme']);
			}
			return $options;
		}


		/**
		 * Action: upgrader_process_complete
		 * 
		 * @param \WP_Upgrader $WP_Upgrader
		 * @param array $options
		 */
		public function upgrader_process_complete($WP_Upgrader, $options)
		{
			if (isset($options['themes']) && is_array($options['themes'])) {
				foreach ($options['themes'] as $theme) {
					$this->restore_templates($theme);
				}
			}
		}
	}
}
