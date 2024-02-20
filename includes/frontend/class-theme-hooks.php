<?php

namespace wams\frontend;


if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\frontend\Theme_Hooks')) {

	/**
	 * Class Secure
	 *
	 * @package wams\frontend
	 *
	 * @since 1.0.0
	 */
	class Theme_Hooks
	{

		/**
		 * Theme_Hooks constructor.
		 * @since 2.6.8
		 */
		public function __construct()
		{
		}

		/**
		 * Adds handlers on form submissions.
		 *
		 * @since 2.6.8
		 */
		public function init()
		{
			if (!WAMS()->options()->get('secure_ban_admins_accounts')) {
				return;
			}

			/**
			 * Checks the integrity of Current User's Capabilities
			 */
			add_action('wams_after_save_registration_details', array($this, 'secure_user_capabilities'), 1);
			add_action('wams_after_save_registration_details', array($this, 'maybe_set_whitelisted_password'), 2);
			if (is_user_logged_in() && !current_user_can('manage_options')) { // Exclude current Logged-in Administrator from validation checks.
				add_action('wams_after_user_updated', array($this, 'secure_user_capabilities'), 1);
				add_action('wams_after_user_account_updated', array($this, 'secure_user_capabilities'), 1);
			}
		}

		/**
		 * Add Login notice for Reset Password
		 *
		 * @since 2.6.8
		 */
		public function reset_password_notice()
		{
			if (!WAMS()->options()->get('display_login_form_notice')) {
				return;
			}

			// phpcs:disable WordPress.Security.NonceVerification
			if (!isset($_REQUEST['notice']) || 'expired_password' !== $_REQUEST['notice']) {
				return;
			}
			// phpcs:enable WordPress.Security.NonceVerification

			echo "<p class='um-notice warning'>";
			echo wp_kses(
				sprintf(
					// translators: One-time change requires you to reset your password
					__('<strong>Important:</strong> Your password has expired. This (one-time) change requires you to reset your password. Please <a href="%s">click here</a> to reset your password via Email.', 'wams'),
					wams_get_core_page('password-reset')
				),
				array(
					'strong' => array(),
					'a'      => array(
						'href' => array(),
					),
				)
			);
			echo '</p>';
		}

		/**
		 * Add Login notice for Under Maintance
		 *
		 * @since 2.6.8
		 */
		public function under_maintenance_notice()
		{
			if (!WAMS()->options()->get('wams_pages_settings')) {
				return;
			}

			// phpcs:disable WordPress.Security.NonceVerification
			if (!isset($_GET['notice']) || 'maintenance' !== $_GET['notice']) {
				return;
			}
			// phpcs:enable WordPress.Security.NonceVerification

			echo "<p class='um-notice warning'>";
			echo wp_kses(
				__('<strong>Important:</strong> Our website is currently under maintenance. Please check back soon.', 'wams'),
				array(
					'strong' => array(),
					'a'      => array(
						'href' => array(),
					),
				)
			);
			echo '</p>';
		}
	}
}
