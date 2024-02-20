<?php

namespace wams\core;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\core\Rewrite')) {

	/**
	 * Class Rewrite
	 * @package wams\core
	 */
	class Rewrite
	{

		/**
		 * Rewrite constructor.
		 */
		public function __construct()
		{
			if (!defined('DOING_AJAX')) {
				add_action('wp_loaded', array($this, 'maybe_flush_rewrite_rules'));
			}

			//add rewrite rules
			add_filter('query_vars', array(&$this, 'query_vars'));
			add_filter('rewrite_rules_array', array(&$this, 'add_rewrite_rules'));

			add_action('template_redirect', array(&$this, 'redirect_author_page'), 9999);
			add_action('template_redirect', array(&$this, 'locate_user_profile'), 9999);
		}

		/**
		 * Update "flush" option for reset rules on wp_loaded hook.
		 */
		public function reset_rules()
		{
			update_option('wams_flush_rewrite_rules', 1);
		}

		/**
		 * Reset Rewrite rules if need it.
		 */
		public function maybe_flush_rewrite_rules()
		{
			if (get_option('wams_flush_rewrite_rules')) {
				flush_rewrite_rules(false);
				delete_option('wams_flush_rewrite_rules');
			}
		}

		/**
		 * Modify global query vars.
		 *
		 * @param array $public_query_vars
		 *
		 * @return array
		 */
		public function query_vars($public_query_vars)
		{
			$public_query_vars[] = 'wams_user';
			$public_query_vars[] = 'wams_tab';
			$public_query_vars[] = 'profiletab';
			$public_query_vars[] = 'subnav';

			$public_query_vars[] = 'wams_page';
			$public_query_vars[] = 'wams_action';
			$public_query_vars[] = 'wams_field';
			$public_query_vars[] = 'wams_form';
			$public_query_vars[] = 'wams_verify';

			return $public_query_vars;
		}

		/**
		 * Add WAMS rewrite rules.
		 *
		 * @param array $rules
		 *
		 * @return array
		 */
		public function add_rewrite_rules($rules)
		{
			$newrules = array();

			$newrules['um-download/([^/]+)/([^/]+)/([^/]+)/([^/]+)/?$'] = 'index.php?wams_action=download&wams_form=$matches[1]&wams_field=$matches[2]&wams_user=$matches[3]&wams_verify=$matches[4]';

			if (isset(WAMS()->config()->permalinks['user'])) {

				$user_page_id = WAMS()->config()->permalinks['user'];
				$user         = get_post($user_page_id);

				if (isset($user->post_name)) {
					$user_slug                              = $user->post_name;
					$newrules[$user_slug . '/([^/]+)/?$'] = 'index.php?page_id=' . $user_page_id . '&wams_user=$matches[1]';
				}

				if (WAMS()->external_integrations()->is_wpml_active()) {
					global $sitepress;

					$active_languages = $sitepress->get_active_languages();

					foreach ($active_languages as $language_code => $language) {
						$lang_post_id  = wpml_object_id_filter($user_page_id, 'post', false, $language_code);
						$lang_post_obj = get_post($lang_post_id);

						if (isset($lang_post_obj->post_name) && $lang_post_obj->post_name !== $user->post_name) {
							$user_slug                              = $lang_post_obj->post_name;
							$newrules[$user_slug . '/([^/]+)/?$'] = 'index.php?page_id=' . $lang_post_id . '&wams_user=$matches[1]&lang=' . $language_code;
						}
					}
				}
			}

			if (isset(WAMS()->config()->permalinks['account'])) {
				$account_page_id = WAMS()->config()->permalinks['account'];
				$account         = get_post($account_page_id);

				if (isset($account->post_name)) {
					$account_slug                             = $account->post_name;
					$newrules[$account_slug . '/([^/]+)?$'] = 'index.php?page_id=' . $account_page_id . '&wams_tab=$matches[1]';
				}

				if (WAMS()->external_integrations()->is_wpml_active()) {
					global $sitepress;

					$active_languages = $sitepress->get_active_languages();

					foreach ($active_languages as $language_code => $language) {
						$lang_post_id  = wpml_object_id_filter($account_page_id, 'post', false, $language_code);
						$lang_post_obj = get_post($lang_post_id);

						if (isset($lang_post_obj->post_name) && $lang_post_obj->post_name !== $account->post_name) {
							$account_slug                              = $lang_post_obj->post_name;
							$newrules[$account_slug . '/([^/]+)/?$'] = 'index.php?page_id=' . $lang_post_id . '&wams_user=$matches[1]&lang=' . $language_code;
						}
					}
				}
			}

			return $newrules + $rules;
		}

		/**
		 * Author page to user profile redirect.
		 */
		public function redirect_author_page()
		{
			if (is_author() && WAMS()->options()->get('author_redirect')) {
				$id = get_query_var('author');
				wams_fetch_user($id);
				wp_safe_redirect(wams_user_profile_url());
				exit;
			}
		}

		/**
		 * Getting the user_id based on the User Profile slug like when Base Permalink setting equals 'user_login'.
		 *
		 * @since 2.7.0
		 *
		 * @return bool|int|mixed
		 */
		private function get_user_id_by_user_login_slug()
		{
			$permalink_base = WAMS()->options()->get('permalink_base');
			if ('custom_meta' === $permalink_base) {
				$custom_meta = WAMS()->options()->get('permalink_base_custom_meta');
				if (empty($custom_meta)) {
					// Set default permalink base if custom meta is empty.
					$permalink_base = 'user_login';
				}
			}

			$user_id = username_exists(wams_queried_user());
			//Try
			if (!$user_id) {
				// Search by Profile Slug
				$args = array(
					'fields'     => 'ids',
					'meta_query' => array(
						array(
							'key'     => 'wams_user_profile_url_slug_' . $permalink_base,
							'value'   => strtolower(wams_queried_user()),
							'compare' => '=',
						),
					),
					'number'     => 1,
				);

				$ids = new \WP_User_Query($args);
				if ($ids->total_users > 0) {
					$user_id = current($ids->get_results());
				}
			}

			// Try nice name
			if (!$user_id) {
				$slug     = wams_queried_user();
				$slug     = str_replace('.', '-', $slug);
				$the_user = get_user_by('slug', $slug);
				if (isset($the_user->ID)) {
					$user_id = $the_user->ID;
				}

				if (!$user_id) {
					$user_id = WAMS()->user()->user_exists_by_email_as_username(wams_queried_user());
				}

				if (!$user_id) {
					$user_id = WAMS()->user()->user_exists_by_email_as_username($slug);
				}
			}

			return $user_id;
		}

		/**
		 * Locate/display a profile.
		 */
		public function locate_user_profile()
		{
			$permalink_base = WAMS()->options()->get('permalink_base');
			if ('custom_meta' === $permalink_base) {
				$custom_meta = WAMS()->options()->get('permalink_base_custom_meta');
				if (empty($custom_meta)) {
					// Set default permalink base if custom meta is empty.
					$permalink_base = 'user_login';
				}
			}

			if (wams_queried_user() && wams_is_core_page('user')) {
				if ('user_login' === $permalink_base) {
					$user_id = $this->get_user_id_by_user_login_slug();
				}

				if ('user_id' === $permalink_base) {
					$user_id = WAMS()->user()->user_exists_by_id(wams_queried_user());
				}

				if ('hash' === $permalink_base) {
					$user_id = WAMS()->user()->user_exists_by_hash(wams_queried_user());
				}

				if ('custom_meta' === $permalink_base) {
					$user_id = WAMS()->user()->user_exists_by_custom_meta(wams_queried_user());
					if (!$user_id) {
						// Try user_login by default.
						$user_id = $this->get_user_id_by_user_login_slug();
					}
				}

				if (in_array($permalink_base, array('name', 'name_dash', 'name_dot', 'name_plus'), true)) {
					$user_id = WAMS()->user()->user_exists_by_name(wams_queried_user());
				}

				/** USER EXISTS SET USER AND CONTINUE **/

				if (!empty($user_id)) {
					wams_set_requested_user($user_id);
					/**
					 * Fires after setting requested user.
					 *
					 * @param {int} $user_id Requested User ID.
					 *
					 * @since 1.3.x
					 * @hook wams_access_profile
					 *
					 * @example <caption>Some action on user access profile and requested user isset.</caption>
					 * add_action( 'wams_access_profile', 'my_access_profile', 10, 1 );
					 * function my_access_profile( $user_id ) {
					 *     // your code here
					 * }
					 */
					do_action('wams_access_profile', $user_id);
				} else {
					wp_safe_redirect(wams_get_core_page('user'));
					exit;
				}
			} elseif (wams_is_core_page('user')) {
				if (is_user_logged_in()) { // just redirect to their profile
					$query = WAMS()->permalinks()->get_query_array();

					$url = wams_user_profile_url(wams_user('ID'));

					if ($query) {
						foreach ($query as $key => $val) {
							$url = add_query_arg($key, $val, $url);
						}
					}
					wp_safe_redirect($url);
					exit;
				}

				/**
				 * Filters the redirect URL from user profile for not logged-in user.
				 *
				 * @param {string} $url Redirect URL. By default, it's a home page.
				 *
				 * @return {string} Redirect URL.
				 *
				 * @since 1.3.x
				 * @hook wams_locate_user_profile_not_loggedin__redirect
				 *
				 * @example <caption>Change redirect URL from user profile for not logged-in user to WordPress native login.</caption>
				 * function my_user_profile_not_loggedin__redirect( $url ) {
				 *     // your code here
				 *     $url = wp_login_url();
				 *     return $url;
				 * }
				 * add_filter( 'wams_locate_user_profile_not_loggedin__redirect', 'my_user_profile_not_loggedin__redirect' );
				 */
				$redirect_to = apply_filters('wams_locate_user_profile_not_loggedin__redirect', home_url());
				if (!empty($redirect_to)) {
					wams_safe_redirect($redirect_to);
				}
			}
		}
	}
}
