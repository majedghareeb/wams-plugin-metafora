<?php

namespace wams\admin\modules;

use wams\admin\core\Admin_Settings_API;
use wams\core\google\GA_Api_Controller;
use wams\core\google\GA_Config;
use wams\core\google\GA_Tools;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\modules\Google_Analytics')) {

	/**
	 * Class Debug
	 * @package wams\admin\modules
	 */
	class Google_Analytics
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
		 * @var object
		 */
		public $ga_api_controller;
		/**
		 * @var object
		 */
		public $ga_config;
		/**
		 * @var string
		 */
		public $authUrl = '';

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
						'page_title' => 'Google_Analytics Settings',
						'menu_title' => 'GA4 Settings',
						'capability' => 'edit_wams_settings',
						'menu_slug' => 'wams_ga',
						'callback' => [$this, 'show_ga_page2']
					]
				]
			];
		}

		function show_ga_page()
		{
			echo '<h1>Google_Analytics Page</h1>';
			$options = (array) json_decode(get_option('wams_ga_options'));
			// $this->ga_api_controller = new GA_Api_Controller;

			// // $this->ga_api_controller->refresh_profiles_ga4();
			// // $accounts = $this->ga_api_controller->service_ga4_admin->accountSummaries->listAccountSummaries(array('pageSize' => 200))->getAccountSummaries();
			// echo '<pre>' . print_r($this->ga_api_controller->service_ga4_admin->accountSummaries->listAccountSummaries(), true) . '</pre>';
			// delete_option('wams_ga_options');
			echo '<pre>' . print_r($options, true) . '</pre>';
			// $this->ga_api_controller = new GA_Api_Controller;
		}
		public function get_link()
		{
			$AuthUrl = '';
			$this->ga_api_controller = new GA_Api_Controller;
			$AuthUrl = $this->ga_api_controller->client->createAuthUrl();
			$link = '<a href="' . $AuthUrl . '">Login</a>';
			return $link;
		}
		public function ga_debug()
		{

			$errors_count = GA_Tools::get_cache('errors_count');
			$error = GA_Tools::get_cache('gapi_errors');
			$error_code = isset($error[0]) ? $error[0] : 'None';
			$error_reason = (isset($error[1]) && !empty($error[1])) ? print_r($error[1], true) : 'None';
			$error_details = isset($error[2]) ? print_r($error[2], true) : 'None';

			echo '<pre>'  . esc_html($error_code) . '</pre>';
			echo '<pre>'  . esc_html($error_reason) . '</pre>';
			echo '<pre>'  . esc_html($error_details) . '</pre>';
		}
		public function show_ga_page2()
		{
			$this->ga_config = new GA_Config;
			$this->ga_api_controller = new GA_Api_Controller;
			$options = (array) json_decode(get_option('wams_ga_options'));

			/** when authentication form submitted and we get access code from Google */
			if (isset($_REQUEST['wams_access_code']) && !$this->ga_config->options['token']) {
				if (1 == !stripos('x' . $_REQUEST['wams_access_code'], 'UA-', 1)) {
					try {
						$wams_access_code = sanitize_text_field($_REQUEST['wams_access_code']);
						update_option('wams_ga_redeemed_code', $wams_access_code);
						GA_Tools::delete_cache('gapi_errors');
						GA_Tools::delete_cache('last_error');

						$token = $this->ga_api_controller->authenticate($wams_access_code);

						$array_token = (array)$token;

						$this->ga_api_controller->client->setAccessToken($array_token);

						$this->ga_config->options['token'] = $this->ga_api_controller->client->getAccessToken();

						$this->ga_config->set_plugin_options();
						$message = "<div class='updated' id='wams-autodismiss'><p>" . __("Plugin authorization succeeded.", 'wams') . "</p></div>";
						if ($this->ga_config->options['token'] && $this->ga_api_controller->client->getAccessToken()) {



							$webstreams = $this->ga_api_controller->refresh_profiles_ga4();
							if (is_array($webstreams) && !empty($webstreams)) {
								$this->ga_config->options['ga4_profiles_list'] = $webstreams;
								if (!$this->ga_config->options['default_profile']) {
									$property = GA_Tools::guess_default_domain($webstreams, 2);
									$this->ga_config->options['default_profile'] = $property;
								}
								$this->ga_config->set_plugin_options();
							}
							// if (isset($_REQUEST['options']['wams_hidden'])) {
							// 	$new_options = $_REQUEST['options'];
							// 	$options['reporting_type'] = 0;
							// 	$options['user_api'] = 0;
							// 	$options = array_merge($options, $new_options);
							// 	$this->ga_config->options = $options;
							// 	$this->ga_config->set_plugin_options();
							// }
						}
					} catch (\Google_Service_Exception $e) {
						$timeout = $this->ga_api_controller->get_timeouts('midnight');
						GA_Tools::set_error($e, $timeout);
						$this->ga_api_controller->reset_token();
					}
				} else {
					if (1 == stripos('x' . $_REQUEST['wams_access_code'], 'UA-', 1)) {
						$message = "<div class='error' id='wams-autodismiss'><p>" . __("The access code is <strong>not</strong> your <strong>Tracking ID</strong> (UA-XXXXX-X) <strong>nor</strong> your <strong>email address</strong>!", 'wams') . ".</p></div>";
					} else {
						$message = "<div class='error' id='wams-autodismiss'><p>" . __("You can only use the access code <strong>once</strong>, please generate a <strong>new access</strong> code following the instructions!", 'wams') . ".</p></div>";
					}
				}
			}
			if (isset($_REQUEST['Save'])) {
				if (isset($_REQUEST['wams_security']) && wp_verify_nonce($_REQUEST['wams_security'], 'wams_form')) {
					$new_options['default_profile'] = $_REQUEST['default_profile'];
					$new_options['reporting_type'] = $_REQUEST['reporting_type'];
					$options = array_merge($options, $new_options);
					$this->ga_config->options = $options;
					$this->ga_config->set_plugin_options();

					$message = "<div class='updated' id='wams-autodismiss'><p>" . __("Settings has been saved.", 'wams') . "</p></div>";
				}
			}
			if (isset($_REQUEST['Refresh'])) {
				if (isset($_REQUEST['wams_security']) && wp_verify_nonce($_REQUEST['wams_security'], 'wams_form')) {
					// $profiles = $this->ga_api_controller->refresh_profiles_ua();
					// // print_r($profiles);
					// if (is_array($profiles) && !empty($profiles)) {
					// 	$this->ga_config->options['ga_profiles_list'] = $profiles;
					// 	if (!$this->ga_config->options['default_profile']) {
					// 		$profile = GA_Tools::guess_default_domain($profiles);
					// 		$this->ga_config->options['default_profile'] = $profile;
					// 	}
					// 	$this->ga_config->set_plugin_options();
					// }

					$webstreams = $this->ga_api_controller->refresh_profiles_ga4();
					if (is_array($webstreams) && !empty($webstreams)) {
						$this->ga_config->options['ga4_profiles_list'] = $webstreams;
						if (!$this->ga_config->options['default_profile']) {
							$property = GA_Tools::guess_default_domain($webstreams, 2);
							$this->ga_config->options['default_profile'] = $property;
						}
						$this->ga_config->set_plugin_options();
						$message = "<div class='updated' id='wams-autodismiss'><p>" . __("Account has been refreshed.", 'wams') . "</p></div>";
					}
					// print_r($this->ga_config->options);
					// if (isset($_REQUEST['options']['wams_hidden'])) {
					// 	$new_options = $_REQUEST['options'];
					// 	$options['reporting_type'] = 0;
					// 	$options['user_api'] = 0;
					// 	$options = array_merge($options, $new_options);
					// 	$this->ga_config->options = $options;
					// 	$this->ga_config->set_plugin_options();
					// }
				}
			}
			if (isset($_REQUEST['Clear'])) {
				if (isset($_REQUEST['wams_security']) && wp_verify_nonce($_REQUEST['wams_security'], 'wams_form')) {
					GA_Tools::clear_cache();
					$message = "<div class='updated' id='wams-autodismiss'><p>" . __("Cleared Cache.", 'wams') . "</p></div>";
				} else {
					$message = "<div class='error' id='wams-autodismiss'><p>" . __("You do not have sufficient permissions to access this page.", 'wams') . "</p></div>";
				}
			}
			if (isset($_REQUEST['Reset'])) {
				print_r($this->ga_api_controller->client->getAccessToken());
				$this->ga_api_controller->reset_token(true);
				GA_Tools::clear_cache();
				// if (isset($_REQUEST['wams_security']) && wp_verify_nonce($_REQUEST['wams_security'], 'wams_form')) {
				// 	$message = "<div class='updated' id='wams-autodismiss'><p>" . __("Token Reseted and Revoked.", 'wams') . "</p></div>";
				// 	// $options = $this->ga_config->options;

				// } else {
				// 	$message = "<div class='error' id='wams-autodismiss'><p>" . __("You do not have sufficient permissions to access this page.", 'wams') . "</p></div>";
				// }
			}
			if (!$this->ga_config->options['token']) {
				echo '<div class="wrap">';
				echo $this->authenicationForm();
				echo '</div>';
				//print_r(json_decode(get_option('wams_ga_redeemed_code')));
			} else {
				$ga_profiles_list = $this->ga_config->options['ga_profiles_list'];
				$ga4_profiles_list = $this->ga_config->options['ga4_profiles_list'];
				$options = $this->ga_config->options;
				$authUrl = $this->ga_api_controller->client->createAuthUrl();
				$token = $this->ga_config->options['token'];
				if (isset($message)) echo wp_kses($message, array('div' => array('class' => array(), 'id' => array()), 'p' => array(), 'a' => array('href' => array())));
				$result = $this->ga_api_controller->get('properties/337038707/dataStreams/4141307294', 'bottomstats', '2024-01-01', '2024-01-01', '/');
				include_once WAMS()->admin()->templates_path . 'google-analytics.php';
			}
		}

		public function authenicationForm()
		{

			echo '<div class="mb-3 row">';
			echo $message = sprintf('<div class="error"><p>%s</p></div>', __('Google Analytics not yet authenticated, please authenticate to continue'));
			echo '<div class="col-sm-10">';
			echo '<a href="' . $this->ga_api_controller->client->createAuthUrl() . '" class="btn btn-primary">Authorize</a>';
			echo '</div>';
			echo '</div>';
			// printf('<div id="ga-warning" class="updated"><p>%1$s <a href="https://www.rakami.net">%2$s</a></p></div>', __('Loading the required libraries. If this results in a blank screen or a fatal error, try this solution:', 'wams'), __('Library conflicts between WordPress plugins', 'wams'));
		}


		public function profilesList()
		{
			return $this->ga_config->options['ga_profiles_list'];
		}
		public function get_ga3_profilesList()
		{
			$ga_profiles_list = $this->ga_config->options['ga_profiles_list'];
			$list = [];
			if (!empty($ga_profiles_list)) :
				foreach ($ga_profiles_list as $items) :
					if ($items[3]) :
						$list[$items[1]] =  esc_html(GA_Tools::strip_protocol($items[3])) . ' - ' . $items[2]  . ' - ' . $items[0];
					endif;
				endforeach;
			endif;
			return $list;
		}
		public function get_ga4_profilesList()
		{
			$list = [];
			$ga4_profiles_list = $this->ga_config->options['ga4_profiles_list'];
			if (!empty($ga4_profiles_list)) :
				foreach ($ga4_profiles_list as $items) :
					if ($items[3]) :
						$list[$items[1]] =  $items[2]  . ' - ' . esc_html(GA_Tools::strip_protocol($items[3])) . ' - ' .  $items[0];
					endif;
				endforeach;
			endif;
			return $list;
		}
	}
}
