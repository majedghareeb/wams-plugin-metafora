<?php

namespace wams\core\rest;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\core\rest\API_v2')) {

	/**
	 * Class API_v2
	 * @package um\core\rest
	 */
	class API_v2 extends API
	{

		/**
		 *
		 */
		const VERSION = '2.0';

		/**
		 * REST_API constructor.
		 */
		public function __construct()
		{
			parent::__construct();

			add_filter('query_vars', array($this, 'query_vars'));
		}

		/**
		 * Registers query vars for API access
		 *
		 * @param $vars
		 *
		 * @return array
		 */
		public function query_vars($vars)
		{
			$vars[] = 'wams_key';
			$vars[] = 'wams_token';
			$vars[] = 'wams_format';
			$vars[] = 'wams_query';
			$vars[] = 'wams_type';
			$vars[] = 'wams_data';
			$vars[] = 'wams_fields';
			$vars[] = 'wams_value';
			$vars[] = 'wams_number';
			$vars[] = 'wams_id';
			$vars[] = 'wams_email';
			$vars[] = 'wams_orderby';
			$vars[] = 'wams_order';
			$vars[] = 'wams_include';
			$vars[] = 'wams_exclude';

			$this->vars = $vars;

			return $vars;
		}

		/**
		 * Validate the API request
		 */
		protected function validate_request()
		{
			global $wp_query;

			$this->override = false;

			// Make sure we have both user and api key
			if (!empty($wp_query->query_vars['wams-api'])) {

				if (empty($wp_query->query_vars['wams_token']) || empty($wp_query->query_vars['wams_key'])) {
					$this->missing_auth();
				}

				// Retrieve the user by public API key and ensure they exist
				if (!($user = $this->get_user($wp_query->query_vars['wams_key']))) {
					$this->invalid_key();
				} else {
					$token  = urldecode($wp_query->query_vars['wams_token']);
					$secret = get_user_meta($user, 'wams_user_secret_key', true);
					$public = urldecode($wp_query->query_vars['wams_key']);

					if (hash_equals(md5($secret . $public), $token)) {
						$this->is_valid_request = true;
					} else {
						$this->invalid_auth();
					}
				}
			}
		}

		/**
		 * Retrieve the user ID based on the public key provided
		 *
		 * @param string $key
		 *
		 * @return bool|mixed|null|string
		 */
		public function get_user($key = '')
		{
			global $wpdb, $wp_query;

			if (empty($key)) {
				$key = urldecode($wp_query->query_vars['wams_key']);
			}

			if (empty($key)) {
				return false;
			}

			$user = get_transient(md5('wams_api_user_' . $key));

			if (false === $user) {
				$user = $wpdb->get_var($wpdb->prepare(
					"SELECT user_id
					FROM $wpdb->usermeta
					WHERE meta_key = 'wams_user_public_key' AND
					      meta_value = %s
					LIMIT 1",
					$key
				));
				set_transient(md5('wams_api_user_' . $key), $user, DAY_IN_SECONDS);
			}

			if ($user != NULL) {
				$this->user_id = $user;
				return $user;
			}

			return false;
		}

		/**
		 * Process Get users API Request
		 *
		 * @param array $args
		 *
		 * @return array
		 */
		public function get_users($args)
		{
			$response = array();

			$number  = array_key_exists('wams_number', $args) && is_numeric($args['wams_number']) ? absint($args['wams_number']) : 10;
			$orderby = array_key_exists('wams_orderby', $args) ? sanitize_key($args['wams_orderby']) : 'user_registered';
			$order   = array_key_exists('wams_order', $args) ? sanitize_key($args['wams_order']) : 'desc';

			$loop_a = array(
				'number'  => $number,
				'orderby' => $orderby,
				'order'   => $order,
			);

			if (array_key_exists('wams_include', $args)) {
				$include           = explode(',', sanitize_text_field($args['wams_include']));
				$loop_a['include'] = $include;
			}

			if (array_key_exists('wams_exclude', $args)) {
				$exclude           = explode(',', sanitize_text_field($args['wams_exclude']));
				$loop_a['exclude'] = $exclude;
			}

			$loop = get_users($loop_a);

			foreach ($loop as $user) {
				unset($user->data->user_status, $user->data->user_activation_key, $user->data->user_pass);

				wams_fetch_user($user->ID);

				foreach ($user as $key => $val) {
					if ('data' !== $key) {
						continue;
					}

					$val->roles                = $user->roles;
					$val->first_name           = wams_user('first_name');
					$val->last_name            = wams_user('last_name');
					$val->account_status       = wams_user('account_status');
					$val->profile_pic_original = wams_get_user_avatar_url('', 'original');
					$val->profile_pic_normal   = wams_get_user_avatar_url('', 200);
					$val->profile_pic_small    = wams_get_user_avatar_url('', 40);
					$val->cover_photo          = $this->getsrc(wams_user('cover_photo', 1000));

					/** This filter is documented in includes/core/rest/class-api-v1.php */
					$response[$user->ID] = apply_filters('wams_rest_userdata', $val, $user->ID);
				}
			}

			return $response;
		}

		/**
		 * Update user API query
		 *
		 * @param $args
		 *
		 * @return array
		 */
		public function update_user($args)
		{
			$response = array();
			$error    = array();

			if (empty($args['wams_id'])) {
				$error['error'] = __('You must provide a user ID', 'wams');
				return $error;
			}

			if (empty($args['wams_data'])) {
				$error['error'] = __('You need to provide data to update', 'wams');
				return $error;
			}

			if (!array_key_exists('wams_value', $args)) {
				$error['error'] = __('You need to provide value to update', 'wams');
				return $error;
			}

			$id    = absint($args['wams_id']);
			$data  = sanitize_text_field($args['wams_data']);
			$value = sanitize_text_field($args['wams_value']);

			wams_fetch_user($id);

			switch ($data) {
				case 'status':
					WAMS()->user()->set_status($value);
					$response['success'] = __('User status has been changed.', 'wams');
					break;
				case 'role':
					$wp_user_object = new \WP_User($id);
					$old_roles      = $wp_user_object->roles;
					$wp_user_object->set_role($value);

					/** This action is documented in includes/core/class-user.php */
					do_action('wams_after_member_role_upgrade', array($value), $old_roles, $id);

					$response['success'] = __('User role has been changed.', 'wams');
					break;
				default:
					update_user_meta($id, $data, $value);
					$response['success'] = __('User meta has been changed.', 'wams');
					break;
			}

			return $response;
		}

		/**
		 * Process delete user via API.
		 *
		 * @param array $args
		 *
		 * @return array
		 */
		public function delete_user($args)
		{
			$response = array();
			$error    = array();

			if (empty($args['wams_id'])) {
				$error['error'] = __('You must provide a user ID', 'wams');
				return $error;
			}

			$id = absint($args['wams_id']);

			$user = get_userdata($id);
			if (!$user) {
				$error['error'] = __('Invalid user specified', 'wams');
				return $error;
			}

			wams_fetch_user($id);
			WAMS()->user()->delete();

			$response['success'] = __('User has been successfully deleted.', 'wams');

			return $response;
		}

		/**
		 * Process Get user API Request
		 *
		 * @param $args
		 *
		 * @return array
		 */
		public function get_auser($args)
		{
			$response = array();
			$error    = array();

			if (empty($args['wams_id'])) {
				$error['error'] = __('You must provide a user ID', 'wams');
				return $error;
			}

			$id   = absint($args['wams_id']);
			$user = get_userdata($id);
			if (!$user) {
				$error['error'] = __('Invalid user specified', 'wams');
				return $error;
			}

			unset($user->data->user_status, $user->data->user_activation_key, $user->data->user_pass);

			wams_fetch_user($user->ID);

			if (array_key_exists('wams_fields', $args)) {
				$fields               = explode(',', sanitize_text_field($args['wams_fields']));
				$response['ID']       = $user->ID;
				$response['username'] = $user->user_login;
				foreach ($fields as $field) {

					switch ($field) {
						default:
							$profile_data       = wams_profile($field);
							$response[$field] = $profile_data ? $profile_data : '';

							/** This filter is documented in includes/core/rest/class-api-v1.php */
							$response = apply_filters('wams_rest_get_auser', $response, $field, $user->ID);
							break;
						case 'cover_photo':
							$response['cover_photo'] = $this->getsrc(wams_user('cover_photo', 1000));
							break;
						case 'profile_pic':
							$response['profile_pic_original'] = wams_get_user_avatar_url('', 'original');
							$response['profile_pic_normal']   = wams_get_user_avatar_url('', 200);
							$response['profile_pic_small']    = wams_get_user_avatar_url('', 40);
							break;
						case 'status':
							$response['status'] = wams_user('account_status');
							break;
						case 'role':
							//get priority role here
							$response['role'] = wams_user('role');
							break;
						case 'email':
						case 'user_email':
							$response['email'] = wams_user('user_email');
							break;
					}
				}
			} else {
				foreach ($user as $key => $val) {
					if ('data' !== $key) {
						continue;
					}

					$val->roles                = $user->roles;
					$val->first_name           = wams_user('first_name');
					$val->last_name            = wams_user('last_name');
					$val->account_status       = wams_user('account_status');
					$val->profile_pic_original = wams_get_user_avatar_url('', 'original');
					$val->profile_pic_normal   = wams_get_user_avatar_url('', 200);
					$val->profile_pic_small    = wams_get_user_avatar_url('', 40);
					$val->cover_photo          = $this->getsrc(wams_user('cover_photo', 1000));

					/** This filter is documented in includes/core/rest/class-api-v1.php */
					$response = apply_filters('wams_rest_userdata', $val, $user->ID);
				}
			}

			return $response;
		}

		/**
		 * Get source
		 *
		 * @param $image
		 *
		 * @return string
		 */
		public function getsrc($image)
		{
			if (preg_match('/<img.+?src(?: )*=(?: )*[\'"](.*?)[\'"]/si', $image, $arr_result)) {
				return $arr_result[1];
			}
			return '';
		}

		/**
		 * Retrieve the output format
		 */
		public function get_output_format()
		{
			global $wp_query;

			$format = isset($wp_query->query_vars['wams_format']) ? $wp_query->query_vars['wams_format'] : 'json';

			/** This filter is documented in includes/core/rest/class-api-v1.php */
			return apply_filters('wams_api_output_format', $format);
		}
	}
}
