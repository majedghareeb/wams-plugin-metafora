<?php

namespace wams\core;

// Exit if executed directly
if (!defined('ABSPATH')) exit;

if (!class_exists('wams\core\AJAX_Common')) {


	/**
	 * Class AJAX_Common
	 * @package wams\core
	 */
	class AJAX_Common
	{


		/**
		 * AJAX_Common constructor.
		 */
		function __construct()
		{
			$ajax_actions = array();

			foreach ($ajax_actions as $action => $nopriv) {

				add_action('wp_ajax_wams_' . $action, array($this, $action));

				if ($nopriv) {
					add_action('wp_ajax_nopriv_wams_' . $action, array($this, $action));
				}
			}
			add_action('wp_ajax_wams_frontend', array(&$this, 'ajax_frontend'));
			// add_action('wp_ajax_nopriv_wams_frontend', array(&$this, 'ajax_frontend'));
		}



		/**
		 *
		 */
		public function ajax_frontend()
		{
			if (!wp_verify_nonce($_POST['nonce'], 'wams-frontend-nonce')) {
				wp_die(esc_attr__('Security Check', 'wams'));
			}

			if (empty($_POST['param'])) {
				wp_send_json_error(__('Invalid Action.', 'wams'));
			}


			$user    = wp_get_current_user();
			$user_roles = $user->roles;
			switch ($_POST['param']) {

				case 'parse-url':
					$formData = wp_parse_args($_POST['formData']);
					$url = $formData['url'];
					$action = $formData['action'];
					switch ($action) {
						case 'get-links':
							$result = WAMS()->web_page_parser()->get_backlinks($url);
							break;
						case 'get-meta-tags':
							$result = WAMS()->web_page_parser()->getPageDetails($url, 'full');
							break;
						case 'get-page-info':
							$result = WAMS()->web_page_parser()->getTitle($url);

							break;
					}
					wp_send_json_success([
						'status' => 'success',
						'message' => __('Settings Saved', 'wams'),
						'result' => $result,
					]);
					break;
				case 'refresh_tasks':
					$user_inbox = delete_transient('wams_inbox_' . $user->id);
					if ($user_inbox)
						wp_send_json_success([
							'status' => 'success',
							'message' => __('User Tasks Refreshed', 'wams'),
						]);
					break;
				case 'refresh_requests':
					$user_requests = delete_transient('wams_user_requests_' . $user->id);
					if ($user_requests)
						wp_send_json_success([
							'status' => 'success',
							'message' => __('User Request Refreshed', 'wams'),
						]);
					break;
				case 'refresh_team_tasks':

					$active_roles_cache = delete_transient('wams_workflow_active_roles');
					if (!empty($user_roles)) {
						foreach ($user_roles as $user_role) {
							$role_inbox_cache = delete_transient('wams_workflow_role_' . $user_role);
						}
					}
					if ($active_roles_cache)
						wp_send_json_success([
							'status' => 'success',
							'message' => __('User Team Tasks Refreshed', 'wams'),
						]);
					break;
			}


			$arr_options = [
				'status' => 'ok',
				'message' => 'AJAX Call is OK'
			];
		}

		function get_tweet()
		{
			$api_key = 'hhSgWy2r7792IAmrGQnvnqHMt';
			$api_key_secret = 'WDT0SCzTZLZGGl9h3TuEDIFD0BUFgL3krwBS72zBrvHTmXy816';
			$token = 'AAAAAAAAAAAAAAAAAAAAALMWoAEAAAAAd1HlTpJCgbjy1XS%2FNwXA6h071js%3DVERdR4t5j2No7TIqK5nKgJLQFQC7zK3490K9uJbo3bQgEX4bnS';
			$access_token = '1395694025865302017-mESSC4faIlAi5tkfMzTOJ1WA4rNglW';
			$access_token_secret = 'dPkGcWwXg8UULUpgjrNvAmXCSusYnki4gfVGBOayPrtV6';
		}
	}
}
