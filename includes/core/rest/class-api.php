<?php

namespace wams\core\rest;

use WP;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\core\rest\API')) {

	/**
	 * Class API
	 * @package um\core\rest
	 */
	class API
	{
		/**
		 * Latest API Version
		 */
		const VERSION = 1;

		/**
		 * Pretty Print?
		 *
		 * @var bool
		 * @access private
		 * @since  1.1
		 */
		private $pretty_print = false;

		/**
		 * Log API requests?
		 *
		 * @var bool
		 * @access public
		 * @since  1.1
		 */
		public $log_requests = true;

		/**
		 * Is this a valid request?
		 *
		 * @var bool
		 * @access private
		 * @since  1.1
		 */
		private $is_valid_request = false;

		/**
		 * User ID Performing the API Request
		 *
		 * @var int
		 * @access public
		 * @since  1.1
		 */
		public $user_id = 0;

		/**
		 * Instance of Wams Stats class
		 *
		 * @var object
		 * @access private
		 * @since  1.1
		 */
		private $stats;

		/**
		 * Response data to return
		 *
		 * @var array
		 * @access private
		 * @since  1.1
		 */
		private $data = array();

		/**
		 * Whether or not to override api key validation.
		 *
		 * @var bool
		 * @access public
		 * @since  1.1
		 */
		public $override = true;

		/**
		 * Version of the API queried
		 *
		 * @var string
		 * @access public
		 * @since  1.1
		 */
		private $queried_version;

		/**
		 * All versions of the API
		 *
		 * @var array
		 * @access protected
		 * @since  1.1
		 */
		protected $versions = array();

		/**
		 * Queried endpoint
		 *
		 * @var string
		 * @access private
		 * @since  1.1
		 */
		private $endpoint;

		/**
		 * Endpoints routes
		 *
		 * @var object
		 * @access private
		 * @since  1.1
		 */
		private $routes;

		/**
		 * Setup the Wams API
		 *
		 * @since  1.1
		 * @access public
		 */
		public function __construct()
		{

			$this->versions = array(
				'v1' => 'WAMS_API_V1',
			);

			add_action('init', array($this, 'add_endpoint'));
			add_action('template_redirect', array($this, 'process_query'), -1);
			add_filter('query_vars', array($this, 'query_vars'));

			// Determine if JSON_PRETTY_PRINT is available
			$this->pretty_print = defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : null;

			// Allow API request logging to be turned off
			$this->log_requests = apply_filters('wams_api_log_requests', $this->log_requests);
		}

		/**
		 * Registers a new rewrite endpoint for accessing the API
		 *
		 * @access public
		 *
		 * @since  1.1
		 */
		public function add_endpoint()
		{
			add_rewrite_endpoint('wams-api', EP_ALL);
		}

		/**
		 * Registers query vars for API access
		 *
		 * @access public
		 * @since  1.1
		 *
		 * @param array $vars Query vars
		 *
		 * @return string[] $vars New query vars
		 */
		public function query_vars($vars)
		{
			$vars[] = 'token';
			$vars[] = 'key';
			$vars[] = 'query';
			$vars[] = 'vendor';
			$vars[] = 'form';
			$vars[] = 'number';
			$vars[] = 'date';
			$vars[] = 'startdate';
			$vars[] = 'enddate';
			$vars[] = 'id';
			$vars[] = 'email';
			return $vars;
		}

		/**
		 * Retrieve the API versions
		 *
		 * @access public
		 * @since  1.1
		 * @return array
		 */
		public function get_versions()
		{
			return $this->versions;
		}

		/**
		 * Retrieve the API version that was queried
		 *
		 * @access public
		 * @since  1.1
		 * @return string
		 */
		public function get_queried_version()
		{
			return $this->queried_version;
		}

		/**
		 * Retrieves the default version of the API to use
		 *
		 * @access public
		 * @since  1.1
		 * @return string
		 */
		public function get_default_version()
		{
			$version = get_option('wams_default_api_version');

			if (defined('WAMS_API_VERSION')) {
				$version = WAMS_VERSION;
			} elseif (!$version) {
				$version = 'v1';
			}

			return $version;
		}

		/**
		 * Sets the version of the API that was queried.
		 *
		 * Falls back to the default version if no version is specified
		 *
		 * @access private
		 * @since  1.1
		 */
		private function set_queried_version()
		{

			global $wp_query;

			$version = $wp_query->query_vars['wams-api'];

			if (strpos($version, '/')) {

				$version = explode('/', $version);
				$version = strtolower($version[0]);

				$wp_query->query_vars['wams-api'] = str_replace($version . '/', '', $wp_query->query_vars['wams-api']);

				if (array_key_exists($version, $this->versions)) {

					$this->queried_version = $version;
				} else {

					$this->is_valid_request = false;
					$this->invalid_version();
				}
			} else {

				$this->queried_version = $this->get_default_version();
			}
		}

		/**
		 * Validate the API request
		 *
		 * Checks for the user's public key and token against the secret key.
		 *
		 * @access private
		 * @global object $wp_query WordPress Query
		 * @uses   Wams_API::get_user()
		 * @uses   Wams_API::invalid_key()
		 * @uses   Wams_API::invalid_auth()
		 * @since  1.1
		 * @return bool
		 */
		private function validate_request()
		{
			global $wp_query;

			$this->override = false;

			// Make sure we have both user and api key
			if (!empty($wp_query->query_vars['wams-api']) && ($wp_query->query_vars['wams-api'] !== 'forms')) {

				if (empty($wp_query->query_vars['key'])) {
					$this->missing_auth();

					return false;
				}

				// Retrieve the user by public API key and ensure they exist
				if (!preg_match('/^[a-f0-9]{32}$/i', $wp_query->query_vars['key'])) {

					$this->invalid_key();

					return false;
				} else {
					$key = urldecode($wp_query->query_vars['key']);

					// Verify that if user has secret key or not
					if (!$key) {
						$this->invalid_auth();
					}

					if (hash_equals(md5('wams_api_key'), $key)) {
						$this->is_valid_request = true;
					} else {
						$this->invalid_auth();

						return false;
					}
				}
			} elseif (!empty($wp_query->query_vars['wams-api']) && $wp_query->query_vars['wams-api'] === 'forms') {
				$this->is_valid_request = true;
				$wp_query->set('key', 'public');
			}
		}

		public function get_secret_key()
		{
		}

		/**
		 * Displays a missing authentication error if all the parameters are not met.
		 * provided
		 *
		 * @access private
		 * @uses   Wams_API::output()
		 * @since  1.1
		 */
		private function missing_auth()
		{
			$error          = array();
			$error['error'] = __('You must specify both a token and API key.', 'wams');

			$this->data = $error;
			$this->output(401);
		}

		/**
		 * Displays an authentication failed error if the user failed to provide valid
		 * credentials
		 *
		 * @access private
		 * @since  1.1
		 * @uses   Wams_API::output()
		 * @return void
		 */
		private function invalid_auth()
		{
			$error          = array();
			$error['error'] = __('Your request could not be authenticated.', 'wams');

			$this->data = $error;
			$this->output(403);
		}

		/**
		 * Displays an invalid API key error if the API key provided couldn't be
		 * validated
		 *
		 * @access private
		 * @since  1.1
		 * @uses   Wams_API::output()
		 * @return void
		 */
		private function invalid_key()
		{
			$error          = array();
			$error['error'] = __('Invalid API key.', 'wams');

			$this->data = $error;
			$this->output(403);
		}

		/**
		 * Displays an invalid version error if the version number passed isn't valid
		 *
		 * @access private
		 * @since  1.1
		 * @uses   Wams_API::output()
		 * @return void
		 */
		private function invalid_version()
		{
			$error          = array();
			$error['error'] = __('Invalid API version.', 'wams');

			$this->data = $error;
			$this->output(404);
		}

		/**
		 * Listens for the API and then processes the API requests
		 *
		 * @access public
		 * @global $wp_query
		 * @since  1.1
		 * @return void
		 */
		public function process_query()
		{

			global $wp_query;

			// Start logging how long the request takes for logging
			$before = microtime(true);

			// Check for wams-api var. Get out if not present
			if (empty($wp_query->query_vars['wams-api'])) {
				return;
			}

			// Determine which version was queried
			$this->set_queried_version();

			// Determine the kind of query
			$this->set_query_mode();

			// Check for a valid user and set errors if necessary
			$this->validate_request();

			// Only proceed if no errors have been noted
			if (!$this->is_valid_request) {
				return;
			}

			if (!defined('WAMS_DOING_API')) {
				define('WAMS_DOING_API', true);
			}

			$data         = array();
			// $this->routes->validate_request();

			switch ($this->endpoint):

				case 'vendors':
					$data = $this->get_vendors();

					break;



			endswitch;

			// Allow extensions to setup their own return data
			$this->data = apply_filters('wams_api_output_data', $data, $this->endpoint, $this);

			$after                       = microtime(true);
			$request_time                = ($after - $before);
			$this->data['request_speed'] = $request_time;

			// Log this API request, if enabled. We log it here because we have access to errors.
			$this->log_request($this->data);

			// Send out data to the output function
			$this->output();
		}

		/**
		 * Returns the API endpoint requested
		 *
		 * @access public
		 * @since  1.1
		 * @return string $query Query mode
		 */
		public function get_query_mode()
		{

			return $this->endpoint;
		}

		/**
		 * Determines the kind of query requested and also ensure it is a valid query
		 *
		 * @access public
		 * @since  1.1
		 * @global $wp_query
		 */
		public function set_query_mode()
		{

			global $wp_query;

			// Whitelist our query options
			$accepted = apply_filters(
				'wams_api_valid_query_modes',
				array(
					'vendors',
					'urls',
				)
			);

			$query = isset($wp_query->query_vars['wams-api']) ? $wp_query->query_vars['wams-api'] : null;
			$query = str_replace($this->queried_version . '/', '', $query);

			$error = array();

			// Make sure our query is valid
			if (!in_array($query, $accepted)) {
				$error['error'] = __('Invalid query.', 'wams');

				$this->data = $error;
				// 400 is Bad Request
				$this->output(400);
			}

			$this->endpoint = $query;
		}

		/**
		 * Process Get Vendorss API Request
		 *
		 * @since 1.1
		 *
		 * @global WPDB $wpdb Used to query the database using the WordPress.
		 *
		 * @param array $args Arguments provided by API Request.
		 *
		 * @return array
		 */
		public function get_vendors($args = array())
		{
			$wams_seach_vendor_field_settings = get_option('wams_seach_vendor_field_settings');
			$vendor_form_id =  $wams_seach_vendor_field_settings['vendor_form'] ?? 0;
			$vendor_name_field_id = $wams_seach_vendor_field_settings['vendor_name_field_id'] ?? 0;
			$is_subsite = (get_current_blog_id() != WAMS_MAIN_BLOG_ID) ? true : false;
			if ($vendor_form_id) {
				$page = 0;
				$batch_size = 100;
				$search_criteria = array(
					'status'        => 'active',
				);
				$sorting = array('key' => $vendor_name_field_id, 'direction' => 'ASC');
				$paging          = array('offset' => $page, 'page_size' => $batch_size);

				if ($is_subsite) switch_to_blog(WAMS_MAIN_BLOG_ID);
				$total_count = 0;
				$entries = \GFAPI::get_entries($vendor_form_id, $search_criteria, $sorting, $paging, $total_count);
				if ($is_subsite) restore_current_blog();

				if ($total_count > 0) {
					return $entries;
				}
			}
			return ['No Vendors'];
		}


		/**
		 * Retrieve the output format.
		 *
		 * Determines whether results should be displayed in XML or JSON.
		 *
		 * @since  1.1
		 * @access public
		 *
		 * @return mixed
		 */
		public function get_output_format()
		{
			global $wp_query;

			$format = isset($wp_query->query_vars['format']) ? $wp_query->query_vars['format'] : 'json';

			return apply_filters('wams_api_output_format', $format);
		}


		/**
		 * Log each API request, if enabled.
		 *
		 * @access private
		 * @since  1.1
		 *
		 * @global WP_Query     $wp_query
		 *
		 * @param array $data
		 *
		 * @return void
		 */
		private function log_request($data = array())
		{
			if (!$this->log_requests) {
				return;
			}

			/**
			 * @var WP_Query $wp_query
			 */
			global $wp_query;

			$query = array(
				'wams-api'    => $wp_query->query_vars['wams-api'],
				'key'         => isset($wp_query->query_vars['key']) ? $wp_query->query_vars['key'] : null,
				'token'       => isset($wp_query->query_vars['token']) ? $wp_query->query_vars['token'] : null,
				'query'       => isset($wp_query->query_vars['query']) ? $wp_query->query_vars['query'] : null,
				'type'        => isset($wp_query->query_vars['type']) ? $wp_query->query_vars['type'] : null,
				'date'        => isset($wp_query->query_vars['date']) ? $wp_query->query_vars['date'] : null,
				'startdate'   => isset($wp_query->query_vars['startdate']) ? $wp_query->query_vars['startdate'] : null,
				'enddate'     => isset($wp_query->query_vars['enddate']) ? $wp_query->query_vars['enddate'] : null,
				'id'          => isset($wp_query->query_vars['id']) ? $wp_query->query_vars['id'] : null,
			);

			$log_data = array(
				'log_type'     => 'api_request',
				'post_excerpt' => http_build_query($query),
				'post_content' => !empty($data['error']) ? $data['error'] : '',
			);

			$log_meta = array(
				'api_query'  => http_build_query($query),
				'request_ip' => '127.0.0.1',
				'user'       => $this->user_id,
				'key'        => isset($wp_query->query_vars['key']) ? $wp_query->query_vars['key'] : null,
				'token'      => isset($wp_query->query_vars['token']) ? $wp_query->query_vars['token'] : null,
				'time'       => $data['request_speed'],
				'version'    => $this->get_queried_version(),
			);
		}


		/**
		 * Retrieve the output data.
		 *
		 * @access public
		 * @since  1.1
		 * @return array
		 */
		public function get_output()
		{
			return $this->data;
		}

		/**
		 * Output Query in either JSON/XML.
		 * The query data is outputted as JSON by default.
		 *
		 * @since 1.1
		 * @global WP_Query $wp_query
		 *
		 * @param int $status_code
		 */
		public function output($status_code = 200)
		{

			$format = $this->get_output_format();

			status_header($status_code);

			/**
			 * Fires before outputting the API.
			 *
			 * @since 1.1
			 *
			 * @param array    $data   Response data to return.
			 * @param Wams_API $this   The Wams_API object.
			 * @param string   $format Output format, XML or JSON. Default is JSON.
			 */
			do_action('wams_api_output_before', $this->data, $this, $format);

			switch ($format):
				case 'json':
					header('Content-Type: application/json');
					if (!empty($this->pretty_print)) {
						echo json_encode($this->data, $this->pretty_print);
					} else {
						echo json_encode($this->data);
					}

					break;

				default:
					/**
					 * Fires by the API while outputting other formats.
					 *
					 * @since 1.1
					 *
					 * @param array    $data Response data to return.
					 * @param Wams_API $this The Wams_API object.
					 */
					// do_action("wams_api_output_{$format}", $this->data, $this);

					break;

			endswitch;

			/**
			 * Fires after outputting the API.
			 *
			 * @since 1.1
			 *
			 * @param array    $data   Response data to return.
			 * @param Wams_API $this   The Wams_API object.
			 * @param string   $format Output format, XML or JSON. Default is JSON.
			 */
			// do_action('wams_api_output_after', $this->data, $this, $format);
			die(-1);
		}
	}
}
