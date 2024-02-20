<?php

namespace wams;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * WAMS Dependency Checker
 *
 * Checks if WAMS plugin is enabled
 */
if (!class_exists('wams\Dependencies')) {

	/**
	 * Class Dependencies
	 *
	 * @package um
	 */
	class Dependencies
	{

		/**
		 * @var
		 */
		private static $active_plugins;

		/**
		 * For backward compatibility checking
		 *
		 * @var array
		 */
		public $ext_required_version = array(
			'bbpress'              => '2.0.7',
			'followers'            => '2.1.6',
			'forumwp'              => '2.1.5',
			'friends'              => '2.1.4',
			'groups'               => '2.4.2',
			'jobboardwp'           => '1.0.7',
			'mailchimp'            => '2.2.0',
			'messaging'            => '2.2.5',
			'mycred'               => '2.2.4',
			'notices'              => '2.0.5',
			'notifications'        => '2.1.3',
			'online'               => '2.1.1',
			'private-content'      => '2.0.5',
			'profile-completeness' => '2.2.7',
			'profile-tabs'         => '1.0.0',
			'recaptcha'            => '2.3.4',
			'reviews'              => '2.1.5',
			'social-activity'      => '2.2.0',
			'social-login'         => '2.2.0',
			'stripe'               => '1.0.0',
			'terms-conditions'     => '2.1.6',
			'unsplash'             => '2.0.2',
			'user-bookmarks'       => '2.1.4',
			'user-locations'       => '1.0.0',
			'user-notes'           => '1.1.0',
			'user-photos'          => '2.0.4',
			'user-tags'            => '2.2.6',
			'verified-users'       => '2.0.5',
			'woocommerce'          => '2.3.7',

			/*????*/
			'restrict-content'     => '2.0',

			/*alpha*/
			'user-exporter'        => '1.0.0',
			'google-authenticator' => '1.0.0',
			'frontend-posting'     => '1.0.0',
			/*in development*/
			'filesharing'          => '1.0.0',
			'beaver-builder'       => '2.0',
			'user-events'          => '1.0.0',

			// deprecated
			'instagram'            => '2.0.5',
		);

		/**
		 * Get all active plugins
		 */
		public static function init()
		{
			self::$active_plugins = (array) get_option('active_plugins', array());

			if (is_multisite()) {
				self::$active_plugins = array_merge(self::$active_plugins, get_site_option('active_sitewide_plugins', array()));
			}
		}

		/**
		 * @return mixed
		 */
		public function get_active_plugins()
		{
			if (!self::$active_plugins) {
				self::init();
			}

			return self::$active_plugins;
		}

		/**
		 * Check if WAMS core plugin is active
		 *
		 * @return bool
		 */
		public static function wams_active_check()
		{
			if (!self::$active_plugins) {
				self::init();
			}

			return in_array('wams/wams.php', self::$active_plugins) || array_key_exists('wams/wams.php', self::$active_plugins);
		}



		/**
		 * Check if Gravity Forms plugin is active
		 *
		 * @return bool
		 */
		public static function gravityforms_active_check()
		{
			if (!self::$active_plugins) {
				self::init();
			}
			return in_array('gravityforms/gravityforms.php', self::$active_plugins) || array_key_exists('gravityforms/gravityforms.php', self::$active_plugins);
		}

		/**
		 * Compare WAMS core and WAMS_ension versions
		 *
		 * @param string $wams_required_ver
		 * @param string $ext_ver
		 * @param string $ext_key
		 * @param string $ext_tiWAMS_
		 * @return bool
		 */
		public function compare_versions($wams_required_ver, $ext_ver, $ext_key, $ext_title)
		{
			if (
				version_compare(WAMS_VERSION, $wams_required_ver, '<')
				|| empty($this->ext_required_version[$ext_key])
				|| version_compare($this->ext_required_version[$ext_key], $ext_ver, '>')
			) {

				$message = '';
				if (version_compare(WAMS_VERSION, $wams_required_ver, '<')) {
					// translators: %1$s is a extension name; %2$s is a plugin name; %3$s is a required version.
					$message = sprintf(__('This version of <strong>"%1$s"</strong> requires the core <strong>%2$s</strong> plugin to be <strong>%3$s</strong> or higher.', 'wams'), $ext_title, WAMS_PLUGIN_NAME, $wams_required_ver) .
						'<br />' .
						// translators: %s: plugin name.
						sprintf(__('Please update <strong>%s</strong> to the latest version.', 'wams'), WAMS_PLUGIN_NAME);
				} elseif (empty($this->ext_required_version[$ext_key]) || version_compare($this->ext_required_version[$ext_key], $ext_ver, '>')) {
					// translators: %1$s is a plugin name; %2$s is a extension name; %3$s is a extension version.
					$message = sprintf(__('Sorry, but this version of <strong>%1$s</strong> does not work with extension <strong>"%2$s" %3$s</strong> version.', 'wams'), WAMS_PLUGIN_NAME, $ext_title, $ext_ver) .
						'<br /> ' .
						// translators: %s: extension name.
						sprintf(__('Please update extension <strong>"%s"</strong> to the latest version.', 'wams'), $ext_title);
				}

				return $message;
			} else {
				//check correct folder name for extensions
				if (!self::$active_plugins) self::init();

				if (!in_array("um-{$ext_key}/um-{$ext_key}.php", self::$active_plugins) && !array_key_exists("um-{$ext_key}/um-{$ext_key}.php", self::$active_plugins)) {
					// translators: %1$s is a extension name; %2$s is a extension version.
					$message = sprintf(__('Please check <strong>"%1$s" %2$s</strong> extension\'s folder name.', 'wams'), $ext_title, $ext_ver) .
						'<br />' .
						// translators: %s: extension name.
						sprintf(__('Correct folder name is <strong>"%s"</strong>', 'wams'), "um-{$ext_key}");

					return $message;
				}
			}

			return true;
		}

		/**
		 * @param string $extension_version Extension version
		 * @return mixed
		 */
		public static function php_version_check($extension_version)
		{
			return version_compare(phpversion(), $extension_version, '>=');
		}
	}
}


if (!function_exists('is_wams_active')) {
	/**
	 * Check WAMS core is active
	 *
	 * @return bool active - true | inactive - false
	 */
	function is_wams_active()
	{
		return Dependencies::wams_active_check();
	}
}
