<?php

namespace wams\core;


// Exit if accessed directly
if (!defined('ABSPATH')) exit;


if (!class_exists('wams\core\Logout')) {


	/**
	 * Class Logout
	 * @package um\core
	 */
	class Logout
	{


		/**
		 * Logout constructor.
		 */
		function __construct()
		{
			add_action('template_redirect', array(&$this, 'logout_page'), 10000);
		}


		/**
		 * @param $redirect_url
		 * @param $status
		 *
		 * @return false|string
		 */
		function safe_redirect_default($redirect_url, $status)
		{
			$login_page_id = WAMS()->config()->permalinks['login'];
			return get_permalink($login_page_id);
		}


		/**
		 * Logout via logout page
		 */
		function logout_page()
		{
			if (is_home()) {
				return;
			}

			$trid = 0;
			//$language_code = '';
			if (WAMS()->external_integrations()->is_wpml_active()) {
				global $sitepress;
				$default_lang = $sitepress->get_default_language();

				/*$language_code = $sitepress->get_current_language();
				if ( $language_code == $default_lang ) {
					$language_code = '';
				}*/

				$current_page_ID = get_the_ID();
				if (function_exists('icl_object_id')) {
					$trid = icl_object_id($current_page_ID, 'page', true, $default_lang);
				} else {
					$trid = wpml_object_id_filter($current_page_ID, 'page', true, $default_lang);
				}
			}

			$logout_page_id = WAMS()->config()->permalinks['logout'];
			if (wams_is_core_page('logout') || ($trid > 0 && $trid == $logout_page_id)) {

				if (is_user_logged_in()) {

					add_filter('wp_safe_redirect_fallback', array(&$this, 'safe_redirect_default'), 10, 2);

					if (isset($_REQUEST['redirect_to']) && '' !== $_REQUEST['redirect_to']) {
						wp_destroy_current_session();
						wp_logout();
						session_unset();
						wams_safe_redirect(esc_url_raw($_REQUEST['redirect_to']));
					} elseif ('redirect_home' === wams_user('after_logout')) {
						wp_destroy_current_session();
						wp_logout();
						session_unset();
						wp_safe_redirect(home_url());
						exit;
					} else {
						/**
						 * Filters URL for redirect after logout.
						 *
						 * @param {string} $logout_redirect_url URL for redirect after logout.
						 * @param {int}    $user_id             User ID who logged out.
						 *
						 * @return {string} Redirect URL.
						 *
						 * @since 2.0
						 * @hook wams_logout_redirect_url
						 *
						 * @example <caption>Change URL for redirect after logout.</caption>
						 * function my_logout_redirect_url( $logout_redirect_url, $user_id ) {
						 *     return '{your_custom_url}';
						 * }
						 * add_filter( 'wams_logout_redirect_url', 'my_logout_redirect_url', 10, 2 );
						 */
						$redirect_url = apply_filters('wams_logout_redirect_url', wams_user('logout_redirect_url'), wams_user('ID'));
						wp_destroy_current_session();
						wp_logout();
						session_unset();
						wams_safe_redirect($redirect_url);
					}
				} else {
					add_filter('wp_safe_redirect_fallback', array(&$this, 'safe_redirect_default'), 10, 2);
					wp_safe_redirect(home_url());
					exit;
				}
			}
		}
	}
}
