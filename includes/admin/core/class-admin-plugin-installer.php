<?php

namespace wams\admin\core;

if (!defined('ABSPATH')) {
	exit;
}
/**
 * Connekt_Plugin_Installer
 *
 * @author   Darren Cooney
 * @link     https://github.com/dcooney/wordpress-plugin-installer
 * @link     https://connekthq.com
 * @version  1.0
 */


if (!class_exists('Admin_Plugin_Installer')) {

	class Admin_Plugin_Installer
	{

		public $required_plugins;
		public $optional_plugins;
		public $plugins = array();

		public function __construct($required_plugins)
		{
			$this->required_plugins = $required_plugins;
			$this->plugins = get_plugins();
		}


		public function getPluginsStatus()
		{

			$required_plugins_arr = [];
			foreach ($this->required_plugins as $slug) {
				$plugin_file = self::get_plugin_file($slug);
				if (array_key_exists($plugin_file, $this->plugins)) {
					$required_plugins_arr[$slug]['name'] = $this->plugins[$plugin_file]['Name'];
					$required_plugins_arr[$slug]['version'] = $this->plugins[$plugin_file]['Version'];
					$required_plugins_arr[$slug]['description'] = $this->plugins[$plugin_file]['Description'];
					$required_plugins_arr[$slug]['installed'] = true;
					$required_plugins_arr[$slug]['activated'] =  (is_plugin_active($plugin_file))  ? true : false;
				} else {
					$required_plugins_arr[$slug]['name'] = 'N/A';
					$required_plugins_arr[$slug]['version'] = 'N/A';
					$required_plugins_arr[$slug]['description'] = 'N/A';
					$required_plugins_arr[$slug]['installed'] = false;
					$required_plugins_arr[$slug]['activated'] = false;
				}
			}
			return $required_plugins_arr;
		}


		/**
		 * get_plugin_file
		 * A method to get the main plugin file.
		 *
		 *
		 * @param  $plugin_slug    String - The slug of the plugin
		 * @return $plugin_file
		 *
		 * @since 1.0
		 */

		public function get_plugin_file($plugin_slug)
		{
			foreach ($this->plugins as $plugin_file => $plugin_info) {

				// Get the basename of the plugin e.g. [askismet]/askismet.php
				$slug = dirname(plugin_basename($plugin_file));

				if ($slug) {
					if ($slug == $plugin_slug) {
						return $plugin_file; // If $slug = $plugin_name
					}
				}
			}
			return null;
		}


		public function print_table($plugins, $header)
		{
			echo '<h4>' . $header . '</h4>';
			echo '<table class="table table-bordered"><thead><tr>';
			echo '<th scope="col">plugin</th>';
			echo '<th scope="col">Name</th>';
			echo '<th scope="col">Version</th>';
			echo '<th scope="col">Installation</th>';
			echo '<th scope="col">Activation</th>';
			echo '</tr></thead><tbody>';

			foreach ($plugins as $slug => $plugin) {
				$activated = '';
				$installed = '';
				if ($plugin['installed'] && !$plugin['activated']) {
					$installed = 'Installed';
					$activated = 'Not Activated';
					$class = 'warning';
				} else {
					$class = 'danger';
				}
				if ($plugin['activated']) {
					$installed = 'Installed';
					$activated = 'Activated';
					$class = 'success';
				}


				echo '<tr class="table-' . $class . '">';
				echo '<th scope="row">' . $slug . '</th>';
				echo '<td>' . $plugin['name'] . '</td>';
				echo '<td>' . $plugin['version'] . '</td>';
				echo '<td>' . $installed . '</td>';
				echo '<td>' . $activated . '</td>';
				echo '</tr>';
			}
			echo '</tbody></table>';
		}

		/*
		* check_file_extension
		* A helper to check file extension
		*
		*
		* @param $filename    String - The filename of the plugin
		* @return boolean
		*
		* @since 1.0
		*/
		public static function check_file_extension($filename)
		{
			if (substr(strrchr($filename, '.'), 1) === 'php') {
				// has .php exension
				return true;
			} else {
				// ./wp-content/plugins
				return false;
			}
		}
	}
}
